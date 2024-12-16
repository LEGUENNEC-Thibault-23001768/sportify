<?php

namespace Models;

use Core\Database;
use Core\Config;
use Stripe\Stripe;

class Subscription
{
    private static function getDb()
    {
        return Database::getConnection();
    }

    public static function syncSubscriptionWithStripe($memberId)
    {
        $localSubscription = self::getStripeSubscriptionId($memberId);
        if (!$localSubscription) {
            return false;
        }

        try {
            Stripe::setApiKey(Config::get('stripe_key'));
            $stripeSubscription = \Stripe\Subscription::retrieve($localSubscription['stripe_subscription_id']);
            
            $status = $stripeSubscription->status;
            $endDate = date('Y-m-d', $stripeSubscription->current_period_end);
            $amount = $stripeSubscription->plan->amount / 100;

            $sql = "UPDATE SUBSCRIPTION SET status = :status, end_date = :end_date, amount = :amount WHERE stripe_subscription_id = :stripe_subscription_id";
            $params = [
                ':status' => $status,
                ':end_date' => $endDate,
                ':amount' => $amount,
                ':stripe_subscription_id' => $localSubscription['stripe_subscription_id']
            ];
            return Database::query($sql, $params)->rowCount() > 0;
        } catch (\Exception $e) {
            error_log('Stripe error: ' . $e->getMessage());
            return false;
        }
    }

    public static function createSubscription($memberId, $stripeSubscriptionId, $subscriptionType, $startDate, $endDate, $amount)
    {
        $sql = "INSERT INTO SUBSCRIPTION (member_id, stripe_subscription_id, subscription_type, start_date, end_date, amount, status) VALUES (:member_id, :stripe_subscription_id, :subscription_type, :start_date, :end_date, :amount, 'Active')";
        $params = [
            ':member_id' => $memberId,
            ':stripe_subscription_id' => $stripeSubscriptionId,
            ':subscription_type' => $subscriptionType,
            ':start_date' => $startDate,
            ':end_date' => $endDate,
            ':amount' => $amount
        ];
        return Database::query($sql, $params)->rowCount() > 0;
    }

    public static function updateSubscriptionStatus($stripeSubscriptionId, $status)
    {
        $sql = "UPDATE SUBSCRIPTION SET status = :status WHERE stripe_subscription_id = :stripe_subscription_id";
        $params = [
            ':status' => $status,
            ':stripe_subscription_id' => $stripeSubscriptionId
        ];
        return Database::query($sql, $params)->rowCount() > 0;
    }

    public static function hasActiveSubscription($memberId)
    {
        self::syncSubscriptionWithStripe($memberId);
        $sql = "SELECT COUNT(*) FROM SUBSCRIPTION WHERE member_id = :member_id AND (status = 'Active' OR status = 'Cancelling')";
        $params = [':member_id' => $memberId];
        return Database::query($sql, $params)->fetchColumn() > 0;
    }

    public static function getActiveSubscription($memberId)
    {
        $sql = "SELECT * FROM SUBSCRIPTION WHERE member_id = :member_id AND status = 'Active' ORDER BY start_date DESC LIMIT 1";
        $params = [':member_id' => $memberId];
        return Database::query($sql, $params)->fetch();
    }

    public static function getStripeSubscriptionId($userId)
    {   
        $sql = "SELECT * FROM SUBSCRIPTION WHERE member_id = :user_id ORDER BY start_date DESC LIMIT 1";
        $params = [':user_id' => $userId];
        $result = Database::query($sql, $params)->fetch();
        return $result ? $result : null;
    }

    public static function updateSubscription($memberId, $stripeSubscriptionId, $subscriptionType, $startDate, $endDate, $amount)
    {
        $sql = "UPDATE SUBSCRIPTION SET stripe_subscription_id = :stripe_subscription_id, subscription_type = :subscription_type, start_date = :start_date, end_date = :end_date, amount = :amount, status = 'Active' WHERE member_id = :member_id AND status IN ('Active', 'Cancelling')";
        $params = [
            ':stripe_subscription_id' => $stripeSubscriptionId,
            ':subscription_type' => $subscriptionType,
            ':start_date' => $startDate,
            ':end_date' => $endDate,
            ':amount' => $amount,
            ':member_id' => $memberId
        ];
        return Database::query($sql, $params)->rowCount() > 0;
    }

    public static function updateSubscriptionDetails($memberId, $subscriptionType, $startDate, $endDate, $amount)
    {
        $userSubscription = self::getStripeSubscriptionId($memberId);
        if (!$userSubscription) {
        }

        $stripeSubscriptionId = $userSubscription['stripe_subscription_id'];

        $sql = "UPDATE SUBSCRIPTION SET subscription_type = :subscription_type, start_date = :start_date, end_date = :end_date, amount = :amount WHERE member_id = :member_id AND stripe_subscription_id = :stripe_subscription_id";
        $params = [
            ':subscription_type' => $subscriptionType,
            ':start_date' => $startDate,
            ':end_date' => $endDate,
            ':amount' => $amount,
            ':member_id' => $memberId,
            ':stripe_subscription_id' => $stripeSubscriptionId
        ];

        return Database::query($sql, $params)->rowCount() > 0;
    }

    public static function cancelSubscription($memberId)
    {
        $activeSubscription = self::getActiveSubscription($memberId);
        if (!$activeSubscription) {
            return false;
        }

        try {
            $stripeSubscription = \Stripe\Subscription::retrieve($activeSubscription['stripe_subscription_id']);
            $stripeSubscription->cancel_at_period_end = true;
            $stripeSubscription->save();

            self::updateSubscriptionStatus($activeSubscription['stripe_subscription_id'], 'Cancelling');
            return true;
        } catch (\Exception $e) {
            error_log('Stripe error: ' . $e->getMessage());
            return false;
        }
    }

    public static function getCancellingSubscription($memberId)
    {
        $sql = "SELECT * FROM SUBSCRIPTION WHERE member_id = :member_id AND status = 'Cancelling' ORDER BY start_date DESC LIMIT 1";
        $params = [':member_id' => $memberId];
        return Database::query($sql, $params)->fetch();
    }

    public static function resumeSubscription($memberId)
    {
        $cancellingSubscription = self::getCancellingSubscription($memberId);
        if (!$cancellingSubscription) {
            return false;
        }

        try {
            $stripeSubscription = \Stripe\Subscription::retrieve($cancellingSubscription['stripe_subscription_id']);
            $stripeSubscription->cancel_at_period_end = false;
            $stripeSubscription->save();

            self::updateSubscriptionStatus($cancellingSubscription['stripe_subscription_id'], 'Active');
            return true;
        } catch (\Exception $e) {
            error_log('Stripe error: ' . $e->getMessage());
            return false;
        }
    }
}