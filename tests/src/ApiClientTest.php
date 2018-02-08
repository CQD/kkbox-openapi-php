<?php

use GuzzleHttp\Psr7\Response;
use KKBOX\OpenAPI\ApiClient;

class ApiClientTest extends TestBase
{
    public function testBasicGet()
    {
        $apiClient = $this->prepareApiClient([
            new Response(200, [], '{"id":"kushan","name":"hiigara, our home"}'),
            new Response(200, [], '{"id":"kushan","name":"hiigara, our home"}'),
        ], ['token' => 'guidestone']);

        $result = $apiClient->get('/aaa', ['kharak' => 'not-burning']);

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('kushan', $result['id']);
        $this->assertArrayHasKey('name', $result);
        $this->assertEquals('hiigara, our home', $result['name']);

        $this->assertEquals(1, count($this->history));
        $this->assertEquals('/v1.1/aaa?kharak=not-burning', (string) ($this->history[0]['request'])->getUri());

        $result = $apiClient->get('/aaa', ['kharak' => 'burning']);
        $this->assertEquals(2, count($this->history));
        $this->assertEquals('/v1.1/aaa?kharak=burning', (string) ($this->history[1]['request'])->getUri());
    }

    public function testRefreshToken()
    {
        $apiClient = $this->prepareApiClient([
            new Response(200, [], '{"access_token": "guidestone","expires_in": 1,"token_type": "Bearer"}'),
        ], ['app_id' => 'love', 'app_secret' => 'joy']);

        $this->assertEquals('guidestone', $apiClient->refreshToken());
    }

    public function testRefreshTokenWithoutSecret()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to refresh token, app_id and app_secret not set!');

        (new ApiClient())->refreshToken();
    }

    public function testRefreshTokenUnknownTokenType()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("token_type 'cat' not supported!");

        $apiClient = $this->prepareApiClient([
            new Response(200, [], '{"access_token": "guidestone","expires_in": 1,"token_type": "cat"}'),
        ], ['app_id' => 'love', 'app_secret' => 'joy']);
        $apiClient->refreshToken();
    }

    public function testRefreshTokenFail()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Failed to obtain access token!");

        $apiClient = $this->prepareApiClient([
            new Response(200, [], '{}'),
        ], ['app_id' => 'love', 'app_secret' => 'joy']);
        $apiClient->refreshToken();
    }
}
