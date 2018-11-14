<?php
// Super simple ugly proxy script using curl

const MY_TOKEN = ''; // PUT YOUR TOKEN HERE - USE A LONG AND RANDOM STRING TO AVOID HACKING !!!
const USER_AGENT = 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';


$url = urldecode($_REQUEST['url']);
$token = urldecode($_REQUEST['token']);
$header = urldecode(!empty($_REQUEST['header'])?$_REQUEST['header']:'');

if($token!==MY_TOKEN || empty(MY_TOKEN) || filter_var($url, FILTER_VALIDATE_URL)===false) {
	header("HTTP/1.1 401 Unauthorized");
	exit;	
}

$custom_header = array();
if(!empty($header)) {
	$custom_header = json_decode($header, true);
}

$result = web_call($url, USER_AGENT, $custom_header);

if ( $result['errno'] != 0 )
    die($result['errmsg']);

// We are not sending the headers back... you could do so by sender header(...) using $result['header']

$page = $result['content'];

echo $page;

/**
 * Get a file (HTML, XHTML, XML, image, etc.) from a URL.  Return an
 * array containing the HTTP server response header fields and content.
 */
function web_call( $url, $user_agent, $custom_header = array(), $type = 'GET' )
{	
	$additionalHeader = array();
	foreach($custom_header as $option=>$value) {
		$additionalHeader[] = $option.': '.$value;
	}

	$ch = curl_init();
	$headers = [];
	
	$options = array(
		CURLOPT_URL			   => $url,
		CURLOPT_CUSTOMREQUEST  => $type,        //set request type post or get
		CURLOPT_POST           => false,        //set to custom request type
		CURLOPT_USERAGENT      => $user_agent, //set user agent
		CURLOPT_COOKIEFILE     => "cookie.txt", //set cookie file
		CURLOPT_COOKIEJAR      => "cookie.txt", //set cookie jar
		CURLOPT_RETURNTRANSFER => 1,     // return web page
		CURLOPT_FOLLOWLOCATION => true,     // follow redirects
		CURLOPT_ENCODING       => "",       // handle all encodings
		CURLOPT_AUTOREFERER    => true,     // set referer on redirect
		CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
		CURLOPT_TIMEOUT        => 120,      // timeout on response
		CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
	);
	
	if(count($additionalHeader)>0) {
		curl_setopt($ch, CURLOPT_HTTPHEADER, $additionalHeader);
	}
	
	// this function is called by curl for each header received - source https://stackoverflow.com/questions/9183178/can-php-curl-retrieve-response-headers-and-body-in-a-single-request
	curl_setopt($ch, CURLOPT_HEADERFUNCTION,
	  function($curl, $header) use (&$headers)
	  {
		$len = strlen($header);
		$header = explode(':', $header, 2);
		if (count($header) < 2) // ignore invalid headers
		  return $len;

		$name = strtolower(trim($header[0]));
		if (!array_key_exists($name, $headers))
		  $headers[$name] = [trim($header[1])];
		else
		  $headers[$name][] = trim($header[1]);

		return $len;
	  }
	);

	curl_setopt_array( $ch, $options );
	$content = curl_exec( $ch );
	$err     = curl_errno( $ch );
	$errmsg  = curl_error( $ch );
	$header  = curl_getinfo( $ch );
	curl_close( $ch );

	return array(
		'errno' => $err,
		'errmsg' => $errmsg,
		'content' => $content,
		'header' => $headers,
	);
}