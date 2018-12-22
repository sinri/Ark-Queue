<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-19
 * Time: 16:19
 */

require_once __DIR__ . '/../../vendor/autoload.php';

(new \sinri\ark\queue\parallel\ParallelQueueDaemon(new \sinri\ark\queue\test\ParallelTest\TestParallelQueueDaemonDelegate()))->loop();