<?php
/**
 * @package Joomla
 * @subpackage joSlider
 * @copyright (C) 2010 - Matthieu BARBE - www.ccomca.com
 * @license GNU/GPL v2
 *
 *
 * joSlider is a module for Joomla, that allows use the  free script SlideDeck.
 * SlideDeck ® (http://www.slidedeck.com/) is a registered trademark of digital-telepathy (http://www.dtelepathy.com/).
 *
 * joSlider is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */
 
 //-- No direct access
defined('JPATH_BASE') or die();

class JElementVersionCheck extends JElement
{
	var	$_name = 'VersionCheck';
    var $_error = null;

	function fetchElement($name, $value, &$node, $control_name)
	{
		
		$cache = & JFactory::getCache('mod_joslider');
		$cache->setCaching( 1 );
		$cache->setLifeTime( 100 );
		$check = $cache->get(array( 'JElementVersionCheck', 'getUpdateModule'), 'module');
		
		if ($check['connect'] == 0)
			{
				$msg ='<div style="background: #FFD5D5;color: #ff0000;padding:5px"> <img src="'.JURI::root(true).'/modules/mod_jslidedeck/assets/images/alert.png" alt ="" align="absmiddle" vspace="5" /> '.JText::_( 'CONNECTION_FAILED' ).'</div>';
			}
			else
			{
				if ($check['current'] == 0) {
		  				$msg ='<div style="background: #e4f3e2;color: #000;padding:5px"> <img src="'.JURI::root(true).'/modules/mod_jslidedeck/assets/images/approved.png" alt ="" align="absmiddle" vspace="5" /> '.sprintf ( JText::_( 'LATEST_VERSION' ),  $check['version'], $check['current_version'], $check['link']).'</div>';
		  			} else {
		  				$msg ='<div style="background: #FFD5D5;color: #ff0000;padding:5px"> <img src="'.JURI::root(true).'/modules/mod_jslidedeck/assets/images/alert.png" alt ="" align="absmiddle" vspace="5" /> '.sprintf ( JText::_( 'NEW_VERSION' ),  $check['version'], $check['current_version'], $check['link']).'</div>';
		  			}
				
			}
			return $msg;
	}
	
	function getUpdateModule()
	 {
	 	$url = 'http://code.google.com/feeds/p/joslider/downloads/basic';
		$data = '';
		$check = array();
		$check['connect'] = 0;
		
		$mod_xml 		= JApplicationHelper::parseXMLInstallFile( JPATH_SITE .DS. 'modules' .DS. 'mod_joslider' .DS. 'mod_joslider.xml' );
		$check['current_version'] = $mod_xml['version'];

		//try to connect via cURL
		if(function_exists('curl_init') && function_exists('curl_exec')) {		
			$ch = @curl_init();
			
			@curl_setopt($ch, CURLOPT_URL, $url);
			@curl_setopt($ch, CURLOPT_HEADER, 0);
			//http code is greater than or equal to 300 ->fail
			@curl_setopt($ch, CURLOPT_FAILONERROR, 1);
			@curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			//timeout of 5s just in case
			@curl_setopt($ch, CURLOPT_TIMEOUT, 5);
						
			$data = @curl_exec($ch);
						
			@curl_close($ch);
		}

		
						
		if( $data ) {
			$xml = & JFactory::getXMLparser('Simple');
			$xml->loadString($data);
			
			foreach ($xml->document->entry as $entry) {
				
				$title = (string) $entry->title[0]->data();
				if (preg_match('/joslider-(.*).zip/', $title, $matches)){
						   
							$version = $matches[1];
				}
				$released 				= & $entry->updated[0];
				$check['released'] 		= & $released->data();
				$linkattribs 			= & $entry->link[0]->attributes();
				$check['link'] 			= & $linkattribs['href'];
			}
			
			
			$check['version'] 		= $version;
			$check['connect'] 		= 1;
			$check['enabled'] 		= 1;
			
			$check['current'] 		= version_compare( $check['current_version'], $check['version'] );
		}
		
		return $check;
	 }

}