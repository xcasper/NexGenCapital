<?php
/**
 * Fabrik Front End Element View
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
 * Fabrik Front End Element View
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
	}
}
