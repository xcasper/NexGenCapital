<?php
/**
 * Slideshow vizualization: bootstrap filter template
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
<form method="post" name="filter" action="<?php echo $this->filterFormURL; ?>">
<?php
	foreach ($this->filters as $table => $filters) :
		if (!empty($filters)) :
		?>
	  <table class="filtertable table table-striped">

	   <thead>
	  	<tr>
	  		<th><?php echo $table ?></th>
	  		<th style="text-align:right">
	  			<a href="#" class="clearFilters">
	  				<i class="icon-refresh"></i> <?php echo JText::_('COM_FABRIK_CLEAR'); ?>
	  			</a>
	  		</th>
	  	</tr>
	  </thead>

	  <tfoot>
	  	<tr>
	  		<th colspan="2" style="text-align:right;">
	  			<button type="submit" class="btn btn-primary">
	  				<i class="icon-filter"></i> <?php echo JText::_('COM_FABRIK_GO') ?>
	  			</button>
	  		</th>
	  	</tr>
	  </tfoot>

	  <tbody>
	  <?php
			$c = 0;
			foreach ($filters as $filter) :
				$required = $filter->required == 1 ? ' class="notempty"' : '';
			 ?>
	    <tr class="fabrik_row oddRow<?php echo ($c % 2); ?>">
	    	<td<?php echo $required ?>><?php echo $filter->label ?> </td>
	    	<td><?php echo $filter->element ?></td>
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

