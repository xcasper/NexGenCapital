<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_quickicon
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$doc = JFactory::getDocument();
JHtml::_('behavior.modal');
$doc->addStylesheet( 'components/com_akquickicons/includes/akicons/css/akicons.css') ;

if( JVERSION >= 3 ): 

$doc->addStyleSheet('modules/mod_akquickicons/css/akquickicons.css');

endif; 

$tabs = count($buttons) > 1 ? true : false ;

$plugin = JPATH_ADMINISTRATOR.'/components/com_akquickicons/includes/plugins/pro/pro.php' ;
if( !JFile::exists($plugin) ) {
	$tabs = false ;
}

$keys = array_keys($buttons);

?>
<?php if (!empty($buttons)): ?>

	<!-- Icons -->	
	<?php echo $tabs ? AkquickiconsHelper::_('panel.startTabs', 'iconTab-' . $uniqid, array( 'active' => 'tab-' . $uniqid . '-'.$keys[0] ) ) : null ; ?>
	
	<?php foreach( $buttons as $key => $group ): ?>
		
		<?php echo $tabs ? AkquickiconsHelper::_('panel.addPanel' , 'iconTab-' . $uniqid, $group[0]['cat_title'] , 'tab-' . $uniqid . '-'.$key ) : null ;?>
	<div class="row-striped">
		<?php foreach( $group as $button ): ?>
		<div class="row-fluid <?php echo $button['class']?>" id="<?php echo JArrayHelper::getValue($button, 'id'); ?>">
			<div class="span6">
				<a href="<?php echo $button['link']; ?>"
					class="<?php echo $button['params']->get('target') == 'modal' ? 'modal' : ''; ?>"
					target="<?php echo $button['params']->get('target') == 'blank' ? '_blank' : '_self'; ?>"
				>
					<i class="icon <?php echo $button['icon_class']; ?>"></i>
					<span>
						<?php echo $button['text']; ?>
					</span>
				</a>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	<?php echo $tabs ? AkquickiconsHelper::_('panel.endPanel' , 'iconTab-' . $uniqid , 'tab-' . $uniqid . '-'.$key ) : null ; ?>
		
	<?php endforeach; ?>
	
	<?php echo $tabs ? AkquickiconsHelper::_('panel.endTabs' ) : null ; ?>
	
	
<?php endif;?>