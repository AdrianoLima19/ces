<?php

declare(strict_types=1);

namespace Note;

use Note\Utils\ArrayBag;

class Request
{
    /** @var string */
    protected string $method;

    /** @var string */
    protected string $scheme;

    /** @var string */
    protected string $host;

    /** @var string|null */
    protected ?string $port;

    /** @var string */
    protected string $requestUri;

    /** @var string|null */
    protected ?string $baseUrl;

    /** @var string */
    protected string $path;

    /** @var string|null */
    protected ?string $fragment;

    /** @var \Note\Utils\ArrayBag */
    protected $query;

    /** @var \Note\Utils\ArrayBag */
    protected $body;

    /** @var \Note\Utils\ArrayBag */
    protected $parameters;

    /** @var \Note\Utils\ArrayBag */
    protected $server;

    /** @var \Note\Utils\ArrayBag */
    protected $files;

    /**
     * Initialize the Request object and populate properties from the global server variables
     */
    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];

        $this->parameters = new ArrayBag();
        $this->server = new ArrayBag($_SERVER);
        $this->query = new ArrayBag($_GET);

        $this->body = new ArrayBag($this->parseBody());

        $this->files = new ArrayBag($this->normalizeFiles($_FILES));

        $this->parseRequestUri();
    }

    /**
     * Get the HTTP method of the request
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get the query parameters
     *
     * @return \Note\Utils\ArrayBag
     */
    public function getQuery(): ArrayBag
    {
        return $this->query;
    }

    /**
     * Get the body parameters
     *
     * @return \Note\Utils\ArrayBag
     */
    public function getBody(): ArrayBag
    {
        return $this->body;
    }

    /**
     * Get the Route parameters, if any
     *
     * @return \Note\Utils\ArrayBag
     */
    public function getParameters(): ArrayBag
    {
        return $this->parameters;
    }

    /**
     * Get the server parameters
     *
     * @return \Note\Utils\ArrayBag
     */
    public function getServer(): ArrayBag
    {
        return $this->server;
    }

    /**
     * Get the file parameters
     *
     * @return \Note\Utils\ArrayBag
     */
    public function getFiles(): ArrayBag
    {
        return $this->files;
    }

    /**
     * Get the scheme (http or https) of the request
     *
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * Get the host of the request
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Get the port of the request, if any
     *
     * @return string|null
     */
    public function getPort(): ?string
    {
        return $this->port;
    }

    /**
     * Get the request URI
     *
     * @return string
     */
    public function getRequestUri(): string
    {
        return $this->requestUri;
    }

    /**
     * Get the base URL of the request
     *
     * @return string|null
     */
    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    /**
     * Get the path of the request
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get the fragment of the request, if any
     *
     * @return string|null
     */
    public function getFragment(): ?string
    {
        return $this->fragment;
    }

    /**
     * Get the URL of the request
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->scheme . '://' . $this->host . ($this->port ? ':' . $this->port : '') . $this->getBaseUrl();
    }

    /**
     * Get the full URL of the request
     *
     * @param  bool   $query
     *
     * @return string
     */
    public function getFullUrl(bool $query = false): string
    {
        $url = $this->getUrl() . $this->path;

        if ($query) {
            $queryString = http_build_query($this->query->all());
            $url .= ($queryString ? '?' . $queryString : '');
        }

        return $url . ($this->fragment ? '#' . $this->fragment : '');
    }

    /**
     * Set the HTTP method of the request
     *
     * @param  string  $method
     *
     * @return Request
     */
    public function setMethod(string $method): Request
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Set the query parameters
     *
     * @param  array|\Note\Utils\ArrayBag $query
     *
     * @return Request
     */
    public function setQuery(array|ArrayBag $query): Request
    {
        $this->query = $this->setArray($query);

        return $this;
    }

    /**
     * Set the body parameters
     *
     * @param  array|\Note\Utils\ArrayBag $body
     *
     * @return Request
     */
    public function setBody(array|ArrayBag $body): Request
    {
        $this->body = $this->setArray($body);

        return $this;
    }

    /**
     * Set the Route parameters
     *
     * @param  array|\Note\Utils\ArrayBag $parameters
     *
     * @return Request
     */
    public function setParameters(array|ArrayBag $parameters): Request
    {
        $this->parameters = $this->setArray($parameters);

        return $this;
    }

    /**
     * Set the server parameters
     *
     * @param  array|\Note\Utils\ArrayBag $server
     *
     * @return Request
     */
    public function setServer(array|ArrayBag $server): Request
    {
        $this->server = $this->setArray($server);

        return $this;
    }

    /**
     * Set the file parameters
     *
     * @param  array|\Note\Utils\ArrayBag $files
     *
     * @return Request
     */
    public function setFiles(array|ArrayBag $files): Request
    {
        $this->files = $this->setArray($files);

        return $this;
    }

    /**
     * Set the scheme of the request
     *
     * @param  string  $scheme
     *
     * @return Request
     */
    public function setScheme(string $scheme): Request
    {
        $this->scheme = $scheme;

        return $this;
    }

    /**
     * Set the host of the request
     *
     * @param  string  $host
     *
     * @return Request
     */
    public function setHost(string $host): Request
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Set the port of the request
     *
     * @param  string|null $port
     *
     * @return Request
     */
    public function setPort(string $port = null): Request
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Set the request URI
     *
     * @param  string  $uri
     *
     * @return Request
     */
    public function setRequestUri(string $uri): Request
    {
        $this->requestUri = $uri;

        return $this;
    }

    /**
     * Set the base URL of the request
     *
     * @param  string|null $baseUrl
     *
     * @return Request
     */
    public function setBaseUrl(string $baseUrl = null): Request
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * Set the path of the request
     *
     * @param  string  $path
     *
     * @return Request
     */
    public function setPath(string $path): Request
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Set the fragment of the request
     *
     * @param  string|null $fragment
     *
     * @return Request
     */
    public function setFragment(string $fragment = null): Request
    {
        $this->fragment = $fragment;

        return $this;
    }

    /**
     * Convert an array or ArrayBag instance into an ArrayBag instance
     *
     * @param  array|\Note\Utils\ArrayBag $arr
     *
     * @return \Note\Utils\ArrayBag
     */
    private function setArray(array|ArrayBag $arr): ArrayBag
    {
        if ($arr instanceof ArrayBag) {
            return $arr;
        }

        return new ArrayBag($arr);
    }

    /**
     * Parse the request body into an array
     *
     * @return array
     */
    protected function parseBody(): array
    {
        $body = [];
        return $body;
    }

    /**
     * Parse the request URI and populate related properties
     *
     * @return void
     */
    protected function parseRequestUri(): void
    {
        $this->scheme = 'http' . ($this->server->get('HTTPS', 'off') == 'on' ? 's' : '');

        $baseHost = $this->server->get('HTTP_X_FORWARDED_HOST', $this->server->get('HTTP_HOST', ''));
        $baseHost = explode(':', $baseHost);

        $this->host = $baseHost[0];
        $this->port = $baseHost[1] ?? null;

        $scriptName = $this->server->get('SCRIPT_NAME');
        $requestUri = $this->server->get('REQUEST_URI');

        if ($requestUri === $scriptName) {
            $baseUrl = '';
        } elseif (strpos($requestUri, $scriptName) === 0) {
            $baseUrl = $scriptName;
        } elseif (strpos($requestUri, dirname($scriptName)) === 0) {
            $baseUrl = rtrim(dirname($scriptName), '/');
        } else {
            $baseUrl = '';
        }

        $path = substr($requestUri, strlen($baseUrl));

        if (($pos = strpos($path, '?')) !== false) {
            $path = substr($path, 0, $pos);
        }

        $this->fragment = '';
        $this->requestUri = $requestUri;
        $this->baseUrl = $baseUrl;
        $this->path = $path;
    }

    /**
     * Normalize the file array to a more convenient structure
     *
     * @param  array $files
     *
     * @return array
     */
    protected function normalizeFiles(array $files): array
    {
        $normalized = [];

        foreach ($files as $key => $value) {
            if (is_array($value) && isset($value['tmp_name'])) {
                $normalized[$key] = $this->parseFileSpecs($value);
            } elseif (is_array($value)) {
                $normalized[$key] = $this->normalizeFiles($value);
            } else {
                throw new \InvalidArgumentException("Invalid value in files specification");
            }
        }

        return $normalized;
    }

    /**
     * Parse the file specs array into a normalized structure
     *
     * @param  array $value
     *
     * @return array
     */
    private function parseFileSpecs(array $value): array
    {
        if (isset($value['tmp_name']) && is_array($value['tmp_name'])) {
            $parsedFiles = [];

            foreach (array_keys($value['tmp_name']) as $key) {
                $file = [
                    'name' => $value['name'][$key] ?? null,
                    'type' => $value['type'][$key] ?? null,
                    'tmp_name' => $value['tmp_name'][$key],
                    'error' => $value['error'][$key] ?? null,
                    'size' => $value['size'][$key] ?? null,
                ];
                $parsedFiles[$key] = $this->parseFileSpecs($file);
            }

            return $parsedFiles;
        }

        return $value;
    }
}
