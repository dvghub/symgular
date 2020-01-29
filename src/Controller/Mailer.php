<?php

namespace App\Controller;

use App\UserCrud;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class Mailer extends AbstractController {
    private $mailer;
    private $logger;

    public function __construct(MailerInterface $mailer, LoggerInterface $logger) {
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public function welcome($id) {
        $crud = new UserCrud();
        $employee = $crud->read($id);
        $email = (new TemplatedEmail())
            ->from(new Address('mailer@symgular.com')) // Change to dieuwer@ethlan.fr to actually send mail
            ->to(new Address($employee['email'])) // Change to own email address to test
            ->subject('Welcome to Symgular!')
            ->htmlTemplate('welcome.html.twig')
            ->context([
                'name' => $employee['first_name'],
                'mail' => $employee['email'],
                'id' => $employee['id']
            ]);
        try {
            $this->mailer->send($email);
            return new Response(json_encode(array(
                'success' => true
            )));
        } catch (TransportExceptionInterface $e) {
            $this->logger->info('Mailer failed with following message: '.$e);
            return new Response(json_encode(array(
                'success' => false
            )));
        }
    }
}
