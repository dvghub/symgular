<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SessionController {
    private $validator;

    public function __construct(LoggerInterface $logger) {
        $this->validator = new Validator($logger);
    }

    public function user() {
        $response = array();
        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);
        $session_id = $body['session_id'];
        session_destroy();
        session_id($session_id);
        session_start();

        if (isset($_SESSION['user'])) {
            $user = $_SESSION['user'];
            $response['user']['email'] = $user->getEmail();
            $response['user']['name'] = $user->getFirstName();
            $response['user']['department'] = $user->getDepartment();
            $response['user']['admin'] = $user->getAdmin();
        }

        return new Response(
            json_encode($response)
        );
    }

    public function create() {
        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);

        return new Response(
            json_encode($this->validator->validateLogin($body))
        );
    }
}