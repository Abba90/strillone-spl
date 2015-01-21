<?
/*
	Copyrightę 2012,2013 Informatici Senza Frontiere Onlus
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

include "functions.php";

$audiodir = "/var/www/walks/strillone/audio";

$available_voices = array();

$available_voices['it'] = array();
$available_voices['en'] = array();

// ITALIAN
$available_voices['it'][0] = 'it_giorgio';
$available_voices['it'][1] = 'it_carla';

//ENGLISH
$available_voices['en'][0] = 'en_us_salli';
$available_voices['en'][1] = 'en_us_ivy';
$available_voices['en'][2] = 'en_us_eric';
$available_voices['en'][3] = 'en_us_jennifer';

$chosen_voice = $available_voices['it'][1];

if (isset($_POST["speech"]) && ($_POST["speech"] != '')) {
    
	$speech = stripslashes(trim($_POST["speech"]));

	$volume_scale = intval($_POST["volume_scale"]);
	if ($volume_scale <= 0) { $volume_scale = 1; }
	if ($volume_scale > 100) { $volume_scale = 100; }

	// continue only if some text was entered for conversion
	if ($speech != '') {
		$filename = md5('ivona'.preg_replace("/[^a-zA-Z0-9]/", "", $speech));
		trace("MD5: ".$filename);
		$speech_file = $audiodir . "/" . $filename;
		trace("FILE: ".$speech_file);
		
		
		if (!file_exists($speech_file)) {
			$soap = new SoapClient('http://www.ivona.com/saasapiwsdl.xml');
			$token = $soap->__soapCall('getToken', array('email'=>'gennaro.delcampo@informaticisenzafrontiere.org'));
			$apikey = 'uWQn9cm8xnTadi3HXLRjz12f2KNTL27u';
			// the text should be read 86% slower
			//$params[] = array('key' => 'Prosody-Volume', 'value' => $volume_scale);
			$params[] = array('key' => 'Prosody-Rate', 'value' => '100');
			// 2.5s of pause between paragraphs
			$params[] = array('key' => 'Paragraph-Break', 'value' => '2500');
			// 1.5s of pause between sentences
			$params[] = array('key' => 'Sentence-Break', 'value' => '1000');
			$speechFile = $soap->__soapCall('createSpeechFile',
				array(
					'token' => $token, 
					'md5' => md5(md5($apikey).$token),
					'text' => $speech, 
					'contentType' => 'text/plain',
					'voiceId' => $chosen_voice,
					'codecId' => 'mp3/22050',
					//'codecId' => 'ogg/22050',
					'params' => $params
				)
			);

            file_put_contents($speech_file, file_get_contents($speechFile['soundUrl']));

			trace("Ivona: ".$speechFile['fileId']." Price ".$speechFile['charactersPrice']." -> ".$speechFile['soundUrl']);

			$return_value = $speechFile['soundUrl'];
		} else {
			$return_value = "audio/".basename($speech_file);
			trace("File exists! ".$return_value);
		}

		trace("Text: ".$speech);
	}


	header('Content-Type: text/html');
	echo $return_value;

}

?>
