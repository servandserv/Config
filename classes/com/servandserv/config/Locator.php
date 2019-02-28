<?php

namespace com\servandserv\config;

class Locator implements ServiceLocator
{
    private static $instance;
    private $env = [];
    
    public static function getInstance()
    {
        if(!self::$instance) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    public function get( $prop, array $args = [] )
    {
        if( isset( $this->env[$prop] ) ) {
            return $this->env[$prop];
        } else if( isset( $_SERVER[$prop] ) ) {
            return $_SERVER[$prop];
        } else {
            error_log( "Config property ".$prop." not found in configuration data in ".__FILE__." on ".__LINE__ );
            exit();
        }
    }
    
    public function has( $prop )
    {
        if( isset( $_SERVER[$prop] ) || isset( $this->env[$prop] ) ) return TRUE;
        else return FALSE;
    }
    
    public function set( $prop, $val )
    {
        $this->env[$prop] = $val;
        return $this;
    }
    
    public function create( $prop, array $args = [], callable $cb = NULL ) 
    {
        if( isset( $this->env[$prop] ) ) {
            $isInvokable = is_object( $this->env[$prop] ) && method_exists( $this->env[$prop], "__invoke" );
            if( $isInvokable ) $obj = call_user_func_array( $this->env[$prop], $args );
            else $obj = $this->env[$prop];
            if( $cb ) {
                return call_user_func_array( $cb, array( $obj ) );
            } else {
                return $obj;
            }
        } else if( isset( $_SERVER[$prop] ) ) {
            if( !class_exists( $_SERVER[$prop] ) ) throw new \Exception( "Environment interface implementation class \"$prop\" defined as ".$_SERVER[$prop]." not exists" );
            $cl = new \ReflectionClass( $_SERVER[$prop] );
            $obj = call_user_func_array( array( &$cl, 'newInstance' ), $args );
            
            return $obj;
        }
        throw new \Exception( "Environment interface \"$prop\" not exists" );
    }
}