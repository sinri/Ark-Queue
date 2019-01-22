<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-19
 * Time: 10:59
 */

namespace sinri\ark\queue\serial;


use sinri\ark\queue\QueueTask;

abstract class SerialQueueTask extends QueueTask
{
    /**
     * @return bool
     */
    public function beforeExecute()
    {
        $this->readyToExecute = true;
        return $this->readyToExecute;
    }

    public function afterExecute()
    {
        $this->readyToFinish = true;
        return $this->readyToFinish;
    }

    public function getLockList()
    {
        return [];
    }
}