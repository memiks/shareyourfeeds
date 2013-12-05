<?php
header('Content-type:text/xml; charset=utf-8');
// Set locale to French
setlocale(LC_ALL, 'fr_FR');

// set timezone to Europe/Paris
date_default_timezone_set('Europe/Paris');

// set charset to utf-8 important since all pages will be transform to utf-8
//header('Content-Type: text/html;charset=utf-8');

// get readability library
require_once dirname(__FILE__).'/inc/Readability.php';

// get Encoding library.
require_once dirname(__FILE__).'/inc/Encoding.php';

// get SyndExport library.
require_once dirname(__FILE__).'/inc/syndexport.php';

// get EpiCurl library.
require_once dirname(__FILE__).'/inc/EpiCurl.php';

// get SQLite class.
require_once dirname(__FILE__).'/class/sqlite.class.php';

// get Flows class.
require_once dirname(__FILE__).'/class/flows.class.php';

// get functions library.
require_once dirname(__FILE__).'/inc/functions.php';

// get Flows class.
require_once dirname(__FILE__).'/class/items.class.php';

function saveFlowToFile($flow,$XML) {
	$flowFile = "./data/flows/flow_".md5($flow->getUrl()).".xml";
	$fh = fopen($flowFile, 'w') or die("can't open file");
	fwrite($fh, $XML);
	fclose($fh);
}

function getFlowFromFile($flow) {
	$flowFile = "./data/flows/flow_".md5($flow->getUrl()).".xml";
	if(file_exists($flowFile)) {
		$fh = fopen($flowFile, 'r');
		$XML = fread($fh, filesize($flowFile));
		fclose($fh);	
		return $XML;
	}
	return null;
}

function CompleteAndSaveFlow($flow,$flux) {
	$items = $flux->exportItems(-1);

	$saveFlow = false;
	
    SQLite::delete_duplicate_flows();
    
	SQLite::insert_flow_datas_in_database($flow);
	
	if(!file_exists($dbname='base_items_'.md5($flow->getUrl()).".sqlite")) {
		SQLite::create_items_for_flows_table($flow);
	}

	$flux->modifyTitleOfFlow($flow->getName()." - ".getHostFromURL($flow->getUrl()));

	$mc = EpiCurl::getInstance();

	for($index=0;$index < count($items); $index++) {
		$url = $items[$index]->getUrl();
		if(!empty($url)) {
			$item = SQLite::get_items_datas_from_flow_and_url($flow,$url);
			if(!isset($item) || empty($item)) {
                 //|| $item->getDate() < $items[$index]->getDate()
				  $ch = curl_init($url);
				  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				  $getFlow[$index] = $mc->addCurl($ch);
			} else {
				$flux->modifyDescriptionOfItem($index,$item->getDescription());
			}
		}
	}

	for($index=0;$index < count($items); $index++) {
		$url = $items[$index]->getUrl();
		
		if(!empty($url) and isset($getFlow[$index]) 
			and ( $getFlow[$index]->code == 200 or $getFlow[$index]->code == 301 )
		) {
			// convert page to utf-8
			$html = Encoding::toUTF8($getFlow[$index]->data);
			//	echo"<xmp>".$html."</xmp><br><br>";
			
			// send result to readability library
			$r = new Readability($html, $url);

			if($r->init()) {
				// return innerhtml of article found
				$saveFlow = true;

				$desc = absolutes_links($r->articleContent->innerHTML,$url);
				
				//echo"<xmp>".$desc."</xmp><br><br>";
				
				$items[$index]->setDescription($desc);

				SQLite::delete_item_datas_in_items_database($flow,$items[$index]);
				SQLite::insert_item_datas_in_items_database($flow,$items[$index]);

				$flux->modifyDescriptionOfItem($index,$desc);
			}	else {
				//echo "ERRORRRRRRRR!!!!";
			}
		} else {
		}
	}
	//print_r($getFlow);

	//$flux->changeEncoding( 'UTF-8');
	$xml = Encoding::toUTF8($flux->asXml());
	//$xml = $flux->asXml();
	
	if($saveFlow or !file_exists("flow_".md5($flow->getUrl()).".xml")) {
		saveFlowToFile($flow,$xml);
	}
	
	$xml=preg_replace('/<\?\s*xml([^\s]*)\?>/', '<?xml $1 encoding="UTF-8"?>', $xml);

	return $xml;
}


if(isset($_GET['url']) && $_GET['url'] != null && trim($_GET['url']) != "") {
	// get url link
	if(strlen(trim($_GET['url'])) > 2048) {
		echo "Error URL is too large !!";
	} else {
		$url = trim($_GET['url']);

		// decode it
		$url = html_entity_decode($url);
		
		SQLite::create_flows_table();
		
		// if url use https protocol change it to http
		if (!preg_match('!^https?://!i', $url)) $url = 'http://'.$url;
		
		$flow = SQLite::get_flow_datas_from_url($url);
		
		if(!isset($flow) || empty($flow) || canBeUpdated($flow)) {
			$update_flow = true;
			$xml = Encoding::toUTF8(get_external_file($url,15));
			$flux = new SyndExport($xml);
		
			$infos = $flux->exportInfos();
            print_r($infos);

			if(isset($flow) && !empty($flow)) {
				SQLite::delete_flow_datas_in_database($flow);
				if($flow->getUpdateDate() < strtotime($infos["last"])) {
					$update_flow = true;
				}
			} else {
				$update_flow = true;
			}

            if(!isset($infos["author"])) {
                $infos["author"] = "";
            }
            if(!isset($infos["email"])) {
                $infos["email"] = "";
            }
            if(!isset($infos["copyright"])) {
                $infos["copyright"] = "";
            }
            
			$flow = new Flows(null, $infos["title"], $url, time(), 0, $infos["author"]." ".$infos["email"]." ".$infos["copyright"]." ".$infos["description"]);
			$xml = CompleteAndSaveFlow($flow,$flux);

		} else {
			$xml = getFlowFromFile($flow);
			if($xml == null) {
				SQLite::delete_flow_datas_in_database($flow);
				$xml = Encoding::toUTF8(get_external_file($url,15));
				$flux = new SyndExport($xml);
				$xml = CompleteAndSaveFlow($flow,$flux);
			}
		}

		$xml = preg_replace('/<\?xml [^>]*>/im', '<?xml version="1.0" encoding="UTF-8" ?>',$xml);
		echo Encoding::toUTF8($xml);

	}
}


