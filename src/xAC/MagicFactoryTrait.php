<?php

namespace Arrowsphere\Client\xAC;

use Arrowsphere\Client\xAC as Client;

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

         // var_Dump($service, $name);
            
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
    
                default:
                    die("can't find type of '$name'");
                    var_dump($name);
                    var_Dump($arguments); die;
                    break;
            }
        } else {
            die("can't find service definition for '$name'\n");
            
            var_dump($name);
            var_Dump($arguments); 
            die;                
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
