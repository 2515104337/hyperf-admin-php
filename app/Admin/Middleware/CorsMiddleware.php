<?php

declare(strict_types=1);

namespace App\Admin\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() === 'OPTIONS') {
            $response = \Hyperf\Context\Context::get(ResponseInterface::class);
            if (! $response instanceof ResponseInterface) {
                $response = new \Hyperf\HttpMessage\Server\Response();
            }
        } else {
            $response = $handler->handle($request);
        }

        // Notes:
        // - Access-Control-Allow-Origin cannot be "*" when Allow-Credentials is true.
        // - We echo the request Origin when it is in the allowlist.
        $origin = $request->getHeaderLine('Origin');
        $allowOriginsRaw = (string) (getenv('CORS_ALLOW_ORIGINS') ?: '*'); // comma-separated
        $allowOrigins = array_values(array_filter(array_map('trim', explode(',', $allowOriginsRaw))));
        $allowCredentials = filter_var((string) (getenv('CORS_ALLOW_CREDENTIALS') ?: 'false'), FILTER_VALIDATE_BOOL);

        // If credentials are enabled, wildcard(*) is unsafe and should not be honored.
        if ($allowCredentials && in_array('*', $allowOrigins, true)) {
            $allowOrigins = array_values(array_diff($allowOrigins, ['*']));
        }

        $allowOriginHeader = null;
        $varyOrigin = false;

        if ($origin !== '') {
            if (in_array('*', $allowOrigins, true)) {
                $allowOriginHeader = '*';
            } elseif (in_array($origin, $allowOrigins, true)) {
                $allowOriginHeader = $origin;
                $varyOrigin = true;
            }
        } else {
            // Non-browser request (no Origin). Use a deterministic value.
            $allowOriginHeader = in_array('*', $allowOrigins, true) ? '*' : ($allowOrigins[0] ?? null);
        }

        if ($request->getMethod() === 'OPTIONS') {
            $response = $response->withStatus(204);
        }

        if ($allowOriginHeader !== null) {
            $response = $response->withHeader('Access-Control-Allow-Origin', $allowOriginHeader);
        }

        if ($varyOrigin) {
            $vary = $response->getHeaderLine('Vary');
            if ($vary === '') {
                $response = $response->withHeader('Vary', 'Origin');
            } elseif (! str_contains($vary, 'Origin')) {
                $response = $response->withHeader('Vary', $vary . ', Origin');
            }
        }

        $response = $response
            ->withHeader('Access-Control-Allow-Headers', 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With, X-Token')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
            ->withHeader('Access-Control-Max-Age', '86400');

        if ($allowCredentials) {
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }
}
