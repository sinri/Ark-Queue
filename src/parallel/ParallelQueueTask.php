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

}