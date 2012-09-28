<?php
class Cookie{	
	public function Cookie($cookieName, $cookieTimeout, $cookiePath){}
	
	/* remove all cookies */
	public static function rmAll(){
		foreach($_COOKIE as $name){
			if(strpos($name,$this->cName) !== false){
				$_COOKIE[$name] = NULL;
				$this->rmCookie($name);
			}
		}
	}
	
	/* read a cookie */
	public static function read($name){
		if(isset($_COOKIE[$name])){
			return $_COOKIE[$name];
		}else{
			return NULL;
		}
	}
	/* remove a cookie */
	public static function rm($cName, $path){ setcookie($cName,"",0,$path); }
	
	/* Write a cookie */
	public static function write($name, $value, $time){	setcookie($name,$value,$time); }
}
?>