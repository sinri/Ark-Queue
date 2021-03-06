<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-19
 * Time: 11:12
 */

namespace sinri\ark\queue\parallel;


use Exception;
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

    /**
     * @param bool $shouldKeepWaitForAll
     */
    protected function recycle($shouldKeepWaitForAll)
    {
        if (!$shouldKeepWaitForAll) {
            $options = WNOHANG | WUNTRACED;
        } else {
            $this->recycle(false);
            if ($this->childrenCount <= 0) {
                // no need to wait any more
                return;
            }
            $options = WUNTRACED;
            $this->delegate->whenStartLongWaiting();
        }

        for ($i = 0; $i < $this->childrenCount; $i++) {
            // pcntl_wait() returns the process ID of the child which exited,
            // -1 on error
            // or zero if WNOHANG was provided as an option (on wait3-available systems) and no child was available.
            $exitedChildProcessID = pcntl_wait($status, $options);
            if ($exitedChildProcessID > 0) {
                $this->childrenCount--;

                $detail = [
                    'isNormalExit' => pcntl_wifexited($status),
                    'isStopped' => pcntl_wifstopped($status),
                    'isExistedByUncaughtSignal' => pcntl_wifsignaled($status),
                    'returnCode' => pcntl_wexitstatus($status),
                    'signalNumber' => pcntl_wtermsig($status),
                    'stopCauseSignal' => pcntl_wstopsig($status),
                ];

                $this->delegate->whenChildProcessConfirmedDead($exitedChildProcessID, $detail);
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
    }

    public function loop()
    {
        $this->delegate->whenLoopStarts();
        while (true) {
            if ($this->delegate->shouldTerminate()) {
                // Check config and recycle since 2.6
                if ($this->delegate->shouldTerminateAfterAllWorkersEnd()) {
                    $this->recycle(true);
                }
                break;
            }
            $this->recycle(false);
            if ($this->childrenCount >= $this->delegate->maxChildProcessCountForSinglePooledStyle()) {
                $this->delegate->whenPoolIsFull();
                if ($this->delegate->shouldWaitForAnyWorkerDone()) {
                    $exitedChildProcessID = pcntl_wait($status);
                    if ($exitedChildProcessID > 0) {
                        $this->childrenCount--;

                        $detail = [
                            'isNormalExit' => pcntl_wifexited($status),
                            'isStopped' => pcntl_wifstopped($status),
                            'isExistedByUncaughtSignal' => pcntl_wifsignaled($status),
                            'returnCode' => pcntl_wexitstatus($status),
                            'signalNumber' => pcntl_wtermsig($status),
                            'stopCauseSignal' => pcntl_wstopsig($status),
                        ];

                        $this->delegate->whenChildProcessConfirmedDead($exitedChildProcessID, $detail);
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

            if ($nextTask->isExclusive()) {
                // now daemon should wait for the other tasks to be over
                $this->recycle(true);
            }

            // since @2.4 nextTask::beforeExecute should be contained in delegate::beforeFork
            if (!$this->delegate->beforeFork($nextTask)) {
                $this->delegate->whenTaskNotExecutable($nextTask);
                continue;
            }

            $childProcessID = pcntl_fork();
            if ($childProcessID == -1) {
                $pcntl_error_number = pcntl_get_last_error();
                $pcntl_error_string = pcntl_strerror($pcntl_error_number);
                $error_message = 'Loop could not fork a child process to execute task. Error No:' . $pcntl_error_number . " Message:" . $pcntl_error_string;
                $this->delegate->whenLoopReportError($error_message);
                $this->delegate->whenTaskRaisedException($nextTask, new Exception($error_message));
            } else if ($childProcessID) {
                // we are the parent
                $this->childrenCount++;
                // @since 2.3 added the third parameter
                $this->delegate->whenChildProcessForked($childProcessID, "For task " . $nextTask->getTaskReference(), $nextTask->getTaskReference());

                if ($nextTask->isExclusive()) {
                    // now daemon should wait for the exclusive task to be over
                    $this->recycle(true);
                }
            } else {
                // we are the child
                $this->delegate->markThisProcessAsWorker();
                $this->delegate->whenToExecuteTask($nextTask);
                try {
                    $nextTask->execute();
                } catch (Exception $exception) {
                    $this->delegate->whenTaskRaisedException($nextTask, $exception);
                }
                // nextTask::afterExecute should be contained in delegate::whenTaskExecuted
                $this->delegate->whenTaskExecuted($nextTask);

                // Lord, now lettest thou thy servant depart in peace, according to thy word: (Luke 2:29, KJV)
                exit(0);
            }

        }
        $this->delegate->whenLoopTerminates();
    }
}