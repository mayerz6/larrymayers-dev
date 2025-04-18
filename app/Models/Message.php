<?php
require_once '../core/Database.php';

class Message {
    private $db;

    public function __construct(){
        $this->db = (new Database())->getConnection();
    }
    public function save($data){
            $sql = "INSERT INTO messages (firstname, lastname, email, topic, message)
                    VALUES (:firstname, :lastname, :email, :topic, :message)";
    
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':firstname' => $data['firstname'],
                ':lastname'  => $data['lastname'],
                ':email'     => $data['email'],
                ':topic'     => $data['topic'],
                ':message'   => $data['message']
            ]);
        }

        public function fetch($data){
            
        }

}