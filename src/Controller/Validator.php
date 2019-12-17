<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Entity\Request;
use App\RequestCrud;
use App\UserCrud;
use DateInterval;
use DateTime;
use Psr\Log\LoggerInterface;

class Validator {
    private $logger;

    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    public function validateLogin($body) {
        $email = $this->testInput($body['email']);
        $password = $this->testInput($body['password']);
        $response['success'] = false;

        if (!$this->validateEmail($email)) {
            $response['email_error'] = 'Unknown email address.';
        } else {
            $crud = new UserCrud();
            $user = $crud->read($email);

            if (!password_verify($password, $user->getPassword())) {
                $response['password_error'] = 'Incorrect password.';
            } else {
                $_SESSION['user'] = $user;
                $response['success'] = true;
                $response['first_name'] = $user->getFirstName();
                $response['last_name'] = $user->getLastName();
                $response['department'] = $user->getDepartment();
                $response['birthday'] = $user->getBirthday();
                $response['admin'] = $user->getAdmin();
                $response['session_id'] = session_id();
            }
        }
        return $response;
    }

    public function validateRegister($body) {
        $first_name = $this->testInput($body['first_name']);
        $last_name = $this->testInput($body['last_name']);
        $email = $this->testInput($body['email']);
        $department = $this->testInput($body['department']);
        $birthday = $this->testInput($body['birthday']);
        $admin = $this->testInput($body['admin']);
        $response['success'] = false;

        if (empty($first_name)) {
            $response['first_name_error'] = 'Please enter a first name.';
        }
        if (empty($last_name)) {
            $response['last_name_error'] = 'Please enter a last name.';
        }
        if (empty($email)) {
            $response['email_error'] = 'Please enter an email address.';
        } else {
            if (!$this->validateEmail($email)) {
                $response['email_error'] = 'Please enter a valid email address.';
            } else {
                $crud = new UserCrud();
                $employee = new Employee();
                $employee->setFirstName($first_name);
                $employee->setLastName($last_name);
                $employee->setEmail($email);
                $employee->setPassword(password_hash('password', PASSWORD_BCRYPT, [10]));
                $employee->setDepartment($department);
                $employee->setBirthday($birthday);
                $admin = $admin ? 1 : 0;
                $employee->setAdmin($admin);

                if ($crud->create($employee)) {
                    $response['success'] = true;
                    $response['first_name'] = $first_name;
                } else {
                    $response['first_name_error'] = 'Something went wrong. Please try again.';
                }
            }
        }
        return $response;
    }

    public function validateUpdate($body) {
        $editor_admin = $this->testInput($body['editor_admin']);
        $email = $this->testInput($body['email']);
        $password_old = $this->testInput($body['password_old']);
        $password = $this->testInput($body['password']);
        $password_repeat = $this->testInput($body['password_repeat']);
        $department = $this->testInput($body['department']);
        $admin = $this->testInput($body['admin']);

        $response['success'] = false;
        $crud = new UserCrud();
        $user = $crud->read($email);
        $values = array();


        if (!$editor_admin) {
            if (empty($password_old)) {
                $response['old_password_error'] = 'Please enter your password.';
            } elseif (!password_verify($password_old, $user->getPassword())) {
                $response['old_password_error'] = 'Password incorrect.';
            }
            if (empty($password)) {
                $response['password_error'] = 'Please enter a new password.';
            } else {
                if (empty($password_repeat)) {
                    $response['repeat_password_error'] = 'Please repeat your new password.';
                } else {
                    if ($password != $password_repeat) {
                        $response['repeat_password_error'] = 'Passwords don\'t match.';
                    } else {
                        $values['password'] = password_hash($password, PASSWORD_BCRYPT, [10]);

                        if (!$crud->setup($values, $email)) {
                            $response['password_error'] = 'Something went wrong. Please try again.';
                        } else {
                            $response['success'] = true;
                        }
                    }
                }
            }
        } else {
            if (!empty($password)) {
                if (empty($password_repeat)) {
                    $response['repeat_password_error'] = 'Please repeat the new password.';
                } else {
                    if ($password != $password_repeat) {
                        $response['repeat_password_error'] = 'Passwords don\'t match.';
                    } else {
                        $values['password'] = password_hash($password, PASSWORD_BCRYPT, [10]);
                    }
                }
            }
            if ($department != $user->getDepartment()) $values['department'] = $department;
            if ($admin != $user->getAdmin()) $values['admin'] = $admin ? 1 : 0;
        }
        if (empty($response['old_password_error']) && empty($response['password_error']) && empty($response['repeat_password_error'])) {
            if (!$crud->setup($values, $email)) {
                $response['password_error'] = 'Something went wrong. Please try again.';
            } else {
                $response['success'] = true;
            }
        }
        return $response;
    }

