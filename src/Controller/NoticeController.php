<?php

namespace App\Controller;

use App\NoticeCrud;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class NoticeController {
    private $validator;

    public function __construct(LoggerInterface $logger) {
        $this->validator = new Validator($logger);
    }

    public function readAll() {
    $crud = new NoticeCrud();

    return new Response(
        json_encode(array(
            'notices' => $crud->readAll()
        ))
    );
}
}