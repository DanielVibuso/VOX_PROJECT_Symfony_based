<?php

namespace App\Tests\Controller;

use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    public function testLoginSucess(): void
    {
        $client = self::createClient();

        $data = [
            'email' => 'admin@example.com',
            'password' => 'adminpassword',
        ];

        $client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $token = json_decode($response->getContent(), true)['data']['token'];

        $this->assertNotNull($token, 'Token JWT should be present in the response');
    }

    public function testLoginWrongCredentials(): void
    {
        $client = self::createClient();

        $data = [
            'email' => 'teste2@hotmail.com',
            'password' => '123456789111',
        ];

        $client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $response = $client->getResponse();

        $this->assertEquals(401, $response->getStatusCode());

        $token = json_decode($response->getContent(), true)['data']['token'];

        $this->assertNull($token, 'Token JWT should not be present in the response');
    }

    public function testCreateUser(): void
    {
        $client = self::createClient();

        $faker = Factory::create();

        $data = [
            'email' => 'admin@example.com',
            'password' => 'adminpassword',
        ];

        $client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $token = json_decode($response->getContent(), true)['data']['token'];

        $this->assertNotNull($token, 'Token JWT should not be present in the response');

        $newUser = [
            'email' => $faker->unique()->safeEmail,
            'password' => '123456789111',
            'role' => ['ROLE_ADMIN'],
        ];

        $client->request('POST', '/api/user/register', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($newUser));

        $response = $client->getResponse();

        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testCreateUserWithoutEmailGetError(): void
    {
        $client = self::createClient();

        $data = [
            'email' => 'admin@example.com',
            'password' => 'adminpassword',
        ];

        $client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $token = json_decode($response->getContent(), true)['data']['token'];

        $this->assertNotNull($token, 'Token JWT should not be present in the response');

        $newUser = [
            'password' => '123456789111',
            'role' => ['ROLE_USER'],
        ];

        $client->request('POST', '/api/user/register', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($newUser));

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }
}
