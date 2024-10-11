<?php



use PHPUnit\Framework\TestCase;
use Models\User;

class UserTest extends TestCase {
    public function testFindByEmail() {
        $user = new User();
        $result = $user->findByEmail('jack@example.com');
        $result_false = $user->findByEmail('CACACACACACACACA@CACACA.COM');
        $this->assertIsArray($result);
        
        $this->assertFalse($result_false);
    }
}