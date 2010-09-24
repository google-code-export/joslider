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

// no direct access
defined('_JEXEC') or die('Restricted access');


$doc = &JFactory::getDocument();


if ($params->get("load_jquery", 1) == 1) $doc->addScript(JURI::Root(true) . '/modules/mod_joslider/assets/js/jquery-1.3.2.min.js');
$doc->addScript(JURI::Root(true) . '/modules/mod_joslider/assets/js/slidedeck.jquery.lite.js');
if ($params->get("load_mousewheel ", 1) == 1) $doc->addScript(JURI::Root(true) . '/modules/mod_joslider/assets/js/jquery.mousewheel.min.js');

$doc->addScriptDeclaration("
jQuery.noConflict();
						   
jQuery(document).ready(function(){
				if (jQuery.browser.safari && document.readyState != 'complete'){
					setTimeout( arguments.callee, 100 );
					return;
				}
				window.Deck = jQuery('.slidedeck').css({
					width: '".$params->get("width_slide", 900)."px',
					height: '".$params->get("height_slide", 300)."px'
				}).slidedeck();
			});


");

if ($params->get("load_css", 1) == 1) $doc->addStyleSheet(JURI::Root(true) . '/modules/mod_joslider/assets/css/template/voyager/skin.css');


?>


<div id="slidedeck_frame" class="skin-voyager">
<dl class="slidedeck">

 <?php foreach ($list as $item): ?>
				<dt> <?php echo $item->title; ?></dt>

				<dd>
                 <?php echo $item->introtext; ?>
                </dd>

<?php endforeach; ?>
</dl>
</div>
