<?php
// Reporte toutes les erreurs PHP (Voir l'historique des modifications)
//error_reporting(E_ALL);

// set charset to utf-8 important since all pages will be transform to utf-8
//header('Content-Type: text/html;charset=utf-8');
//header('Content-type:text/xml; charset=utf-8');
// Set locale to French
setlocale(LC_ALL, 'fr_FR');

// set timezone to Europe/Paris
date_default_timezone_set('Europe/Paris');


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

SQLite::delete_duplicate_flows();

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
?>
<html><body>
<table border=1><thead><tr><th>id</th><th>name</th><th>url</th><th>update date</th><th>comment</th><th>number of articles</th><th>Can be updated ?</th><th>Share Your Feeds Link</th></tr></thead>
<tbody>
    <?php
    //$flows = ;
    foreach(SQLite::get_flows_in_database() as $flow) {
        echo "<tr><td>".$flow->getId()."</td><td>".$flow->getName()."</td><td>".$flow->getUrl()."</td><td>".strftime ("%d/%m/%Y %H:%M",$flow->getUpdateDate())."</td><td>"
        .$flow->getComment()."</td><td>".$flow->getNumberOfArticles()."</td><td>".(canBeUpdated($flow) ? "YES":"NO")."</td><td><a href='completerss.php?url=".urlencode($flow->getUrl())."'>SYF ".$flow->getName()."</a></td></tr>";
    }
    ?>
</tbody>
</table>
</body></html>