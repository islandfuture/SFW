<?php
namespace IslandFuture\Sfw;

class Logger {
    public $timer;
    public $min_time;
    public $max_time;
    public $filename='php://stdout';
    
    public function __construct(){
        $this->timer = array();
    }
    
    public function startTimer($name){
        $this->timer[ $name ]['start'] = microtime(true);
        return $this;
    }

    public function finishTimer($name){
        $this->timer[ $name ]['end'] = microtime(true);
        return $this;
    }
    
    public function getTimer($name){
        if( isset($this->timer[ $name ]) ){
            return $this->timer[ $name ]['end'] - $this->timer[ $name ]['start'];    
        } else {
            return 0;
        }
    }
    
    public function printTime($name){
        $str = '';
        if( isset($this->timer[ $name ]) ){
            $str = "Time [$name] = ".($this->timer[ $name ]['end'] - $this->timer[ $name ]['start'])."\r\n";
        } else {
            $str = "Time [$name] = empty\r\n";
        }
        
        $f = fopen($this->filename,'a');
        fwrite($f, $str);
        fclose($f);
        return $this;
    }
    
    public function write($str, $mode='a'){
        $f = fopen($this->filename,$mode);
        fwrite($f, $str);
        fclose($f);
        return $this;
    }
    
}
?>