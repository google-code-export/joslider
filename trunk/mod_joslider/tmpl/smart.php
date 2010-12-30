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
defined('_JEXEC') or die('Restricted access');


$doc = &JFactory::getDocument();


if ($params->get("load_jquery", 1) == 1) $doc->addScript(JURI::Root(true) . '/modules/mod_joslider/assets/js/jquery-1.3.2.min.js');
if ($params->get("cufonRefresh", 0) == 1) $doc->addScript(JURI::Root(true) . '/modules/mod_joslider/assets/js/cufon-yui.js');
if ($params->get("cufonRefresh", 0) == 1) $doc->addScript(JURI::Root(true) . '/'.$params->get("cufon_font"));
if ($params->get("slidedeck_pro", 0) == 0) $doc->addScript(JURI::Root(true) . '/modules/mod_joslider/assets/js/slidedeck.jquery.lite.js'); else $doc->addScript(JURI::Root(true) . '/'.$params->get("slidedeck_pro_directory"));
if ($params->get("load_mousewheel ", 1) == 1) $doc->addScript(JURI::Root(true) . '/modules/mod_joslider/assets/js/jquery.mousewheel.min.js');



$idSlidedeck = $params->get("id_slidedeck", "slidedeck_frame");
if ($params->get("scroll", 1) == 1) $scroll = "true"; else $scroll = "false";
if ($params->get("keys", 1) == 1) $keys = "true"; else $keys = "false";
if ($params->get("activeCorner", 1) == 1) $activeCorner = "true"; else $activeCorner = "false";
if ($params->get("autoPlay", 0) == 1) $autoPlay = "true"; else $autoPlay = "false";
if ($params->get("cycle", 0) == 1) $cycle = "true"; else $cycle = "false";
if ($params->get("cufonRefresh", 0) == 1) $font = "Cufon.replace('dl.slidedeck dt');"; else $font = "";

$doc->addScriptDeclaration("
jQuery.noConflict();
".$font."			   
jQuery(document).ready(function(){
				if (jQuery.browser.safari && document.readyState != 'complete'){
					setTimeout( arguments.callee, 100 );
					return;
				}
				
				
				
				jQuery('#".$idSlidedeck."').css({
					width: '".$params->get("width_slide", 900)."px',
					height: '".$params->get("height_slide", 300)."px'
				});
				jQuery('#".$idSlidedeck."').slidedeck({
					speed: ".$params->get("speed", 500).",
					start: ".$params->get("start", 1).",
					scroll: ".$scroll.",
					keys: ".$keys.",
					activeCorner: ".$activeCorner.",
					hideSpines: true,
					autoPlay: ".$autoPlay.",
					autoPlayInterval: ".$params->get("autoPlayInterval", 5000).",
					cycle: ".$cycle.",
					cufonRefresh: 'dl.slidedeck dt'
				});
				
			})

			


");


?>


<div  class="slidedeck_frame skin-<?php echo $theme['class'] ?>">
<dl class="slidedeck" id="<?php echo $idSlidedeck ?>" >

 <?php foreach ($list as $item): ?>
				<dt> <?php echo $item->title; ?></dt>

				<dd class="slide">
                
                 <div class="sd-node sd-node-container">
                    <div class="sd-node sd-node-content">
                        <div class="sd-node sd-node-title">
                            <a href="<?php echo $item->link; ?>" target="<?php echo $params->get("target", '_parent'); ?>"><?php echo $item->title; ?></a>
                        </div>
                        <div class="sd-node sd-node-date"><?php echo $item->dateSmall; ?></div>
                        <?php if ($params->get("dateType", 'dateCreation') != 'dateNone') : ?>
                        <div class="sd-node sd-node-timesince"><?php echo JText::_('POSTED'); ?> <?php echo $item->date; ?></div>
                        <?php endif; ?>
                        <div class="sd-node sd-node-excerpt">
                           <?php echo $item->introtext; ?>
                        </div>
                        
                        <?php if ($params->get("displayReadMore", 1) == 1) : ?>
                        <div class="sd-node sd-node-permalink"><a href="<?php echo $item->link; ?>" target="<?php echo $params->get("target", '_parent'); ?>">
                        <?php if ($params->get("textReadMore", '') == '') : ?>
						<?php echo JText::_('READ_MORE'); ?>
                         <?php else: ?>
                        <?php echo $params->get("textReadMore"); ?>
                         <?php endif; ?>
                        
                        </a></div>
                          <?php endif; ?>
                        
                    	</div>
                      
                    
                    <?php if ($params->get('skin_type') == 'smart' && $params->get('extractImage') == 1 && $item->thumb != "") : ?>
                    <div class="sd-node sd-node-image">
                        <div class="sd-node sd-node-image-child">
                           <a href="<?php echo $item->link; ?>" target="<?php echo $params->get("target", '_parent'); ?>">
                               <img src="<?php echo $item->thumb; ?>"  alt="image" width="<?php echo $item->thumbSizes['width']; ?>" height="<?php echo $item->thumbSizes['height']; ?>" /> 
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                
                
                </dd>

<?php endforeach; ?>
</dl>

 <a class="sd-node sd-node-nav-link sd-node-previous" href="#previous" target="_blank"><?php echo JText::_('PREVIOUS'); ?></a><a class="sd-node sd-node-nav-link sd-node-next" href="#next" target="_blank"><?php echo JText::_('NEXT'); ?></a>
        
        <ul class="sd-node sd-node-nav sd-node-nav-primary sd-node-navigation-type-<?php echo $params->get("typeNavigation", 'post-titles'); ?>"></ul>
</div>

<?php echo '<script type="text/javascript" src="'.JURI::Root() . ''.$theme['path'].''.$theme['skinjs'].'"></script>'; ?>
