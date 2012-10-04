<?php
$hostname_conn = "localhost";
$username_conn = "root";
$password_conn = "123";
$database_conn = "MCP"; 
 
$conn = mysqli_connect($hostname_conn, $username_conn, $password_conn, $database_conn);
 
//mysql_select_db("MCP");

//mysql_real_escape_string POST'ed data for security purposes
$user = mysqli_real_escape_string($conn, $_POST["user"]);

//a little more (probably unecessary) security
$code_entities_match = array('--','"','!','@','#','$','%','^','&','*','(',')','_','+','{','}','|',':','"','<','>','?','[',']','\\',';',"'",',','.','/','*','+','~','`','=');
$user = str_replace($code_entities_match, "", $user); 
$pass = "temp1234";
 
mysqli_query($conn, "INSERT INTO Users (Name, pass, department)
VALUES ('Peter', 'Griffin',35)");

mysqli_close($conn);
?>
