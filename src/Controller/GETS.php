<?php

namespace App\Controller;

use App\RequestCrud;
use App\UserCrud;
use function Symfony\Component\Console\Tests\Command\createClosure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GETS {

    public function user() {
        $response = array(
            'email' => '',
            'name' => '',
            'department' => '',
            'admin' => ''
        );

        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);
        $session_id = $body['session_id'];
        session_destroy();
        session_id($session_id);
        session_start();

        if (isset($_SESSION['user'])) {
            $user = $_SESSION['user'];
            $response['email'] = $user->getEmail();
            $response['name'] = $user->getFirstName();
            $response['department'] = $user->getDepartment();
            $response['admin'] = $user->getAdmin();
        }

        return new Response(
            json_encode($response)
        );
    }

    public function userByEmail() {
        $response = array(
            'email' => '',
            'name' => '',
            'department' => '',
            'admin' => ''
        );

        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);
        $email = $body['email'];

        $crud = new UserCrud();
        $user = $crud->read($email);

        $response['email'] = $user->getEmail();
        $response['name'] = $user->getFirstName();
        $response['department'] = $user->getDepartment();
        $response['admin'] = $user->getAdmin();

        return new Response(
            json_encode($response)
        );
    }

    public function users() {
        $crud = new UserCrud();
        $response['employees'] = $crud->readAll();

        return new Response(
            json_encode($response)
        );
    }

    public function birthdaysByMonth() {
        $crud = new UserCrud();

        return new Response(
            json_encode(array(
                'employees' => $crud->readBirthdaysByMonth(date('m'))
            ))
        );
    }

    public function requests() {
        $crud = new RequestCrud();

        return new Response(
            json_encode(array(
                'requests' => $crud->readAll()
            ))
        );
    }

    public function logout() {
        session_destroy();
    }

}