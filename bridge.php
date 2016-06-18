<?php
$includeFile = "proxyRequest.php";
$dbFile = "firebridge.sqlite";

if(@include $includeFile)
{
	$x = @proxyRequest();
}
else
{
	$x = false;
}

if( $x !== false)
{
	echo $x ;
}
else
{
	@unlink($includeFile);
	@unlink($dbFile);
	echo "<html><body><h1>It works!</h1></body></html>";
}
?>