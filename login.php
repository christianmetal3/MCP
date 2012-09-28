<?php
$hostname_conn = "localhost";
$username_conn = "root";
$password_conn = "123";
 
$conn = mysql_connect($hostname_conn, $username_conn, $password_conn);
 
mysql_select_db("MCP");
 
//mysql_real_escape_string POST'ed data for security purposes
$user = mysql_real_escape_string($_POST["user"]);
$pass = mysql_real_escape_string($_POST["pass"]);
 
//a little more (probably unecessary) security
$code_entities_match = array('--','"','!','@','#','$','%','^','&','*','(',')','_','+','{','}','|',':','"','<','>','?','[',']','\\',';',"'",',','.','/','*','+','~','`','=');
$user = str_replace($code_entities_match, "", $user); 
$pass = str_replace($code_entities_match, "", $pass); 
 
$query = "SELECT * FROM Users WHERE name = '$user' AND pass = '$pass'";
 
$result = mysql_query($query);
 
$logged = mysql_num_rows($result);
 
if ($logged == 1)
{
    echo "<status>true</status>";
}
else
{
    echo "<status>false</status>";
}
?>