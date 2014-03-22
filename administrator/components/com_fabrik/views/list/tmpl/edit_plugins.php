<?php
/**
 * Admin List Edit:plugins Tmpl
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.0
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

?>
<?php echo JHtml::_('tabs.panel',JText::_('COM_FABRIK_GROUP_LABEL_PLUGINS_DETAILS'), 'list-plugins-panel');?>

<fieldset class="adminform">
	<div id="plugins" class="pane-sliders"></div>
	<a href="#" id="addPlugin" class="addButton"><?php echo JText::_('COM_FABRIK_ADD'); ?></a>
</fieldset>