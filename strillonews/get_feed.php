<?
/*
	Copyright� 2012,2013 Informatici Senza Frontiere Onlus
	http://www.informaticisenzafrontiere.org

    This file is part of Strillone - spoken news for visually impaired people.

    Strillone is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Strillone is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Strillone.  If not, see <http://www.gnu.org/licenses/>.
*/ 

$reorder = false;
$convert = false;

$giornale = 'favole e racconti';
if (isset($_GET['giornale']) && ($_GET['giornale'] != '')) {
	$giornale = $_GET['giornale'];
}

switch ($giornale) {

	case 'gdb':
	$sUrl = 'http://ipad.giornaledibrescia.it/edizioni.xml';
	break;

    case 'Repubblica punto it':
	case 'Repubblica%20punto%20it':
	$reorder = false;
	$convert = false;
    $sUrl = './feeds/repubblicait.xml';
	break;

	case 'go fasano':
	case 'go%20fasano':
	$reorder = true;
	$convert = true;
	$sUrl = 'http://gofasano.it/xmlArticoli.php';
	break;

	case 'go bari':
	case 'go%20bari':
	$reorder = true;
	$convert = true;
	$sUrl = 'http://www.go-bari.it/xmlArticoli.php';
	break;

	case 'favole e racconti':
	case 'favole%20e%20racconti':
	$convert = false;
	$sUrl = './feeds/favole_racconti.xml';
	break;

	case 'onu organizzazione delle nazioni unite':
	case 'onu%20organizzazione%20delle%20nazioni%20unite':
	$convert = false;
	$sUrl = './feeds/dudu_onu.xml';
	break;

	case 'test inglese':
	case 'test%20inglese':
	$convert = false;
	$sUrl = './feeds/test_inglese.xml';
	break;

	case 'test portoghese':
	case 'test%20portoghese':
	$convert = false;
	$sUrl = './feeds/test_portoghese.xml';
	break;

	case 'test repubblica':
	case 'test%20repubblica':
	$convert = false;
	$sUrl = './feeds/test_repubblica.xml';
    break;
}

$file_xml = file_get_contents($sUrl);

// applico alcune trasformazioni affinch� il file sia letto bene da tutti i sistemi

$device = 'app';
if (isset($_GET['device']) && ($_GET['device'] != '')) {
	$device = $_GET['device'];
}

$wrongs = array();
$rights = array();

switch ($device) {
	case 'app':
	$wrongs[] = 'version="1.0"?'; 	$rights[] = 'version="1.0" encoding="iso-8859-1"?';
	$wrongs[] = '&nbsp;'; 			$rights[] = ' ';
	$wrongs[] = '\r\n\r\n'; 		$rights[] = '\r\n';

	$wrongs[] = '&ldquo;'; 			$rights[] = '"';
	$wrongs[] = '&rdquo;'; 			$rights[] = '"';
	$wrongs[] = '&rsquo;'; 			$rights[] = '\'';
	$wrongs[] = '&lsquo;'; 			$rights[] = '\'';
	$wrongs[] = '&raquo;'; 			$rights[] = '"';
	$wrongs[] = '&laquo;'; 			$rights[] = '"';

	$wrongs[] = '&deg;'; 			$rights[] = '�';
	$wrongs[] = '&ndash;'; 			$rights[] = '-';
	$wrongs[] = '<nome>'; 			$rights[] = '<nome><![CDATA[';
	$wrongs[] = '</nome>'; 			$rights[] = ']]></nome>';

	$wrongs[] = '&agrave;'; 		$rights[] = '�';
	$wrongs[] = '&eacute;'; 		$rights[] = '�';
	$wrongs[] = '&egrave;'; 		$rights[] = '�';
	$wrongs[] = '&Egrave;'; 		$rights[] = '�';
	$wrongs[] = '&igrave;'; 		$rights[] = '�';
	$wrongs[] = '&ograve;'; 		$rights[] = '�';
	$wrongs[] = '&ugrave;'; 		$rights[] = '�';

	$wrongs[] = '�'; 				$rights[] = '...';

	$file_xml = str_replace($wrongs,$rights,$file_xml);

	header('Content-Type: text/xml; charset=iso-8859-1');
	break;

	case 'web':
	$wrongs[] = 'version="1.0"?'; 	$rights[] = 'version="1.0" encoding="ISO-8859-1"?';
	$wrongs[] = '&nbsp;'; 			$rights[] = ' ';
	$wrongs[] = '\r\n\r\n'; 		$rights[] = '\r\n';

	$wrongs[] = '&ldquo;'; 			$rights[] = '"';
	$wrongs[] = '&rdquo;'; 			$rights[] = '"';
	$wrongs[] = '&rsquo;'; 			$rights[] = '\'';
	$wrongs[] = '&lsquo;'; 			$rights[] = '\'';
	$wrongs[] = '&raquo;'; 			$rights[] = '"';
	$wrongs[] = '&laquo;'; 			$rights[] = '"';
	$wrongs[] = '&deg;'; 			$rights[] = '�';

	$wrongs[] = '<nome>'; 			$rights[] = '<nome><![CDATA[';
	$wrongs[] = '</nome>'; 			$rights[] = ']]></nome>';

	$wrongs[] = '�'; 		$rights[] = '&agrave;';
	$wrongs[] = '�'; 		$rights[] = '&eacute;';
	$wrongs[] = '�'; 		$rights[] = '&egrave;';
	$wrongs[] = '�'; 		$rights[] = '&Egrave;';
	$wrongs[] = '�'; 		$rights[] = '&igrave;';
	$wrongs[] = '�'; 		$rights[] = '&ograve;';
	$wrongs[] = '�'; 		$rights[] = '&ugrave;';

	$wrongs[] = '�'; 				$rights[] = '...';


	$file_xml = str_replace($wrongs,$rights,$file_xml);

	header('Content-Type: text/xml; charset=iso-8859-1');
	break;

}

