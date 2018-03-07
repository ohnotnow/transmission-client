<?php

namespace Tests;

use Ohffs\Transmission\FakeClient;
use PHPUnit\Framework\TestCase;

class ClientFakeTest extends TestCase
{
    use CommonTests;

    protected function getClient()
    {
        return new FakeClient(
            getenv('TRANSMISSION_HOST'),
            getenv('TRANSMISSION_PORT'),
            getenv('TRANSMISSION_USERNAME'),
            getenv('TRANSMISSION_PASSWORD')
        );
    }
}
