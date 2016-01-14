<?php

namespace Arrowsphere\Client;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ClientException;


class xAC
{
    use xAC\MagicFactoryTrait;
    
    /**
     * API Key used to consume services
     * @var string
     */
    static protected $apiKey;
    
    /**
     * Which API version to use
     * @var integer
     */
    static protected $apiVersion = 2;
    
    /**
     * Singleton instance of the client
     * @var xAC\Client
     */
    static protected $client;
    
    /**
     * Whether to return object or array (default)
     * @var boolean
     */
    static protected $returnObject = false;
    
    /**
     * API Base URL
     * @var string
     */
    static protected $url;
    
    /**
     * Array of available services
     * @var array
     */
    static protected $services = [];
    
    /**
     * Last API response as array
     * @var array
     */
    protected $lastResponse = [];
    
    /**
     * HTTP Transport
     * @var GuzzleHttp\Client
     */
    protected $transport;
    
    
    public function __construct($key = null, $version = null)
    {
        if (! is_null($key)) {
            self::setApiKey($key);
        }
        
        if (! is_null($version)) {
            self::setApiVersion($version);
        }
    }
    
    /**
     * Set API key to be used for all instances
     * @param string $key
     */
    public static function setApiKey($key)
    {
        self::$apiKey = $key;
    }

    /**
     * Set API base url to be used for all instances
     * @param string $url
     */
    public static function setApiBaseUrl($url)
    {
        self::$url = $url;
    }
    
    /**
     * Define which API version to use
     * @param integer $version
     */
    public static function setApiVersion($version)
    {
        if (! in_array($version, [1,2])) {
            throw new \Exception(sprintf("The %d version is not supported", $version));
        }
        self::$apiVersion = $version;
    }
    
    /**
     * Retrieve definition of available API services 
     * @param boolean $refresh
     */
    public static function initServices($refresh = false)
    {
        $path = '/tmp/xac-services.txt';
        
        if ($refresh === true || ! file_exists($path)) {
            $client = new self;
            $res = $client->call('endpoints');
            file_put_contents($path, serialize($res));
            self::$services = $res;
        } else {
            self::$services = unserialize(file_get_contents($path));
        }
    }
    
    /**
     * 
     * @param xAC\Entity $entity
     */
    public static function getServices(xAC\Entity $entity = null)
    {
        if ($entity) {
            return $entity->getServices();
        }
        
        if (count(self::$services) == 0) {
            self::initServices();
        }
        
        return self::$services;
    }
    
    
    /**
     * Make a HTTP Request and return response
     * @param string $endpoint
     * @param string $method
     * @throws \Exception
     * @return array
     */
    public function call($endpoint, $method = 'GET', array $data = [])
    {
        $params = [];
        if (count($data) > 0) {
            $params['json'] = $data;
        }
        
        try {
            $this->initTransport();
            $res = $this->transport->request(
                $method, 
                $endpoint, 
                $params
            );
        } catch (ConnectException $e) {
            throw new \Exception($e->getMessage());
        
        } catch (ServerException $e) {
            throw new \Exception($e->getMessage());
        
        } catch (ClientException $e) {
            throw new \Exception($e->getMessage());
             
        } finally {
            if (isset($res)) {
                $response = json_decode((string) $res->getBody(), !self::$returnObject);
                //if ($response['code'])
                $response = $response['ACResponse'];
            }
        }
        
        $this->lastResponse = $response;
        
        return $response['body']['data'];
    }
    
    public function getLastResponse()
    {
        return $this->lastResponse;
    }
    
    public static function getInstance()
    {
        if (is_null(self::$client)) {
            self::$client = new self();
        }
        
        return self::$client;
    }
    
    /**
     * Instanciate new HTTP transport instance
     */
    protected function initTransport()
    {
        if (is_null($this->transport)) {
            $this->transport = new HttpClient([
                'base_uri' => self::$url,
                'headers'=> [
                    'Accept'     => sprintf('application/vnd.xac.v%d+json', self::$apiVersion),
                    'AC-API-KEY' => self::$apiKey,
                ],
            ]);
        }
    }
}
