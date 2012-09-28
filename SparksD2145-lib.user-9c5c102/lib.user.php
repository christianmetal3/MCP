<?php
/* -------------
 * LIB.USER
 * Developed by Thomas Ibarra
 * http://github.com/SparksD2145
 * -------------
 */

require dirname(__FILE__) . ('/lib/lib.interpret.php');
require dirname(__FILE__) . ('/lib/lib.cookie.php');
require dirname(__FILE__) . ('/lib/lib.encrypt.php');

class User {	
	private static $host;
	private static $db;
	private static $user;
	private static $token;
	private static $signedCookieName;
	private static $expires;
	
	public function __construct() {  }
	
	/* Add a user */
	public static function add($user, $token, $table, $key){
		try{
			if(isset($user,$token,$table,$key)){
				$ident = Encryptor::open($key);
				$result = Encryptor::encrypt($token);
				$result = self::queryDB("INSERT INTO $table (screen_name,token,last_act_code) VALUES ('$user','$result','".mysql_escape_string($ident)."')");	
				Encryptor::close();
				if(!isset($result)){ throw new Exception("Users: Could not create new user.", E_USER_WARNING); }
				else { return true; }
			} else { throw new Exception("User [add]: Required variables not set."); }
		}catch(Exception $e){
			self::error($e);
		}
	}
	
	/* Sign a user in */
	public static function signIn($uid, $token, $table, $key){
		try{
			if(isset($uid,$token,$table,$key)){
				date_default_timezone_set ('America/Chicago');	
				if(self::checkToken($uid, $token, $table, $key)){
					$time = date(DATE_ATOM,time());
					$sessionid = self::queryDB("SELECT sid FROM $table WHERE uid=$uid LIMIT 1");
					self::queryDB("UPDATE $table SET sid=". ($sessionid[0]['sid'] + 1) .",signed=1,time='".$time."' WHERE uid=$uid;");
					$data = "$uid,".($sessionid[0]['sid'] + 1);
					Cookie::write(self::$signedCookieName, $data, self::$expires);
					return true;
				} else throw new Exception("User: Token incorrect.", E_USER_WARNING);
				Encryptor::close();
			} else { throw new Exception("User [signIn]: Required variables not set."); }
		}catch(Exception $e){
			self::error($e);
		}
	}
	
	/* Sign a user out */
	public static function signOut($uid, $table){
		try{
			if(isset($uid,$table)){
				$time = date(DATE_ATOM,time());
				$result = self::queryDB("UPDATE $table SET signed=0, time='".$time."' WHERE uid=$uid");
				Cookie::rm($signedCookieName, "/");
				if(isset($result)) return TRUE;
				else throw new Exception("User: Could not sign out.", E_USER_WARNING);;
			} else { throw new Exception("User [signOut]: Required variables not set."); }
		} catch (exception $e){
			self::error($e);
		}
	}

	/* Check if user is logged in */
	public static function checkToken($uid,$token,$table,$key){
		try{
			if(isset($uid,$token,$table,$key)){
				$iv = self::queryDB("SELECT last_act_code FROM $table WHERE uid='$uid' LIMIT 1");
				Encryptor::setIV($iv[0]['last_act_code']);
				Encryptor::open($key);
				$password = self::queryDB("SELECT token FROM $table WHERE uid='$uid' LIMIT 1");
				$password = Encryptor::decrypt($password[0]['token']);
				Encryptor::close();
				if($token == $password) return TRUE;
				else return FALSE;
			} else { throw new Exception("User [checkToken]: Required variables not set."); }
		} catch (exception $e){
			self::error($e);
		} 
	}
	
	public static function checkCookieSession($table,$key){
		try{
			if(isset($table,$key)){
				date_default_timezone_set ('America/Chicago');	
				$cData = Cookie::read(self::$signedCookieName);
				$cDataArray = explode(",", $cData);
				$issigned = self::queryDB("SELECT signed FROM $table WHERE uid='".$cData[0]."' LIMIT 1");
				$recorded_time = self::queryDB("SELECT time FROM $table WHERE uid='".$cData[0]."' LIMIT 1");
				$session = self::queryDB("SELECT sid FROM $table WHERE uid='".$cData[0]."' LIMIT 1"); 
				if(date(DATE_ATOM,self::$expires + time()) > $recorded_time[0]['time'] &&
			 	$session[0]['sid'] == $cData[1] && 
			 	$issigned[0]['signed'] == TRUE) { return true; }
				else return false;
			} else { throw new Exception("User [checkCookieSession]: Required variables not set."); }
		} catch (exception $e){
				self::error($e);
		} 
	}
	
	/* Check session validity */
	public static function checkSession($uid, $sid, $table){
		try{
			if(isset($uid,$sid,$table)){
				date_default_timezone_set ('America/Chicago');	
				$recorded_time = self::queryDB("SELECT time FROM $table WHERE uid='$uid' LIMIT 1");
				$session = self::queryDB("SELECT sid FROM $table WHERE uid='$uid' LIMIT 1");
				$issigned = self::queryDB("SELECT signed FROM $table WHERE uid='$uid' LIMIT 1");  
				if(date(DATE_ATOM, self::$expires + time()) > $recorded_time[0]['time'] && $session[0]['sid'] == $sid && $issigned[0]['signed'] == TRUE) { return true; }
				else return false;
			} else { throw new Exception("User [checkSession]: Required variables not set."); }
		} catch (Exception $e){
			self::error($e);
		}
	}
	
	/* Define a user against the database */
	public static function defineUser($username, $table){
		try{
			if(isset($username, $table)){
				$result = self::queryDB("SELECT uid FROM $table WHERE screen_name='$username'");
				if(!isset($result[0]['uid'])){ throw new Exception("Users: Could not retrieve user id.", E_USER_WARNING); }
				else { return $result[0]['uid']; }
			} else { throw new Exception("User [defineUser]: Required variables not set."); }
		}catch(Exception $e){
			self::error($e);
		}
	}
	
	/* Query the database using Lib.Interpret */
	private static function queryDB($query){
		try{
			if(self::validateRequired() && isset($query)){
				$intpr = new Interpreter();
				$resultant = $intpr->push($intpr->generateQuery(self::$host,
					self::$user,
					self::$token,
					self::$db,
					$query));
				return $resultant;
			} else {
				throw new Exception("User: Could not connect, required fields absent.", E_USER_WARNING);				
			}
		} catch (Exception $e){
			self::error($e);
		}
	}
		
	/* Produce an error */
	public static function error(Exception $e){ throw $e; }
	
	/* Cookie utilities */
	public static function getCookieName(){ return self::$signedCookieName; }
	public static function setCookieName($name){ self::$signedCookieName = $name; }
	public static function getExpireTime(){ return self::$expires; }
	public static function setExpireTime($time){ self::$expires = time() + $time; }
	
	/* Host utilities */
	public static function getHost(){ return self::$host; }
	public static function setHost($host){ self::$host = $host; }
	
	/* DB Utilities */
	public static function getDB(){ return self::$db; }
	public static function setDB($db){ self::$db = $db; }
	
	/* User utilities */
	public static function getDBUser(){ return self::$user; }
	public static function setDBUser($user){ self::$user = $user; }
	
	/* Token utilities */
	public static function getToken(){ return self::$token; }
	public static function setToken($token){ self::$token = $token; }
	
	private static function validateRequired(){
		if(isset(self::$host) && isset(self::$db) && isset(self::$user) && isset(self::$token)){ return TRUE; }
		else { return FALSE; }
	}
}
?>