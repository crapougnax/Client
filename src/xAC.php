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
    static protected $lastResponse = [];
    
    /**
     * HTTP Transport
     * @var GuzzleHttp\Client
     */
    protected $transport;
    
    
    /**
     * Class constructor
     * @param string $key
     * @param string $url
     * @param $version $version
     */
    public function __construct($key = null, $url = null, $version = null)
    {
        if (! is_null($key)) {
            self::setApiKey($key);
        }

        if (! is_null($url)) {
            self::setApiBaseUrl($url);
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
        $path = sprintf('%s%sxac-services-%d.txt', sys_get_temp_dir(), DIRECTORY_SEPARATOR, self::$apiVersion);
        if ($refresh === true || ! file_exists($path)) {
            $client = new self;
            $res = $client->call('endpoints?version=' . self::$apiVersion);
            file_put_contents($path, serialize($res));
            self::$services = $res;
        } else {
            self::$services = unserialize(file_get_contents($path));
        }
    }
    
    /**
     * Return the available services as array (or actions if the $entity param is filled)
     * @param xAC\Entity $entity
     * @return array
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
     * @param array  $data
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
            $res = $this->transport->request($method, $endpoint, $params);
        } catch (ConnectException $e) {
            throw new \Exception($e->getMessage());
        
        } catch (ServerException $e) {
            $response = json_decode((string) $e->getResponse()->getBody(), true);
            self::$lastResponse = $response['ACResponse'];
            self::$lastResponse['httpCode'] = $e->getResponse()->getStatusCode();
            throw new \Exception(self::$lastResponse['message']);
        
        } catch (ClientException $e) {
            $response = json_decode((string) $e->getResponse()->getBody(), true);
            self::$lastResponse = $response['ACResponse'];
            self::$lastResponse['httpCode'] = $e->getResponse()->getStatusCode();
            throw new \Exception(self::$lastResponse['message']);
             
        } finally {
            if (isset($res)) {
                $response = json_decode((string) $res->getBody(), !self::$returnObject);
                //if ($response['code'])
                $response = $response['ACResponse'];
            }
        }
        
        self::$lastResponse = $response;
        
        return $response['body']['data'];
    }
    
    /**
     * Return server last response as array
     * @return array
     */
    public static function getLastResponse()
    {
        return self::$lastResponse;
    }
    
    /**
     * Return a singleton instance of the class
     * @return Arrowsphere\Client\xAC
     */
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
