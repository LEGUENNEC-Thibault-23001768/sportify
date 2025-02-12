<?php
namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use Models\User;

class UserTest extends TestCase {
    public function testFindByEmailIsInDb() {
        $user = new User();
        $result = $user->findByEmail('jack@example.com');
        $this->assertIsArray($result);
    }

    public function testFindByEmailIsNotInDb() {
        $user = new User();
        $result = $user->findByEmail('nonexistent@example.com');
        $this->assertFalse($result);
    }
}