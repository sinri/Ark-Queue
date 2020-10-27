<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-19
 * Time: 11:04
 */

namespace sinri\ark\queue\serial;


use sinri\ark\queue\AbstractQueueDaemon;

class SerialQueueDaemon extends AbstractQueueDaemon
{
    /**
     * @var SerialQueueDaemonDelegate
     */
    protected $delegate;

    /**
     * SerialQueueDaemon constructor.
     * @param SerialQueueDaemonDelegate $delegate
     */
    public function __construct($delegate)
    {
        $this->delegate = $delegate;
    }

    public function loop()
    {
        $this->delegate->whenLoopStarts();
        while (true) {
            if ($this->delegate->shouldTerminate()) {
                break;
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

            if (!$nextTask->beforeExecute()) {
                $this->delegate->whenTaskNotExecutable($nextTask);
                continue;
            }

            $this->delegate->whenToExecuteTask($nextTask);
            try {
                $nextTask->execute();
            } catch (\Exception $exception) {
                $this->delegate->whenTaskRaisedException($nextTask, $exception);
            }
            $this->delegate->whenTaskExecuted($nextTask);
        }
        $this->delegate->whenLoopTerminates();
    }

}