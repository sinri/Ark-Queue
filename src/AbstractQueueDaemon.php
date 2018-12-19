<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-19
 * Time: 11:06
 */

namespace sinri\ark\queue;


abstract class AbstractQueueDaemon
{
    abstract public function loop();
}