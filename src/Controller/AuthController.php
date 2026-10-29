<?php

namespace App\Controller;

use App\Service\SpringBackendClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_auth_')]
class AuthController extends AbstractController
{
    public function __construct(
        private SpringBackendClient $springClient
    ) {}

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): Response
    {
        return $this->springClient->forward($request, '/auth/login');
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(Request $request): Response
    {
        return $this->springClient->forward($request, '/auth/me');
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(Request $request): Response
    {
        return $this->springClient->forward($request, '/auth/logout');
    }
}
