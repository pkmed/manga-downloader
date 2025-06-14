<?php

namespace App\Factory\GuzzleClient\Enum;

/**
 * Parameters used to configure the Guzzle HTTP client itself.
 */
enum GuzzleClientParameters: string
{
    /**
     * The base URI for all requests made by the client.
     * It is prepended to the relative URI in each request.
     * Example: 'https://api.example.com'
     */
    case BASE_URI = 'base_uri';

    /**
     * HTTP headers to be sent with each request.
     * Can include things like 'Authorization' or 'Content-Type'.
     * Example: ['Authorization' => 'Bearer token']
     */
    case HEADERS = 'headers';

    /**
     * Timeout (in seconds) for the request.
     * If the request takes longer than this time, it will be aborted.
     * Example: 30 (seconds)
     */
    case TIMEOUT = 'timeout';

    /**
     * Whether to output verbose information for debugging purposes.
     * If true, Guzzle will output detailed request and response information.
     * Example: true
     */
    case VERBOSE = 'verbose';

    /**
     * Whether to verify the SSL certificate of the server.
     * Can be set to true or false. Setting to false can be useful for development, but it's not recommended in production.
     * Example: true
     */
    case VERIFY = 'verify';

    /**
     * A proxy server to use for requests.
     * Can be used to route requests through a specific proxy.
     * Example: 'http://proxy.example.com:8080'
     */
    case PROXY = 'proxy';

    /**
     * Whether or not to send cookies with requests.
     * If true, Guzzle will automatically handle cookies in the request.
     * Example: true
     */
    case COOKIES = 'cookies';

    /**
     * Whether to follow redirects automatically.
     * If false, Guzzle will not follow 3xx redirects and will instead return the response.
     * Example: true
     */
    case ALLOW_REDIRECTS = 'allow_redirects';

    /**
     * Whether HTTP errors should be thrown as exceptions.
     * If true, Guzzle will throw exceptions for responses with status codes of 400 or higher.
     * Example: true
     */
    case HTTP_ERRORS = 'http_errors';

    /**
     * A map of decoders to decode certain content types (e.g., JSON, XML).
     * Allows you to automatically decode response bodies based on their content type.
     * Example: ['application/json' => 'json_decode']
     */
    case DECODERS = 'decoders';

    /**
     * Events that can be triggered during request processing (such as logging).
     * Example: ['request' => function() { ... }]
     */
    case EVENTS = 'events';

    /**
     * For sending multipart requests, such as file uploads.
     * Example: [['name' => 'file', 'contents' => fopen('file.txt', 'r')]]
     */
    case MULTIPART = 'multipart';

    /**
     * For sending form data in the request body with `application/x-www-form-urlencoded`.
     * Example: ['param1' => 'value1', 'param2' => 'value2']
     */
    case FORM_PARAMS = 'form_params';

    /**
     * For sending query parameters in the URL.
     * Example: ['param1' => 'value1', 'param2' => 'value2']
     */
    case QUERY = 'query';

    /**
     * For streaming the response body.
     * If true, the body is streamed rather than being fully downloaded into memory.
     * Example: true
     */
    case STREAM = 'stream';

    /**
     * cURL-specific options to pass directly to the cURL handler.
     * You can configure low-level cURL options if necessary.
     * Example: [CURLOPT_SSL_VERIFYPEER => false]
     */
    case CURL_OPTIONS = 'curl';

    /**
     * Custom handler to manage request and response processing.
     * This is typically used for defining a custom middleware stack.
     * Example: A custom handler stack or middleware pipeline.
     */
    case HANDLER = 'handler';

}