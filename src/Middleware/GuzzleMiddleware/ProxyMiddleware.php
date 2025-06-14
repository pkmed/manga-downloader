<?php

namespace App\Middleware\GuzzleMiddleware;

use App\Factory\GuzzleClient\Enum\GuzzleRequestParameters;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;

class ProxyMiddleware
{
    private string $proxyUrl = 'http://scraperapi{binary_target}:447dd0ccd903473c24f340d986f44721@proxy-server.scraperapi.com:8001';

    public function __construct(
        private readonly bool $isBinaryTarget = false
    )
    {
    }

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array &$options) use ($handler): PromiseInterface {
            $proxyUrl = str_replace('{binary_target}', $this->isBinaryTarget ? '.binary_target=true' : '', $this->proxyUrl);

            $options[GuzzleRequestParameters::PROXY->value] = $proxyUrl;
            return $handler($request, $options);
        };
    }
}