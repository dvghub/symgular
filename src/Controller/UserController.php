<?php

namespace App\Controller;

use App\UserCrud;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController {
    private $validator;

    public function __construct(LoggerInterface $logger) {
        $this->validator = new Validator($logger);
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
}
