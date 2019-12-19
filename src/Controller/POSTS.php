<?php

namespace App\Controller;

use App\NoticeCrud;
use App\RequestCrud;
use App\UserCrud;
use DateInterval;
use DateTime;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class POSTS extends AbstractController {
    private $validator;
    private $logger;
    private $start_hours = 200;

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

    public function requestById() {
        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);
        $crud = new RequestCrud();
        $request = $crud->read($body['id']);

        $splits = explode(' ', $request['start']);
        $request['start_date'] = $splits[0];
        $request['start_time'] = substr($splits[1], 0, 5);
        $splits = explode(' ', $request['end']);
        $request['end_date'] = $splits[0];
        $request['end_time'] = substr($splits[1], 0, 5);

        return new Response(
            json_encode($request)
        );
    }

    public function requestsByMonth() {
        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);

        if ($this->validator->validateEmail($body['email'])) {
            $email = $body['email'];

            $crud = new UserCrud();

            $id = $crud->readByEmail($email)->getId();

            $crud = new RequestCrud();

            $response['success'] = true;
            $response['days'] = $crud->readByMonth($body['month'], $body['year'], $body['department'], $id);
            $this->logger->info(json_encode($response['days']));
        } else {
            $response['success'] = false;
        }

        return new Response(
            json_encode($response)
        );
    }

    public function requestsByEmployee() {
        $crud = new UserCrud();
        $response['success'] = false;

        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);

        $email = $this->validator->testInput($body['email']);

        $user = $crud->readByEmail($email);

        if ($this->validator->validateEmail($email)) {
            $hours = $this->hours($user->getId());
            $this->logger->info(json_encode($hours));
            if (!is_string($hours)) {
                $crud = new RequestCrud();
                $response['success'] = true;
                $response['requests'] = $crud->readByEmployee($user->getId());
                $response['hours'] = $hours;
            }
        }

        return new Response(
            json_encode($response)
        );
    }

    public function hours($id) {
        $hours = 0;
        $response['success'] = false;
        $crud = new UserCrud();
        $user = $crud->read($id);
        $crud = new RequestCrud();
        $requests = $crud->readByEmployee($id);

        $requests = array_filter($requests, function ($v) {
            return $v['type'] == 'pto';
        });

        foreach ($requests as $request) {
            $this->logger->info('Processing request '.$request['id'].'...');

            $start = new DateTime(date($request['start']));
            $end = new DateTime(date($request['end']));

            if ($start->format('Y-m-d') < date('Y').'-01-01') $start = new DateTime(date('Y').'-01-01 09:00:00');
            if ($end->format('Y-m-d') > date('Y').'-12-31') $end = new DateTime(date('Y').'-12-31 17:00:00');

            if ($start->format('Y-m-d') == $end->format('Y-m-d')) {
                $this->logger->info('Counting one day...');
                $result = $this->validator->getDayHours($start->format('Y-m-d'), $start->format('H:i'), $end->format('H:i'), $user->getDepartment());
                if (is_string($result)) {
                    $response['error'] = 'Something went wrong. Please refresh the page.';
                } else $hours += $result;
                $this->logger->info('Hours one day: '.$result.' -- Hours total: '.$hours);
            } elseif ($end->format('Y-m-d') == $start->add(new DateInterval('P1D'))->format('Y-m-d')) {
                $this->logger->info('Counting two days...');
                $result = $this->validator->getDayHours($start->format('Y-m-d'), $start->format('H:i'), '17:00', $user->getDepartment());
                if (is_string($result)) {
                    $response['error'] = 'Something went wrong. Please refresh the page.';
                } else $hours += $result;
                $this->logger->info('Hours two days -- day one: '.$result.' -- Hours total: '.$hours);
                $result = $this->validator->getDayHours($end->format('Y-m-d'), '09:00', $end->format('H:i'), $user->getDepartment());
                if (is_string($result)) {
                    $response['error'] = 'Something went wrong. Please refresh the page.';
                } else $hours += $result;
                $this->logger->info('Hours two days -- day two: '.$result.' -- Hours total: '.$hours);
            } else {
                $this->logger->info('Counting 3+ days...');
                $result = $this->validator->getDayHours($start->format('Y-m-d'), $start->format('H:i'), '17:00', $user->getDepartment());
                if (is_string($result)) {
                    $response['error'] = 'Something went wrong. Please refresh the page.';
                } else $hours += $result;
                $this->logger->info('Hours 3+ days -- day one: '.$result.' -- Hours total: '.$hours);

                $full_days = date_diff($start, $end)->format('%a');
                $result = ($full_days - 2) * 8;
                $this->logger->info('Hours 3+ days -- middle count: '.$result.' -- Hours total: '.$hours);
                $hours += $result;

                $result = $this->validator->getDayHours($end->format('Y-m-d'), '09:00', $end->format('H:i'), $user->getDepartment());
                if (is_string($result)) {
                    $response['error'] = 'Something went wrong. Please refresh the page.';
                } else $hours += $result;
                $this->logger->info('Hours 3+ days -- last day: '.$result.' -- Hours total: '.$hours);
            }
        }

        if (!array_key_exists('error', $response)) {
            return $this->start_hours - $hours;
        } else return 0;
    }

    public function approve() {
        $crud = new RequestCrud();
        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);
        $id = $this->validator->testInput($body['id']);

        return new Response(
            json_encode(array(
                'response' => $crud->setup(array('approved' => 1), $id)
            ))
        );
    }

    public function deny() {
        $crud = new RequestCrud();
        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);
        $id = $this->validator->testInput($body['id']);

        return new Response(
            json_encode(array(
                'response' => $crud->delete($id)
            ))
        );
    }

    public function deleteRequest() {
        $crud = new RequestCrud();
        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);
        $id = $this->validator->testInput($body['id']);

        return new Response(
            json_encode(array(
                'response' => $crud->delete($id)
            ))
        );
    }

    public function editRequest() {
        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);

        return new Response(
            json_encode(array(
                $this->validator->validateEdit($body)
            ))
        );
    }

    public function notice() {
        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);

        return new Response(
            json_encode(array(
                'success' => $this->validator->validateNotice($body)
            ))
        );
    }

    public function deleteNotice() {
        $crud = new NoticeCrud();
        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);

        $id = $this->validator->testInput($body['id']);

        return new Response(
            json_encode(array(
                'success' => $crud->delete($id)
            ))
        );
    }
}
