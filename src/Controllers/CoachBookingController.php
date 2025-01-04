<?php

namespace Controllers;

use Core\View;
use Models\CoachRegistration;
use Models\Team;

class CoachBookingController
{
    // Affiche toutes les réservations
    public function index()
    {
        try {
            // Récupérer les réservations
            $coachBookings = CoachRegistration::getAllBookings();

            // Vérification en cas d'erreur
            if (isset($coachBookings['error'])) {
                throw new \Exception($coachBookings['error']);
            }

            // Envoyer les réservations à la vue
            echo View::render('/dashboard/trainers/index', ['coachBookings' => $coachBookings]);
        } catch (\Exception $e) {
            error_log('Erreur lors de l\'affichage des réservations : ' . $e->getMessage());
            echo 'Une erreur s\'est produite. Veuillez réessayer plus tard.';
        }
    }

    // Affiche le formulaire de création
    public function create()
    {
        try {
            $coaches = CoachRegistration::getAllCoaches();
            $teams = Team::getAll();

            echo View::render('/dashboard/trainers/create', ['coaches' => $coaches, 'teams' => $teams]);
        } catch (\Exception $e) {
            error_log('Erreur lors de l\'affichage du formulaire de création : ' . $e->getMessage());
            echo 'Une erreur s\'est produite. Veuillez réessayer plus tard.';
        }
    }

    // Enregistre une nouvelle réservation
    public function store()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        // Débogage : Affiche les données reçues
        error_log('Données reçues : ' . json_encode($data));

        // Validation des données
        if (empty($data['coach_id']) || empty($data['reservation_date']) || empty($data['start_time']) || empty($data['end_time']) || empty($data['member_id']) || empty($data['activity'])) {
            echo json_encode(['success' => false, 'message' => 'Données manquantes ou invalides.']);
            exit();
        }

        // Vérifier la disponibilité du coach
        $availability = CoachRegistration::checkCoachAvailability(
            $data['coach_id'],
            $data['reservation_date'],
            $data['start_time'],
            $data['end_time'],
            $data['activity']
        );

        if (!$availability['isAvailable']) {
            echo json_encode(['success' => false, 'message' => $availability['message']]);
            exit();
        }

        // Créer la réservation
        $reservation_date = date("Y-m-d H:i:s", strtotime($data['reservation_date']));
        try {
            CoachRegistration::createBooking(
                $data['coach_id'],
                $reservation_date,
                $data['start_time'],
                $data['end_time'],
                $data['member_id'],
                $data['activity']
            );
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            error_log('Erreur lors de la réservation : ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la réservation.']);
        }
    }


    // Supprime une réservation
    public function delete($bookingId)
    {
        try {
            CoachRegistration::deleteBooking($bookingId);
            header('Location: /dashboard/trainers');
            exit();
        } catch (\Exception $e) {
            error_log('Erreur lors de la suppression de la réservation : ' . $e->getMessage());
            echo 'Erreur lors de la suppression de la réservation. Veuillez réessayer plus tard.';
        }
    }

    // Vérifie la disponibilité d'un coach
    public function checkAvailability()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = json_decode(file_get_contents('php://input'), true);

                // Validation des données
                if (!$this->validateAvailabilityData($data)) {
                    $this->jsonResponse(['isAvailable' => false, 'message' => 'Données manquantes ou invalides.'], 400);
                }

                // Vérification de disponibilité
                $availability = CoachRegistration::checkCoachAvailability(
                    $data['coach_id'],
                    $data['reservation_date'],
                    $data['start_time'],
                    $data['end_time'], // Assurez-vous que cette clé existe dans $data
                    $data['activity']  // Ajout de l'argument manquant
                );

                $this->jsonResponse($availability, 200);
            } catch (\Exception $e) {
                error_log('Erreur lors de la vérification de disponibilité : ' . $e->getMessage());
                $this->jsonResponse(['isAvailable' => false, 'message' => 'Erreur serveur.'], 500);
            }
        }
    }

    // Méthode pour valider les données de réservation
    private function validateBookingData($data)
    {
        $requiredFields = ['coach_id', 'reservation_date', 'start_time', 'end_time', 'member_id', 'activity'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        return true;
    }


    public function getCoaches()
    {
        try {
            // Appel de la méthode getAllCoaches du modèle pour récupérer tous les coachs
            $coaches = CoachRegistration::getAllCoaches();
            echo json_encode($coaches);
        } catch (\Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getTrainerDetails($coach_id)
    {
        try {
            // Exemple d'une requête SQL pour récupérer les détails d'un coach par coach_id
            $query = "SELECT * FROM COACH WHERE coach_id = :coach_id";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':coach_id', $coach_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $trainer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($trainer) {
                echo json_encode($trainer);
            } else {
                echo json_encode(['error' => 'Coach non trouvé']);
            }
        } catch (\Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    

    // Méthode pour valider les données de disponibilité
    private function validateAvailabilityData($data)
    {
        $requiredFields = ['coachId', 'date', 'startTime', 'endTime'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        return true;
    }

    // Méthode pour envoyer une réponse JSON
    private function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }
}

