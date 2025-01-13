<?php
   namespace Models;
  use Core\Database;

  class Court {
  //  Find a court by its ID en PDO ou query a utiliser avec votre  système.
   public static function findById($court_id)  { 
           $sql = "SELECT * FROM COURT WHERE court_id = :court_id";
        $params = [':court_id' => $court_id];
            return Database::query($sql, $params)->fetch();
      }
    
   }