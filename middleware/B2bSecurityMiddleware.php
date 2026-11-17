<?php

namespace Grocy\Middleware;

use Grocy\Services\B2bSecurityService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class B2bSecurityMiddleware extends BaseMiddleware
{
	private ?B2bSecurityService $securityService = null;

	private function SecurityService(): B2bSecurityService
	{
		if ($this->securityService === null) {
			$this->securityService = new B2bSecurityService();
		}
		return $this->securityService;
	}

	public function __invoke(Request $request, RequestHandler $handler): Response
	{
		$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

		// 1. Rate Limiting Preventivo (150 peticiones por minuto por cliente)
		if (!$this->SecurityService()->CheckRateLimit($ip, 200, 60)) {
			$response = $this->ResponseFactory->createResponse(429);
			$response->getBody()->write(json_encode([
				'error' => 'Too Many Requests',
				'message' => 'Límite de peticiones excedido. Por favor intente más tarde.',
				'status' => 429
			], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
			return $response->withHeader('Content-Type', 'application/json')->withHeader('Retry-After', '60');
		}

		// 2. Validación opcional de X-API-KEY / Bearer Token en llamadas a la API B2B
		$apiKey = $request->getHeaderLine('X-API-KEY');
		if (empty($apiKey)) {
			$authHeader = $request->getHeaderLine('Authorization');
			if (str_starts_with($authHeader, 'Bearer ')) {
				$apiKey = substr($authHeader, 7);
			}
		}

		if (!empty($apiKey)) {
			$authInfo = $this->SecurityService()->ValidateApiKey($apiKey);
			if ($authInfo === null) {
				$response = $this->ResponseFactory->createResponse(401);
				$response->getBody()->write(json_encode([
					'error' => 'Unauthorized',
					'message' => 'La llave de API (X-API-KEY) proporcionada es inválida o ha expirado.',
					'status' => 401
				], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
				return $response->withHeader('Content-Type', 'application/json');
			}

			// Inyectar datos de autenticación en la solicitud
			$request = $request->withAttribute('b2b_auth', $authInfo);
		}

		// 3. Procesar solicitud
		$response = $handler->handle($request);

		// 4. Inyectar Encabezados de Seguridad Corporativa
		return $response
			->withHeader('X-Content-Type-Options', 'nosniff')
			->withHeader('X-Frame-Options', 'SAMEORIGIN')
			->withHeader('X-XSS-Protection', '1; mode=block')
			->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
	}
}
