<?php

namespace App\Controller;

use App\CRUD;
use App\Entity\Employee;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;

class Validator extends AbstractController {
    private $logger;

    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    public function login() {
        $crud = new CRUD();

        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);

        $email = $body['email'];
        $password = $body['password'];

        $employee = $crud->read($email);

        $result = array(
            'logged' => false,
            'email_error' => '',
            'password_error' => '',
            'session_id' => ''
        );

        if ($employee == null) {
            $result['email_error'] = 'Unknown email address.';
        } else {
            if (!password_verify($password, $employee->getPassword())) {
                $result['password_error'] = 'Incorrect password';
            } else {
                $_SESSION['user'] = $employee;
                $result['logged'] = true;
                $result['first_name'] = $employee->getFirstName();
                $result['last_name'] = $employee->getLastName();
                $result['department'] = $employee->getDepartment();
                $result['birthday'] = $employee->getBirthday();
                $result['admin'] = $employee->getAdmin();
                $result['session_id'] = session_id();
            }
        }

        return new Response(
            json_encode($result)
        );
    }

    public function register() {
        $crud = new CRUD();

        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);

        $employee = new Employee();

        $employee->setFirstName($body['first_name']);
        $employee->setLastName($body['last_name']);
        $employee->setEmail($body['email']);
        $employee->setPassword(password_hash('password', PASSWORD_BCRYPT, [10]));
        $employee->setDepartment($body['department']);
        $employee->setBirthday($body['birthday']);
        $employee->setAdmin((int) $body['admin']);

        $id = $crud->create($employee);

        if ($id != 0) {
            $result = array(
                'registered' => true,
                'registered_name' => $employee->getFirstName()
            );
        } else {
            $result = array(
                'registered' => false
            );
        }

        return new Response(
            json_encode($result)
        );
    }

    public function update() {
        $crud = new CRUD();

        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);

        $employee = $crud->read($body['email']);

        $values = array();
        $statement = 'UPDATE employees SET ';

        $response = array();

        $this->logger->info(json_encode($body));

        if (!empty($body['password'])) {
            if ($body['editor_admin']) {
                if ($body['password'] == $body['password_repeat']) {
                    $statement .= 'password = :password, ';
                    $values['password'] = password_hash($body['password'], PASSWORD_BCRYPT, 10);
                } else {
                    $response['password_error'] = "Passwords don't match.";
                    $response['response'] = false;
                    return new Response(json_encode($response));
                }
            } else {
                if (password_verify($body['password_old'], $employee->getPassword())) {
                    if ($body['password'] == $body['password_repeat']) {
                        $statement .= 'password = :password, ';
                        $values['password'] = $body['password'];
                    } else {
                        $response['password_error'] = "Passwords don't match.";
                    }
                } else {
                    $response['old_password_error'] = "Incorrect password.";
                }
            }
        }

        if (!empty($body['department'])) {
            $this->logger->info('Adding department...');
            $statement .= 'department = :department, ';
            $values['department'] = $body['department'];
        }

        if (!empty($body['admin'])) {
            $statement .= 'admin = :admin, ';
            $values['admin'] = $body['admin'];
        }

        if (count($values) > 0) {
            $statement = rtrim($statement, ", ");
            $statement .= ' WHERE id = :id';
            $values['id'] = $employee->getId();

            return new Response(
                json_encode($response['response'] = $crud->update($statement, $values))
            );
        } else {
            return new Response(
                json_encode($response['response'] = false)
            );
        }
    }
}