if ($reorder) {

	$dom = new DOMDocument();
	$dom->load($sUrl,LIBXML_DTDLOAD|LIBXML_DTDATTR);

	/*create the xPath object _after_  loading the xml source, otherwise the query won't work:*/
	$xPath = new DOMXPath($dom);

	/*now get the nodes in a DOMNodeList:*/
	$lingua = $xPath->query("//*[local-name() = 'lingua']");
	$testata = $xPath->query("//*[local-name() = 'testata']");
	$edizione = $xPath->query("//*[local-name() = 'edizione']");
	$nodeList = $xPath->query("//*[local-name() = 'sezione']");

	/*create a new DOMDocument and add a root element:*/
	$newDom = new DOMDocument('1.0','iso-8859-1');
	$newDom->formatOutput = true;
	$newDom->preserveWhiteSpace = false ;
	$root = $newDom->createElement('giornale');
	$newLingua = $newDom->createElement('lingua', $lingua->item(0)->nodeValue);
	$newTestata = $newDom->createElement('testata', $testata->item(0)->nodeValue);
	$newEdizione = $newDom->createElement('edizione', $edizione->item(0)->nodeValue);
	$root->appendChild($newLingua);
	$root->appendChild($newTestata);
	$root->appendChild($newEdizione);
	$sections = array();

	/* append all nodes from $nodeList to the new dom, as children of $root:*/
	foreach ($nodeList as $domElement){
		$figli = $domElement->childNodes ;
		foreach ($figli as $singleNode){
		if($singleNode->nodeType != 3){
			if($singleNode->nodeName == "nome"){
				if(!array_key_exists($singleNode->nodeValue, $sections)){
					$sections[$singleNode->nodeValue] = array();
				}
				$var = $singleNode->nextSibling->nextSibling;
				$tempArray = array();
				foreach($var->childNodes as $node)
				{
					if($node->nodeName=="titolo")
						$tempArray['titolo'] = $node->nodeValue;
					else if ($node->nodeName=="testo")
						$tempArray['testo'] =  $node->nodeValue;
				}
			$sections[$singleNode->nodeValue][]= $tempArray;
			}
		}
		}
	}
	foreach($sections as $key => $section ){
		$nodeSection = $newDom->createElement('sezione');
		$newDom->appendChild($nodeSection);
		$namenode = $newDom->createElement('nome', $key);
		$nodeSection->appendChild($namenode);
		foreach($section as $child){ 
			$articolo = $newDom->createElement('articolo');
			//$titolo = $newDom->createElement('titolo', $child['titolo']);
			//$testo = $newDom->createElement('testo', $child['testo']);
			$titolo = $newDom->createElement('titolo');
			$corpotitolo = $newDom->createCDATASection($child['titolo']);
			$testo = $newDom->createElement('testo');
			$corpotesto = $newDom->createCDATASection($child['testo']);
			$testo->appendChild($corpotesto);
			$titolo->appendChild($corpotitolo);
			$articolo->appendChild($titolo);
			$articolo->appendChild($testo);
			$nodeSection->appendChild($articolo);
		}	
		$root->appendChild($nodeSection);
	}
	$newDom->appendChild($root);
	//$urlsave = './newDOM.xml';
	//$newDom->saveXML();

	header('Content-Type: text/xml; charset=iso-8859-1');
	echo $newDom->saveXML();

} else {
	echo $file_xml;
}


?>
