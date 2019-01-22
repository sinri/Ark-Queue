<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-19
 * Time: 11:22
 */

namespace sinri\ark\queue;


use sinri\ark\queue\parallel\ParallelQueueTask;
use sinri\ark\queue\serial\SerialQueueTask;

abstract class AbstractQueueDaemonDelegate
{
    const QUEUE_RUNTIME_COMMAND_PAUSE = "PAUSE";
    const QUEUE_RUNTIME_COMMAND_CONTINUE = "CONTINUE";
    const QUEUE_RUNTIME_COMMAND_STOP = "STOP";
    const QUEUE_RUNTIME_COMMAND_FORCE_STOP = "FORCE-STOP";
    const QUEUE_RUNTIME_COMMAND_RESTART = "RESTART";
    const QUEUE_RUNTIME_COMMAND_FORCE_RESTART = "FORCE-RESTART";


    /**
     * QueueDaemon constructor.
     * @param array $config Put any properties here
     */
    abstract public function __construct($config = []);

    /**
     * @param string $error
     */
    abstract public function whenLoopReportError($error);

    /**
     * If not runnable, the daemon loop would sleep.
     * @return bool
     */
    abstract public function isRunnable();

    /**
     * Tell daemon loop to exit.
     * @return bool
     */
    abstract public function shouldTerminate();

    /**
     * When the loop gets ready to terminate by shouldTerminate instructed, execute this
     */
    abstract public function whenLoopTerminates();

    /**
     * Sleep for a certain while.
     * @return void
     */
    abstract public function whenLoopShouldNotRun();

    /**
     * @return QueueTask|SerialQueueTask|ParallelQueueTask|false
     */
    abstract public function checkNextTask();

    /**
     * When the loop cannot check for a task to do next, execute this
     */
    abstract public function whenNoTaskToDo();

    /**
     * @since 0.2.0 this is done before fork in pooled style
     * @param QueueTask $task
     */
    abstract public function whenTaskNotExecutable($task);

    /**
     * @since 2.1 Note: Any exceptions should be caught inside.
     * @param QueueTask $task
     */
    abstract public function whenToExecuteTask($task);

    /**
     * @since 2.1 Note: Any exceptions should be caught inside.
     * @param QueueTask $task
     */
    abstract public function whenTaskExecuted($task);

    /**
     * @param QueueTask $task
     * @param \Exception $exception
     */
    abstract public function whenTaskRaisedException($task, $exception);

}