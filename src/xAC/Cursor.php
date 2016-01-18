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
    /**
     * Cursor endpoint parameters
     * @var array
     */
    protected $params = [];
    
    /**
     * xAC Client instance
     * @var Arrowsphere\Client\xAC
     */
    protected $client;
    
    /**
     * Parent context of the collection if it is related to a particular entity
     * @var Arrowsphere\Client\xAC\Entity
     */
    protected $context;
    
    /**
     * Array of query filters
     * @var array
     */
    protected $filters = [];
    
    /**
     * Current page number, default value is 1
     * @var integer
     */
    protected $page = 1;
    
    /**
     * Number of results per page, default value is 15
     * @var integer
     */
    protected $perpage = 15;
    
    /**
     * Pagination data related to the current state
     * @var array
     */
    protected $pagination = [];
    
    
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
        
        // add filter if value is not empty
        foreach ($this->filters as $filter) {
            if (! empty($filter['value'])) {
                $uri .= sprintf("&%s=%s"
                    , $filter['field']
                    , rawurlencode($filter['value'])
                    );
            }
        }
        
        $res = $this->client->call($uri);

        // get and preserve pagination
        $lr = Client::getLastResponse();
        $this->pagination = isset($lr['body']) && isset($lr['body']['pagination']) ? $lr['body']['pagination'] : [];
        
        // convert array to Entities collection
        $_ = [];
        foreach ($res as $data) {
            $_[] = (new Entity([], $this->client))->setData($data);
        }
        
        return $_;
    }
    
    /**
     * Set page number, applied on next get() call
     * @param integer $page
     * @return \Arrowsphere\Client\xAC\Cursor
     */
    public function setPage($page)
    {
        $this->page = (int) $page;
        return $this;
    }
    
    /**
     * Set per page value, reset page value to 1
     * @param integer $perpage
     * @return \Arrowsphere\Client\xAC\Cursor
     */
    public function setPerPage($perpage)
    {
        if ($perpage > 0) {
            $this->perpage = (int) $perpage;
            $this->page = 1;
        }
        
        return $this;
    }
    
    /**
     * Return the pagination data related to the current batch
     * @return array
     */
    public function getPagination()
    {
        return $this->pagination;
    }
    
    
    /**
     * Add a filter that will be passed to the API query
     * @param string $field
     * @param string $value
     * @param string $operator
     * @return \Arrowsphere\Client\xAC\Cursor
     */
    public function addFilter($field, $value, $operator= '=')
    {
        $this->filter[] = [
          'field' => $field,
          'value' => $value,
          'operator' => $operator,
        ];
        
        return $this;
    }
    
    /**
     * Remove filter matching given identifier or all filters if empty 
     * @param string $field
     * @return \Arrowsphere\Client\xAC\Cursor
     */
    public function resetFilters($field = null)
    {
        if (is_null($field)) {
            $this->filters = [];
        } else {
            if (isset($this->filters[$field])) {
                unset($this->filters[$field]);
            }
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
