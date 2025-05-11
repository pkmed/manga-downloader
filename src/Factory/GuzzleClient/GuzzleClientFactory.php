<?php

namespace App\Factory\GuzzleClient;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\HandlerStack;

class GuzzleClientFactory
{
    /**
     * @param array|null $guzzleClientParams example [GuzzleClientParameters->value => *parameter value*]
     * @param callable[]|null $guzzleClientMiddlewares
     * @return GuzzleClient
     */
    public static function createClient(array $guzzleClientParams = null, array $guzzleClientMiddlewares = null): GuzzleClient
    {
        $clientParams = [];

        if (!empty($guzzleClientParams)) {
            $filteredParams = array_filter(
                $guzzleClientParams,
                fn (string $key) => GuzzleClientParameters::tryFrom($key),
                ARRAY_FILTER_USE_KEY
            );
            $clientParams = $filteredParams;
        }

        if (!empty($guzzleClientMiddlewares)) {
            $stack = HandlerStack::create(new CurlMultiHandler());
            foreach ($guzzleClientMiddlewares as $middleware) {
                $stack->push($middleware);
            }

            $clientParams[GuzzleClientParameters::HANDLER->value] = $stack;
            $clientParams['debug'] = true;
        }

        return new GuzzleClient($clientParams);
    }
}