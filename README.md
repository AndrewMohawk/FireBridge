# FireBridge
Simple method of stopping replay attacks by defenders/others and ensuring your end destination is not discovered
# Overview 
The idea is relatively basic:

* You have a series of 'proxies' that dont know about anything apart from the nextHop in the chain
* Proxies all make sure that data passing through is correctly encrypted (checking for tampering)
* Proxies all make sure data is not being replayed
* If a proxy detects replay/invalid encryption it removes all files associated with the nextHop 

Implementation was not too difficult, whipped something up in PHP that works like this:

* All requests to nextHop include a POST variable ‘key’ that contains a key made up of the following (B64(RIJNDAEL256(B64(secretkey))):

1. b64_1 = Base64_encode(‘text’)
2. RIJ_2 = RIJNDAEL_256_encode(b64_1)
3. b64_3 = Base64_encode(RIJ_2)

* All requests hit a ‘bridge.php’ page that does:
* @Include ‘proxy.php’, call function proxyRequest(); which checks auth above and replay attacks via SQLite db
* If proxyRequest() returns false, remove the SQLite database and ‘proxy.php’ script leaving the person chasing you with a 5 line php file that once included something
* If proxyRequest() returns != false, simply return the page to the browser.

# Files
- SQLite db for requests
- proxyRequest.php ( Which will be removed if tampering is found, does all the work)
- bridge.php ( Simply calls and removes the above if found)
- exampleRequest.php ( Example implementation to make a request ) 
