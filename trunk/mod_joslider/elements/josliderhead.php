<?php
/**
 * @package Joomla
 * @subpackage joSlider
 * @copyright (C) 2010 - Matthieu BARBE - www.ccomca.com
 * @license GNU/GPL v2
 * 
 *
 * joSlider is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

//-- No direct access
defined('_JEXEC') or die();

class JElementJosliderHead extends JElement
{
	var	$_name = 'Joslider';

	function fetchTooltip($label, $description, &$node, $control_name, $name) {
		return '&nbsp;';
	}

	function fetchElement($name, $value, &$node, $control_name)
	{
		
		if ($node->attributes( 'icon' ) != "") $icon = "<img src=\"".JURI::root(true)."/modules/mod_jslidedeck/assets/images/icons/".$node->attributes( 'icon' )."\" alt =\"" . JText::_($value) . "\" align=\"absmiddle\" vspace=\"5\" />";
		
		if ($value) {
			return '<p style="background: #CCE6FF;color: #0069CC;padding:5px">'.$icon.' <strong>' . JText::_($value) . '</strong></p>';
		} else {
			return '<hr />';
		}
	}
}
?>