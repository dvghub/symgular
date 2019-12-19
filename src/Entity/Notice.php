<?php

namespace App\Entity;

class Notice {
    private $id;
    private $title;
    private $message;
    private $creator;
    private $timestamp;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getMessage() {
        return $this->message;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function getCreator() {
        return $this->creator;
    }

    public function setCreator($creator) {
        $this->creator = $creator;
    }

    public function getTimestamp() {
        return $this->timestamp;
    }

    public function setTimestamp($timestamp) {
        $this->timestamp = $timestamp;
    }


}