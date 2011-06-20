<?php
/**
 * @package Joomla
 * @subpackage Jcampaignmonitor
 * @copyright (C) 2010 - Matthieu BARBE - www.ccomca.com
 * @license GNU/GPL v2
 * 
 * Jcampaignmonitor is a derivative work of the excellent Campaign Monitor Module (from Van Eldijk Studios) and CampaignMonitor Ajax subscription Module (from Joomailer)
 * see http://www.vaneldijk.nl/ and http://www.joomailer.com/ for more information
 *
 * Jcampaignmonitor uses :
 * CampaignMonitorLib (CMBase.php => http://code.google.com/p/campaignmonitor-php/)
 * json class by Michal Migurski, Matt Knapp, Brett Stimmerman
 * 1 000 free "Farm Fresh Web Icons" => http://www.fatcow.com/free-icons/
 * extendedlistnamevalue.php & extendedlist.js are inspired by RocketTheme !
 *
 * Jcampaignmonitor is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */
defined('JPATH_BASE') or die;
jimport('joomla.form.formfield');

class JFormFieldVersionCheck extends JFormField
{
	protected 	$_name = 'VersionCheck';
    protected  $_error = null;

	protected function getInput() {
		
		$cache = & JFactory::getCache('mod_joslider');
		$cache->setCaching( 1 );
		$cache->setLifeTime( 100 );
		$check = $cache->get(array( 'JFormFieldVersionCheck', 'getUpdateModule'), 'module');
		
		if ($check['connect'] == 0)
			{
				$msg ='<div style="background: #FFD5D5;color: #ff0000;padding:5px"> <img src="'.JURI::root(true).'/modules/mod_jcampaignmonitor/assets/images/alert.png" alt ="" align="absmiddle" vspace="5" /> '.JText::_( 'JOSLIDER_CONNECTION_FAILED' ).'</div>';
			}
			else
			{
				if ($check['current'] == 0) {
		  				$msg ='<div style="background: #e4f3e2;color: #000;padding:5px"> <img src="'.JURI::root(true).'/modules/mod_jcampaignmonitor/assets/images/approved.png" alt ="" align="absmiddle" vspace="5" /> '.sprintf ( JText::_( 'JOSLIDER_LATEST_VERSION' ),  $check['version'], $check['current_version'], $check['link']).'</div>';
		  			} else {
		  				$msg ='<div style="background: #FFD5D5;color: #ff0000;padding:5px"> <img src="'.JURI::root(true).'/modules/mod_jcampaignmonitor/assets/images/alert.png" alt ="" align="absmiddle" vspace="5" /> '.sprintf ( JText::_( 'JOSLIDER_NEW_VERSION' ),  $check['version'], $check['current_version'], $check['link']).'</div>';
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
			$check['version'] 		= "";
			
			foreach ($xml->document->entry as $entry) {
				
				$title = (string) $entry->title[0]->data();
				if ($title != "")
					{
						if (preg_match('/joslider16-(.*).zip/', $title, $matches)){	   
									$version = $matches[1];
						}
						$check['version'] 		= $version;
						$released 				= & $entry->updated[0];
						$check['released'] 		= & $released->data();
						$linkattribs 			= & $entry->link[0]->attributes();
						$check['link'] 			= & $linkattribs['href'];
					}
				}
			
			$check['connect'] 		= 1;
			$check['enabled'] 		= 1;
			
			$check['current'] 		= version_compare( $check['current_version'], $check['version'] );
		}
		
		return $check;
	 }

}
?>