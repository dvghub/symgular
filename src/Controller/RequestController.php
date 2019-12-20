<?php

namespace App\Controller;

use App\RequestCrud;
use App\UserCrud;
use DateInterval;
use DateTime;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestController {
    private $validator;
    private $start_hours = 200;

    public function __construct(LoggerInterface $logger) {
        $this->validator = new Validator($logger);
    }

    public function create() {
        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);

        return new Response(
            json_encode($this->validator->validateRequest($body))
        );
    }

    public function readAll() {
        $crud = new RequestCrud();

        return new Response(
            json_encode(array(
                'requests' => $crud->readAll()
            ))
        );
    }

    public function readMonth() {
        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);
        $response['success'] = false;

        if ($this->validator->validateEmail($body['email'])) {
            $email = $body['email'];

            $crud = new UserCrud();

            $id = $crud->readByEmail($email)->getId();

            $crud = new RequestCrud();

            $response['success'] = true;
            $response['days'] = $crud->readByMonth($body['month'], $body['year'], $body['department'], $id);
        }

        return new Response(
            json_encode($response)
        );
    }

    public function readEmployee() {
        $crud = new UserCrud();
        $response['success'] = false;

        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);

        $email = $this->validator->testInput($body['email']);

        $user = $crud->readByEmail($email);

        if ($this->validator->validateEmail($email)) {
            $hours = $this->hours($user->getId());
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

    public function read() {
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

    public function readUnapproved() {
        $crud = new RequestCrud();

        return new Response(
            json_encode(array(
                'requests' => $crud->readUnapproved()
            ))
        );
    }

    public function update() {
        $request = Request::createFromGlobals();
        $body = json_decode($request->getContent(), true);

        return new Response(
            json_encode(array(
                $this->validator->validateEdit($body)
            ))
        );
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

    public function delete() {
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

            $start = new DateTime(date($request['start']));
            $end = new DateTime(date($request['end']));

            if ($start->format('Y-m-d') < date('Y').'-01-01') $start = new DateTime(date('Y').'-01-01 09:00:00');
            if ($end->format('Y-m-d') > date('Y').'-12-31') $end = new DateTime(date('Y').'-12-31 17:00:00');

            if ($start->format('Y-m-d') == $end->format('Y-m-d')) {
                $result = $this->validator->getDayHours($start->format('Y-m-d'), $start->format('H:i'), $end->format('H:i'), $user->getDepartment());
                if (is_string($result)) {
                    $response['error'] = 'Something went wrong. Please refresh the page.';
                } else $hours += $result;
            } elseif ($end->format('Y-m-d') == $start->add(new DateInterval('P1D'))->format('Y-m-d')) {
                $result = $this->validator->getDayHours($start->format('Y-m-d'), $start->format('H:i'), '17:00', $user->getDepartment());
                if (is_string($result)) {
                    $response['error'] = 'Something went wrong. Please refresh the page.';
                } else $hours += $result;
                $result = $this->validator->getDayHours($end->format('Y-m-d'), '09:00', $end->format('H:i'), $user->getDepartment());
                if (is_string($result)) {
                    $response['error'] = 'Something went wrong. Please refresh the page.';
                } else $hours += $result;
            } else {
                $result = $this->validator->getDayHours($start->format('Y-m-d'), $start->format('H:i'), '17:00', $user->getDepartment());
                if (is_string($result)) {
                    $response['error'] = 'Something went wrong. Please refresh the page.';
                } else $hours += $result;

                $full_days = date_diff($start, $end)->format('%a');
                $result = ($full_days - 2) * 8;
                $hours += $result;

                $result = $this->validator->getDayHours($end->format('Y-m-d'), '09:00', $end->format('H:i'), $user->getDepartment());
                if (is_string($result)) {
                    $response['error'] = 'Something went wrong. Please refresh the page.';
                } else $hours += $result;
            }
        }

        if (!array_key_exists('error', $response)) {
            return $this->start_hours - $hours;
        } else return 0;
    }
}