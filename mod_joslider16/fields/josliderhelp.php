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
defined('_JEXEC') or die();

class JFormFieldJosliderHelp extends JFormField
{
	var	$_name = 'Joslider';

	protected function getTooltip() {
		return '&nbsp;';
	}
	
	protected function getInput()
	{
		return ' ';
	}

	protected function getLabel()
	{
		
		if ($this->element['icon'] != "") $icon = "<img src=\"".JURI::root(true)."/modules/mod_joslider/assets/images/icons/".$this->element['icon']."\"  align=\"absmiddle\" vspace=\"5\" />";
		
		if ($this->element['default']) {
			return '<p style="background: #fed674;color: #000;padding:5px;clear:left;">'.$icon.' <strong>'.JText::_($this->element['default']).'</strong></p>';
		} else {
			return '<hr />';
		}
	}
	
	protected function getTitle()
	{
		return $this->getLabel();
	}
}
?>