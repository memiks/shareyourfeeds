<?php
header('Content-type:text/xml; charset=utf-8');
// Set locale to French
setlocale(LC_ALL, 'fr_FR');

// set timezone to Europe/Paris
date_default_timezone_set('Europe/Paris');

// set charset to utf-8 important since all pages will be transform to utf-8
header('Content-Type: text/html;charset=utf-8');

// get SyndExport library.
require_once dirname(__FILE__).'/inc/simple_html_dom.php';

// appel de la libraire RainTPL.
require_once dirname(__FILE__).'/inc/rain.tpl.class.php';

// get functions library.
require_once dirname(__FILE__).'/inc/functions.php';

function saveUrlToFile($url,$XML) {
	$flowFile = "flow_".md5($url).".xml";
	$fh = fopen($flowFile, 'w') or die("can't open file");
	fwrite($fh, $XML);
	fclose($fh);
}

function getUrlFromFile($url) {
	$flowFile = "flow_".md5($url).".xml";
	if(file_exists($flowFile)) {
		$fh = fopen($flowFile, 'r');
		$XML = fread($fh, filesize($flowFile));
		fclose($fh);	
		return $XML;
	}
	return null;
}
// Write a function with parameter "$element"
function my_callback($element) {
        // Hide all <b> tags
        if ($element->tag=='script' || ( $element->tag=='span' && $element->id != null && startsWith( $element->id, "debutDefinition_") )) {
                $element->outertext = '';
		}
}

// to retrieve selected html data, try these DomXPath examples:

$url = "http://www.techno-science.net/?onglet=news&news=11092";
//$xml = get_external_file($url,15);
$xml = getUrlFromFile($url);

// Create a DOM object from a string
$html = str_get_html($xml);

// Register the callback function with it's function name
$html->set_callback('my_callback');

// example 1: for everything with an id
$elements = "div[class='texte']";

// Find all <div> which attribute id=foo
$ret = $html->find($elements); 

// example 1: for everything with an id
$elements = "title";

// Find all <div> which attribute id=foo
$title = $html->find($elements); 

if(count($ret) == 1) {
	generate_page($url,$title[0]->innertext,absolutes_links($ret[0],$url));

//	echo absolutes_links($ret[0],$url);
}

