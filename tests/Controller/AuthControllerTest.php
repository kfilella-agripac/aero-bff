<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
    public function testLoginRouteExists(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'invalid@user.com',
            'password' => 'wrong'
        ]));

        $response = $client->getResponse();
        // Con Spring actualizado, credenciales inexistentes devuelven 401
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testCatalogRoutesExist(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/pistas');

        // La ruta devuelve 200 OK con el listado de pistas
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