    public function validateRequest($body) {
        $start_date = $this->testInput($body['start_date']);
        $start_time = $this->testInput($body['start_time']);
        $end_date = $this->testInput($body['end_date']);
        $end_time = $this->testInput($body['end_time']);
        $type = $this->testInput($body['type']);
        $description = $this->testInput($body['description']);
        $email = $this->testInput($body['email']);

        $response['success'] = false;
        $crud = new UserCrud();
        $user = $crud->read($email);
        $hours = 0;

        if(!$this->validateStartDate($start_date)) {
            $response['start_time_error'] = 'Start date must be in the future.';
        } elseif (!$this->validateEndDate($start_date, $end_date)) {
            $response['end_time_error'] = 'End date must be the same or after start date.';
        } else {
            if (empty($description)) {
                $response['description_error'] = 'Description is required.';
            }
            if (new DateTime($start_time) < new DateTime('08:00') || new DateTime($start_time) > new DateTime('17:00')) {
                $response['start_time_error'] = 'Start time must be between 08:00 and 17:00.';
            }
            if (new DateTime($end_time) > new DateTime('18:00') || new DateTime($end_time) < new DateTime('09:00')) {
                $response['end_time_error'] = 'End time must be between 09:00 and 18:00.';
            }
            if ($start_date == $end_date && new DateTime($start_time) > new DateTime($end_time)) {
                $response['end_time_error'] = 'End time must be after start time.';
            }
            if (!key_exists('start_time_error', $response) &&
                !key_exists('end_time_error', $response) &&
                !key_exists('description_error', $response)) {

                if (!$this->containsOverlap($start_date.' '.$start_time, $end_date.' '.$end_time, $user->getId()) || $type == 'standard') {
                    switch ($type) {
                        case 'pto':
                        case 'special':
                            if ($start_date == $end_date) {
                                $result = $this->getDayHours($start_date, $start_time, $end_time, $user->getDepartment());
                                if (!is_string($result)) {
                                    $hours += $result;
                                    $response = $this->enterRequest($start_date, $start_time, $end_date, $end_time, $type, $description, $user, $hours);
                                } else {
                                    $response['description_error'] = $result;
                                }
                            } elseif (new DateTime($end_date) == date_add(new DateTime($start_date), new DateInterval('P1D'))) {
                                $result = $this->getDayHours($start_date, $start_time, '17:00', $user->getDepartment());
                                if (!is_string($result)) {
                                    $hours += $result;
                                    $result = $this->getDayHours($end_date, '09:00', $end_time, $user->getDepartment());
                                    if (!is_string($result)) {
                                        $hours += $result;
                                        $response = $this->enterRequest($start_date, $start_time, $end_date, $end_time, $type, $description, $user, $hours);
                                    }
                                } else {
                                    $response['description_error'] = $result;
                                }
                            } else {
                                $result = $this->getDayHours($start_date, $start_time, '17:00', $user->getDepartment());

                                if (!is_string($result)) {
                                    $hours += $result;

                                    $start = new DateTime($start_date);
                                    $end = new DateTime($end_date);
                                    $date = $start->add(new DateInterval("P1D"));
                                    $full_days = date_diff($start, $end)->format('%a');

                                    for ($i = 0; $i < $full_days; $i ++) {
                                        $date = $date->add(new DateInterval("P".$i."D"));
                                        $result = $this->getDayHours($date->format('Y-m-d'), '09:00', '17:00', $user->getDepartment());

                                        if (!is_string($result)) {
                                            $hours += $result;
                                        } else {
                                            $response['description_error'] = $result;
                                        }
                                    }

                                    if (!key_exists('description_error', $response)) {
                                        $result = $this->getDayHours($end_date, '09:00', $end_time, $user->getDepartment());

                                        if (!is_string($result)) {
                                            $hours += $result;

                                            $this->logger->info("End result: ".$hours);
                                            $response = $this->enterRequest($start_date, $start_time, $end_date, $end_time, $type, $description, $user, $hours);
                                        } else {
                                            $response['description_error'] = $result;
                                        }
                                    }
                                } else {
                                    $response['description_error'] = $result;
                                }
                            }
                            break;
                        case 'appointment':
                            if ($start_date != $end_date) {
                                $response['end_time_error'] = 'This type of leave can not be longer than one day.';
                            } else {
                                $result = $this->getDayHours($start_date, $start_time, $end_time, $user->getDepartment());
                                if (intval($result)) {
                                    $hours += $result;
                                    $response = $this->enterRequest($start_date, $start_time, $end_date, $end_time, $type, $description, $user, $hours);
                                } else {
                                    $response['description_error'] = $result;
                                }
                            }
                            break;
                        case 'standard':
                            if ($start_date != $end_date) {
                                $response['end_time_error'] = 'This type of leave can not be longer than one day.';
                            } else {
                                if ($this->isWeekendDay(new DateTime($start_date))) {
                                    $result = 0;
                                } else {
                                    $result = round(abs(strtotime($start_time) - strtotime($end_time)) / 3600, 1);
                                }
                                if (intval($result)) {
                                    $hours += $result;
                                    $request = new Request();

                                    $request->setStartDate($start_date.' '.$start_time.':00');
                                    $request->setEndDate($end_date.' '.$end_time.':00');
                                    $request->setType($type);
                                    $request->setDescription($description);
                                    $request->setApproved(1);
                                    $request->setEditable(0);
                                    $request->setStandard(1);

                                    foreach ($crud->readAll() as $employee) {
                                        $request->setEmployeeId($employee['id']);
                                        $crud = new RequestCrud();

                                        if ($crud->create($request)) {
                                            $user_hours = ($employee['hours']/10) - $hours;
                                            $values = array('hours' => $user_hours * 10);
                                            $crud = new UserCrud();

                                            if ($crud->setup($values, $employee['email'])) {
                                                $response['success'] = true;
                                            } else {
                                                $response['description_error'] = 'Something went wrong. Please try again.';
                                            }
                                        }
                                    }
                                } else {
                                    $response['description_error'] = $result;
                                }
                            }
                    }
                } else {
                    $response['description_error'] = 'Request overlaps with existing request.';
                }
            }
        }
        return $response;
    }

