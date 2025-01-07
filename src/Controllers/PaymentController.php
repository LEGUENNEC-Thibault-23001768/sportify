<?php

namespace Controllers;

use Core\Config;
use Core\View;
use Models\Subscription;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\StripeClient;

class PaymentController
{
    private StripeClient $stripe;

    public function __construct()
    {
        Stripe::setApiKey(Config::get("stripe_key"));
        $this->stripe = new StripeClient((Config::get("stripe_key")));
    }

    /**
     * @return void
     * @throws ApiErrorException
     */
    public function createCheckoutSession(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            return;
        }

        $userEmail = $_SESSION['user_email'];

        $existingCustomers = $this->stripe->customers->search([
            'query' => "email:'$userEmail'",
        ]);

        if (!empty($existingCustomers->data)) {
            $customerId = $existingCustomers->data[0]->id;
        } else {
            $newCustomer = $this->stripe->customers->create([
                'email' => $userEmail,
            ]);
            $customerId = $newCustomer->id;
        }

        $session = Session::create([
            'customer' => $customerId,
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => 'price_1QBAtU01Olm6yDgOPUmJnGEf',
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => Config::get("server_url") . '/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => Config::get("server_url") . '/dashboard',
            'client_reference_id' => $_SESSION['user_id'],
        ]);

        header("Location: " . $session->url);
    }

    /**
     * @return void
     */
    public function success(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            return;
        }

        $sessionId = $_GET['session_id'] ?? null;
        if (!$sessionId) {
            $_SESSION['error'] = "Erreur : Session ID manquant.";
            header('Location: /dashboard');
            return;
        }

        try {
            $session = Session::retrieve($sessionId);
            $subscription = $this->stripe->subscriptions->retrieve($session->subscription);
            $customer = $this->stripe->customers->retrieve($subscription->customer);

            $memberId = $session->client_reference_id;
            $startDate = date('Y-m-d', $subscription->current_period_start);
            $endDate = date('Y-m-d', $subscription->current_period_end);
            $amount = $subscription->plan->amount / 100;

            $existingSubscription = Subscription::getActiveSubscription($memberId);

            if ($existingSubscription) {
                Subscription::updateSubscription(
                    $memberId,
                    $subscription->id,
                    $subscription->plan->nickname,
                    $startDate,
                    $endDate,
                    $amount
                );
                $_SESSION['message'] = "Votre abonnement a été mis à jour avec succès.";
            } else {
                Subscription::createSubscription(
                    $memberId,
                    $subscription->id,
                    $subscription->plan->nickname,
                    $startDate,
                    $endDate,
                    $amount
                );
                $_SESSION['message'] = "Merci pour votre abonnement ! Vous avez été abonné avec succès.";
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = "Une erreur est survenue lors de la gestion de l'abonnement : " . $e->getMessage();
        }

        header('Location: /dashboard');
    }

    /**
     * @return void
     */
    public function listInvoices(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            return;
        }
        try {
            $activeSubscription = Subscription::getStripeSubscriptionId($_SESSION['user_id']);

            if (!$activeSubscription) {
                $_SESSION['error'] = "Aucun abonnement actif trouvé.";
                echo "pas d'abonnement the fuck?";
                return;
            }

            $stripeSubscription = $this->stripe->subscriptions->retrieve($activeSubscription['stripe_subscription_id']);
            $invoices = $this->stripe->invoices->all([
                'customer' => $stripeSubscription->customer,
                'limit' => 10,
                'expand' => ['data.payment_intent']
            ]);

            $formattedInvoices = [];
            foreach ($invoices->data as $invoice) {
                $paymentMethod = null;
                if ($invoice->payment_intent && $invoice->payment_intent->payment_method) {
                    $paymentMethod = $this->stripe->paymentMethods->retrieve($invoice->payment_intent->payment_method);
                }

                $lastFourDigits = $paymentMethod ? $paymentMethod->card->last4 : 'N/A';
                $cardBrand = $paymentMethod ? $paymentMethod->card->brand : 'N/A';

                $formattedInvoices[] = [
                    'id' => $invoice->id,
                    'number' => $invoice->number,
                    'amount_due' => $invoice->amount_due / 100,
                    'currency' => strtoupper($invoice->currency),
                    'status' => $invoice->status,
                    'created' => date('d M Y', $invoice->created),
                    'due_date' => date('d M Y', $invoice->due_date),
                    'pdf_url' => $invoice->invoice_pdf,
                    'last_four_digits' => $lastFourDigits,
                    'card_brand' => $cardBrand
                ];
            }

            echo View::render('dashboard/invoices', ['invoices' => $formattedInvoices]);
        } catch (\Exception $e) {
            echo "erreur lors de récupération: " . $e->getMessage();
            return;
        }
    }

    /**
     * @return void
     */
    public function cancelSubscription(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            return;
        }

        try {
            $activeSubscription = Subscription::getActiveSubscription($_SESSION['user_id']);

            if (!$activeSubscription) {
                return;
            }

            $stripeSubscription = $this->stripe->subscriptions->update(
                $activeSubscription['stripe_subscription_id'],
                ['cancel_at_period_end' => true]
            );

            Subscription::updateSubscriptionStatus($activeSubscription['stripe_subscription_id'], 'Cancelling');

            $_SESSION['message'] = "Votre abonnement sera annulé à la fin de la période de facturation en cours.";
        } catch (\Exception $e) {
            $_SESSION['error'] = "Une erreur est survenue lors de l'annulation de l'abonnement : " . $e->getMessage();
        }

        header('Location: /dashboard');
    }
}