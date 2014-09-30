<?php

class MemcacheDb
{

    protected $link = array();
    protected $server_number = 1;

    public $serverSum = 1;

    protected $flag = MEMCACHE_COMPRESSED;

    protected $expire = 2592000;

    protected $server_now = -1;

    protected $config;
    public function __construct( $config )
    {
        $this->config = $config;
    	$this->server_number = count( $this->config );
    	$this->serverSum = $this->server_number;
    }

    protected function select_server( $key = '' , $server_id = false )
    {        
		if($server_id === false ) $server_id = 1 ;
		
        if( ( $this->server_now != $server_id ) && ( !array_key_exists( $server_id , $this->link ) ) )
        {
			$this->link[$server_id] = new Memcache();
            if ( !$this->link[$server_id]->connect( $this->config[$server_id]['host'] , $this->config[$server_id]['port'] ) )
            {
				header('HTTP/1.1 500 Internal Server Error');
				die();
            }
        }
        $this->server_now = $server_id;
    }

    public function set( $key , $value , $expire = 0 , $flag = 0 , $server_id = false )
    {
        $this->select_server( $key , $server_id );
        $expire = ( $expire > 0 ) ? $expire : $this->expire;
        $flag = ( $flag > 0 ) ? $flag : $this->flag;
        return $this->link[$this->server_now]->set( $key , $value , $flag , $expire );
		return;
    }

    public function add( $key , $value , $expire = 0 , $flag = 0 )
    {
        $this->select_server( $key );
        $expire = ( $expire > 0 ) ? $expire : $this->expire;
        $flag = ( $flag > 0 ) ? $flag : $this->flag;
        return $this->link[$this->server_now]->add( $key , $value , $flag , $expire );
		return;
    }

    public function replace( $key , $value , $expire = 0 , $flag = 0 )
    {
        $this->select_server( $key );
        $expire = ( $expire > 0 ) ? $expire : $this->expire;
        $flag = ( $flag > 0 ) ? $flag : $this->flag;
        return $this->link[$this->server_now]->replace( $key , $value , $flag , $expire );
    }

    public function get( $key , $server_id = false )
    {
        $this->select_server( $key , $server_id );
        return $this->link[$this->server_now]->get( $key );
    }

    public function increment( $key , $value)
    {
        $this->select_server( $key );
        return $this->link[$this->server_now]->increment( $key , $value );
    }

    public function delete( $key , $time_out = 0 , $server_id = false )
    {
        $this->select_server( $key , $server_id );
        return $this->link[$this->server_now]->delete( $key , $time_out );
    }
}

