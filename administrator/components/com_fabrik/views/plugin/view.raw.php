<?php
/**
 * View to grab plugin form fields.
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * View to grab plugin form fields.
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since       3.0.6
 */

class FabrikAdminViewPlugin extends JViewLegacy
{
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  template
	 *
	 * @return  void
	 */

	public function display($tpl = null)
	{
		$model = $this->getModel();
		$app = JFactory::getApplication();
		$this->setStates();

		if ($app->input->get('task') == 'top')
		{
			echo $model->top();

			return;
		}

		echo $model->render();
	}

	/**
	 * Set the model state from request
	 *
	 * @return  void
	 */

	protected function setStates()
	{
		$model = $this->getModel();
		$app = JFactory::getApplication();
		$input = $app->input;
		$model->setState('type', $input->get('type'));
		$model->setState('plugin', $input->get('plugin'));
		$model->setState('c', $input->getInt('c'));
		$model->setState('id', $input->getInt('id', 0));
		$model->setState('plugin_published', $input->get('plugin_published'));
		$model->setState('show_icon', $input->get('show_icon'));
	}
}
