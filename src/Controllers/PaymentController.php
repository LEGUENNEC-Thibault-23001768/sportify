<?php

namespace Controllers;

use Core\View;
use Models\User;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Models\Subscription;

require_once __DIR__ . '/../../vendor/autoload.php';


class PaymentController
{
    public function __construct()
    {
        \Stripe\Stripe::setApiKey('sk_test_51Q80Nv01Olm6yDgOjM3A9yXbw0WgaWxqmrh4Xfjnfh2kwTmFlAyzplOz5jIfnzUm9y3iGrCZqrsgfBwn81ofPb9X00hLSncyxX');
    }

    public function createCheckoutSession()
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $DOMAIN = 'http://localhost:8888';

        // Créer une session Stripe Checkout
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => 'price_1Q80V201Olm6yDgOR1TVO9zG',
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => $DOMAIN . '/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $DOMAIN . '/dashboard',
        ]);

        // Rediriger l'utilisateur vers la page de paiement
        header("Location: " . $session->url);
        exit();
    }

    public function success()
    {
        session_start();

        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $sessionId = $_GET['session_id'] ?? null; // Récupérer l'ID de la session Stripe
        if (!$sessionId) {
            echo "Erreur : Session ID manquant.";
            return;
        }

        Stripe::setApiKey('sk_test_51Q80Nv01Olm6yDgOjM3A9yXbw0WgaWxqmrh4Xfjnfh2kwTmFlAyzplOz5jIfnzUm9y3iGrCZqrsgfBwn81ofPb9X00hLSncyxX');

        // Récupérer les informations de la session de paiement
        $session = Session::retrieve($sessionId);
        if (!$session) {
            echo "Erreur : Impossible de récupérer les informations de session Stripe.";
            return;
        }

        // Récupérer l'ID de l'utilisateur connecté
        $memberId = $_SESSION['user_id'];
        
        // Définir les dates de début et de fin de l'abonnement
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime('+1 month')); // Abonnement d'un mois
        
        // Créer une nouvelle instance de Subscription
        $subscriptionModel = new Subscription();
        $subscriptionType = 'Standard'; // Vous pouvez définir différents types ici
        $amount = $session->amount_total / 100; // Assurez-vous que ce montant correspond bien au prix de l'abonnement

        // Enregistrer l'abonnement dans la base de données
        $subscriptionModel->createSubscription($memberId, $subscriptionType, $startDate, $endDate, $amount);

        // Redirection vers le tableau de bord avec un message de succès
        $_SESSION['message'] = "Merci pour votre abonnement ! Vous avez été abonné avec succès.";
        header('Location: /dashboard');
        exit();
    }
}


?>