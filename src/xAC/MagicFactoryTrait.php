<?php

namespace Arrowsphere\Client\xAC;

use Arrowsphere\Client\xAC as Client;
use Arrowsphere\Client\xAC\Entity\Action;

trait MagicFactoryTrait {
    
    /**
     * Magic method
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callstatic($name, $arguments)
    {
        // Get global services or entity services
        $services = Client::getServices(isset($arguments['context']) ? $arguments['context'] : null);
    
        if (array_key_exists($name, $services)) {
            $service = $services[$name];

            switch ($service['type']) {
    
                case 'collection':
                    return new Cursor(
                        $service, 
                        Client::getInstance(), 
                        isset($arguments['context']) ? $arguments['context'] : null
                    );
                    break;
    
                case 'entity':
                    return new Entity(
                        $service, 
                        Client::getInstance(), 
                        isset($arguments[0]) ? $arguments[0] : null
                    );
                    break;
                
                // $name represents an action on an entity
                case 'action':
                    return new Action(
                        $service,
                        $arguments['context'],
                        Client::getInstance()
                    );
                    break;
    
                default:
                    die("can't find type of '$name'");
                    var_dump($name);
                    var_Dump($arguments); die;
                    break;
            }
        } else {
            throw new \Exception("can't find service definition for '$name'");
        }
    }
    
    /**
     * 
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $arguments['context'] = $this;
        return self::__callstatic($name, $arguments);
    }
}
