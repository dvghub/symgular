<?php

namespace App\Controller;

use App\RequestCrud;
use App\UserCrud;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class POSTS extends AbstractController {
    private $validator;
    private $logger;

    public function __construct(LoggerInterface $logger) {
        $this->validator = new Validator($logger);
        $this->logger = $logger;
    }

    public function login() {
        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);

        return new Response(
            json_encode($this->validator->validateLogin($body))
        );
    }

    public function register() {
        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);

        return new Response(
            json_encode($this->validator->validateRegister($body))
        );
    }

    public function update() {
        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);

        $this->logger->info(json_encode($body));

        return new Response(
            json_encode($this->validator->validateUpdate($body))
        );
    }

    public function request() {
        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);

        return new Response(
            json_encode($this->validator->validateRequest($body))
        );
    }

    public function requestsByMonth() {
        return new Response(
            json_encode(array('response' => 'success'))
        );
    }


    public function requestsByEmployee() {
        $crud = new UserCrud();

        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);

        $email = $this->validator->testInput($body['email']);

        $user = $crud->read($email);

        if ($this->validator->validateEmail($email)) {
            $crud = new RequestCrud();
            $response['success'] = true;
            $response['requests'] = $crud->readByEmployee($user->getId());
        } else {
            $response['success'] = false;
        }

        return new Response(
            json_encode($response)
        );
    }

    public function hours() {
        $crud = new UserCrud();

        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);

        $user = $crud->read($body['email']);

        return new Response(
            json_encode(array(
                'response' => 'success',
                'hours' => $user->getHours()))
        );
    }

    public function approve() {

    }

    public function denied() {

    }
}