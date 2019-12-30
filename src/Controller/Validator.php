<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Entity\Notice;
use App\Entity\Request;
use App\NoticeCrud;
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
            $response['emailError'] = 'Unknown email address.';
        } else {
            $crud = new UserCrud();
            $user = $crud->readByEmail($email);

            if (!password_verify($password, $user->getPassword())) {
                $response['passwordError'] = 'Incorrect password.';
            } else {
                $_SESSION['user'] = $user;
                $response['success'] = true;
                $response['user']['id'] = $user->getId();
                $response['user']['firstName'] = $user->getFirstName();
                $response['user']['lastName'] = $user->getLastName();
                $response['user']['department'] = $user->getDepartment();
                $response['user']['birthday'] = $user->getBirthday();
                $response['user']['admin'] = $user->getAdmin();
                $response['sessionId'] = session_id();
            }
        }
        return $response;
    }

    public function validateRegister($body) {
        $firstName = $this->testInput($body['first_name']);
        $lastName = $this->testInput($body['last_name']);
        $email = $this->testInput($body['email']);
        $department = $this->testInput($body['department']);
        $birthday = $this->testInput($body['birthday']);
        $admin = $this->testInput($body['admin']);
        $response['success'] = false;

        if (empty($firstName)) {
            $response['firstNameError'] = 'Please enter a first name.';
        }
        if (empty($lastName)) {
            $response['lastNameError'] = 'Please enter a last name.';
        }
        if (empty($email)) {
            $response['emailError'] = 'Please enter an email address.';
        } else {
            if (!$this->validateEmail($email)) {
                $response['emailError'] = 'Please enter a valid email address.';
            } else {
                $crud = new UserCrud();
                $employee = new Employee();
                $employee->setFirstName($firstName);
                $employee->setLastName($lastName);
                $employee->setEmail($email);
                $employee->setPassword(password_hash('password', PASSWORD_BCRYPT, [10]));
                $employee->setDepartment($department);
                $employee->setBirthday($birthday);
                $admin = $admin ? 1 : 0;
                $employee->setAdmin($admin);

                if ($crud->create($employee)) {
                    $response['success'] = true;
                } else {
                    $response['firstNameError'] = 'Something went wrong. Please try again.';
                }
            }
        }
        return $response;
    }

    public function validateUpdate($body, $id) {
        $editorAdmin = $this->testInput($body['editor_admin']);
        $password_old = $this->testInput($body['password_old']);
        $password = $this->testInput($body['password']);
        $password_repeat = $this->testInput($body['password_repeat']);
        $department = $this->testInput($body['department']);
        $admin = $this->testInput($body['admin']);

        $response['success'] = false;
        $crud = new UserCrud();
        $user = $crud->read($id);
        $values = array();


        if (!$editorAdmin) {
            if (empty($password_old)) {
                $response['oldPasswordError'] = 'Please enter your password.';
            } elseif (!password_verify($password_old, $user['password'])) {
                $response['oldPasswordError'] = 'Password incorrect.';
            }
            if (empty($password)) {
                $response['passwordError'] = 'Please enter a new password.';
            } else {
                if (empty($password_repeat)) {
                    $response['repeatPasswordError'] = 'Please repeat your new password.';
                } else {
                    if ($password != $password_repeat) {
                        $response['repeatPasswordError'] = 'Passwords don\'t match.';
                    } else {
                        $values['password'] = password_hash($password, PASSWORD_BCRYPT, [10]);

                        if (!$crud->setup($values, $id)) {
                            $response['passwordError'] = 'Something went wrong. Please try again.';
                        } else {
                            $response['success'] = true;
                        }
                    }
                }
            }
        } else {
            if (!empty($password)) {
                if (empty($password_repeat)) {
                    $response['repeatPasswordError'] = 'Please repeat the new password.';
                } else {
                    if ($password != $password_repeat) {
                        $response['repeatPasswordError'] = 'Passwords don\'t match.';
                    } else {
                        $values['password'] = password_hash($password, PASSWORD_BCRYPT, [10]);
                    }
                }
            }
            if ($department != $user['password']) $values['department'] = $department;
            if ($admin != $user['admin']) $values['admin'] = $admin ? 1 : 0;
        }
        if (empty($response['oldPasswordError']) && empty($response['passwordError']) && empty($response['repeatPasswordError'])) {
            if (!$crud->setup($values, $id)) {
                $response['passwordError'] = 'Something went wrong. Please try again.';
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
        $user = $crud->readByEmail($email);
        $hours = 0;

        if(!$this->validateStartDate($start_date)) {
            $response['startTimeError'] = 'Start date must be in the future.';
        } elseif (!$this->validateEndDate($start_date, $end_date)) {
            $response['endTimeError'] = 'End date must be the same or after start date.';
        } else {
            if (empty($description)) {
                $response['descriptionError'] = 'Description is required.';
            }
            if (new DateTime($start_time) < new DateTime('08:00') || new DateTime($start_time) > new DateTime('17:00')) {
                $response['startTimeError'] = 'Start time must be between 08:00 and 17:00.';
            }
            if (new DateTime($end_time) > new DateTime('18:00') || new DateTime($end_time) < new DateTime('09:00')) {
                $response['endTimeError'] = 'End time must be between 09:00 and 18:00.';
            }
            if ($start_date == $end_date && new DateTime($start_time) > new DateTime($end_time)) {
                $response['endTimeError'] = 'End time must be after start time.';
            }
            if (!key_exists('startTimeError', $response) &&
                !key_exists('endTimeError', $response) &&
                !key_exists('descriptionError', $response)) {

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
                                    $response['descriptionError'] = $result;
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
                                    $response['descriptionError'] = $result;
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
                                            $response['descriptionError'] = $result;
                                        }
                                    }

                                    if (!key_exists('descriptionError', $response)) {
                                        $result = $this->getDayHours($end_date, '09:00', $end_time, $user->getDepartment());

                                        if (!is_string($result)) {
                                            $hours += $result;

                                            $this->logger->info("End result: ".$hours);
                                            $response = $this->enterRequest($start_date, $start_time, $end_date, $end_time, $type, $description, $user, $hours);
                                        } else {
                                            $response['descriptionError'] = $result;
                                        }
                                    }
                                } else {
                                    $response['descriptionError'] = $result;
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
                                    $response['descriptionError'] = $result;
                                }
                            }
                            break;
                        case 'standard':
                            if ($start_date != $end_date) {
                                $response['end_timeError'] = 'This type of leave can not be longer than one day.';
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
                                                $response['descriptionError'] = 'Something went wrong. Please try again.';
                                            }
                                        }
                                    }
                                } else {
                                    $response['descriptionError'] = $result;
                                }
                            }
                    }
                } else {
                    $response['descriptionError'] = 'Request overlaps with existing request.';
                }
            }
        }
        return $response;
    }

    public function validateEdit($body, $id) {
        $start_date = $this->testInput($body['start_date']);
        $start_time = $this->testInput($body['start_time']);
        $end_date = $this->testInput($body['end_date']);
        $end_time = $this->testInput($body['end_time']);
        $description = $this->testInput($body['description']);
        $email = $this->testInput($body['email']);

        $response['success'] = false;
        $crud = new UserCrud();
        $user = $crud->readByEmail($email);

        if(!$this->validateStartDate($start_date)) {
            $response['startTimeError'] = 'Start date must be in the future.';
        } elseif (!$this->validateEndDate($start_date, $end_date)) {
            $response['endTimeError'] = 'End date must be the same or after start date.';
        } else {
            if (empty($description)) {
                $response['descriptionError'] = 'Description is required.';
            }
            if (new DateTime($start_time) < new DateTime('08:00') || new DateTime($start_time) > new DateTime('17:00')) {
                $response['startTimeError'] = 'Start time must be between 08:00 and 17:00.';
            }
            if (new DateTime($end_time) > new DateTime('18:00') || new DateTime($end_time) < new DateTime('09:00')) {
                $response['endTimeError'] = 'End time must be between 09:00 and 18:00.';
            }
            if ($start_date == $end_date && new DateTime($start_time) > new DateTime($end_time)) {
                $response['endTimeError'] = 'End time must be after start time.';
            }
            if (!key_exists('startTimeError', $response) &&
                !key_exists('endTimeError', $response) &&
                !key_exists('descriptionError', $response)) {

                if (!$this->containsOverlap($start_date.' '.$start_time, $end_date.' '.$end_time, $user->getId(), $id)) {
                    $start = new DateTime($start_date);
                    $end = new DateTime($end_date);
                    $result = $this->getDayHours($start->format('Y-m-d'), $start_time, '17:00', $user->getDepartment());

                    if (is_string($result)) {
                        $response['descriptionError'] = $result;
                    }

                    $date = $start->add(new DateInterval("P1D"));
                    $full_days = date_diff($start, $end)->format('%a');

                    for ($i = 0; $i < $full_days; $i ++) {
                        $date = $date->add(new DateInterval("P".$i."D"));
                        $result = $this->getDayHours($date->format('Y-m-d'), '09:00', '17:00', $user->getDepartment());

                        if (is_string($result)) {
                            $response['descriptionError'] = $result;
                        }
                    }
                    if (!key_exists('descriptionError', $response)) {
                        $result = $this->getDayHours($end->format('Y-m-d'), '09:00', $end_time, $user->getDepartment());

                        if (!is_string($result)) {
                            $values['start'] = $start_date.' '.$start_time.':00';
                            $values['end'] = $end_date.' '.$end_time.':00';
                            $values['description'] = $description;

                            $crud = new RequestCrud();
                            $response['success'] = $crud->setup($values, $id);
                        } else {
                            $response['descriptionError'] = $result;
                        }
                    }
                } else {
                    $response['descriptionError'] = 'Request overlaps with existing request.';
                }
            }
        }
        return $response;
    }

    public function validateNotice($body) {
        $notice = new Notice();
        $notice->setTitle($this->testInput($body['title']));
        $notice->setMessage($this->testInput($body['message']));

        $crud = new UserCrud();
        $name = $crud->readByEmail($this->testInput($body['email']))->getFirstName();

        $notice->setCreator($name);

        $crud = new NoticeCrud();
        return $crud->create($notice);
    }

    public function getDayHours(String $date, $start, $end, $department) {
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

    private function isStandardDay(String $date) {
        $crud = new RequestCrud();
        $result = $crud->readStandard($date);
        return count($result) > 0;
    }

    private function isFull(String $date, $department) {
        $crud = new RequestCrud();
        return $crud->readFull($date, $department);
    }

    private function containsOverlap($start, $end, $id, $request_id = null) {
        $crud = new RequestCrud();
        $result = $crud->readOverlap($start, $end, $id, $request_id);
        return count($result) > 0;
    }

    private function enterRequest($start_date, $start_time, $end_date, $end_time, $type, $description, Employee $user, $hours) {
        $crud = new RequestCrud();
        $posts = new RequestController($this->logger);
        $user_hours = $posts->hours($user->getId()) - $hours;
        if ($user_hours < 0) {
            $response['descriptionError'] = 'Not enough hours available.';
        } else {
            $request = new Request();

            $request->setEmployeeId($user->getId());
            $request->setStartDate($start_date.' '.$start_time.':00');
            $request->setEndDate($end_date.' '.$end_time.':00');
            $request->setType($type);
            $request->setDescription($description);

            $request_id = $crud->create($request);

            if ($request_id > 0) {
                $response['success'] = true;
                $response['hours'] = $user_hours;
            } else {
                $response['descriptionError'] = 'Something went wrong. Please try again.';
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
