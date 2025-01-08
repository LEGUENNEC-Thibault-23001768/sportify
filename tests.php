<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
use PHPUnit\Framework\TestSuite;

class AllTests
{
    public static function suite()
    {
        $suite = new TestSuite('All Tests');

        $suite->addTestSuite(Tests\AutoloaderTest::class);
        $suite->addTestSuite(Tests\CoreAPIControllerTest::class);
        $suite->addTestSuite(Tests\Core\APIResponseTest::class);
        $suite->addTestSuite(Tests\Core\AuthTest::class);
        $suite->addTestSuite(Tests\Core\ConfigTest::class);
        $suite->addTestSuite(Tests\Core\DatabaseTest::class);
        $suite->addTestSuite(Tests\Core\RouterTest::class);
        $suite->addTestSuite(Tests\Models\UserTest::class);

        return $suite;
    }
}