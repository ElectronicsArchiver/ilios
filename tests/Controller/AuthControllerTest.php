<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\Fixture\LoadAuthenticationData;
use App\Tests\GetUrlTrait;
use Firebase\JWT\JWT;
use DateTime;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Tests\Traits\JsonControllerTest;
use App\Service\JsonWebTokenManager;

class AuthControllerTest extends WebTestCase
{
    use JsonControllerTest;
    use FixturesTrait;
    use GetUrlTrait;

    protected $apiVersion = 'v2';

    /**
     * @var string
     */
    protected $jwtKey;

    /**
     * @var KernelBrowser
     */
    protected $kernelBrowser;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->kernelBrowser = self::createClient();
        $this->loadFixtures([
            LoadAuthenticationData::class
        ]);

        $this->jwtKey = JsonWebTokenManager::PREPEND_KEY . $this->getContainer()->getParameter('kernel.secret');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->kernelBrowser);
        unset($this->fixtures);
    }

    public function testMissingValues()
    {
        $this->kernelBrowser->request('POST', '/auth/login');

        $response = $this->kernelBrowser->getResponse();

        $this->assertJsonResponse($response, Response::HTTP_BAD_REQUEST);

        $content = $response->getContent();
        $data = json_decode($content);
        $this->assertSame($data->status, 'error');
        $this->assertTrue(in_array('missingUsername', $data->errors));
        $this->assertTrue(in_array('missingPassword', $data->errors));
    }

    public function testAuthenticateLegacyUser()
    {
        $this->kernelBrowser->request('POST', '/auth/login', [], [], [], json_encode([
            'username' => 'legacyuser',
            'password' => 'legacyuserpass'
        ]));

        $response = $this->kernelBrowser->getResponse();

        $this->assertJsonResponse($response, Response::HTTP_OK);
        $content = $response->getContent();
        $data = json_decode($content);
        $this->assertSame($data->status, 'success');
        $this->assertTrue(property_exists($data, 'jwt'));
        $token = (array) JWT::decode($data->jwt, $this->jwtKey, array('HS256'));
        $this->assertTrue(array_key_exists('user_id', $token));
        $this->assertSame(1, $token['user_id']);
    }

    public function testAuthenticateUser()
    {
        $this->kernelBrowser->request('POST', '/auth/login', [], [], [], json_encode([
            'username' => 'newuser',
            'password' => 'newuserpass'
        ]));

        $response = $this->kernelBrowser->getResponse();

        $this->assertJsonResponse($response, Response::HTTP_OK);

        $content = $response->getContent();
        $data = json_decode($content);
        $this->assertSame($data->status, 'success');
        $this->assertTrue(property_exists($data, 'jwt'));

        $token = (array) JWT::decode($data->jwt, $this->jwtKey, array('HS256'));
        $this->assertTrue(array_key_exists('user_id', $token));
        $this->assertSame(2, $token['user_id']);
    }

    public function testAuthenticateLegacyUserCaseInsensitve()
    {
        $this->kernelBrowser->request('POST', '/auth/login', [], [], [], json_encode([
            'username' => 'LEGACYUSER',
            'password' => 'legacyuserpass'
        ]));

        $response = $this->kernelBrowser->getResponse();

        $this->assertJsonResponse($response, Response::HTTP_OK);
        $content = $response->getContent();
        $data = json_decode($content);
        $this->assertSame($data->status, 'success');
        $this->assertTrue(property_exists($data, 'jwt'));

        $token = (array) JWT::decode($data->jwt, $this->jwtKey, array('HS256'));
        $this->assertTrue(array_key_exists('user_id', $token));
        $this->assertSame(1, $token['user_id']);
    }

    public function testAuthenticateUserCaseInsensitive()
    {
        $this->kernelBrowser->request('POST', '/auth/login', [], [], [], json_encode([
            'username' => 'NEWUSER',
            'password' => 'newuserpass'
        ]));
        $response = $this->kernelBrowser->getResponse();

        $this->assertJsonResponse($response, Response::HTTP_OK);
        $content = $response->getContent();
        $data = json_decode($content);
        $this->assertSame($data->status, 'success');
        $this->assertTrue(property_exists($data, 'jwt'));

        $token = (array) JWT::decode($data->jwt, $this->jwtKey, array('HS256'));
        $this->assertTrue(array_key_exists('user_id', $token));
        $this->assertSame(2, $token['user_id']);
    }

    public function testWrongLegacyPassword()
    {
        $this->kernelBrowser->request('POST', '/auth/login', [], [], [], json_encode([
            'username' => 'legacyuser',
            'password' => 'wronglegacyuserpass'
        ]));

        $response = $this->kernelBrowser->getResponse();

        $this->assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);

        $content = $response->getContent();
        $data = json_decode($content);
        $this->assertSame($data->status, 'error');
        $this->assertTrue(in_array('badCredentials', $data->errors));
    }

    public function testWrongPassword()
    {
        $this->kernelBrowser->request('POST', '/auth/login', [], [], [], json_encode([
            'username' => 'newuser',
            'password' => 'wrongnewuserpass'
        ]));

        $response = $this->kernelBrowser->getResponse();

        $this->assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);

        $content = $response->getContent();
        $data = json_decode($content);
        $this->assertSame($data->status, 'error');
        $this->assertTrue(in_array('badCredentials', $data->errors));
    }

    public function testAuthenticatingLegacyUserChangesHash()
    {
        $em = $this->kernelBrowser->getContainer()
            ->get('doctrine')
            ->getManager();

        $legacyUser = $em->getRepository(User::class)->find(1);
        $authentication = $legacyUser->getAuthentication();
        $this->assertTrue($authentication->isLegacyAccount());
        $this->assertNotEmpty($authentication->getPasswordSha256());
        $this->assertEmpty($authentication->getPasswordHash());


        $this->kernelBrowser->request('POST', '/auth/login', [], [], [], json_encode([
            'username' => 'legacyuser',
            'password' => 'legacyuserpass'
        ]));

        $response = $this->kernelBrowser->getResponse();
        $this->assertJsonResponse($response, Response::HTTP_OK);

        $this->kernelBrowser->request('POST', '/auth/login', [], [], [], json_encode([
            'username' => 'legacyuser',
            'password' => 'legacyuserpass'
        ]));

        $response = $this->kernelBrowser->getResponse();
        $this->assertJsonResponse($response, Response::HTTP_OK);
        $content = $response->getContent();
        $data = json_decode($content);
        $this->assertSame($data->status, 'success');
        $this->assertTrue(property_exists($data, 'jwt'));

        $token = (array) JWT::decode($data->jwt, $this->jwtKey, array('HS256'));
        $this->assertTrue(array_key_exists('user_id', $token));
        $this->assertSame(1, $token['user_id']);

        $legacyUser = $em->getRepository(User::class)->find(1);
        $authentication = $legacyUser->getAuthentication();
        $this->assertFalse($authentication->isLegacyAccount());
        $this->assertEmpty($authentication->getPasswordSha256());
        $this->assertNotEmpty($authentication->getPasswordHash());
    }

    public function testWhoAmI()
    {
        $jwt = $this->getAuthenticatedUserToken($this->kernelBrowser);
        $this->makeJsonRequest(
            $this->kernelBrowser,
            'get',
            $this->getUrl($this->kernelBrowser, 'ilios_authentication.whoami'),
            null,
            $jwt
        );

        $response = $this->kernelBrowser->getResponse();
        $this->assertJsonResponse($response, Response::HTTP_OK);
        $response = json_decode($response->getContent(), true);

        $this->assertTrue(
            array_key_exists('userId', $response),
            'Response has user_id: ' . var_export($response, true)
        );
        $this->assertSame(
            $response['userId'],
            2,
            'Response has the correct user id: ' . var_export($response, true)
        );
    }

    public function testGetToken()
    {
        $jwt = $this->getAuthenticatedUserToken($this->kernelBrowser);
        $token = (array) JWT::decode($jwt, $this->jwtKey, array('HS256'));
        $this->makeJsonRequest(
            $this->kernelBrowser,
            'get',
            $this->getUrl($this->kernelBrowser, 'ilios_authentication.token'),
            null,
            $jwt
        );
        $response = $this->kernelBrowser->getResponse();
        $response = json_decode($response->getContent(), true);
        $token2 = (array) JWT::decode($response['jwt'], $this->jwtKey, array('HS256'));

        // figure out the delta between issued and expiration datetime
        $exp = DateTime::createFromFormat('U', $token['exp']);
        $iat = DateTime::createFromFormat('U', $token['iat']);
        $interval = $iat->diff($exp);

        // do it again for the new token
        $exp2 = DateTime::createFromFormat('U', $token2['exp']);
        $iat2 = DateTime::createFromFormat('U', $token2['iat']);
        $interval2 = $iat2->diff($exp2);

        // test for sameness
        $this->assertSame($token['user_id'], $token2['user_id']);
        $this->assertSame($token['iss'], $token2['iss']);
        $this->assertSame($token['aud'], $token2['aud']);
        // http://php.net/manual/en/dateinterval.format.php
        $this->assertSame($interval->format('%R%Y/%M/%D %H:%I:%S'), $interval2->format('%R%Y/%M/%D %H:%I:%S'));
    }

    public function testGetTokenWithNonDefaultTtl()
    {
        $jwt = $this->getAuthenticatedUserToken($this->kernelBrowser);
        $this->makeJsonRequest(
            $this->kernelBrowser,
            'get',
            $this->getUrl($this->kernelBrowser, 'ilios_authentication.token') . '?ttl=P2W',
            null,
            $jwt
        );

        $response = $this->kernelBrowser->getResponse();
        $response = json_decode($response->getContent(), true);
        $token = (array) JWT::decode($response['jwt'], $this->jwtKey, array('HS256'));


        $now = new DateTime();
        $expiresAt = DateTime::createFromFormat('U', $token['exp']);

        $this->assertTrue($now->diff($expiresAt)->d > 5);
    }

    public function testInvalidateToken()
    {
        $jwt = $this->getAuthenticatedUserToken($this->kernelBrowser);
        sleep(1);

        $this->makeJsonRequest(
            $this->kernelBrowser,
            'get',
            $this->getUrl($this->kernelBrowser, 'ilios_authentication.invalidate_tokens'),
            null,
            $jwt
        );
        $response = $this->kernelBrowser->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), $response->getContent());

        $this->makeJsonRequest(
            $this->kernelBrowser,
            'GET',
            $this->getUrl(
                $this->kernelBrowser,
                'ilios_api_get',
                ['object' => 'users', 'version' => $this->apiVersion, 'id' => 1]
            ),
            null,
            $jwt
        );
        $response2 = $this->kernelBrowser->getResponse();
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response2->getStatusCode());
        $this->assertRegExp('/Invalid JSON Web Token: Not issued after/', $response2->getContent());
    }
}
