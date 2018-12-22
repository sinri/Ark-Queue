<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-19
 * Time: 14:53
 */

namespace sinri\ark\queue\test\ParallelTest;


use sinri\ark\queue\parallel\ParallelQueueDaemonDelegate;
use sinri\ark\queue\parallel\ParallelQueueTask;
use sinri\ark\queue\QueueTask;

class TestParallelQueueDaemonDelegate extends ParallelQueueDaemonDelegate
{

    /**
     * QueueDaemon constructor.
     * @param array $config Put any properties here
     */
    public function __construct($config = [])
    {
        //parent::__construct($config);
    }

    /**
     * @param string $error
     */
    public function whenLoopReportError($error)
    {
        echo "[" . time() . "] " . __METHOD__ . " " . $error . PHP_EOL;
    }

    /**
     * If not runnable, the daemon loop would sleep.
     * @return bool
     */
    public function isRunnable()
    {
        //echo "[".time()."] ".__METHOD__.PHP_EOL;
        return true;
    }

    /**
     * Tell daemon loop to exit.
     * @return bool
     */
    public function shouldTerminate()
    {
        //echo "[".time()."] ".__METHOD__.PHP_EOL;
        return false;
    }

    /**
     * Sleep for a certain while.
     * @return void
     */
    public function whenLoopShouldNotRun()
    {
        echo "[" . time() . "] " . __METHOD__ . " Sleep 2 seconds." . PHP_EOL;
        sleep(2);
    }

    /**
     * When the loop cannot check for a task to do next, execute this
     */
    public function whenNoTaskToDo()
    {
        echo "[" . time() . "] " . __METHOD__ . " sleep 4 seconds" . PHP_EOL;
    }

    /**
     * @since 0.2.0 this is done before fork in pooled style
     * @param QueueTask $task
     */
    public function whenTaskNotExecutable($task)
    {
        echo "[" . time() . "] " . __METHOD__ . " vain" . PHP_EOL;
    }

    /**
     *
     * @param QueueTask $task
     */
    public function whenToExecuteTask($task)
    {
        echo "[" . time() . "] " . __METHOD__ . " for " . $task->getTaskReference() . "@" . $task->getTaskType() . PHP_EOL;
    }

    /**
     * @param QueueTask $task
     */
    public function whenTaskExecuted($task)
    {
        echo "[" . time() . "] " . __METHOD__ . " for " . $task->getTaskReference() . "@" . $task->getTaskType() . PHP_EOL;
    }

    /**
     * @param QueueTask $task
     * @param \Exception $exception
     */
    public function whenTaskRaisedException($task, $exception)
    {
        echo "[" . time() . "] " . __METHOD__ . " for " . $task->getTaskReference() . "@" . $task->getTaskType() . " Threw " . $exception->getMessage() . PHP_EOL;
    }

    /**
     * @return ParallelQueueTask|false
     */
    public function checkNextTaskImplement()
    {
        if (rand(1, 10) > 2) {
            return new TestParallelQueueTask();
        } else {
            return false;
        }
    }

    /**
     * When a child process is forked
     * @param int $pid
     * @param string $note
     */
    public function whenChildProcessForked($pid, $note = '')
    {
        echo "[" . time() . "] " . __METHOD__ . " PID: " . $pid . " " . $note . PHP_EOL;
    }

    /**
     * When a child process is observed dead by WAIT function
     * @param int $pid
     */
    public function whenChildProcessConfirmedDead($pid)
    {
        echo "[" . time() . "] " . __METHOD__ . " PID: " . $pid . PHP_EOL;
    }

    /**
     * When the daemon has made the pool full of child processes to work
     * It is recommended to take a sleep here
     */
    public function whenPoolIsFull()
    {
        echo "[" . time() . "] " . __METHOD__ . " sleep 5 seconds" . PHP_EOL;
        sleep(5);
    }

    /**
     * You can close all opened DB connection here
     */
    public function beforeFork()
    {
        //echo "[".time()."] ".__METHOD__.PHP_EOL;
    }

    /**
     * The daemon would fork child processes up to the certain number
     * @return int
     */
    public function maxChildProcessCountForSinglePooledStyle()
    {
        return 5;
    }

    public function whenStartLongWaiting()
    {
        echo "[" . time() . "] " . __METHOD__ . " ..." . PHP_EOL;
    }
}