    public function validateEdit($body) {
        $start_date = $this->testInput($body['start_date']);
        $start_time = $this->testInput($body['start_time']);
        $end_date = $this->testInput($body['end_date']);
        $end_time = $this->testInput($body['end_time']);
        $description = $this->testInput($body['description']);
        $email = $this->testInput($body['email']);
        $id = $this->testInput($body['id']);

        $response['success'] = false;
        $crud = new UserCrud();
        $user = $crud->read($email);

        if(!$this->validateStartDate($start_date)) {
            $response['start_time_error'] = 'Start date must be in the future.';
        } elseif (!$this->validateEndDate($start_date, $end_date)) {
            $response['end_time_error'] = 'End date must be the same or after start date.';
        } else {
            if (empty($description)) {
                $response['description_error'] = 'Description is required.';
            }
            if (new DateTime($start_time) < new DateTime('08:00') || new DateTime($start_time) > new DateTime('17:00')) {
                $response['start_time_error'] = 'Start time must be between 08:00 and 17:00.';
            }
            if (new DateTime($end_time) > new DateTime('18:00') || new DateTime($end_time) < new DateTime('09:00')) {
                $response['end_time_error'] = 'End time must be between 09:00 and 18:00.';
            }
            if ($start_date == $end_date && new DateTime($start_time) > new DateTime($end_time)) {
                $response['end_time_error'] = 'End time must be after start time.';
            }
            if (!key_exists('start_time_error', $response) &&
                !key_exists('end_time_error', $response) &&
                !key_exists('description_error', $response)) {

                if (!$this->containsOverlap($start_date.' '.$start_time, $end_date.' '.$end_time, $user->getId(), $id)) {
                    $start = new DateTime($start_date);
                    $end = new DateTime($end_date);
                    $date = $start->add(new DateInterval("P1D"));
                    $full_days = date_diff($start, $end)->format('%a');

                    for ($i = 0; $i < $full_days; $i ++) {
                        $date = $date->add(new DateInterval("P".$i."D"));
                        $result = $this->getDayHours($date->format('Y-m-d'), '09:00', '17:00', $user->getDepartment());

                        if (is_string($result)) {
                            $response['description_error'] = $result;
                        }
                    }
                    if (!key_exists('description_error', $response)) {
                        $result = $this->getDayHours($end_date, '09:00', $end_time, $user->getDepartment());

                        if (!is_string($result)) {
                            $values['start'] = $start_date.' '.$start_time.':00';
                            $values['end'] = $end_date.' '.$end_time.':00';
                            $values['description'] = $description;

                            $crud = new RequestCrud();
                            $response['success'] = $crud->setup($values, $id);
                        } else {
                            $response['description_error'] = $result;
                        }
                    }
                } else {
                    $response['description_error'] = 'Request overlaps with existing request.';
                }
            }
        }
        return $response;
    }

