<?php

namespace App\Controller;

use App\Entity\Employee;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController {
    public function create(Employee $employee) {
        $entity_manager = $this->getDoctrine()->getManager();

        $entity_manager->persist($employee);
        $entity_manager->flush();

        return new Response(
            $employee->getFirstName()
        );
    }

    public function read(int $id) {
        $entity_manager = $this->getDoctrine()->getRepository(Employee::class);
        $entity_manager->find($id);
    }
}
