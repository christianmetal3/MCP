<?php

define( "DATABASE_SERVER", "localhost:3306" );

define( "DATABASE_USERNAME", "root" );

define( "DATABASE_PASSWORD", "123" );

define( "DATABASE_NAME", "MCP" );

//connect to the database

$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD) or die(mysql_error());

//select the database

mysql_select_db( DATABASE_NAME );

//asign the data passed from Flex to variables

$username = mysql_real_escape_string($_POST["username"]);

$password = mysql_real_escape_string($_POST["password"]);

//Query the database to see if the given username/password combination is valid.

$query = "SELECT * FROM Users WHERE name = '$username' AND pass = '$password'";

$result = mysql_query($query);

//start outputting the XML

$output = "<loginsuccess>";

//if the query returned true, the output <loginsuccess>yes</loginsuccess> else output <loginsuccess>no</loginsuccess>

if(!$result)

{

$output .= "no";		

}else{

$output .= "yes";	

}

$output .= "</loginsuccess>";

//output all the XML

print ($output);

?>