<?php
/**
 * Single element raw view
 *
 * @package     Joomla
 * @subpackage  Fabik
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * Single element raw view
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       3.0
 */

class FabrikViewElement extends JViewLegacy
{
	/**
	 * Element id (not used?)
	 *
	 * @var int
	 */
	protected $id = null;

	/**
	 * Is mambot (not used?)
	 *
	 * @var bool
	 */
	public $isMambot = null;

	/**
	 * Set id
	 *
	 * @param   int  $id  Element id
	 *
	 * @deprecated ?
	 *
	 * @return  void
	 */

	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * Display the template
	 *
	 * @param   string  $tpl  Template
	 *
	 * @return void
	 */

	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$pluginManager = JModelLegacy::getInstance('Pluginmanager', 'FabrikFEModel');
		$ids = $input->get('plugin', array(), 'array');

		foreach ($ids as $id)
		{
			$plugin = $pluginManager->getElementPlugin($id);
		}
	}
}
