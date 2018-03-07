<?php

namespace Tests;

use Ohffs\Transmission\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    use CommonTests;

    protected function getClient()
    {
        return new Client(
            getenv('TRANSMISSION_HOST'),
            getenv('TRANSMISSION_PORT'),
            getenv('TRANSMISSION_USERNAME'),
            getenv('TRANSMISSION_PASSWORD')
        );
    }
}
