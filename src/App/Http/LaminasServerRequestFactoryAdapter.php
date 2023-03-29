<?php

declare(strict_types=1);

namespace Whirlwind\Adapter\League\App\Http;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\ServerRequestFilter\FilterServerRequestInterface;
use Whirlwind\App\Http\ServerRequestFactoryInterface;

class LaminasServerRequestFactoryAdapter extends ServerRequestFactory implements ServerRequestFactoryInterface
{
    public static function fromGlobals(
        ?array $server = null,
        ?array $query = null,
        ?array $body = null,
        ?array $cookies = null,
        ?array $files = null,
        ?FilterServerRequestInterface $requestFilter = null
    ): ServerRequest {
        return parent::fromGlobals($server, $query, $body, $cookies, $files);
    }
}
