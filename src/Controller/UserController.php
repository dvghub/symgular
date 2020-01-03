<?php

namespace App\Controller;

use App\UserCrud;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController {
    private $validator;
    private $logger;

    public function __construct(LoggerInterface $logger) {
        $this->validator = new Validator($logger);
        $this->logger = $logger;
    }

    public function create() {
        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);

        return new Response(
            json_encode($this->validator->validateRegister($body))
        );
    }

    public function readAll() {
        $crud = new UserCrud();

        return new Response(
            json_encode(array(
                'employees' => $crud->readAll()
            ))
        );
    }

    public function readBirthdays() {
        $crud = new UserCrud();

        return new Response(
            json_encode(array(
                'employees' => $crud->readBirthdaysByMonth(date('m'))
            ))
        );
    }

    public function read($id) {
        $crud = new UserCrud();

        return new Response(
            json_encode(array(
                'employee' => $crud->read($id)
            ))
        );
    }

    public function readPassword($id) {
        $crud = new UserCrud();
        $isset = $crud->readPassword($id) != null;

        return new Response(
            json_encode(array(
                'isset' => $isset
            ))
        );
    }

    public function email() {
        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);
        $email = $body['email'];
        $crud = new UserCrud();
        $user = $crud->readByEmail($email);
        $response['email'] = $user->getEmail();
        $response['name'] = $user->getFirstName();
        $response['department'] = $user->getDepartment();
        $response['admin'] = $user->getAdmin();

        return new Response(
            json_encode($response)
        );
    }

    public function update($id) {
        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);

        return new Response(
            json_encode(array(
                $this->validator->validateUpdate($body, $id)
            ))
        );
    }

    public function updatePassword($id) {
        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);
        $crud = new UserCrud();

        return new Response(
            json_encode(array(
                'success' => $crud->setup(array('password' => password_hash($body['password'], PASSWORD_BCRYPT, [10])), $id)
            ))
        );
    }
}
