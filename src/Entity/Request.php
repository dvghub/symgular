<?php

namespace App\Entity;

class Request {
    private $id;
    private $employee_id;
    private $start_date;
    private $end_date;
    private $type;
    private $description;
    private $approved = 0;
    private $editable = 1;
    private $standard = 0;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getEmployeeId() {
        return $this->employee_id;
    }

    public function setEmployeeId($employee_id) {
        $this->employee_id = $employee_id;
    }

    public function getStartDate() {
        return $this->start_date;
    }

    public function setStartDate($start_date) {
        $this->start_date = $start_date;
    }

    public function getEndDate() {
        return $this->end_date;
    }

    public function setEndDate($end_date) {
        $this->end_date = $end_date;
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function getApproved() {
        return $this->approved;
    }

    public function setApproved($approved) {
        $this->approved = $approved;
    }

    public function getEditable() {
        return $this->editable;
    }

    public function setEditable($editable) {
        $this->editable = $editable;
    }

    public function getStandard() {
        return $this->standard;
    }

    public function setStandard($standard) {
        $this->standard = $standard;
    }


}