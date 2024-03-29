<?php
/**
 * Renders a list of ascending / decending options
 *
 * @package     Joomla
 * @subpackage  Form
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/helpers/element.php';

/**
 * Renders a list of ascending / decending options
 *
 * @package     Joomla
 * @subpackage  Form
 * @since       1.6
 */

class JFormFieldAscendingdescending extends JFormFieldList
{
	/**
	 * Element name
	 * @var		string
	 */
	protected $name = 'Ascendingdescending';

	/**
	 * Method to get the field options.
	 *
	 * @return  array	The field option objects.
	 */

	protected function getOptions()
	{
		$opts[] = JHTML::_('select.option', 'ASC', JText::_('COM_FABRIK_ASCENDING'));
		$opts[] = JHTML::_('select.option', 'DESC', JText::_('COM_FABRIK_DESCENDING'));

		return $opts;
	}
}
