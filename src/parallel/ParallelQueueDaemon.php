<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-19
 * Time: 11:12
 */

namespace sinri\ark\queue\parallel;


use sinri\ark\queue\AbstractQueueDaemon;

class ParallelQueueDaemon extends AbstractQueueDaemon
{
    /**
     * @var ParallelQueueDaemonDelegate
     */
    protected $delegate;
    /**
     * @var int
     */
    protected $childrenCount;

    /**
     * SerialQueueDaemon constructor.
     * @param ParallelQueueDaemonDelegate $delegate
     */
    public function __construct($delegate)
    {
        $this->delegate = $delegate;
    }

    public function loop()
    {
        while (true) {
            if ($this->delegate->shouldTerminate()) {
                break;
            }
            for ($i = 0; $i < $this->childrenCount; $i++) {
                // pcntl_wait() returns the process ID of the child which exited,
                // -1 on error
                // or zero if WNOHANG was provided as an option (on wait3-available systems) and no child was available.
                $exitedChildProcessID = pcntl_wait($status, WNOHANG | WUNTRACED);
                if ($exitedChildProcessID > 0) {
                    $this->childrenCount--;
                    $this->delegate->whenChildProcessConfirmedDead($exitedChildProcessID);
                } elseif ($exitedChildProcessID === -1) {
                    $pcntl_error_number = pcntl_get_last_error();
                    $pcntl_error_string = pcntl_strerror($pcntl_error_number);
                    $error_message = 'Loop could not wait a child process to stop. Error No:' . $pcntl_error_number . " Message:" . $pcntl_error_string;
                    $this->delegate->whenLoopReportError($error_message);
                    break;
                } else {
                    break;
                }
            }
            if ($this->childrenCount >= $this->delegate->maxChildProcessCountForSinglePooledStyle()) {
                $this->delegate->whenPoolIsFull();
                if ($this->delegate->shouldWaitForAnyWorkerDone()) {
                    $exitedChildProcessID = pcntl_wait($status);
                    if ($exitedChildProcessID > 0) {
                        $this->childrenCount--;
                        $this->delegate->whenChildProcessConfirmedDead($exitedChildProcessID);
                    } elseif ($exitedChildProcessID === -1) {
                        $pcntl_error_number = pcntl_get_last_error();
                        $pcntl_error_string = pcntl_strerror($pcntl_error_number);
                        $error_message = 'Loop could not wait a child process to stop. Error No:' . $pcntl_error_number . " Message:" . $pcntl_error_string;
                        $this->delegate->whenLoopReportError($error_message);
                    }
                }
                continue;
            }
            if (!$this->delegate->isRunnable()) {
                $this->delegate->whenLoopShouldNotRun();
                continue;
            }
            $nextTask = $this->delegate->checkNextTask();
            if ($nextTask === false) {
                $this->delegate->whenNoTaskToDo();
                continue;
            }

            // since @0.2.0 it is executed before fork
            if (!$nextTask->beforeExecute()) {
                $this->delegate->whenTaskNotExecutable($nextTask);
                continue;
            }

            $this->delegate->beforeFork();
            $childProcessID = pcntl_fork();
            if ($childProcessID == -1) {
                $pcntl_error_number = pcntl_get_last_error();
                $pcntl_error_string = pcntl_strerror($pcntl_error_number);
                $error_message = 'Loop could not fork a child process to execute task. Error No:' . $pcntl_error_number . " Message:" . $pcntl_error_string;
                $this->delegate->whenLoopReportError($error_message);
                $this->delegate->whenTaskRaisedException($nextTask, new \Exception($error_message));
            } else if ($childProcessID) {
                // we are the parent
                $this->childrenCount++;
                $this->delegate->whenChildProcessForked($childProcessID, "For task " . $nextTask->getTaskReference());
            } else {
                // we are the child
                $this->delegate->markThisProcessAsWorker();
                try {
                    $this->delegate->whenToExecuteTask($nextTask);
                    $nextTask->execute();
                    $this->delegate->whenTaskExecuted($nextTask);
                } catch (\Exception $exception) {
                    $this->delegate->whenTaskRaisedException($nextTask, $exception);
                }

                // Lord, now lettest thou thy servant depart in peace, according to thy word: (Luke 2:29, KJV)
                exit(0);
            }

        }
        $this->delegate->whenLoopTerminates();
    }
}