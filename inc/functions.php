<?php
// function define to retrieve url content
function get_external_file($url, $timeout) {
	// spoofing FireFox 18.0
	$useragent="Mozilla/5.0 (Windows NT 5.1; rv:18.0) Gecko/20100101 Firefox/18.0";

	if  (in_array  ('curl', get_loaded_extensions())) {	
		// Fetch feed from URL
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		 
		// FeedBurner requires a proper USER-AGENT...
		curl_setopt($curl, CURL_HTTP_VERSION_1_1, true);
		curl_setopt($curl, CURLOPT_ENCODING, "gzip, deflate");
		curl_setopt($curl, CURLOPT_USERAGENT, $useragent);

		$data = curl_exec($curl);
		
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		
		$httpcodeOK = isset($httpcode) and ($httpcode == 200 or $httpcode == 301);
		
		curl_close($curl);
	} else {

		// create http context and add timeout and user-agent
		$context = stream_context_create(array('http'=>array('timeout' => $timeout, // Timeout : time until we stop waiting for the response.
																					'header'=> "User-Agent: ".$useragent, // spoot Mozilla Firefox
																					'follow_location' => true
														)));

		// only download page lesser than 4MB
		$data = @file_get_contents($url, false, $context, -1, 4000000); // We download at most 4 MB from source.
		//	echo "<pre>http_response_header : ".print_r($http_response_header);
		
		if(isset($http_response_header) and isset($http_response_header[0])) {
			$httpcodeOK = isset($http_response_header) and isset($http_response_header[0]) and ((strpos($http_response_header[0], '200 OK') !== FALSE) or (strpos($http_response_header[0], '301 Moved Permanently') !== FALSE));
		}
	}

	// if response is not empty and response is OK
	if (isset($data) and isset($httpcodeOK) and $httpcodeOK ) {

		// take charset of page and get it
		preg_match('#<meta .*charset=.*>#Usi', $data, $meta);

		// if meta tag is found
		if (!empty($meta[0])) {
			// retrieve encoding in $enc
			preg_match('#charset="?(.*)"#si', $meta[0], $enc);

			// if charset is found set it otherwise, set it to utf-8
			$html_charset = (!empty($enc[1])) ? strtolower($enc[1]) : 'utf-8';

		} else { 
			$html_charset = 'utf-8';
			$enc[1] = '';
		}

		// replace charset of url to charset of page
		$data = str_replace('charset='.$enc[1], 'charset='.$html_charset, $data);

		return $data;
	}
	else {
		return FALSE;
	}
}

