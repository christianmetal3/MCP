<?php

$hostname_conn = "localhost";
$username_conn = "root";
$password_conn = "123";
$db_conn = "MCP";
// connect to database
$db = mysqli_connect($hostname_conn, $username_conn, $password_conn,$db_conn);
 
// alter header for XML output
header("Content-type: application/xml");

// begin XML root tag
echo "<RESULTS>\r\n";

// query table for table
$res = mysqli_query($db,"SELECT ID, Name FROM Users WHERE Name = 'Manager1'");

// return results
while( $data = mysqli_fetch_array($res) )
{
	echo "\t<STATE ABV=\"".$data[1]."\">";
	echo $data[0]."</STATE>\n";
}

// close root tag of XML
echo "</RESULTS>\r\n";

// close database
mysqli_close($db);

?>