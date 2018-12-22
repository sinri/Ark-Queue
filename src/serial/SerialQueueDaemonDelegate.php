<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-19
 * Time: 11:00
 */

namespace sinri\ark\queue\serial;


use sinri\ark\queue\AbstractQueueDaemonDelegate;

abstract class SerialQueueDaemonDelegate extends AbstractQueueDaemonDelegate
{
    /**
     * When the loop gets ready to terminate by shouldTerminate instructed, execute this
     */
    public function whenLoopTerminates()
    {
        // do nothing by default, you can write some logs here
    }

    /**
     * @return SerialQueueTask|false
     */
    public final function checkNextTask()
    {
        return $this->checkNextTaskImplement();
    }

    /**
     * @return SerialQueueTask|false
     */
    abstract public function checkNextTaskImplement();

}