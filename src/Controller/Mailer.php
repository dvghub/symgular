<?php

namespace App\Controller;

use App\UserCrud;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class Mailer extends AbstractController {
    private $mailer;

    public function __construct(MailerInterface $mailer) {
        $this->mailer = $mailer;
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
        $this->mailer->send($email);
        return new Response();
    }

    public function test() {
        $email = (new Email())
            ->from(new Address('dieuwer@ethlan.fr'))
            ->to(new Address('dieuwer.greevenbroek@gmail.com'))
            ->subject('Mailer test')
            ->text('Ayy');
        $this->mailer->send($email);
        return new Response();
    }
}
