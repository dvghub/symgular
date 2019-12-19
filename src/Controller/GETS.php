<?php

namespace App\Controller;

use App\NoticeCrud;
use App\RequestCrud;
use App\UserCrud;
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
        $user = $crud->readByEmail($email);

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

        return new Response(
            json_encode(array(
                'employees' => $crud->readAll()
            ))
        );
    }

    public function notices() {
        $crud = new NoticeCrud();

        return new Response(
            json_encode(array(
                'notices' => $crud->readAll()
            ))
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

    public function unapproved() {
        $crud = new RequestCrud();

        return new Response(
            json_encode(array(
                'requests' => $crud->readUnapproved()
            ))
        );
    }

    public function logout() {
        session_destroy();
    }

}