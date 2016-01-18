<?php

namespace Arrowsphere\Client\xAC;

use Arrowsphere\Client\xAC as Client;

/**
 * This class can be used as an entity wrapper on a xAC API object
 * 
 *
 */
class Entity
{
    use MagicFactoryTrait;
    
    /**
     * Entity ID
     * @var string
     */
    protected $id;
    
    /**
     * Entity endpoint parameters
     * @var array
     */
    protected $params = [];
    
    /**
     * xAC Client
     * @var Arrowsphere\Client\xAC
     */
    protected $client;
    
    /**
     * Entity data
     * @var array
     */
    protected $data = [];
    
    
    public function __construct(array $params, Client $client, $id = null)
    {
        $this->params = $params;
        $this->client = $client;
        $this->id = $id;
    }
 
    public function getBaseUri()
    {
        return sprintf('%s%s'
            , $this->params['endpoint']
            , ! empty($this->id) ? '/' . $this->id : null
        );
    }
    
    public function getServices()
    {
        return $this->params['actions'];
    }
    
    
    public function get()
    {
        if (! is_null($this->id) && count($this->data) == 0) {
            $this->data = $this->client->call($this->getBaseUri());
        }
        
        return $this->data;
    }
    
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }
    
    public function toArray()
    {
        $this->get();
        return $this->data;
    }
    
    public function __get($name)
    {
        if (count($this->data) == 0) {
            $this->get();
        }
        
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }
}
