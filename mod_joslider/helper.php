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

// no direct access
defined( '_JEXEC' ) or die('Restricted access');

require_once (JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');

class modjosliderHelper
{
	
	function getList(&$params)
	{
		global $mainframe;
		
		$cparams	    =& $mainframe->getParams('com_content');

		$db			    =& JFactory::getDBO();
		$user		    =& JFactory::getUser();
		$userId		    = (int) $user->get('id');

		$count		    = $params->get('tab_count',4); 
		$catid		    = trim( $params->get('catid') );
		$secid		    = trim( $params->get('secid') );
		$show_front	    = $params->get('show_front', 1);
		$aid		    = $user->get('aid', 0);
		$content_type   = $params->get('content_type','joomla');
		$ordering       = $params->get('itemsOrdering');
		$cid            = $params->get('category_id', NULL);

		$contentConfig  = &JComponentHelper::getParams( 'com_content' );
		$access		    = !$contentConfig->get('shownoauth');

		$nullDate	    = $db->getNullDate();

		$date =& JFactory::getDate();
		$now = $date->toMySQL();
		$where = '';
		
		
		// ensure should be published
		$where .= " AND ( a.publish_up = ".$db->Quote($nullDate)." OR a.publish_up <= ".$db->Quote($now)." )";
		$where .= " AND ( a.publish_down = ".$db->Quote($nullDate)." OR a.publish_down >= ".$db->Quote($now)." )";
		
	    // ordering
		switch ($ordering) {
			case 'date' :
				$orderby = 'a.created ASC';
				break;
			case 'rdate' :
				$orderby = 'a.created DESC';
				break;
			case 'alpha' :
				$orderby = 'a.title';
				break;
			case 'ralpha' :
				$orderby = 'a.title DESC';
				break;
			case 'order' :
				$orderby = 'a.ordering';
				break;
			case 'random' :
				$orderby = 'rand()';
				break;
			default :
				$orderby = 'a.id DESC';
				break;
		}
		
		// content specific stuff
        if ($content_type=='joomla') {
            // start Joomla specific
            
            $catCondition = '';
            $secCondition = '';

            if ($show_front != 2) {
        		if ($catid)
        		{
        			$ids = explode( ',', $catid );
        			JArrayHelper::toInteger( $ids );
        			$catCondition = ' AND (cc.id=' . implode( ' OR cc.id=', $ids ) . ')';
        		}
        		if ($secid)
        		{
        			$ids = explode( ',', $secid );
        			JArrayHelper::toInteger( $ids );
        			$secCondition = ' AND (s.id=' . implode( ' OR s.id=', $ids ) . ')';
        		}
        	}
		
    		// Content Items only
    		$query = 'SELECT a.*, s.name as sectioname, ' .
    			' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug,'.
    			' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug'.
    			' FROM #__content AS a' .
    			($show_front == '0' ? ' LEFT JOIN #__content_frontpage AS f ON f.content_id = a.id' : '') .
    			($show_front == '2' ? ' INNER JOIN #__content_frontpage AS f ON f.content_id = a.id' : '') .
    			' INNER JOIN #__categories AS cc ON cc.id = a.catid' .
    			' INNER JOIN #__sections AS s ON s.id = a.sectionid' .
    			' WHERE a.state = 1'. $where .' AND s.id > 0' .
    			($access ? ' AND a.access <= ' .(int) $aid. ' AND cc.access <= ' .(int) $aid. ' AND s.access <= ' .(int) $aid : '').
    			($catid && $show_front != 2 ? $catCondition : '').
    			($secid && $show_front != 2 ? $secCondition : '').
    			($show_front == '0' ? ' AND f.content_id IS NULL ' : '').
    			' AND s.published = 1' .
    			' AND cc.published = 1' .
    			' ORDER BY '. $orderby;
    		// end Joomla specific
	    } 
		
			
		$db->setQuery($query, 0, $count);

		$rows = $db->loadObjectList();

        $i=0;
		$lists	= array();
		
		if (is_array($rows) && count($rows)>0) {
    		foreach ( $rows as $row )
    		{
    		    //process content plugins
    		    $text = JHTML::_('content.prepare',$row->introtext,$cparams);
    			$lists[$i]->id = $row->id;
				$lists[$i]->link = JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catslug, $row->sectionid));
    			$lists[$i]->title = modjosliderHelper::getTitle ($params, $row->title);
				$lists[$i]->date =  modjosliderHelper::getDate ($params, $row->created , $row->modified);
				$lists[$i]->dateSmall =  modjosliderHelper::getDateSmall ($params, $row->created , $row->modified);
    			$lists[$i]->introtext = modjosliderHelper::getText ($params,  $text );
				if ($params->get('skin_type') == 'smart' && $params->get('extractImage') == 1)
					{
					$thumb_size = $params->get('thumbSize',90);
					$images = modjosliderHelper::getImages($row->introtext,$thumb_size);
					$lists[$i]->image = $images->image;
    				$lists[$i]->thumb = $images->thumb;
					$lists[$i]->thumbSizes = $images->thumbSizes;
					}
    			$i++;
    		}
        }
		return $lists;
	}
	
	function getTheme (&$params)
	{
		
		$custom	= $params->get('custom_theme',0);
		
		if ($params->get('skin_type') == 'smart')
			{
				$theme = $params->get('skinSmart', 'smart');
			}
			else
			{
				$theme = $params->get('skin', 'default');
			}
		$themeparameters = array();
		
		if ($custom == 0)
			{
			switch ($theme) {
			case 'default' :
					$themeparameters['path'] = '/modules/mod_joslider/assets/css/template/default/';
					$themeparameters['class'] = 'slidedeck';
					$themeparameters['css'] = 'slidedeck.skin.css';
					$themeparameters['cssie'] = 'slidedeck.skin.ie.css';
					$themeparameters['cssie7'] = '';
					$themeparameters['cssie8'] = '';
					$themeparameters['skinjs'] = '';
					break;
				case 'voyager' :
					$themeparameters['path'] = '/modules/mod_joslider/assets/css/template/voyager/';
					$themeparameters['class'] = 'voyager';
					$themeparameters['css'] = 'skin.css';
					$themeparameters['cssie'] = '';
					$themeparameters['cssie7'] = '';
					$themeparameters['cssie8'] = '';
					$themeparameters['skinjs'] = '';
					break;
				case 'invasion' :
					$themeparameters['path'] = '/modules/mod_joslider/assets/css/template/invasion/';
					$themeparameters['class'] = 'invasion';
					$themeparameters['css'] = 'skin.css';
					$themeparameters['cssie'] = '';
					$themeparameters['cssie7'] = '';
					$themeparameters['cssie8'] = '';
					$themeparameters['skinjs'] = '';
					break;
				case 'literally' :
					$themeparameters['path'] = '/modules/mod_joslider/assets/css/template/literally/';
					$themeparameters['class'] = 'literally';
					$themeparameters['css'] = 'skin.css';
					$themeparameters['cssie'] = '';
					$themeparameters['cssie7'] = '';
					$themeparameters['cssie8'] = 'skin.ie8.css';
					$themeparameters['skinjs'] = '';
					break;
				case 'stitch' :
					$themeparameters['path'] = '/modules/mod_joslider/assets/css/template/stitch/';
					$themeparameters['class'] = 'stitch';
					$themeparameters['css'] = 'skin.css';
					$themeparameters['cssie'] = '';
					$themeparameters['cssie7'] = '';
					$themeparameters['cssie8'] = '';
					$themeparameters['skinjs'] = '';
					break;
				case 'ribbons' :
					$themeparameters['path'] = '/modules/mod_joslider/assets/css/template/ribbons/';
					$themeparameters['class'] = 'ribbons';
					$themeparameters['css'] = 'skin.css';
					$themeparameters['cssie'] = 'skin.ie.css';
					$themeparameters['cssie7'] = '';
					$themeparameters['cssie8'] = '';
					$themeparameters['skinjs'] = '';
					break;
				case 'smart' :
					$themeparameters['path'] = '/modules/mod_joslider/assets/css/template/smart/';
					$themeparameters['class'] = 'light';
					$themeparameters['css'] = 'skin.css';
					$themeparameters['cssie'] = '';
					$themeparameters['cssie7'] = 'skin.ie7.css';
					$themeparameters['cssie8'] = '';
					$themeparameters['skinjs'] = 'skin.js';
					break;
				default :
					$themeparameters['path'] = '/modules/mod_joslider/assets/css/template/default/';
					$themeparameters['class'] = 'slidedeck';
					$themeparameters['css'] = 'slidedeck.skin.css';
					$themeparameters['cssie'] = 'slidedeck.skin.ie.css';
					$themeparameters['cssie7'] = '';
					$themeparameters['cssie8'] = '';
					$themeparameters['skinjs'] = '';
					break;
				}
			}
			else
			{
				$themeparameters['path'] = $params->get('theme_repertory', '/modules/mod_joslider/assets/css/template/custom/');
				$themeparameters['class'] = $params->get('theme_class');
				$themeparameters['css'] = $params->get('skin_theme');
				$themeparameters['cssie'] = $params->get('skin_ie');
				$themeparameters['cssie7'] = $params->get('skin_ie7');
				$themeparameters['cssie8'] = $params->get('skin_ie8');
				$themeparameters['skinjs'] = $params->get('skin_js');
				
			}
		
		$doc =& JFactory::getDocument();
		$doc->addStyleSheet(JURI::Root(true) . ''.$themeparameters['path'].''.$themeparameters['css']);
		
		
		
		if ($themeparameters['cssie'])
			{
			$cssie = '<!--[if IE]>' ."\n";
			$cssie .= '<link rel="stylesheet" type="text/css" href="'.JURI::Root(true) . ''.$themeparameters['path'].''.$themeparameters['cssie'].'" media="screen,handheld" />' ."\n";
			$cssie .= '<![endif]-->' ."\n";
			$doc->addCustomTag($cssie);
			}
		
		if ($themeparameters['cssie7'])
			{
			$cssie = '<!--[if lte IE 7]>' ."\n";
			$cssie .= '<link rel="stylesheet" type="text/css" href="'.JURI::Root(true) . ''.$themeparameters['path'].''.$themeparameters['cssie8'].'" media="screen,handheld" />' ."\n";
			$cssie .= '<![endif]-->' ."\n";
			$doc->addCustomTag($cssie);
			}
			
		if ($themeparameters['cssie8'])
			{
			$cssie = '<!--[if lte IE 8]>' ."\n";
			$cssie .= '<link rel="stylesheet" type="text/css" href="'.JURI::Root(true) . ''.$themeparameters['path'].''.$themeparameters['cssie8'].'" media="screen,handheld" />' ."\n";
			$cssie .= '<![endif]-->' ."\n";
			$doc->addCustomTag($cssie);
			}
		if ($themeparameters['cssie9'])
			{
			$cssie = '<!--[if lte IE 9]>' ."\n";
			$cssie .= '<link rel="stylesheet" type="text/css" href="'.JURI::Root(true) . ''.$themeparameters['path'].''.$themeparameters['cssie9'].'" media="screen,handheld" />' ."\n";
			$cssie .= '<![endif]-->' ."\n";
			$doc->addCustomTag($cssie);
			}

			
		return $themeparameters;
	}
	
	
	function getTitle (&$params, $title)
	{
		if ((int)$params->get('truncateTitle', 0) == 0) $titleDisplay = $title; else $titleDisplay = modjosliderHelper::truncate ($title, (int)$params->get('truncateTitle'), '...', false);
		
		return $titleDisplay;
	}
	
	function getText (&$params, $text)
	{
		if ($params->get('skin_type') == 'smart')
		{
			if ((int)$params->get('truncateText', 0) == 0) $textDisplay = $text; else $textDisplay = modjosliderHelper::truncate ($text, (int)$params->get('truncateText'), '...', true);
			
			//remove image
			if ( $params->get('extractImage') == 1)
				{
				$textDisplay = preg_replace( "/<img[^>]+\>/i", "", $textDisplay );
				}
		}
		else
		{
			if ((int)$params->get('truncateText', 0) == 0) $textDisplay = $text; else $textDisplay = modjosliderHelper::truncate ($text, (int)$params->get('truncateText'), '...', true);
		}
		return $textDisplay;
	}
	
	function getDate (&$params, $dateCreation, $dateModification)
	{
		if ($params->get('dateType', 'dateCreation') == 'dateCreation') $dateFormat = $dateCreation; else $dateFormat = $dateModification;
		return	$dateDisplay = JHTML::_('date', $dateFormat, JText::_($params->get('dateFormat', 'DATE_FORMAT_LC')));
	}
	
	function getDateSmall (&$params, $dateCreation, $dateModification)
	{
		if ($params->get('dateType', 'dateCreation') == 'dateCreation') $dateFormat = $dateCreation; else $dateFormat = $dateModification;
		return	$dateDisplay = JHTML::_('date', $dateFormat, JText::_('DATE_FORMAT_SHORT'));
	}
	
	function getImages($text, $thumb_size=70) {	  
		
		preg_match("/\<img.+?src=\"(.+?)\".+?\/>/", $text, $matches);
		
		$images = new stdClass();
		$images->image = false;
		$images->thumb = false;
		$images->thumbSizes = array('width' => $thumb_size, 'height' => 'auto');

		$paths = array();
		
		if (isset($matches[1])) {
			$image_path = $matches[1];

			//joomla 1.5 only
			$full_url = JURI::base();
			
			//remove any protocol/site info from the image path
			$parsed_url = parse_url($full_url);
			
			$paths[] = $full_url;
			if (isset($parsed_url['path']) && $parsed_url['path'] != "/") $paths[] = $parsed_url['path'];
			
			
			foreach ($paths as $path) {
				if (strpos($image_path,$path) === 0) {
					$image_path = substr($image_path,strpos($image_path, $path)+strlen($path));
				}
			}
			
			// remove any / that begins the path
			if (substr($image_path, 0 , 1) == '/') $image_path = substr($image_path, 1);
			
			//if after removing the uri, still has protocol then the image
			//is remote and we don't support thumbs for external images
			if (strpos($image_path,'http://') !== false ||
				strpos($image_path,'https://') !== false) {
				return false;
			}
			
			$images->image = JURI::Root(True)."/".$image_path;
			
			// create a thumb filename
			$file_div = strrpos($image_path,'.');
			$thumb_ext = substr($image_path, $file_div);
			$thumb_prev = substr($image_path, 0, $file_div);
			$thumb_path = $thumb_prev . "_thumb" . $thumb_ext;
	
			// check to see if this file exists, if so we don't need to create it
			if (function_exists("gd_info")) {
				// file doens't exist, so create it and save it
				if (!class_exists("Thumbnail")) include_once('library/thumbnail.inc.php');
				
				if (file_exists($thumb_path)) {
				    $existing_thumb = new Thumbnail($thumb_path);
				    $current_size = $existing_thumb->getCurrentWidth();
					$images->thumbSizes = $existing_thumb->currentDimensions;
				}

                if (!file_exists($thumb_path) || $current_size!=$thumb_size) {
				
				    $thumb = new Thumbnail($image_path);
				
    				if ($thumb->error) { 
    					echo "JOSLIDER ERROR: " . $thumb->errmsg . ": " . $image_path; 
    					return false;
    				}
    				$thumb->resize($thumb_size);
    				if (!is_writable(dirname($thumb_path))) {
    					$thumb->destruct();
    					return false;
    				}
    				$thumb->save($thumb_path);
					$images->thumbSizes = $thumb->currentDimensions;
    				$thumb->destruct();
    			}
			}
			$images->thumb = $thumb_path;
		} 
		return $images;
	}
	
	//+ Jonas Raoni Soares Silva
	//@ http://jsfromhell.com

	public static function truncate($text, $length, $suffix = '&hellip;', $isHTML = true){
		$i = 0;
		$simpleTags=array('br'=>true,'hr'=>true,'input'=>true,'image'=>true,'link'=>true,'meta'=>true);
		$tags = array();
		if($isHTML){
			preg_match_all('/<[^>]+>([^<]*)/', $text, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
			foreach($m as $o){
				if($o[0][1] - $i >= $length)
					break;
				$t = substr(strtok($o[0][0], " \t\n\r\0\x0B>"), 1);
				// test if the tag is unpaired, then we mustn't save them
				if($t[0] != '/' && (!isset($simpleTags[$t])))
					$tags[] = $t;
				elseif(end($tags) == substr($t, 1))
					array_pop($tags);
				$i += $o[1][1] - $o[0][1];
			}
		}
		
		// output without closing tags
		$output = substr($text, 0, $length = min(strlen($text),  $length + $i));
		// closing tags
		$output2 = (count($tags = array_reverse($tags)) ? '</' . implode('></', $tags) . '>' : '');
		
		// Find last space or HTML tag (solving problem with last space in HTML tag eg. <span class="new">)
		$pos = (int)end(end(preg_split('/<.*>| /', $output, -1, PREG_SPLIT_OFFSET_CAPTURE)));
		// Append closing tags to output
		$output.=$output2;

		// Get everything until last space
		$one = substr($output, 0, $pos);
		// Get the rest
		$two = substr($output, $pos, (strlen($output) - $pos));
		// Extract all tags from the last bit
		preg_match_all('/<(.*?)>/s', $two, $tags);
		// Add suffix if needed
		if (strlen($text) > $length) { $one .= $suffix; }
		// Re-attach tags
		$output = $one . implode($tags[0]);

		//added to remove  unnecessary closure
		$output = str_replace('</!-->','',$output); 

		return $output;
	}




	
}
