<?php
/**
 * View to edit a form.
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * View to edit a form.
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since       3.0
 */

class FabrikAdminViewForm extends JViewLegacy
{
	/**
	 * Form
	 *
	 * @var JForm
	 */
	protected $form;

	/**
	 * Form item
	 *
	 * @var JTable
	 */
	protected $item;

	/**
	 * View state
	 *
	 * @var object
	 */
	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template
	 *
	 * @return  void
	 */

	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$w = new FabrikWorker;
		$config = JFactory::getConfig();
		$model = JModelLegacy::getInstance('Form', 'FabrikFEModel');
		$model->render();

		if (!$model->canPublish())
		{
			if (!$app->isAdmin())
			{
				echo JText::_('COM_FABRIK_FORM_NOT_PUBLISHED');

				return false;
			}
		}

		$this->access = $model->checkAccessFromListSettings();

		if ($this->access == 0)
		{
			return JError::raiseWarning(500, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$model->getJoinGroupIds();
		$groups = $model->getGroupsHiarachy();
		$gkeys = array_keys($groups);
		$JSONarray = array();
		$JSONHtml = array();

		for ($i = 0; $i < count($gkeys); $i++)
		{
			$groupModel = $groups[$gkeys[$i]];
			$groupTable = $groupModel->getGroup();
			$group = new stdClass;
			$groupParams = $groupModel->getParams();
			$aElements = array();

			// Check if group is acutally a table join
			$repeatGroup = 1;
			$foreignKey = null;

			if ($groupModel->canRepeat())
			{
				if ($groupModel->isJoin())
				{
					$joinModel = $groupModel->getJoinModel();
					$joinTable = $joinModel->getJoin();
					$foreignKey = '';

					if (is_object($joinTable))
					{
						$foreignKey = $joinTable->table_join_key;

						// $$$ rob test!!!
						if (!$groupModel->canView())
						{
							continue;
						}

						$elementModels = $groupModel->getPublishedElements();
						reset($elementModels);
						$tmpElement = current($elementModels);
						$smallerElHTMLName = $tmpElement->getFullName(true, false);
						$repeatGroup = count($model->data[$smallerElHTMLName]);
					}
				}
			}

			$groupModel->repeatTotal = $repeatGroup;
			$aSubGroups = array();

			for ($c = 0; $c < $repeatGroup; $c++)
			{
				$aSubGroupElements = array();
				$elCount = 0;
				$elementModels = $groupModel->getPublishedElements();

				foreach ($elementModels as $elementModel)
				{
					if (!$model->isEditable())
					{
						/* $$$ rob 22/03/2011 changes element keys by appending "_id" to the end, means that
						 * db join add append data doesn't work if for example the popup form is set to allow adding,
						 * but not editing records
						 * $elementModel->inDetailedView = true;
						 */
						$elementModel->setEditable(false);
					}

					// Force reload?
					$elementModel->HTMLids = null;
					$elementHTMLId = $elementModel->getHTMLId($c);

					if (!$model->isEditable())
					{
						$JSONarray[$elementHTMLId] = $elementModel->getROValue($model->data, $c);
					}
					else
					{
						$JSONarray[$elementHTMLId] = $elementModel->getValue($model->data, $c);
					}
					// Test for paginate plugin
					if (!$model->isEditable())
					{
						$elementModel->HTMLids = null;
						$elementModel->inDetailedView = true;
					}

					$JSONHtml[$elementHTMLId] = htmlentities($elementModel->render($model->data, $c), ENT_QUOTES, 'UTF-8');
				}
			}
		}

		$data = array("id" => $model->getId(), 'model' => 'table', "errors" => $model->errors, "data" => $JSONarray, 'html' => $JSONHtml,
			'post' => $_REQUEST);
		echo json_encode($data);
	}

	/**
	 * Alias for display()
	 *
	 * @param   string  $tpl  Template
	 *
	 * @return  void
	 */
	public function form($tpl = null)
	{
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 */

	protected function addToolbar()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$input->set('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId = $user->get('id');
		$isNew = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		$canDo = FabrikAdminHelper::getActions($this->state->get('filter.category_id'));
		JToolBarHelper::title($isNew ? JText::_('COM_FABRIK_MANAGER_FORM_NEW') : JText::_('COM_FABRIK_MANAGER_FORM_EDIT'), 'form.png');

		if ($isNew)
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::apply('form.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('form.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::addNew('form.save2new', 'JTOOLBAR_SAVE_AND_NEW');
			}

			JToolBarHelper::cancel('form.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			// Can't save the record if it's checked out.
			if (!$checkedOut)
			{
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId))
				{
					JToolBarHelper::apply('form.apply', 'JTOOLBAR_APPLY');
					JToolBarHelper::save('form.save', 'JTOOLBAR_SAVE');

					// We can save this record, but check the create permission to see if we can return to make a new one.
					if ($canDo->get('core.create'))
					{
						JToolBarHelper::addNew('form.save2new', 'JTOOLBAR_SAVE_AND_NEW');
					}
				}
			}

			if ($canDo->get('core.create'))
			{
				JToolBarHelper::custom('form.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}

			JToolBarHelper::cancel('form.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_COMPONENTS_FABRIK_FORMS_EDIT');
	}
}
