<?php

namespace Models;

use Core\Database;
use PDO;

class Booking
{

    /**
     * @param $court_id
     * @param $reservation_date
     * @param $start_time
     * @param $end_time
     * @return bool
     */
    public static function isAvailable($court_id, $reservation_date, $start_time, $end_time): bool
    {
        $sql = "
            SELECT COUNT(*) 
            FROM COURT_RESERVATION 
            WHERE court_id = :court_id 
            AND reservation_date = :reservation_date 
            AND ((start_time BETWEEN :start_time AND :end_time) 
            OR (end_time BETWEEN :start_time AND :end_time))
        ";
        $params = [
            ':court_id' => $court_id,
            ':reservation_date' => $reservation_date,
            ':start_time' => $start_time,
            ':end_time' => $end_time
        ];
        return Database::query($sql, $params)->fetchColumn() == 0;
    }

    public static function getMembersByBookingId($booking_id) {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT m.* FROM MEMBER m INNER JOIN booking_members bm ON m.member_id = bm.member_id WHERE bm.booking_id = :booking_id"); // Assumes you have a joining table 'booking_members'
        $stmt->execute(['booking_id' => $booking_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array
     */
    public static function getAllReservations(): array
    {
        $sql = "SELECT cr.*, u.last_name AS member_name, c.court_name 
                FROM COURT_RESERVATION cr
                JOIN MEMBER u ON cr.member_id = u.member_id
                JOIN COURT c ON cr.court_id = c.court_id
                ORDER BY cr.reservation_date DESC";
        return Database::query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array
     */
    public static function getAllCourts(): array
    {
        $sql = "SELECT * FROM COURT";
        return Database::query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param $member_id
     * @param $court_id
     * @param $reservation_date
     * @param $start_time
     * @param $end_time
     * @return bool
     */
    public static function addReservation($member_id, $court_id, $reservation_date, $start_time, $end_time)
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("INSERT INTO COURT_RESERVATION (member_id, court_id, reservation_date, start_time, end_time) VALUES (:member_id, :court_id, :reservation_date, :start_time, :end_time)");
            $stmt->execute([
                'member_id' => $member_id,
                'court_id' => $court_id,
                'reservation_date' => $reservation_date,
                'start_time' => $start_time,
                'end_time' => $end_time,
            ]);
            return true;
        } catch (\PDOException $e) {
            error_log("PDOException in addReservation: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param $reservation_id
     * @return bool
     */
    public static function deleteReservation($reservation_id)
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("DELETE FROM COURT_RESERVATION WHERE reservation_id = :reservation_id");
            $stmt->execute(['reservation_id' => $reservation_id]);
            return true;
        } catch (\PDOException $e) {
            error_log("PDOException in deleteReservation: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param $reservation_id
     * @return mixed
     */
    public static function getReservationById($reservation_id)
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT * FROM COURT_RESERVATION WHERE reservation_id = :reservation_id");
            $stmt->execute(['reservation_id' => $reservation_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("PDOException in getReservationById: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param $reservation_id
     * @param $reservation_date
     * @param $start_time
     * @param $end_time
     * @param $event_id
     * @return bool
     */
    public static function updateReservation($reservation_id, $reservation_date, $start_time, $end_time, $event_id = null)
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("UPDATE COURT_RESERVATION SET reservation_date = :reservation_date, start_time = :start_time, end_time = :end_time, event_id = :event_id WHERE reservation_id = :reservation_id");
            $stmt->execute([
                'reservation_id' => $reservation_id,
                'reservation_date' => $reservation_date,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'event_id' => $event_id,
            ]);
            return true;
        } catch (\PDOException $e) {
            error_log("PDOException in updateReservation: " . $e->getMessage());
            return false;
        }
    }

    public static function getTeamName($team_id) {
        try {
             $pdo = Database::getConnection();
             $stmt = $pdo->prepare("SELECT team_name FROM TEAM WHERE team_id = :team_id");
             $stmt->execute(['team_id' => $team_id]);
             $result =  $stmt->fetch(PDO::FETCH_ASSOC);
             return $result['team_name'] ?? null;
   
         } catch (\PDOException $e) {
             error_log("PDOException in getTeamName: " . $e->getMessage());
             return false;
         }
      }

      public static function allInvitationsAnswered($team_id)
    {
        try {
            $pdo = Database::getConnection();
            $sql = "SELECT COUNT(*) FROM TEAM_INVITATION WHERE team_id = :team_id AND status = 'pending'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':team_id' => $team_id]);
            $pendingCount = $stmt->fetchColumn();
            return $pendingCount == 0; // Retourne true si toutes les invitations ont reçu une réponse
        } catch (\PDOException $e) {
            error_log("PDOException in allInvitationsAnswered: " . $e->getMessage());
            return false;
        }
    } 

    public static function addTeamParticipants($team_id)
    {
        try {
            $pdo = Database::getConnection();
            // Insère seulement les participants qui ont accepté l'invitation
            $sql = "INSERT INTO TEAM_PARTICIPANT (team_id, member_id)
                    SELECT :team_id, member_id
                    FROM TEAM_INVITATION
                    WHERE team_id = :team_id AND status = 'accepted'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':team_id' => $team_id]);
            return true;
        } catch (\PDOException $e) {
            error_log("PDOException in addTeamParticipants: " . $e->getMessage());
            return false;
        }
    }

   public static function createReservationFromTeam($team_id) {
          try {
              $pdo = Database::getConnection();

              // Récupérer les informations nécessaires pour la réservation
              $sqlSelect = "SELECT
                      ti.member_id,
                      cr.court_id,
                      cr.reservation_date,
                      cr.start_time,
                      cr.end_time
                  FROM
                      TEAM_INVITATION ti
                  JOIN
                      COURT_RESERVATION cr ON ti.team_id = :team_id
                  WHERE ti.team_id = :team_id and ti.status = 'accepted'
                    LIMIT 1;
                  ";
              $stmtSelect = $pdo->prepare($sqlSelect);
              $stmtSelect->execute([':team_id' => $team_id]);
              $reservationInfo = $stmtSelect->fetch(PDO::FETCH_ASSOC);

              if (!$reservationInfo) {
                  error_log("Aucune information de réservation trouvée pour l'équipe ID : " . $team_id);
                  return false;
              }

             // Créer la réservation avec les informations récupérées
                $sqlReservation = "INSERT INTO COURT_RESERVATION (member_id, court_id, reservation_date, start_time, end_time, team_id)
                                 VALUES (:member_id, :court_id, :reservation_date, :start_time, :end_time, :team_id)";

                $params = [
                    ':member_id' => $reservationInfo['member_id'],
                    ':court_id' => $reservationInfo['court_id'],
                    ':reservation_date' => $reservationInfo['reservation_date'],
                    ':start_time' => $reservationInfo['start_time'],
                    ':end_time' => $reservationInfo['end_time'],
                    ':team_id' => $team_id,
                ];

                $stmtReserve = $pdo->prepare($sqlReservation);
                if (!$stmtReserve->execute($params)) {
                    throw new \Exception("Erreur lors de la création de la réservation : " . json_encode($stmtReserve->errorInfo()));
                }

              return true; // Indique que la réservation a été créée avec succès
          } catch (\PDOException $e) {
              error_log("PDOException in createReservationFromTeam: " . $e->getMessage());
              return false;
          }
      }

}

?>