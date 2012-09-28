<?php
header("Content-type: application/xml");
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
 
$query = "SELECT ID, question1, question2, pic FROM Users WHERE Name = '$user'";
 
$result = mysql_query($query);
 
$logged = mysql_num_rows($result);

 
$xml_output = "<results>\r\n"; 
for($x = 0 ; $x < $logged ; $x++){ 
    $row = mysql_fetch_assoc($result); 
$xml_output .= "\t<status>true</status>";
$xml_output .= "\t<user>" . $row['ID'] . "</user>";
$xml_output .= "\t<pic>" . $row['pic'] . "</pic>";		
$xml_output .= "\t<questions>\n"; 
$xml_output .= "\t\t<q1>" . $row['question1'] . "</q1>\n";
$xml_output .= "\t\t<q2>" . $row['question2'] . "</q2>\n"; 
$xml_output .= "\t</questions>\n";
}
$xml_output .= "</results>\r\n";
if ($logged == 1)
{
	echo $xml_output;
   // echo "\t<results><status>true</status><q>$row['question1']</q></results>";
	
}
else
{
    echo "<results><status>false</status></results>";
}
?>