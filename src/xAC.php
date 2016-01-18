<?php

namespace Arrowsphere\Client;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ClientException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


/**
 * xAC Client Facade class
 * @author olivier@lepine.fr
 *
 */
class xAC
{
    /**
     * This trait provides a __staticCall() method to handle "magic" instances
     * of Cursor() and Entity() classes
     */
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
     * 
     * @var Monolog\Logger;
     */
    static protected $logger;
    
    /**
     * Folder path where to write logs (with no trailing separator!)
     * If this property is not setted, the temporary folder will be used
     * @var string
     */
    static protected $logPath;
    
    /**
     * HTTP Transport
     * @var GuzzleHttp\Client
     */
    protected $transport;
    
    /**
     * Time To Live of definitions files (one day per default)
     * Set to zero while in dev mode to refresh on every call
     * @var integer
     */
    public static $ttl = 86400;
    
    
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
        if (! self::$url) {
            throw new \Exception("Please set API Base URL before changing version");
        }
        if (! in_array($version, [1,2])) {
            throw new \Exception(sprintf("The %d version is not supported", $version));
        }
        self::$apiVersion = $version;
        // Refresh services if version is changed
        self::initServices();
    }
    
    /**
     * Define Log folder path (default is system temp folder)
     * @param string $path
     */
    public static function setLogPath($path)
    {
        self::$logPath = $path;
    }
    
    /**
     * Retrieve definition of available API services 
     * @param boolean $refresh
     */
    public static function initServices($refresh = false)
    {
        // Define full definition file path
        $path = sprintf('%s%sxac-services-v%d.conf'
            , ! empty(self::$logPath) ? self::$logPath : sys_get_temp_dir()
            , DIRECTORY_SEPARATOR
            , self::$apiVersion
            );
        
        if ($refresh === true || ! file_exists($path)) {
            self::log(sprintf("Refresh services definition on-demand for v%d API", self::$apiVersion));
            $client = new self;
            $res = $client->call('endpoints');
            $res['_timestamp'] = time();
            file_put_contents($path, serialize($res));
            self::$services = $res;
        } else {
            self::$services = unserialize(file_get_contents($path));
            // Check that content is not expired
            if (time() - self::$services['_timestamp'] > self::$ttl) {
                self::log(sprintf("Refresh outdated services definition for v%d API", self::$apiVersion));
                self::initServices(true);
            }
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
<<<<<<< devel
        if (substr($endpoint, -1) == '/') {
            $endpoint = substr($endpoint, 0, strlen($endpoint)-1);
        }
        
        self::log("Calling $method /api/$endpoint");
=======
        self::$logger->addInfo("Calling $method /api/$endpoint");
>>>>>>> 918a017 Dirty hack
            
        $params = [];
        if (count($data) > 0) {
            $params['json'] = $data;
        }
        
        try {
            $this->initTransport();
            $res = $this->transport->request($method, $endpoint, $params);
        } catch (ConnectException $e) {
            self::log($e->getMessage(), Logger::ERROR);
            throw new \Exception($e->getMessage());
        
        } catch (ServerException $e) {
            $response = json_decode((string) $e->getResponse()->getBody(), true);
            self::$lastResponse = $response['ACResponse'];
            self::$lastResponse['httpCode'] = $e->getResponse()->getStatusCode();
            self::log($e->getMessage(), Logger::ERROR);
            throw new \Exception(self::$lastResponse['message']);
        
        } catch (ClientException $e) {
            $response = json_decode((string) $e->getResponse()->getBody(), true);
            self::$lastResponse = $response['ACResponse'];
            self::$lastResponse['httpCode'] = $e->getResponse()->getStatusCode();
            self::log($e->getMessage(), Logger::ERROR);
            throw new \Exception(self::$lastResponse['message']);
             
        } finally {
            if (isset($res)) {
                $response = json_decode((string) $res->getBody(), !self::$returnObject);
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
     * Log messages to client log file
     * @param string $message
     * @param integer $level
     * @return boolean
     */
    public static function log($message, $level = Logger::INFO)
    {
        if (! self::$logger) {
            $logfile = sprintf('%s%sarrowsphere-client.log', sys_get_temp_dir(), DIRECTORY_SEPARATOR);
            
            // create a log channel
            self::$logger = new Logger('xAC');
            self::$logger->pushHandler(new StreamHandler($logfile, Logger::INFO));
        }
        
        return self::$logger->addRecord($level, $message);
    }
    
    
    /**
     * Return a singleton instance of the class
     * @return Arrowsphere\Client\xAC
     */
    public static function getInstance()
    {
        if (is_null(self::$client)) {
<<<<<<< devel
=======
            // create a log channel
            self::$logger = new Logger('xAC');
            self::$logger->pushHandler(new StreamHandler('/tmp/xac-client.log', Logger::INFO));
            
>>>>>>> 918a017 Dirty hack
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
