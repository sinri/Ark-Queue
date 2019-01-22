<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/21
 * Time: 15:15
 */

namespace sinri\ark\queue;


abstract class QueueTask
{
    /**
     * @var bool
     */
    protected $readyToExecute;
    /**
     * @var bool
     */
    protected $done;
    /**
     * @var string
     */
    protected $executeFeedback;
    /**
     * @var mixed
     */
    protected $executeResult;
    /**
     * @var bool
     */
    protected $readyToFinish;

    public function __construct()
    {
        $this->readyToExecute = false;
        $this->done = false;
        $this->executeFeedback = "Not Executed Yet";
        $this->executeResult = null;
    }

    /**
     * @return bool
     */
    public function isReadyToFinish()
    {
        return $this->readyToFinish;
    }

    /**
     * @return bool
     */
    public function isReadyToExecute()
    {
        return $this->readyToExecute;
    }

    /**
     * Fetch the unique reference of this task, such as TASK_ID.
     * Note, this reference should be globally unique.
     * @since 0.1.2
     * @return int|string
     */
    abstract public function getTaskReference();

    /**
     * Fetch the type of this task
     * @since 0.1.7
     * @return string
     */
    abstract public function getTaskType();

    /**
     * @return bool
     */
    public function isDone()
    {
        return $this->done;
    }

    /**
     * @return string
     */
    public function getExecuteFeedback()
    {
        return $this->executeFeedback;
    }

    /**
     * @return mixed
     */
    public function getExecuteResult()
    {
        return $this->executeResult;
    }

    /**
     * To prepare and lock task before executing.
     * You should update property $readyToExecute as the result of this method
     * @return bool
     */
    abstract public function beforeExecute();

    /**
     * Execute a task then:
     * (1) store extra output data in property $executeResult
     * (2) give a feedback string in property $executeFeedback
     * (3) give a boolean value in property $done and return
     * @return bool
     */
    abstract public function execute();

    /**
     * Do anything after execution, no matter the situation.
     * You may need to release the locks here.
     * Update $readyToFinish and return it.
     * @return bool
     */
    abstract public function afterExecute();

    /**
     * One queue task might have several locks.
     * I.e. If A task holding lock x and y started,
     * no any tasks could be started
     * if they hold x, hold y or hold both of them.
     * The implementation of LOCK depends on the delegate.
     *
     * @return string[]
     */
    abstract public function getLockList();
}