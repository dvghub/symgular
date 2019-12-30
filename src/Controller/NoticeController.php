<?php

namespace App\Controller;

use App\NoticeCrud;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NoticeController {
    private $validator;

    public function __construct(LoggerInterface $logger) {
        $this->validator = new Validator($logger);
    }

    public function create() {
        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);

        return new Response(
            json_encode(array(
                'success' => $this->validator->validateNotice($body))
            )
        );
    }

    public function readAll() {
        $crud = new NoticeCrud();

        return new Response(
            json_encode(array(
                'notices' => $crud->readAll()
            ))
        );
    }

    public function delete($id) {
        $crud = new NoticeCrud();

        return new Response(
            json_encode(array(
                'success' => $crud->delete($id)
            ))
        );
    }
}