    private function getDayHours($date, $start, $end, $department) {
        $full = $this->isFull($date, $department);
        if (!is_string($full)) {
            if (!$full) {
                if ($this->isWeekendDay(new DateTime($date)) || $this->isStandardDay($date)) {
                    $diff = 0;
                } else {
                    $diff = round(abs(strtotime($start) - strtotime($end)) / 3600, 1);
                }
            } else {
                $diff = 'Request overlaps with fully booked day.';
            }
        } else {
            $diff = 'Something went wrong, please try again.';
        }
        return $diff;
    }

    private function isWeekendDay(DateTime $date) {
        return $date->format('N') >= 6;
    }

    private function isStandardDay($date) {
        $crud = new RequestCrud();
        $result = $crud->readStandard($date);
        return count($result) > 0;
    }

    private function isFull($date, $department) {
        $crud = new RequestCrud();
        return $crud->readFull($date, $department);
    }

    private function containsOverlap($start, $end, $id, $request_id = null) {
        $crud = new RequestCrud();
        $result = $crud->readOverlap($start, $end, $id, $request_id);
        return count($result) > 0;
    }

    private function enterRequest($start_date, $start_time, $end_date, $end_time, $type, $description, Employee $user, $hours) {
        $user_hours = $user->getHours() - $hours;
        if ($user_hours < 0) {
            $response['description_error'] = 'Not enough hours available.';
        } else {
            $request = new Request();

            $request->setEmployeeId($user->getId());
            $request->setStartDate($start_date.' '.$start_time.':00');
            $request->setEndDate($end_date.' '.$end_time.':00');
            $request->setType($type);
            $request->setDescription($description);

            $crud = new RequestCrud();

            $request_id = $crud->create($request);

            if ($request_id > 0) {
                if ($type == 'pto') {
                    $crud = new UserCrud();

                    $this->logger->info("User hours after sub: ".$user_hours);

                    $values = array('hours' => $user_hours * 10);

                    if ($crud->setup($values, $user->getEmail())) {
                        $response['success'] = true;
                        $response['hours'] = $user_hours;
                    } else {
                        $response['description_error'] = 'Something went wrong. Please try again.';
                    }
                } else {
                    $response['success'] = true;
                    $response['hours'] = $user->getHours();
                }
            } else {
                $response['description_error'] = 'Something went wrong. Please try again.';
            }
        }
        return $response;
    }

    private function validateStartDate($date) {
        return $date > date('Y-m-d');
    }

    private function validateEndDate($start_date, $end_date) {
        return $end_date >= $start_date;
    }

    public function validateEmail($email) {
        return preg_match('/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD', $email) == 1 ? true : false;
    }

    public function testInput($data) {
        $data = trim($data);
        $data = addslashes($data);
        $data = htmlentities($data);
        return $data;
    }
}
