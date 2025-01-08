<?php

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Core\Auth;
use Models\User;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
      
        if (!isset($_SESSION)) {
            session_start();
        }
         $_SESSION = []; // clear session for tests
        $user = new \Models\User;
        $userData = [
            'email' => 'test@example.com',
            'password' => 'test',
            'first_name' => 'test',
            'last_name' => 'user',
            'status' => 'membre',
          
            'birth_date' => null,
            'address' => null,
            'phone' => null
        ];
         $userDataAdmin = [
            'email' => 'admin@example.com',
            'password' => 'admin',
            'first_name' => 'test',
            'last_name' => 'user',
            'status' => 'admin',
            'birth_date' => null,
            'address' => null,
            'phone' => null
        ];
         $user->create($userData);
          $user->create($userDataAdmin);

         $loginUser = $user->login('test@example.com', 'test');
          $loginAdmin = $user->login('admin@example.com', 'admin');
         if ($loginUser) {
             $_SESSION['user_id'] = $loginUser['member_id'];
         }

           if ($loginAdmin) {
            $_SESSION['admin_id'] = $loginAdmin['member_id'];
        }
    }

     protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testIsLoggedIn()
    {
        $this->assertTrue(Auth::isLoggedIn());
      
        $_SESSION = [];
        $this->assertFalse(Auth::isLoggedIn());
        
         $user = new \Models\User;
         $loginUser = $user->login('test@example.com', 'test');
          if ($loginUser) {
             $_SESSION['user_id'] = $loginUser['member_id'];
         }
         $this->assertTrue(Auth::isLoggedIn());
    }

    public function testRequireLogin()
    {
        $middleware = Auth::requireLogin();

          $_SESSION = [];
        $this->assertFalse($middleware());
            $user = new \Models\User;
         $loginUser = $user->login('test@example.com', 'test');
          if ($loginUser) {
             $_SESSION['user_id'] = $loginUser['member_id'];
         }
       $this->assertTrue($middleware());

    }

    public function testIsAdmin()
    {
         $middleware = Auth::isAdmin();
          $_SESSION = [];
        $this->assertFalse($middleware());
         $user = new \Models\User;
         $loginAdmin = $user->login('admin@example.com', 'admin');
          if ($loginAdmin) {
             $_SESSION['user_id'] = $loginAdmin['member_id'];
        }
        $this->assertTrue($middleware());

          $_SESSION = [];
         $loginUser = $user->login('test@example.com', 'test');
          if ($loginUser) {
             $_SESSION['user_id'] = $loginUser['member_id'];
         }
         $this->assertFalse($middleware());
       
        
    }


    public function testIsCoach()
    {
        $middleware = Auth::isCoach();
        $_SESSION = [];
        $this->assertFalse($middleware());

        $user = new \Models\User;
        $userData = [
            'email' => 'coach@example.com',
            'password' => 'coach',
            'first_name' => 'test',
            'last_name' => 'user',
            'status' => 'coach',
            'birth_date' => null,
            'address' => null,
            'phone' => null
        ];

        $user->create($userData);
       $loginCoach = $user->login('coach@example.com', 'coach');

        if ($loginCoach) {
            $_SESSION['user_id'] = $loginCoach['member_id'];
       }
       $this->assertTrue($middleware());
     }


    
}