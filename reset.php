<?php 
header("Content-type: application/xml");
$hostname_conn = "localhost";
$username_conn = "root";
$password_conn = "123";
 
$conn = mysql_connect($hostname_conn, $username_conn, $password_conn);
 
mysql_select_db("MCP");

 
//mysql_real_escape_string POST'ed data for security purposes
$user = mysql_real_escape_string($_POST["user"]);
$pass1 = mysql_real_escape_string($_POST["pass1"]);
$pass2 = mysql_real_escape_string($_POST["pass2"]);
 
//a little more (probably unecessary) security
$code_entities_match = array('--','"','!','@','#','$','%','^','&','*','(',')','_','+','{','}','|',':','"','<','>','?','[',']','\\',';',"'",',','.','/','*','+','~','`','=');
$user = str_replace($code_entities_match, "", $user); 
$pass1 = str_replace($code_entities_match, "", $pass1);
$pass2 = str_replace($code_entities_match, "", $pass2); 
 
mysql_query("UPDATE Users SET pass = '$pass1' WHERE ID='$user'");
$validation = "SELECT pass FROM Users WHERE ID = '$user'";

$result = mysql_query($validation);

$logged = mysql_num_rows($result);

 
$xml_output = "<results>\r\n"; 
for($x = 0 ; $x < $logged ; $x++){ 
    $row = mysql_fetch_assoc($result); 
$xml_output .= "\t<status>true</status>";
}
$xml_output .= "</results>\r\n";
if ($pass1 == $row['pass'])
{
	echo $xml_output;
   // echo "\t<results><status>true</status><q>$row['question1']</q></results>";
	
}
else
{
    echo "<results><status>false</status></results>";
}
?>