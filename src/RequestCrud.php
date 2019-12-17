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

    public function read($id) {
        $stmt = $this->conn->prepare("SELECT * FROM requests
                                          WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
    }

    public function readStandard($date) {
        $stmt = $this->conn->prepare("SELECT id FROM requests
                                          WHERE type = standard
                                          AND :date BETWEEN start and end");
        $stmt->bindValue(':date', $date);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function readFull($date, $department) {
        $stmt = $this->conn->prepare("SELECT COUNT(id) FROM requests
                                          WHERE employee_id IN (SELECT id FROM employees
                                          WHERE department = :department)
                                          AND (:date > start AND :date2 < end)");
        $stmt->bindValue(':date', $date);
        $stmt->bindValue(':date2', $date);
        $stmt->bindValue(':department', $department);
        $stmt->execute();
        $requests = $stmt->fetchColumn();

        $stmt = $this->conn->prepare("SELECT COUNT(id) FROM employees
                                          WHERE department = :department");
        $stmt->bindValue(':department', $department);
        $stmt->execute();
        $department_employees = $stmt->fetchColumn();

        if (!is_int($requests) || !is_int($department_employees)) {
            return 'error';
        } else {
            return $requests >= $department_employees / 2;
        }
    }

    public function readOverlap($start, $end, $id, $request_id) {
        $sql = "SELECT id FROM requests
                    WHERE type != 'standard' 
                    AND (:start BETWEEN start AND end
                    OR :end BETWEEN start AND end
                    OR (:start2 < start AND :end2 > end))
                    AND employee_id = :id";

        if ($request_id != null) $sql .= " AND id != :request_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':start', $start);
        $stmt->bindValue(':end', $end);
        $stmt->bindValue(':start2', $start);
        $stmt->bindValue(':end2', $end);
        $stmt->bindValue('id', $id);

        if ($request_id != null) $stmt->bindValue(':request_id', $request_id);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function readUnapproved() {
        $stmt = $this->conn->prepare("SELECT * FROM requests
                                          WHERE approved = 0");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readAll() {
        $stmt = $this->conn->prepare("SELECT * FROM requests");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readByMonth($month, $year, $department, $id) {
        $stmt = $this->conn->prepare("SELECT COUNT(id) FROM  employees
                                          WHERE department = :department");
        $stmt->bindValue(':department', $department);
        $stmt->execute();

        $department_size = $stmt->fetchColumn();

        $days = array();

        for ($i = 1; $i <= cal_days_in_month(CAL_GREGORIAN, $month, $year); $i++) {
            if (strlen($i) < 2) $i = '0'.$i;
            $date = $year.'-'.$month.'-'.$i;

            $stmt = $this->conn->prepare("SELECT id, employee_id, type, approved FROM requests
                                              WHERE employee_id IN (SELECT id FROM employees WHERE department = :department)
                                              AND (:date = start OR (:date2 > start AND :date3 < end) OR :date4 = end)");
            $stmt->bindValue(':department', $department);
            $stmt->bindValue(':date', $date.' 23:59:59');
            $stmt->bindValue(':date2', $date.' 23:59:59');
            $stmt->bindValue(':date3', $date.' 00:00:00');
            $stmt->bindValue(':date4', $date.' 00:00:00');
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $standard = false;

            foreach ($result as $request) {
                if ($request['type'] == 'standard') $standard = true;
            }

            $i = ltrim($i, '0');

            if ($standard) {
                $days[$i] = 'standard';
            } else {
                $own_leave = false;
                $approved = false;

                foreach ($result as $request) {
                    if ($request['employee_id'] == $id) {
                        $own_leave = true;

                        $approved = $request['approved'] == 1;
                    }
                }

                if ($own_leave) {
                    if ($date > date('Y-m-d')) {
                        $days[$i] = $approved ? 'approved' : 'hold';
                    } else {
                        $days[$i] = 'past';
                    }
                } else {
                    if (count($result) >= floor($department_size / 2)) {
                        $days[$i] = 'full';
                    } else {
                        if ($department_size > 2 && count($result) >= ($department_size / 2) - 1) {
                            $days[$i] = 'near';
                        } else {
                            $days[$i] = 'empty';
                        }
                    }
                }
            }
        }
        return $days;
    }

    public function readByEmployee($id) {
        $stmt = $this->conn->prepare("SELECT * FROM requests
                                          WHERE employee_id = :id
                                          AND type != 'standard'");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($statement, $values) {
        $stmt = $this->conn->prepare($statement);

        foreach ($values as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        return $stmt->execute();
    }

    public function setup($values, $id) {
        $statement = 'UPDATE requests SET ';
        $set = array();

        foreach ($values as $key => $value) {
            $statement .= $key.' = :'.$key.', ';
            $set[':'.$key] = $value;
        }

        $statement = rtrim($statement, ', ');
        $statement .= ' WHERE id = :id';
        $set[':id'] = $id;

        return $this->update($statement, $set);
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM requests
                                          WHERE id = :id");
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }
}