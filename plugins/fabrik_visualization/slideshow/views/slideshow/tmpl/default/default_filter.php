<?php
/**
 * Slideshow vizualization: default filter template
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.slideshos
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

if ($this->showFilters) :
?>
<form method="post" action="" name="filter" action="<?php echo $this->filterFormURL; ?>">
<?php
	foreach ($this->filters as $table => $filters) :
		if (!empty($filters)) :
			?>
	  <table class="filtertable fabrikTable fabrikList">
		  <thead>
		  	<tr>
		  		<th colspan="2"><?php echo $table ?></th>
		  	</tr>
		  </thead>
		  <tfoot>
		  	<tr>
			  	<th colspan="2" style="text-align:right;">
					  <?php // Needed when rendered as a J content plugin - otherwise it defaults to 1 each time ?>
					  <input type="hidden" name="clearfilters" value="0" />
					  <input type="hidden" name="resetfilters" value="0" />
					  <input type="submit" class="button" value="<?php echo JText::_('COM_FABRIK_GO') ?>" />
			  	</th>
			  </tr>
		  </tfoot>
		  <tbody>
		  <tr>
				<th style="text-align:left"><?php echo JText::_('COM_FABRIK_SEARCH'); ?>:</th>
				<th style="text-align:right"><a href="#" class="clearFilters"><?php echo JText::_('COM_FABRIK_CLEAR'); ?></a></th>
			</tr>
		  <?php
			$c = 0;
			foreach ($filters as $filter) :
			?>
		    <tr class="fabrik_row oddRow<?php echo ($c % 2); ?>">
		    	<td><?php echo $filter->label ?> </td>
		    	<td style="text-align:right;"><?php echo $filter->element ?></td>
		    </tr>
		  <?php
				$c++;
			endforeach;
			?>
		  </tbody>
	  </table>
	  <?php
		endif;
	endforeach;
	?>
</form>
<?php
endif;
