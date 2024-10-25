<?php


namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use Models\User;

class UserTest extends TestCase {
    public function testFindByEmailIsInDb() {
        $user = new User();
        $result = $user->findByEmail('jack@example.com');
        $result_false = $user->findByEmail('CACACACACACACACA@CACACA.COM');
        
        $this->assertIsArray($result);
        $this->assertFalse($result_false);
    }

    public function testFindByEmailIsNotInDb() {
        $user = new User();
        $result_false = $user->findByEmail('CACACACACACACACA@CACACA.COM');
        
        $this->assertFalse($result_false);
    }
}