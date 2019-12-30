<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-19
 * Time: 11:13
 */

namespace sinri\ark\queue\parallel;


use sinri\ark\queue\QueueTask;

abstract class ParallelQueueTask extends QueueTask
{
    /**
     * Determine if this task should be run in Exclusive Mode,
     * which would wait for other all tasks stopped before starts,
     * and never run other tasks until it ends.
     * @return bool
     */
    public function isExclusive()
    {
        return false;
    }

    /**
     * Locks should be checked here
     * Should be called by Delegate::checkNextTaskImplement
     * @return bool
     * @since 2.5
     */
    public function checkIfLocked()
    {
        return false;
    }

    /**
     * Array of Lock Names
     * @return string[]
     */
    public function getLockList()
    {
        return [];
    }

    /**
     * It should be run in Delegate::beforeFork to control if this task should run now
     * @return bool
     */
    public function beforeExecute()
    {
        $this->readyToExecute = true;
        return $this->readyToExecute;
    }

    /**
     * It should be run in Delegate::whenTaskExecuted to control how the task should feedback
     * @return bool
     */
    public function afterExecute()
    {
        $this->readyToFinish = true;
        return $this->readyToFinish;
    }


}