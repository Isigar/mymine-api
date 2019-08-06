<?php


namespace App\Core\Api;


class Error implements \JsonSerializable
{
    private $title;
    private $msg;
    private $time;

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     * @return Error
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * @param mixed $msg
     * @return Error
     */
    public function setMsg($msg)
    {
        $this->msg = $msg;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param mixed $time
     * @return Error
     */
    public function setTime($time)
    {
        $this->time = $time;
        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'title' => $this->getTitle(),
            'time' => $this->getTime(),
            'msg' => $this->getMsg()
        ];
    }
}