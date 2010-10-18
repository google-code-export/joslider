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
    			$lists[$i]->created = $row->created;
    			$lists[$i]->modified = $row->modified;
    			$lists[$i]->title = htmlspecialchars( $row->title );
    			$lists[$i]->introtext = $text;
    			$i++;
    		}
        }
		return $lists;
	}
	
	function getTheme (&$params)
	{
		
		$custom	= $params->get('custom_theme',0);
		$theme = $params->get('skin', 'default');
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
					break;
				case 'voyager' :
					$themeparameters['path'] = '/modules/mod_joslider/assets/css/template/voyager/';
					$themeparameters['class'] = 'voyager';
					$themeparameters['css'] = 'skin.css';
					$themeparameters['cssie'] = '';
					$themeparameters['cssie7'] = '';
					$themeparameters['cssie8'] = '';
					break;
				case 'invasion' :
					$themeparameters['path'] = '/modules/mod_joslider/assets/css/template/invasion/';
					$themeparameters['class'] = 'invasion';
					$themeparameters['css'] = 'skin.css';
					$themeparameters['cssie'] = '';
					$themeparameters['cssie7'] = '';
					$themeparameters['cssie8'] = '';
					break;
				case 'literally' :
					$themeparameters['path'] = '/modules/mod_joslider/assets/css/template/literally/';
					$themeparameters['class'] = 'literally';
					$themeparameters['css'] = 'skin.css';
					$themeparameters['cssie'] = '';
					$themeparameters['cssie7'] = '';
					$themeparameters['cssie8'] = 'skin.ie8.css';
					break;
				case 'stitch' :
					$themeparameters['path'] = '/modules/mod_joslider/assets/css/template/stitch/';
					$themeparameters['class'] = 'stitch';
					$themeparameters['css'] = 'skin.css';
					$themeparameters['cssie'] = '';
					$themeparameters['cssie7'] = '';
					$themeparameters['cssie8'] = '';
					break;
				case 'ribbons' :
					$themeparameters['path'] = '/modules/mod_joslider/assets/css/template/ribbons/';
					$themeparameters['class'] = 'ribbons';
					$themeparameters['css'] = 'skin.css';
					$themeparameters['cssie'] = 'skin.ie.css';
					$themeparameters['cssie7'] = '';
					$themeparameters['cssie8'] = '';
					break;
				default :
					$themeparameters['path'] = '/modules/mod_joslider/assets/css/template/default/';
					$themeparameters['class'] = 'slidedeck';
					$themeparameters['css'] = 'slidedeck.skin.css';
					$themeparameters['cssie'] = 'slidedeck.skin.ie.css';
					$themeparameters['cssie7'] = '';
					$themeparameters['cssie8'] = '';
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

			
		return $themeparameters;
	}
	

	
}
