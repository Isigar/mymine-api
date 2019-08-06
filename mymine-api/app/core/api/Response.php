<?php


namespace App\Core\Api;


class Response
{
    private $errors;
    private $title;
    private $msg;
    private $time;
    private $data;

    public function prepare(){
        return [
            'title' => $this->getTitle(),
            'msg' => $this->getMsg(),
            'time' => $this->getTime() ?? time(),
            'data' => $this->getData() ?? [],
            'errors' => $this->getErrors() ?? []
        ];
    }

    public function addError(Error $error){
        $this->errors[] = $error;
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param mixed $errors
     * @return Response
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     * @return Response
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
     * @return Response
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
     * @return Response
     */
    public function setTime($time)
    {
        $this->time = $time;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return Response
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }



}