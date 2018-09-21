<?php

namespace damijanc\FTP\Tests;

use damijanc\FTP\Client;

/**
 * @group integration
 * Class IntegrationClientTest
 * @package damijanc\FTP\Tests
 */
class IntegrationClientTest extends \PHPUnit_Framework_TestCase
{
    private function getOptions()
    {
        $options = array();
        $options['server'] = getenv('FTP_SERVER');
        $options['port'] = 21;
        $options['user'] = getenv('FTP_USER');
        $options['pass'] = getenv('FTP_PASSWORD');

        return $options;
    }


    public function testClient()
    {
        $client = new Client($this->getOptions());
        $client->connect();
        $client->cd('pub');
        $client->put(__DIR__ . '/Resources/dummy.gif');
        $contents = $client->ls();
        $this->assertContains("dummy.gif", $contents);
        $client->rm("dummy.gif");
        $contents = $client->ls();
        $this->assertEquals(null, $contents);
    }
}
