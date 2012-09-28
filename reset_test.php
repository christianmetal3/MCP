<?php
//header("Content-type: application/xml");
$hostname_conn = "localhost";
$username_conn = "root";
$password_conn = "123";
 
$conn = mysql_connect($hostname_conn, $username_conn, $password_conn);
 
mysql_select_db("MCP");

$user = $_POST["user"];
$pass = $_POST["pass"];

$query = "UPDATE Users SET pass='$pass' WHERE ID='$user'";

$result = mysql_query($query);





?>