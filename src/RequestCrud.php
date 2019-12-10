<?php

namespace App;

use App\Entity\Employee;
use App\Entity\Request;
use PDO;

class RequestCrud {
    private $conn;

    public function __construct() {
        $this->conn = $this->connect();
    }

    public function connect() {
        $db_host = '127.0.0.1';     //Something something ip config (makes website fast)
        $db_username = 'sql_manager';
        $db_password = 'lookatmeimastrongpassword';
        $db_name = 'symgular_database';
        $conn = new PDO('mysql:host='.$db_host.';dbname='.$db_name, $db_username, $db_password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute( PDO::ATTR_EMULATE_PREPARES, false ); //Disables automatic stringifying
        return $conn;
    }

    public function create(Request $request) {
        $stmt = $this->conn->prepare("INSERT INTO requests ( employee_id, start, end, type, description, approved, editable, standard) 
                                          VALUES (:employee_id, :start, :end, :type, :description, :approved, :editable, :standard)");
        $stmt->bindValue(':employee_id', $request->getEmployeeId());
        $stmt->bindValue(':start', $request->getStartDate());
        $stmt->bindValue(':end', $request->getEndDate());
        $stmt->bindValue(':type', $request->getType());
        $stmt->bindValue(':description', $request->getDescription());
        $stmt->bindValue(':approved', $request->getApproved());
        $stmt->bindValue(':editable', $request->getEditable());
        $stmt->bindValue(':standard', $request->getStandard());
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function read(Employee $employee) {}

    public function readStandard($date) {
        $stmt = $this->conn->prepare("SELECT id FROM requests
                                          WHERE type = standard
                                          AND :date BETWEEN start and end");
        $stmt->bindValue(':date', $date);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function readFull($date, $department) {
        $stmt = $this->conn->prepare("SELECT id FROM requests
                                          WHERE employee_id IN (SELECT id FROM employees
                                          WHERE department = :department)
                                          AND (:date > start AND :date2 < end)");
        $stmt->bindValue(':date', $date);
        $stmt->bindValue(':date2', $date);
        $stmt->bindValue(':department', $department);
        $stmt->execute();
        $requests = count($stmt->fetchAll());

        $stmt = $this->conn->prepare("SELECT id FROM employees
                                          WHERE department = :department");
        $stmt->bindValue(':department', $department);
        $stmt->execute();
        $department_employees = count($stmt->fetchAll());

        return $requests >= $department_employees / 2;
    }

    public function readOverlap($start, $end, $id) {
        $stmt = $this->conn->prepare("SELECT id FROM requests
                                          WHERE type != 'standard' 
                                          AND (:start BETWEEN start AND end
                                          OR :end BETWEEN start AND end
                                          OR (:start2 < start AND :end2 > end))
                                          AND employee_id = :id");
        $stmt->bindValue(':start', $start);
        $stmt->bindValue(':end', $end);
        $stmt->bindValue(':start2', $start);
        $stmt->bindValue(':end2', $end);
        $stmt->bindValue('id', $id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function readAll() {

    }

    public function readByMonth($month, $year, $department) {
        $stmt = $this->conn->prepare("SELECT * FROM requests
                                          WHERE employee_id IN (SELECT id FROM employees
                                                                WHERE department = :department)
                                          AND start REGEXP '".$year."-".$month."-[0-9]{2}'
                                          OR end REGEXP '".$year."-".$month."-[0-9]{2}'");
        $stmt->bindValue(':department', $department);
    }

    public function update() {

    }

    public function delete() {

    }
}