<?php

namespace App\Tests\Controller;

use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class CompanyControllerTest extends WebTestCase
{
    private $client;
    private $token;
    private $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
        $this->client = $this->kernel instanceof KernelInterface ? $this->client : self::createClient();

        $loginAdm = [
            'email' => 'admin@example.com',
            'password' => 'adminpassword',
        ];

        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($loginAdm)
        );

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $this->token = json_decode($response->getContent(), true)['data']['token'];
    }

    public function testCreateCompany(): void
    {
        $newCompanyData = [
            'name' => $this->faker->name,
            'cnpj' => $this->generateFakeCnpj(),
        ];

        $this->client->request('POST', '/api/company', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$this->token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($newCompanyData));

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testCreatePartner(): void
    {
        $newPartnerData = [
            'name' => $this->faker->name,
            'cpf' => $this->generateFakeCpf(),
        ];

        $this->client->request('POST', '/api/partner', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$this->token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($newPartnerData));

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testAddPartnerToCompany(): void
    {
        // add company
        $newCompanyData = [
            'name' => $this->faker->name,
            'cnpj' => $this->generateFakeCnpj(),
        ];
        $this->client->request('POST', '/api/company', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$this->token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($newCompanyData));

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $company = json_decode($response->getContent(), true)['data'];

        // add partner
        $newPartnerData = [
            'name' => $this->faker->name,
            'cpf' => $this->generateFakeCpf(),
        ];
        $this->client->request('POST', '/api/partner', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$this->token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($newPartnerData));
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $partner = json_decode($response->getContent(), true)['data'];

        // sync both
        $this->client->request('POST', '/api/company/'.$company['id'].'/partner', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$this->token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['partnerId' => $partner['id']]));
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        // get partners from company and assert
        $this->client->request('GET', '/api/company/'.$company['id'].'/partners', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$this->token,
            'CONTENT_TYPE' => 'application/json',
        ]);

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $expectedPartner = $partner;
        $this->assertContains($expectedPartner, json_decode($response->getContent(), true)['data']);
    }

    public function testRemovePartnerFromCompany(): void
    {
        // add company
        $newCompanyData = [
            'name' => $this->faker->name,
            'cnpj' => $this->generateFakeCnpj(),
        ];
        $this->client->request('POST', '/api/company', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$this->token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($newCompanyData));

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $company = json_decode($response->getContent(), true)['data'];

        // add partner
        $newPartnerData = [
            'name' => $this->faker->name,
            'cpf' => $this->generateFakeCpf(),
        ];
        $this->client->request('POST', '/api/partner', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$this->token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($newPartnerData));
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $partner = json_decode($response->getContent(), true)['data'];

        // sync both
        $this->client->request('POST', '/api/company/'.$company['id'].'/partner', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$this->token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['partnerId' => $partner['id']]));
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        // get partners from company and assert that partner is there
        $this->client->request('GET', '/api/company/'.$company['id'].'/partners', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$this->token,
            'CONTENT_TYPE' => 'application/json',
        ]);

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $expectedPartner = $partner;
        $this->assertContains($expectedPartner, json_decode($response->getContent(), true)['data']);

        // remove partner from company and assert that is empty
        $this->client->request('DELETE', '/api/company/'.$company['id']."/remove-partner/{$partner['id']}", [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$this->token,
            'CONTENT_TYPE' => 'application/json',
        ]);
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        // call endpoint to see if is empty now
        $this->client->request('GET', '/api/company/'.$company['id'].'/partners', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$this->token,
            'CONTENT_TYPE' => 'application/json',
        ]);

        $response = $this->client->getResponse();

        $this->assertEmpty(json_decode($response->getContent(), true)['data']);
    }

    private function generateFakeCnpj(): string
    {
        $n1 = random_int(0, 9);
        $n2 = random_int(0, 9);
        $n3 = random_int(0, 9);
        $n4 = random_int(0, 9);
        $n5 = random_int(0, 9);
        $n6 = random_int(0, 9);
        $n7 = random_int(0, 9);
        $n8 = random_int(0, 9);
        $n9 = random_int(0, 9);
        $n10 = random_int(0, 9);
        $n11 = random_int(0, 9);
        $n12 = random_int(0, 9);

        $d1 = $n12 * 2 + $n11 * 3 + $n10 * 4 + $n9 * 5 + $n8 * 6 + $n7 * 7 + $n6 * 8 + $n5 * 9 + $n4 * 2 + $n3 * 3 + $n2 * 4 + $n1 * 5;
        $d1 = 11 - ($d1 % 11);
        if ($d1 >= 10) {
            $d1 = 0;
        }

        $d2 = $d1 * 2 + $n12 * 3 + $n11 * 4 + $n10 * 5 + $n9 * 6 + $n8 * 7 + $n7 * 8 + $n6 * 9 + $n5 * 2 + $n4 * 3 + $n3 * 4 + $n2 * 5 + $n1 * 6;
        $d2 = 11 - ($d2 % 11);
        if ($d2 >= 10) {
            $d2 = 0;
        }

        return "$n1$n2$n3$n4$n5$n6$n7$n8$n9$n10$n11$n12$d1$d2";
    }

    private function generateFakeCpf(): string
    {
        $n1 = random_int(0, 9);
        $n2 = random_int(0, 9);
        $n3 = random_int(0, 9);
        $n4 = random_int(0, 9);
        $n5 = random_int(0, 9);
        $n6 = random_int(0, 9);
        $n7 = random_int(0, 9);
        $n8 = random_int(0, 9);
        $n9 = random_int(0, 9);

        // primeiro dígito verificador
        $d1 = $n1 * 10 + $n2 * 9 + $n3 * 8 + $n4 * 7 + $n5 * 6 + $n6 * 5 + $n7 * 4 + $n8 * 3 + $n9 * 2;
        $d1 = 11 - ($d1 % 11);
        if ($d1 >= 10) {
            $d1 = 0;
        }

        // pegundo dígito verificador
        $d2 = $n1 * 11 + $n2 * 10 + $n3 * 9 + $n4 * 8 + $n5 * 7 + $n6 * 6 + $n7 * 5 + $n8 * 4 + $n9 * 3 + $d1 * 2;
        $d2 = 11 - ($d2 % 11);
        if ($d2 >= 10) {
            $d2 = 0;
        }

        return "$n1$n2$n3$n4$n5$n6$n7$n8$n9$d1$d2";
    }
}
