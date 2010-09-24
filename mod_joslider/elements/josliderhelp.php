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

class JElementJosliderHelp extends JElement
{
	var	$_name = 'Joslider';

	function fetchTooltip($label, $description, &$node, $control_name, $name) {
		return '&nbsp;';
	}

	function fetchElement($name, $value, &$node, $control_name)
	{
		
		if ($node->attributes( 'icon' ) != "") $icon = "<img src=\"".JURI::root(true)."/modules/mod_jslidedeck/assets/images/icons/".$node->attributes( 'icon' )."\"  align=\"absmiddle\" vspace=\"5\" />";
		
		if ($value) {
			return '<p style="background: #fed674;color: #000;padding:5px">'.$icon.' <strong>' . JText::_($value) . '</strong></p>';
		} else {
			return '<hr />';
		}
	}
}
?>