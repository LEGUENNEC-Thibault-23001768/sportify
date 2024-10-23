<?php

namespace Models;

use Core\Database;
use Core\Config;
use Stripe\Stripe;

class Subscription
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function syncSubscriptionWithStripe($memberId)
    {
        $localSubscription = $this->getActiveSubscription($memberId);
        if (!$localSubscription) {
            return false;
        }

        try {
            Stripe::setApiKey(Config::get('stripe_key'));
            $stripeSubscription = \Stripe\Subscription::retrieve($localSubscription['stripe_subscription_id']);
            
            $status = $stripeSubscription->status;
            $endDate = date('Y-m-d', $stripeSubscription->current_period_end);
            $amount = $stripeSubscription->plan->amount / 100;

            $stmt = $this->db->prepare("UPDATE SUBSCRIPTION SET status = ?, end_date = ?, amount = ? WHERE stripe_subscription_id = ?");
            return $stmt->execute([$status, $endDate, $amount, $localSubscription['stripe_subscription_id']]);
        } catch (\Exception $e) {
            // Gérer l'erreur Stripe
            error_log('Stripe error: ' . $e->getMessage());
            return false;
        }
    }

    public function createSubscription($memberId, $stripeSubscriptionId, $subscriptionType, $startDate, $endDate, $amount)
    {
        $stmt = $this->db->prepare("INSERT INTO SUBSCRIPTION (member_id, stripe_subscription_id, subscription_type, start_date, end_date, amount, status) VALUES (?, ?, ?, ?, ?, ?, 'Active')");
        return $stmt->execute([$memberId, $stripeSubscriptionId, $subscriptionType, $startDate, $endDate, $amount]);
    }

    public function updateSubscriptionStatus($stripeSubscriptionId, $status)
    {
        $stmt = $this->db->prepare("UPDATE SUBSCRIPTION SET status = ? WHERE stripe_subscription_id = ?");
        return $stmt->execute([$status, $stripeSubscriptionId]);
    }

    public function hasActiveSubscription($memberId)
    {
        $this->syncSubscriptionWithStripe($memberId);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM SUBSCRIPTION WHERE member_id = ? AND status = 'Active'");
        $stmt->execute([$memberId]);
        return $stmt->fetchColumn() > 0;
    }

    public function getActiveSubscription($memberId)
    {
        $stmt = $this->db->prepare("SELECT * FROM SUBSCRIPTION WHERE member_id = ? AND status = 'Active' ORDER BY start_date DESC LIMIT 1");
        $stmt->execute([$memberId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    public function getStripeSubscription($stripeSubscriptionId)
    {
        try {
            return \Stripe\Subscription::retrieve($stripeSubscriptionId);
        } catch (\Exception $e) {
            // Gérer l'erreur (par exemple, logger l'erreur)
            return null;
        }
    }


    public function updateSubscription($memberId, $stripeSubscriptionId, $subscriptionType, $startDate, $endDate, $amount)
    {
        $stmt = $this->db->prepare("UPDATE SUBSCRIPTION SET stripe_subscription_id = ?, subscription_type = ?, start_date = ?, end_date = ?, amount = ?, status = 'Active' WHERE member_id = ? AND status IN ('Active', 'Cancelling')");
        return $stmt->execute([$stripeSubscriptionId, $subscriptionType, $startDate, $endDate, $amount, $memberId]);
    }

    public function cancelSubscription($memberId)
    {
        $activeSubscription = $this->getActiveSubscription($memberId);
        if (!$activeSubscription) {
            return false;
        }

        try {
            $stripeSubscription = \Stripe\Subscription::retrieve($activeSubscription['stripe_subscription_id']);
            $stripeSubscription->cancel_at_period_end = true;
            $stripeSubscription->save();

            $this->updateSubscriptionStatus($activeSubscription['stripe_subscription_id'], 'Cancelling');
            return true;
        } catch (\Exception $e) {
            // Gérer l'erreur Stripe
            error_log('Stripe error: ' . $e->getMessage());
            return false;
        }
    }

    public function getCancellingSubscription($memberId)
    {
        $stmt = $this->db->prepare("SELECT * FROM SUBSCRIPTION WHERE member_id = ? AND status = 'Cancelling' ORDER BY start_date DESC LIMIT 1");
        $stmt->execute([$memberId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function resumeSubscription($memberId)
    {
        $cancellingSubscription = $this->getCancellingSubscription($memberId);
        if (!$cancellingSubscription) {
            return false;
        }

        try {
            $stripeSubscription = \Stripe\Subscription::retrieve($cancellingSubscription['stripe_subscription_id']);
            $stripeSubscription->cancel_at_period_end = false;
            $stripeSubscription->save();

            $this->updateSubscriptionStatus($cancellingSubscription['stripe_subscription_id'], 'Active');
            return true;
        } catch (\Exception $e) {
            // Gérer l'erreur Stripe
            error_log('Stripe error: ' . $e->getMessage());
            return false;
        }
    }
}