<?php

namespace Models;

use Core\Database;

class Subscription
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createSubscription($memberId, $subscriptionType, $startDate, $endDate, $amount)
    {
        $stmt = $this->db->prepare("INSERT INTO SUBSCRIPTION (member_id, subscription_type, start_date, end_date, amount) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$memberId, $subscriptionType, $startDate, $endDate, $amount]);
    }

    public function hasActiveSubscription($memberId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM SUBSCRIPTION WHERE member_id = ? AND status = 'Active'");
        $stmt->execute([$memberId]);
        return $stmt->fetchColumn() > 0;
    }

}
