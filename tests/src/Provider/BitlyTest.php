<?php

namespace Bybrand\OAuth2\Client\Test\Provider;

use PHPUnit\Framework\TestCase;
use Bybrand\OAuth2\Client\Provider\Bitly;
use Mockery as m;

class BitlyTest extends TestCase
{
    protected $provider;

    protected function setUp()
    {
        $this->provider = new Bitly([
            'clientId'     => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri'  => 'none',
        ]);
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    /**
     * @group Bitly
     * @group Bitly.AuthorizationUrl
     */
    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }

    /**
     * @group Bitly
     * @group Bitly.GetAuthorizationUrl
     */
    public function testGetAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);

        $this->assertEquals('/oauth/authorize', $uri['path']);
    }

    /**
     * @group Bitly
     * @group Bitly.GetBaseAccessTokenUrl
     */
    public function testGetBaseAccessTokenUrl()
    {
        $params = [];

        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);

        $this->assertEquals('/oauth/access_token', $uri['path']);
    }

    /**
     * @group Bitly
     * @group Bitly.GetAccessToken
     */
    public function testGetAccessToken()
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')
            ->andReturn('{"access_token": "mock_access_token", "token_type": "bearer", "login": "mock_login", "apiKey": "api_key_id"}');
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $response->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);

        $this->provider->setHttpClient($client);

        $token = $this->provider
            ->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertNull($token->getExpires());
        $this->assertNull($token->getRefreshToken());

        $this->assertEquals('mock_login', $token->getResourceOwnerId());
    }
}
