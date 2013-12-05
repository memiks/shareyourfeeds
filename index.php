<?php
// Reporte toutes les erreurs PHP (Voir l'historique des modifications)
error_reporting(E_ALL);

// set charset to utf-8 important since all pages will be transform to utf-8
header('Content-Type: text/html;charset=utf-8');
//header('Content-type:text/xml; charset=utf-8');
// Set locale to French
setlocale(LC_ALL, 'fr_FR');

// set timezone to Europe/Paris
date_default_timezone_set('Europe/Paris');
?>
<html>
<body>
	<form action="./completerss.php">
		<input type="text" name="url" id="url" maxlength="2048" size="80" />
		<input type="submit">
	</form>
    <?php
        include("show.php");
    ?>
    <br>
    <a href="opml.php">export OPML</a>
</body>
</html>