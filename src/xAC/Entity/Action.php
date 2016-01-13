<?php

namespace Arrowsphere\Client\xAC\Entity;

use Arrowsphere\Client\xAC\Entity;
use Arrowsphere\Client\xAC as Client;

class Action
{
    protected $entity;
    
    protected $params;
    
    protected $client;
    
    protected $data;
    
    
    public function __construct(array $params, Entity $entity, Client $client)
    {
        $this->entity = $entity;
        $this->client = $client;
        $this->params = $params;
    }
    
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }
    
    
    public function execute()
    {
        // Build URI
        $uri = $this->entity->getBaseUri();
        if ($this->params['endpoint'] != 'default') {
            $uri .= '/' . $this->params['endpoint'];
        }
        
        try {    
            $res = $this->client->call(
                $uri, 
                $this->params['method'],
                $this->data
            );
        } catch (\Exception $e) {
            return false;
        }
        
        return true;
    }
}
