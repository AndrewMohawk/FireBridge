<?php
function createFireBridgeKey($string)
{
	$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	$cryptKey = 'Secret_^&Key!@#$FireBr!dge@@112A';
	$encodedClearData = base64_encode($string);
	$encryptedEncodedData = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $cryptKey, $encodedClearData, MCRYPT_MODE_ECB, $iv);    
	$encodedEncryptedEncodedData = base64_encode($encryptedEncodedData);

	return $encodedEncryptedEncodedData;
}


function randomData($length = 8)
{     
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$result = '';
    for ($p = 0; $p < $length; $p++)
    {
        $result .= ($p%2) ? $chars[mt_rand(19, 23)] : $chars[mt_rand(0, 18)];
    }
   
    return $result;
}

$testString = "This is going to my CNC";
$testString = $testString . randomData(); // just to make sure i dont send the same request too may times (would be a replay then)
$testString = createFireBridgeKey($testString);

$postArray = array ("key"=>$testString);

$ch = curl_init();
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_URL, 'http://next.hop.andrewmohawk.com/fireBridges/bridge.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postArray)); // forward all POST data
$output = curl_exec($ch);
curl_close($ch);

echo $output;

?>
