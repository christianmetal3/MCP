<?php
//header("Content-type: application/xml");
$hostname_conn = "localhost";
$username_conn = "root";
$password_conn = "123";
 
$conn = mysql_connect($hostname_conn, $username_conn, $password_conn);
 
mysql_select_db("MCP");
 
//mysql_real_escape_string POST'ed data for security purposes
$user = mysql_real_escape_string($_POST["user"]);
$answer1 = mysql_real_escape_string($_POST["answer1"]);
$answer2 = mysql_real_escape_string($_POST["answer2"]);
 
//a little more (probably unecessary) security
$code_entities_match = array('--','"','!','@','#','$','%','^','&','*','(',')','_','+','{','}','|',':','"','<','>','?','[',']','\\',';',"'",',','.','/','*','+','~','`','=');
$user = str_replace($code_entities_match, "", $user); 
$answer1 = str_replace($code_entities_match, "", $answer1);
$answer2 = str_replace($code_entities_match, "", $answer2); 
 
$query = "UPDATE Users SET pass = ' . $pass1 . ' WHERE ID = ' . $user'";
 
$result = mysql_query($query);
 
/*$logged = mysql_num_rows($result);
 
$xml_output = "<results>\r\n"; 
for($x = 0 ; $x < $logged ; $x++){ 
    $row = mysql_fetch_assoc($result); 
$xml_output .= "\t<status>true</status>";
$xml_output .= "\t<user>" . $row['ID'] . "</user>";		
}
$xml_output .= "</results>\r\n";
if ($answer1 == $row['answer1'] and $answer2 == $row['answer2'])
{
	echo $xml_output;
	
}
else
{
    echo "<results><status>false</status></results>";
}*/
?>