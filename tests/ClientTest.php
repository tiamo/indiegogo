<?php

namespace Indiegogo\Tests;

use Indiegogo\Client;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @return array
     */
    public function testConstructor()
    {
        $email = '';
        $password = '';

        if ($this->client->auth($email, $password)) {
            print_r($this->client->getFavorites());
        }
    }

    protected function setUp()
    {
        $this->client = new Client('');
    }
}