function startsWith($haystack, $needle)
{
    return !strncmp($haystack, $needle, strlen($needle));
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

function createBaseURL($rel,$url) {
    /* parse base URL and convert to local variables:
       $scheme, $host, $path */
    extract(parse_url($url));

    /* remove non-directory element from path */
    $path = preg_replace('#/[^/]*$#', '', $path);

    /* destroy path if relative url points to root */
    if (isset($rel) && $rel[0] == '/') $path = '';

    /* dirty absolute URL */
    return "$host$path/$rel";
}

function getHostFromURL($url) {
    /* parse base URL and convert to local variables:
       $scheme, $host, $path */
    extract(parse_url($url));

    return "$host";
}

function canBeUpdated($flow) {
    if((time() - $flow->getUpdateDate()) > 3600) {
		return true;
	}

    return false;
}


function rel2abs($rel, $base)
{
    /* return if already absolute URL */
    if (parse_url($rel, PHP_URL_SCHEME) != '') return $rel;

    /* queries and anchors */
    if (isset($rel) && ($rel[0]=='#' || $rel[0]=='?')) return $base.$rel;

	$abs = createBaseURL($rel,$base);

    /* replace '//' or '/./' or '/foo/../' with '/' */
    $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
    for($n=1; $n>0; $abs=preg_replace($re, '/', $abs, -1, $n)) {}

    extract(parse_url($base));

    /* absolute URL is ready! */
    return $scheme.'://'.$abs;
}

// $str=preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#','$1="http://wintermute.com.au/$2$3',$str);

function absolutes_links($data, $base) {
	// cherche les balises 'a' qui contiennent un href
	$matches = array();
	preg_match_all('#(href|src)="([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#Si', $data, $matches, PREG_SET_ORDER);

	// ne conserve que les liens ne commen�ant pas par un protocole � protocole:// � ni par une ancre � # �
	foreach($matches as $i => $link) {
		$link[1] = trim($link[1]);

		if (!preg_match('#^(([a-z]+://)|(\#))#', $link[1]) ) {

			$absolutePath=rel2abs($link[2],$base);

			$data = str_replace($matches[$i][2], $absolutePath, $data);
		}

	}
	return $data;
}

function url(){
  $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
  return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

function generate_page($url,$title,$content) {
	raintpl::$tpl_dir = './tpl/'; // template directory
	raintpl::$cache_dir = "./cache/"; // cache directory
	raintpl::$base_url = url(); // base URL of blog
	raintpl::configure( 'path_replace', false );
	raintpl::configure('debug', false); 

	$tpl = new raintpl(); //include Rain TPL

	$tpl->assign( "url", $url);
	$tpl->assign( "title", $title);
	$tpl->assign( "content", $content);
	
	$tpl->assign( "version", VERSION);
	
	$tpl->draw( "index"); // draw the template
}













/*
// function define to retrieve url content
function get_external_file($url, $timeout) {
	// spoofing FireFox 18.0
	$useragent="Mozilla/5.0 (Windows NT 5.1; rv:18.0) Gecko/20100101 Firefox/18.0";

	if  (in_array  ('curl', get_loaded_extensions())) {	
		// Fetch feed from URL
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		 
		// FeedBurner requires a proper USER-AGENT...
		curl_setopt($curl, CURL_HTTP_VERSION_1_1, true);
		curl_setopt($curl, CURLOPT_ENCODING, "gzip, deflate");
		curl_setopt($curl, CURLOPT_USERAGENT, $useragent);

		$data = curl_exec($curl);
		
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		
		$httpcodeOK = isset($httpcode) and ($httpcode == 200 or $httpcode == 301);
		
		curl_close($curl);
	} else {

		// create http context and add timeout and user-agent
		$context = stream_context_create(array('http'=>array('timeout' => $timeout, // Timeout : time until we stop waiting for the response.
																					'header'=> "User-Agent: ".$useragent, // spoot Mozilla Firefox
																					'follow_location' => true
														)));

		// only download page lesser than 4MB
		$data = @file_get_contents($url, false, $context, -1, 4000000); // We download at most 4 MB from source.
		//	echo "<pre>http_response_header : ".print_r($http_response_header);
		
		if(isset($http_response_header) and isset($http_response_header[0])) {
			$httpcodeOK = isset($http_response_header) and isset($http_response_header[0]) and ((strpos($http_response_header[0], '200 OK') !== FALSE) or (strpos($http_response_header[0], '301 Moved Permanently') !== FALSE));
		}
	}

	// if response is not empty and response is OK
	if (isset($data) and isset($httpcodeOK) and httpcodeOK ) {

		// take charset of page and get it
		preg_match('#<meta .*charset=.*>#Usi', $data, $meta);

		// if meta tag is found
		if (!empty($meta[0])) {
			// retrieve encoding in $enc
			preg_match('#charset="?(.*)"#si', $meta[0], $enc);

			// if charset is found set it otherwise, set it to utf-8
			$html_charset = (!empty($enc[1])) ? strtolower($enc[1]) : 'utf-8';

		} else { 
			$html_charset = 'utf-8';
			$enc[1] = '';
		}

		// replace charset of url to charset of page
		$data = str_replace('charset='.$enc[1], 'charset='.$html_charset, $data);

		return $data;
	}
	else {
		return FALSE;
	}
}
function rel2abs($rel, $base)
{
    // return if already absolute URL
    if (parse_url($rel, PHP_URL_SCHEME) != '') return $rel;

    // queries and anchors
    if ($rel[0]=='#' || $rel[0]=='?') return $base.$rel;

    // parse base URL and convert to local variables:
    //   $scheme, $host, $path
    extract(parse_url($base));

    // remove non-directory element from path
    $path = preg_replace('#/[^/]*$#', '', $path);

    // destroy path if relative url points to root
    if ($rel[0] == '/') $path = '';

    // dirty absolute URL
    $abs = "$host$path/$rel";

    // replace '//' or '/./' or '/foo/../' with '/'
    $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
    for($n=1; $n>0; $abs=preg_replace($re, '/', $abs, -1, $n)) {}

    // absolute URL is ready! 
    return $scheme.'://'.$abs;
}

// $str=preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#','$1="http://wintermute.com.au/$2$3',$str);

function absolutes_links($data, $base) {
	// cherche les balises 'a' qui contiennent un href
	$matches = array();
	preg_match_all('#(href|src)="([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#Si', $data, $matches, PREG_SET_ORDER);

	// ne conserve que les liens ne commen�ant pas par un protocole � protocole:// � ni par une ancre � # �
	foreach($matches as $i => $link) {
		$link[1] = trim($link[1]);

		if (!preg_match('#^(([a-z]+://)|(\#))#', $link[1]) ) {

			$absolutePath=rel2abs($link[2],$base);

			$data = str_replace($matches[$i][2], $absolutePath, $data);
		}

	}
	return $data;
}
*/