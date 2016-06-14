<?php
	/** Set up Database **/
	//connect or die with error message.
	$conn = mysqli_connect("serveraddress","SQLuser","databasepassword","databasename");
	if (!$conn) {
		die("Connection failed: " . mysqli_connect_error());
	}
?>
