<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-19
 * Time: 14:53
 */

namespace sinri\ark\queue\test\ParallelTest;


use sinri\ark\queue\parallel\ParallelQueueTask;

class TestParallelQueueTask extends ParallelQueueTask
{
    protected $taskReference;
    protected $taskType;
    protected $exclusive;

    private $simulateSleep;

    public function __construct()
    {
        parent::__construct();

        $this->taskReference = uniqid();
        $this->exclusive = rand(0, 10) > 5;
        $this->taskType = ($this->exclusive ? "exclusive_task" : "non_exclusive_task");

        $this->simulateSleep = rand(3, 10);


        echo "TASK [" . $this->getTaskReference() . "]: " . ($this->exclusive ? "E" : "N") . " " . $this->simulateSleep . PHP_EOL;
    }

    /**
     * Fetch the unique reference of this task, such as TASK_ID
     * @since 0.1.2
     * @return int|string
     */
    public function getTaskReference()
    {
        return $this->taskReference;
    }

    /**
     * Fetch the type of this task
     * @since 0.1.7
     * @return string
     */
    public function getTaskType()
    {
        return $this->taskType;
    }

    /**
     * To prepare and lock task before executing.
     * You should update property $readyToExecute as the result of this method
     * @return bool
     */
    public function beforeExecute()
    {
        //echo "[".time()."] ".__METHOD__." ".$this->taskReference.'@'.$this->taskType.PHP_EOL;
        $this->readyToExecute = true;
        return $this->readyToExecute;
    }

    /**
     * Execute a task then:
     * (1) store extra output data in property $executeResult
     * (2) give a feedback string in property $executeFeedback
     * (3) give a boolean value in property $done and return
     * @return bool
     */
    public function execute()
    {
        echo "[" . time() . "] " . __METHOD__ . " " . $this->taskReference . '@' . $this->taskType . " costs " . $this->simulateSleep . PHP_EOL;
        sleep($this->simulateSleep);

        $this->executeResult = $this->simulateSleep;
        $this->done = ($this->simulateSleep % 2 == 0);
        $this->executeFeedback = ($this->done ? "SUCCESS" : "FAIL");

        return $this->done;
    }

    public function isExclusive()
    {
        return $this->exclusive;
    }
}