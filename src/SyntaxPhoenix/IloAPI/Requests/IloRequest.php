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

    public function logout(): bool
    {
        if ($this->session) {
            $url = substr($this->location, strlen($this->basicUrl), strlen($this->location) - strlen($this->basicUrl));
            $data = $this->delete($url);

            if ($data && isset($data['body']) && isset($data['body']['Messages']) && isset($data['body']['Messages'][0]) 
                && isset($data['body']['Messages'][0]['MessageID']) && $data['body']['Messages'][0]['MessageID'] == 'Base.0.10.Success') {
                $this->session = null;
                $this->location = null;
                return true;
            }
        }

        return false;        
    }

    public function getSession(): string
    {
        return $this->session;
    }

    public function delete(string $url): array
    {
        return $this->requestCurl($url, 'DELETE');
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
        } else if ($requestMethod == 'DELETE') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
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
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $this->verifySsl);

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
        
        $content = curl_exec($curl);
        if ($content === false) {
            throw new IloResponseException(curl_error($curl), curl_errno($curl));
        }
        $response = json_decode($content, true);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($status < 200 || $status > 299) {
            throw new IloResponseException($response['Messages'][0]['MessageID'], $status);
        }
        
        curl_close($curl);

        return [
            'header' => $headers,
            'body' => $response,
            'status' => $status
        ];
    }
}