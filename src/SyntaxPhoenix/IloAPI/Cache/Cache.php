<?php

namespace src\SyntaxPhoenix\IloAPI\Cache;

class Cache
{

    /** @var mixed */
    private $cachedRequests;

    public function __construct()
    {
        $cachedRequests = [];
    }

    public function cacheSite(string $requestUrl, array $data): void 
    {
        $this->cachedRequests[$requestUrl] = $data;
    }

    public function isCached(string $requestUrl): bool
    {
        return isset($this->cachedRequests[$requestUrl]);
    }

    public function getCachedSite(string $requestUrl): array
    {
        if ($this->isCached($requestUrl)) {
            return $this->cachedRequests[$requestUrl];
        }
        return null;
    }

}