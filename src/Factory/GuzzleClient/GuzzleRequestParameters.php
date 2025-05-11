<?php

namespace App\Factory\GuzzleClient;

/**
 * Parameters used for configuring individual HTTP requests.
 */
enum GuzzleRequestParameters: string
{
    /**
     * The body of the request.
     * It can be a string, an array (for form data), or a resource (like a file).
     * Example: 'text content', ['param' => 'value'], fopen('file.txt', 'r')
     */
    case BODY = 'body';

    /**
     * Query parameters to append to the URI.
     * These are appended to the URL as a query string.
     * Example: ['param1' => 'value1', 'param2' => 'value2']
     */
    case QUERY = 'query';

    /**
     * HTTP headers to send with the request.
     * Example: ['Content-Type' => 'application/json', 'Authorization' => 'Bearer token']
     */
    case HEADERS = 'headers';

    /**
     * Form data to send with the request.
     * This is sent as `application/x-www-form-urlencoded` by default.
     * Example: ['param1' => 'value1', 'param2' => 'value2']
     */
    case FORM_PARAMS = 'form_params';

    /**
     * Multipart form data for file uploads.
     * Example: [['name' => 'file', 'contents' => fopen('file.txt', 'r')]]
     */
    case MULTIPART = 'multipart';

    /**
     * An array of cookies to send with the request.
     * Example: ['cookie_name' => 'cookie_value']
     */
    case COOKIES = 'cookies';

    /**
     * Whether to stream the response body.
     * If true, the response body is streamed rather than being loaded into memory entirely.
     * Example: true
     */
    case STREAM = 'stream';

    /**
     * Timeout in seconds for the request.
     * This is the maximum time to wait for the entire request to complete.
     * Example: 30 (seconds)
     */
    case TIMEOUT = 'timeout';

    /**
     * Timeout in seconds for establishing a connection.
     * This controls how long to wait while establishing the initial connection to the server.
     * Example: 10 (seconds)
     */
    case CONNECTION_TIMEOUT = 'connection_timeout';

    /**
     * Whether to verify SSL certificates.
     * Set to true to enable SSL verification (recommended for production).
     * Example: true or false
     */
    case VERIFY = 'verify';

    /**
     * The proxy server to route the request through.
     * Example: 'http://proxy.example.com:8080'
     */
    case PROXY = 'proxy';

    /**
     * Whether to follow redirects automatically.
     * Set to true to allow automatic redirection (default behavior).
     * Example: true
     */
    case ALLOW_REDIRECTS = 'allow_redirects';

    /**
     * Whether to throw exceptions for HTTP errors (status codes 4xx and 5xx).
     * If true, Guzzle will throw exceptions on error responses (default behavior).
     * Example: true
     */
    case HTTP_ERRORS = 'http_errors';

    /**
     * Whether or not to include request headers in the response (for debugging purposes).
     * Example: true
     */
    case DEBUG = 'debug';

    /**
     * The name of a custom handler to use for the request.
     * Typically, this is a custom handler stack for modifying requests and responses.
     * Example: a custom handler stack.
     */
    case HANDLER = 'handler';

    /**
     * The response to send in case of an error.
     * Example: An error response for simulating a server-side failure.
     */
    case ERROR_RESPONSE = 'error_response';

    /**
     * Custom user data to send with the request (arbitrary data).
     * Example: ['user' => 'john_doe', 'type' => 'admin']
     */
    case USER_DATA = 'user_data';

    /**
     * Whether to include the request headers in the response's debug output.
     * Example: true
     */
    case VERBOSE = 'verbose';
}