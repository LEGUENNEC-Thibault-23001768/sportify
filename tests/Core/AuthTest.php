<?php
namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Core\Auth;
use Models\User;

class AuthTest extends TestCase
{
    public function setUp(): void
    {
       $_SESSION = [];
    }
    
    public function testIsLoggedInWhenLoggedIn()
    {
        $_SESSION['user_id'] = 1;
        $this->assertTrue(Auth::isLoggedIn());
    }

    public function testIsLoggedInWhenNotLoggedIn()
    {
        $this->assertFalse(Auth::isLoggedIn());
    }
    
     public function testRequireLoginRedirectsWhenNotLoggedIn()
    {
        $middleware = Auth::requireLogin();
         $this->expectOutputRegex('/Location: \/login/');
        $middleware();
    }
    
    public function testRequireLoginAllowsAccessWhenLoggedIn()
    {
        $_SESSION['user_id'] = 1;
        $middleware = Auth::requireLogin();
         $this->assertTrue($middleware());
    }
    
     public function testIsAdminRedirectsWhenNotAdmin()
    {
         $_SESSION['user_id'] = 1;
        
        $mockUser = $this->createMock(User::class);
          $mockUser->method('getUserById')->willReturn(['status' => 'user']);
        $reflection = new \ReflectionClass(User::class);
        $reflectionProperty = $reflection->getProperty('instance');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null, $mockUser);

        $middleware = Auth::isAdmin();
        $this->expectOutputRegex('/Location: \/dashboard/');
         $middleware();
    }
    
      public function testIsAdminAllowsAccessWhenAdmin()
    {
        $_SESSION['user_id'] = 1;
          $mockUser = $this->createMock(User::class);
          $mockUser->method('getUserById')->willReturn(['status' => 'admin']);
        $reflection = new \ReflectionClass(User::class);
        $reflectionProperty = $reflection->getProperty('instance');
        $reflectionProperty->setAccessible(true);
         $reflectionProperty->setValue(null, $mockUser);

        $middleware = Auth::isAdmin();
       $this->assertTrue($middleware());
    }
}