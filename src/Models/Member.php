<?php

namespace Models;

use PDO;    
use Core\Database;

class Member
{

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public static function getAll()
    {
        return $this->db->query("SELECT * FROM MEMBER");

    }

    public function find($memberId) {
        $stmt = $this->db->prepare("SELECT * FROM MEMBER WHERE member_id = ?");
        $stmt->execute([$memberId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
