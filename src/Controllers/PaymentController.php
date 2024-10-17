<?php

namespace Controllers;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Models\Subscription;
use Core\Config;

class PaymentController
{
    public function __construct()
    {
        Stripe::setApiKey(Config::get("stripe_key"));
    }

    public function createCheckoutSession()
    {
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $DOMAIN = 'http://localhost:8888';

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => 'price_1Q80V201Olm6yDgOR1TVO9zG',
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => $DOMAIN . '/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $DOMAIN . '/dashboard',
        ]);

        header("Location: " . $session->url);
        exit();
    }

    public function success()
    {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $sessionId = $_GET['session_id'] ?? null; 
        if (!$sessionId) {
            echo "Erreur : Session ID manquant.";
            return;
        }

        Stripe::setApiKey(Config::get('stripe_key'));

        $session = Session::retrieve($sessionId);
        if (!$session) {
            echo "Erreur : Impossible de récupérer les informations de session Stripe.";
            return;
        }

        $memberId = $_SESSION['user_id'];
        
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime('+1 month')); 
        
        $subscriptionModel = new Subscription();
        $subscriptionType = 'Standard'; 
        $amount = $session->amount_total / 100; 

        $subscriptionModel->createSubscription($memberId, $subscriptionType, $startDate, $endDate, $amount);

        $_SESSION['message'] = "Merci pour votre abonnement ! Vous avez été abonné avec succès.";
        header('Location: /dashboard');
        exit();
    }
}