<?php

namespace Arrowsphere\Client\xAC;

use Arrowsphere\Client\xAC as Client;

/**
 * This class can be used as a cursor on a xAC API collection
 * 
 *
 */
class Cursor
{
    protected $params = [];
    
    protected $client;
    
    protected $context;
    
    protected $filters = [];
    
    protected $page = 1;
    
    protected $perpage = 15;
    
    protected $meta = [];
    
    
    /**
     * Class constructor
     * @param array $params
     * @param xAC\Client $client
     * @param xAC\Entity $context
     */
    public function __construct(array $params, Client $client, Entity $context = null)
    {
        $this->params = $params;
        $this->client = $client;
        $this->context = $context;
    }
    
    /**
     * Get current data
     * @return array
     */
    public function get()
    {
        $uri = null;
        if ($this->context) {
            $uri = $this->context->getBaseUri() . '/';
        }
        $uri .= $this->params['endpoint'];
        
        $uri .= sprintf("?page=%d&perpage=%d"
            , $this->page
            , $this->perpage
            );
        
        foreach ($this->filters as $filter) {
            $uri .= sprintf("&%s=%s"
                , $filter['field']
                , rawurlencode($filter['value'])
                );
        }
        
        $res = $this->client->call($uri, 'GET');
        
        // convert array to Entities collection
        $_ = [];
        foreach ($res as $data) {
            $_ = (new Entity([], $this->client))->setData($data);
        }
        return $_;
    }
    
    public function setPerPage($perpage)
    {
        if ($perpage > 0) {
            $this->perpage = (int) $perpage;
        }
        
        return $this;
    }
    
    public function addFilter($field, $value, $operator= '=')
    {
        $this->filter[] = [
          'field' => $field,
          'value' => $value,
          'operator' => $operator,
        ];
        
        return $this;
    }
    
    public function resetFilters($field = null)
    {
        if (is_null($field)) {
            $this->filters = [];
        } else {
            
        }
        
        return $this;
    }
    
    /**
     * Increase page number and get next results batch
     * return array
     */
    public function next()
    {
        $this->page++;
        
        return $this->get();
    }

    /**
     * Decrease page number and get next results batch
     * return array
     */
    public function prev()
    {
        if ($this->page > 0) {
            $this->page--;
            return $this->get();
        }
        
        return false;
    }
}
