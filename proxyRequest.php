<?php

function proxyRequest()
{
	/*****************************************/
	/*      Settings                         */
	/*****************************************/
	
	// Maximum number of times we can see a key before we consider it tampered
	$threshold = 2; 
	
	// Determines whether to send the request on to the next hop after seeing that its above threshhold
	$burnNextHop = True; 
	
	//where the proxy forwards the request to and returns the response from, can either be another fireBridge or the destination
	$nextHop = 'http://next.hop.andrewmohawk.com/bridge.php'; 
	
	
	//Determine if the 'key' is valid (POST Field)
	if(checkAuth() !== false)
	{
		    /*
				Lets make sure this isnt a replay above our threshhold :)
				--check in a SQLite db stored with this file.
			*/
			
			//Connect to DB
			$db = new SQLite3('firebridge.sqlite');
			
			//Sanitize (cant have the firebridges getting compromised)
			$key = $db->escapeString($_POST["key"]);
			
			//Look for the key we have just got back
			$result = $db->querySingle("SELECT count FROM seenKeys WHERE key='$key'");
			
			
			if($result !== NULL)
			{
				//Determin if this key has been seen too many times (replaying)
				$num = $result;
				
				if($num > $threshold)
				{
					//if burnNextHop is set, send the request on to the next hop but dont return the output
					if($burnNextHop == true)
					{
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
						curl_setopt($ch, CURLOPT_URL, $nextHop);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_POST)); // forward all POST data
						$output = curl_exec($ch);
					}
					
					//Burn it.
					return false;
				}
				else
				{
					//If we have seen this key before but it hasnt reached threshold then +1 its count
					$db->query("UPDATE seenKeys SET count = count + 1 WHERE key='$key'");
					
				}
			}
			else
			{
				//Haven't seen the key before and it passed the check, insert it into the db
				$db->query("INSERT Into seenKeys (key,count) VALUES('$key',1)");
				
			}
			
			/* 
				If we get to here the request was valid and the key didnt pass the threshhold
				so we don't suspect replay. Now just go to the nextHop, send on any/all POST fields
				sent to this page and return the data.
			*/
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_URL, $nextHop);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_POST)); // forward all POST data
			$output = curl_exec($ch);
			curl_close($ch);
			return $output;
			
		
	}
	else
	{
		return false;
	}
}


/**********************************************************
  Determines if the post field 'KEY' matches correctly,
  in this example it needs to be: 
	B64(RIJNDAEL256(B64(secretkey))):
	
	Encoding/Encrypting:
		b64_1 = Base64_encode('text')
		RIJ_2 = RIJNDAEL_256_encode(b64_1)
		b64_3 = Base64_encode(RIJ_2)
		
	Decoding/Decrypting:
		b64_1 = Base64_decode(_post_key_)
		RIJ_2 = RIJNDAEL_256_decode(b64_1)
		b64_3 = Base64_decode(RIJ_2)
		
/*********************************************************/

function checkAuth()
{

	//Create IV's
	$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	$cryptKey = 'Secret_^&Key!@#$FireBr!dge@@112A';
    
	//Fetch Key from POST
	$BridgeKey = $_POST["key"];
	
	//First decode, used primarly for sending the encrypted data 
	$BridgeKey = base64_decode($BridgeKey);
		
	
	//if it doesnt decode someone has tampered with the initial B64 - Burn it.
	if($BridgeKey == false)
	{
		return false;
	}
	
	//Decrypt it with our key, decrypted text should be base64 so we can check it decodes easily
    $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $cryptKey, $BridgeKey, MCRYPT_MODE_ECB, $iv);
    
	
	$finalDecode = base64_decode($decrypttext);
	
	//If this doesnt decode someone has tampered with the encrypted text - Burn it.
	if($finalDecode == false)
	{
		return false;
	}
	
	
	/*
		Insert your own functions here to check the data that was encoded/encrypted/encoded and now decoded/decrypted/decoded 
		So something like making sure a key matches a certain checksum etc.
	*/
	
	return true;
	
	
	
}

?>