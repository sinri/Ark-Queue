<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-19
 * Time: 11:13
 */

namespace sinri\ark\queue\parallel;


use sinri\ark\queue\AbstractQueueDaemonDelegate;

abstract class ParallelQueueDaemonDelegate extends AbstractQueueDaemonDelegate
{

    /**
     * When the loop gets ready to terminate by shouldTerminate instructed, execute this
     */
    public function whenLoopTerminates()
    {
        // do nothing by default, you can write some logs here
    }

    /**
     * @return ParallelQueueTask|false
     */
    public final function checkNextTask()
    {
        return $this->checkNextTaskImplement();
    }

    /**
     * @return ParallelQueueTask|false
     */
    abstract public function checkNextTaskImplement();

    // for pooled style daemon

    /**
     * The daemon would fork child processes up to the certain number
     * @return int
     */
    abstract public function maxChildProcessCountForSinglePooledStyle();

    /**
     * When a child process is forked
     * @param int $pid
     * @param string $note
     */
    abstract public function whenChildProcessForked($pid, $note = '');

    /**
     * When a child process is observed dead by WAIT function
     * @param int $pid
     */
    abstract public function whenChildProcessConfirmedDead($pid);

    /**
     * When the daemon has made the pool full of child processes to work
     * It is recommended to take a sleep here
     */
    abstract public function whenPoolIsFull();

    /**
     * 如果返回true，则在执行完whenPoolIsFull之后会进行阻塞wait子进程
     * @return bool
     */
    public function shouldWaitForAnyWorkerDone()
    {
        return true;
    }

    /**
     * Before waiting for tasks done (without WNOHANG option).
     * @return void
     */
    public function whenStartLongWaiting()
    {
        // do nothing, maybe you want to write logs
    }

    const PROCESS_TYPE_WORKER = "WORKER";

    protected $typeOfThisProcess = null;

    /**
     * When use worker process pool style, the worker progress should have chance to declare this identity.
     */
    public function markThisProcessAsWorker()
    {
        $this->typeOfThisProcess = self::PROCESS_TYPE_WORKER;
    }

    /**
     * You can close all opened DB connection here
     */
    abstract public function beforeFork();

}