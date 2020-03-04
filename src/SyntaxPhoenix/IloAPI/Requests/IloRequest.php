<?php

namespace src\SyntaxPhoenix\IloAPI\Requests;

use src\SyntaxPhoenix\IloAPI\Cache\Cache;
use src\SyntaxPhoenix\IloAPI\Exceptions\IloResponseException;

class IloRequest {

    /** @var string */
    private $session = null;

    /** @var string */
    private $basicUrl;

    /** @var bool */
    private $verifySsl;

    /** @var string */
    private $location;

    /** @var Cache */
    private $cache;

    public function __construct(string $basicUrl)
    {
        $this->basicUrl = $basicUrl;
        $this->cache = new Cache();
    }

    public function login(string $username, string $password, bool $verifySsl = false): bool
    {
        $this->verifySsl = $verifySsl;
        if (!$this->session) {
            $requestBody = [
                'UserName' => $username,
                'Password' => $password
            ];
    
            $data = $this->post('/redfish/v1/sessions/', $requestBody);
    
            if ($data && isset($data['header']) && isset($data['header']['x-auth-token']) && isset($data['header']['location'])) {
                $this->session = $data['header']['x-auth-token'][0];
                $this->location = $data['header']['location'][0];
                return true;
            }
        }

        return false;
    }

    public function logout(): void
    {

    }

    public function getSession(): string
    {
        return $this->session;
    }

    public function post(string $url, array $body): array
    {
        return $this->requestCurl($url, 'POST', $body); 
    }

    public function get(string $url, bool $useCaching = true): array
    {
        if ($useCaching && $this->cache->isCached($url)) {
            return $this->cache->getCachedSite($url);
        }   
        $response = $this->requestCurl($url, 'GET');    
        $this->cache->cacheSite($url, $response);
        return $response;
    }

    private function requestCurl(string $url, string $requestMethod, array $body = []): array 
    {
        $curl = curl_init();
        $headers = [];

        $requestUrl = $this->basicUrl . $url;

        curl_setopt($curl, CURLOPT_URL, $requestUrl);

        if ($requestMethod == 'POST') {
            $bodyString = json_encode($body);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $bodyString);
        }
        
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $requestHeaders = [
            'Content-Type: application/json'
        ];
        if ($this->session) {
            $requestHeaders[] = 'X-Auth-Token: ' . $this->session;
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, $this->verifySsl);

        curl_setopt($curl, CURLOPT_HEADERFUNCTION,
            function($curl, $header) use (&$headers)
            {
                $length = strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2) { // ignore invalid headers
                    return $length;
                }

                $headers[strtolower(trim($header[0]))][] = trim($header[1]);

                return $length;
            }
        );
        
        $response = json_decode(curl_exec($curl), true);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($status < 200 || $status > 299) {
            throw new IloResponseException('Ilo-Exception', $status);
        }
        
        curl_close($curl);

        return [
            'header' => $headers,
            'body' => $response,
            'status' => $status
        ];
    }
}