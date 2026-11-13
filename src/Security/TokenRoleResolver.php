<?php

namespace App\Security;

use App\Service\SpringBackendClient;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

/**
 * Obtiene el rol del usuario a partir de su token Bearer consultando /auth/me en Spring.
 *
 * El BFF todavía no autentica (pendiente P2), así que no puede confiar en X-User-Role:
 * lo envía el cliente. El rol se cachea 60 s por token para no consultar Spring en cada petición.
 */
class TokenRoleResolver
{
    private const TTL_SECONDS = 60;

    public function __construct(
        private readonly SpringBackendClient $springClient,
        private readonly CacheInterface $cache,
    ) {
    }

    public function resolve(?string $authorization): ?string
    {
        if ($authorization === null || !preg_match('/^Bearer\s+(\S+)$/i', trim($authorization), $matches)) {
            return null;
        }
        $token = $matches[1];

        return $this->cache->get('bff_token_role_' . hash('sha256', $token), function (ItemInterface $item) use ($token): ?string {
            $item->expiresAfter(self::TTL_SECONDS);
            try {
                $response = $this->springClient->request('GET', '/auth/me', [
                    'headers' => ['Authorization' => 'Bearer ' . $token],
                ]);
                if ($response->getStatusCode() !== 200) {
                    $item->expiresAfter(5);

                    return null;
                }
                $data = $response->toArray(false);
                $user = $data['data'] ?? $data['user'] ?? $data;

                return isset($user['role']) ? (string) $user['role'] : null;
            } catch (ExceptionInterface) {
                $item->expiresAfter(5);

                return null;
            }
        });
    }
}
