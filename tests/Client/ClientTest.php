<?php

namespace damijanc\FTP\Tests;

use damijanc\FTP\Client;
use damijanc\FTP\Adapter\FTPAdapterInterface;

/**
 * @group unit
 * Class ClientTest
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FTPAdapterInterface
     */
    private $adapter;

    /**
     * @var []
     */
    private $options;

    private function getAdapter()
    {
        $this->options = $this->getOptions();

        $adapter = $this->getMockBuilder('damijanc\FTP\Adapter\FTPAdapterInterface')
            ->setMethods(['ftp_connect', "ftp_login"])
            ->getMockForAbstractClass();

        $adapter
            ->expects($this->once())
            ->method('ftp_connect')
            ->with(
                $this->equalTo($this->options['server']),
                $this->equalTo($this->options['port']),
                $this->equalTo(5)
            )
            ->will($this->returnValue('c'));

        //ftp_login($this->conn, $this->user, $this->password);
        $adapter
            ->expects($this->once())
            ->method('ftp_login')
            ->with(
                $this->equalTo('c'),
                $this->equalTo('user'),
                $this->equalTo('password')
            )
            ->will($this->returnValue('c'));

        return $adapter;
    }

    private function getOptions()
    {
        $options = array();
        $options['server'] = 'ftp.example.com';
        $options['port'] = 21;
        $options['user'] = 'user';
        $options['pass'] = 'password';

        return $options;
    }

    public function testConnect()
    {
        $ftp = new Client($this->getOptions(), $this->getAdapter());
        $this->assertEquals($ftp->connect(), true);
    }
}
