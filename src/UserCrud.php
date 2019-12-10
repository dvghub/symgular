<?php

namespace App;

use App\Entity\Employee;
use PDO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserCrud extends AbstractController {
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

    public function create(Employee $employee){
        $stmt = $this->conn->prepare("INSERT INTO employees (first_name, last_name, email, password, department, birthday, admin) 
                                          VALUES (:first_name, :last_name, :email, :password, :department, :birthday, :admin)");
        $stmt->bindValue(':first_name', $employee->getFirstName());
        $stmt->bindValue(':last_name', $employee->getLastName());
        $stmt->bindValue(':email', $employee->getEmail());
        $stmt->bindValue(':password', $employee->getPassword());
        $stmt->bindValue(':department', $employee->getDepartment());
        $stmt->bindValue(':birthday', $employee->getBirthday());
        $stmt->bindValue(':admin', $employee->getAdmin());
        $stmt->execute();
        return $this->conn->lastInsertId();
    }

    public function read($email) {
        $stmt = $this->conn->prepare("SELECT * FROM employees WHERE email = :email");
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        $result =  $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            $result = $result[0];
            $employee = new Employee();

            $employee->setId($result['id']);
            $employee->setFirstName($result['first_name']);
            $employee->setLastName($result['last_name']);
            $employee->setEmail($result['email']);
            $employee->setPassword($result['password']);
            $employee->setDepartment($result['department']);
            $employee->setBirthday($result['birthday']);
            $employee->setAdmin($result['admin']);
            $employee->setHours($result['hours']/10);

            return $employee;
        } else return null;
    }

    public function readAll() {
        $emails = array();

        $stmt = $this->conn->prepare("SELECT * FROM employees");
        $stmt->execute();
        $results =  $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $result) {
            array_push($emails, $result);
        }

        return $emails;
    }

    public function readBirthdaysByMonth($month) {
        $employees = array();

        $stmt = $this->conn->prepare("SELECT first_name, last_name, birthday FROM employees
                                                       WHERE birthday REGEXP '[0-9]{4}-" . $month . "-[0-9]{2}'
                                                       AND birthday NOT REGEXP '1000-01-01'");
        $stmt->execute();
        $results =  $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $result) {
            array_push($employees, $result);
        }

        return $employees;
    }

    public function update($statement, $values) {
        $stmt = $this->conn->prepare($statement);

        foreach ($values as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        return $stmt->execute();
    }

    public function setup($values, $email) {
        $statement = 'UPDATE employees SET ';
        $set = array();

        foreach ($values as $key => $value) {
            $statement .= $key.' = :'.$key.', ';
            $set[':'.$key] = $value;
        }

        $statement = rtrim($statement, ', ');
        $statement .= ' WHERE email = :email';
        $set[':email'] = $email;

        return $this->update($statement, $set);
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM employees WHERE id = :id");
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
}
