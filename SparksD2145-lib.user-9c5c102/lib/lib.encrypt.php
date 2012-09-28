<?php
class Encryptor{
	private static $resource;
	private static $cipher = 'rijndael-256';
	private static $mode = 'ofb';
	private static $iv;
	private static $ks;
	private static $key;

	public function Encryptor(){  }
	public static function open($key){
		try{
			self::$key = $key;
			self::$resource = mcrypt_module_open(self::$cipher, '', self::$mode, '');
			if(empty(self::$iv)) self::$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size(self::$resource), MCRYPT_DEV_RANDOM);
			mcrypt_generic_init(self::$resource, self::$key, self::$iv);
			return self::$iv;
		} catch (exception $e){
			self::error($e);
		}
	}
	public static function setIV($iv){ self::$iv = $iv; }
	public static function getIV(){ return self::$iv; }
	public static function setKey($key){ self::$key = $key; }
	public static function getKey(){ return self::$key; }
	public static function setCipher($cipher){ self::$cipher = $cipher; }
	public static function getCipher(){ return self::$cipher; }
	public static function setMode($mode){ self::$mode = $mode; }
	public static function getMode(){ return self::$mode; }
	
	public static function encrypt($input){
		try{
			if(isset($input)){	$encrypted = mcrypt_generic(self::$resource, $input);	return $encrypted;	}
			else return NULL;
		} catch (exception $e){
			self::error($e);
		}
	}
	public static function decrypt($input){
		try{
			if(isset($input)){	$decrypted = mdecrypt_generic(self::$resource, $input); return $decrypted; }
			else return NULL;
		} catch (exception $e){
			self::error($e);
		}	
	}
	public static function close(){
		try{
			self::$iv = null;
			mcrypt_generic_deinit(self::$resource);
			mcrypt_module_close(self::$resource);
		} catch (exception $e){
			self::error($e);
		}
	}
	private function error($e){
		echo $e->getMessage();
	}
}
?>