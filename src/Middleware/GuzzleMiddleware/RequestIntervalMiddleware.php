<?php

namespace App\Middleware\GuzzleMiddleware;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Sets the request interval to avoid flooding the server.
 */
readonly class RequestIntervalMiddleware
{
    public function __construct(
        private int $minSleep = 1,
        private int $maxSleep = 2
    )
    {
    }

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler): PromiseInterface {
            sleep(rand($this->minSleep, $this->maxSleep));
            return $handler($request, $options);
        };
    }
}