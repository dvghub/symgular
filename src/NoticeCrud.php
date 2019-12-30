<?php

namespace App;

use App\Entity\Notice;
use PDO;

class NoticeCrud {
    private $conn;

    public function __construct() {
        $this->conn = $this->connect();
    }

    public function connect() {
        $db_host = '127.0.0.1';     //Something something ip config (makes website fast)
        $db_username = 'sql_manager';
        $db_password = 'lookatmeimastrongpassword';
        $db_name = 'symgular_database';
        $conn = new PDO('mysql:host='.$db_host.';dbname='.$db_name, $db_username, $db_password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute( PDO::ATTR_EMULATE_PREPARES, false ); //Disables automatic stringifying
        return $conn;
    }

    public function create(Notice $notice) {
        $stmt = $this->conn->prepare("INSERT INTO notices (title, message, creator)
                                          VALUES (:title, :message, :creator)");
        $stmt->bindValue(':title', $notice->getTitle());
        $stmt->bindValue(':message', $notice->getMessage());
        $stmt->bindValue(':creator', $notice->getCreator());
        return $stmt->execute();
    }

    public function read($id) {
        $stmt = $this->conn->prepare('SELECT * FROM notices
                                          WHERE id = :id');
        $stmt->bindValue(':id', $id);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
        $notice = new Notice();
        $notice->setId($result['id']);
        $notice->setTitle($result['title']);
        $notice->setMessage($result['message']);
        $notice->setCreator($result['creator']);
        $notice->setTimestamp($result['timestamp']);

        return $notice;
    }

    public function readAll() {
        $stmt = $this->conn->prepare("SELECT * FROM notices");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($id) {
        $stmt = $this->conn->prepare('DELETE FROM notices
                                          WHERE id = :id');
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }
}
