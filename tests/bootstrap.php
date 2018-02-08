<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use KKBOX\OpenAPI\ApiClient;

class TestBase extends TestCase
{
    protected $history = [];

    protected function prepareApiClient($responses = [], $options = [])
    {
        $options['guzzle_client'] = $this->prepareGuzzleClient($responses, $options);
        return new ApiClient($options);
    }

    protected function prepareGuzzleClient($responses = [], $options = [])
    {
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);

        $this->history = [];
        $stack->push(Middleware::history($this->history));

        return new GuzzleClient(['handler' => $stack]);
    }
}
