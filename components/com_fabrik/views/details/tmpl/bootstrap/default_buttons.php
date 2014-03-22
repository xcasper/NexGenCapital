<?php
/**
 * Bootstrap Details Template
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.1
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

if ($this->showEmail): ?>
	<a class="btn fabrikWin" href="<?php echo $this->emailURL?>">
		<i class="icon-envelope"></i>
		<?php echo JText::_('JGLOBAL_EMAIL'); ?>
	</a>
<?php endif;

if ($this->showPDF): ?>
	<a class="btn" href="<?php echo $this->pdfURL?>">
		<i class="icon-file"></i>
		<?php echo JText::_('COM_FABRIK_PDF')?>
	</a>
<?php endif;

if ($this->showPrint): ?>
	<a class="btn" href="<?php echo $this->printURL?>">
		<i class="icon-print"></i>
		<?php echo JText::_('JGLOBAL_PRINT')?>
	</a>
<?php
endif;
