<?php

namespace KKBOX\OpenAPI;

use GuzzleHttp\Client as GuzzleClient;

class ApiClient
{
    private $guzzleClient;
    private $options;
    private $version = '0.0.0';

    public function __construct($options = [])
    {
        $this->options = $options + [
            'app_id' => null,
            'app_secret' => null,
            'token' => null,
            'base_uri' => 'https://api.kkbox.com',
            'guzzle_client' => null,
        ];

        $this->guzzleClient = $this->options['guzzle_client'] ?: new GuzzleClient([
            'base_uri' => $this->options['base_uri'],
        ]);
    }

    public function get($path, $queryParams = [])
    {
        $path = '/v1.1/' . ltrim($path, '/');
        $resp = $this->guzzleClient->request('GET', $path, [
            'query' => $queryParams,
            'headers' => [
                'Authorization' => sprintf('Bearer %s', $this->token()),
                'User-Agent' => $this->uaString(),
            ],
        ]);

        return json_decode((string) $resp->getBody(), true);
    }

    public function token()
    {
        return $this->options['token'] ?: $this->refreshToken();
    }

    public function refreshToken()
    {
        if (!$this->options['app_id'] || !$this->options['app_secret']) {
            throw new \RuntimeException('Failed to refresh token, app_id and app_secret not set!');
        }

        $auth = base64_encode(sprintf("%s:%s", $this->options['app_id'], $this->options['app_secret']));
        $resp = $this->guzzleClient->request('POST', 'https://account.kkbox.com/oauth2/token', [
            'auth' => [$this->options['app_id'], $this->options['app_secret']],
            'headers' => [
                'User-Agent' => $this->uaString(),
            ],
            'form_params' => [
                'grant_type' => 'client_credentials'
            ],
        ]);

        $data = json_decode((string) $resp->getBody(), true);
        if (!isset($data['access_token'])) {
            throw new \Exception("Failed to obtain access token!");
        }

        $tokenType = @$data['token_type'] ?: '(empty)';
        if ($tokenType !== 'Bearer') {
            throw new \Exception("token_type '{$tokenType}' not supported!");
        }

        $token = $data['access_token'];

        $this->options['token'] = $token;
        return $token;
    }

    private function uaString()
    {
        return sprintf("OpenApi-Client-PHP/%s", $this->version);
    }
}
