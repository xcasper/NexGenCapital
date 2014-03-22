<?php
/**
 * Fabrik Calendar Plug-in Model
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.calendar
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

require_once JPATH_SITE . '/components/com_fabrik/models/visualization.php';

/**
 * Fabrik Calendar Plug-in Model
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.calendar
 * @since       3.0
 */

class FabrikModelCalendar extends FabrikFEModelVisualization
{
	/**
	 * Array of Fabrik lists containing events
	 *
	 * @var array
	 */
	protected $eventLists = null;

	/**
	 * JS name for calendar
	 *
	 * @var string
	 */
	protected $calName = null;

	/**
	 * Event info
	 *
	 * @var array
	 */
	public $events = null;

	/**
	 * Filters from url
	 *
	 * @var array
	 */
	public $filters = array();

	/**
	 * Can add events to lists
	 *
	 * @var bool
	 */
	public $canAdd = null;

	/**
	 * Set an array of list id's whose data is used inside the visualaziation
	 *
	 * @return  void
	 */

	protected function setListIds()
	{
		if (!isset($this->listids))
		{
			$this->listids = (array) $this->getParams()->get('calendar_table');
			JArrayHelper::toInteger($this->listids);
		}
	}

	/**
	 * Get the lists that contain events
	 *
	 * @return array
	 */

	public function &getEventLists()
	{
		if (is_null($this->eventLists))
		{
			$this->eventLists = array();
			$db = FabrikWorker::getDbo(true);
			$params = $this->getParams();
			$lists = (array) $params->get('calendar_table');
			JArrayHelper::toInteger($lists);
			$dateFields = (array) $params->get('calendar_startdate_element');
			$dateFields2 = (array) $params->get('calendar_enddate_element');
			$labels = (array) $params->get('calendar_label_element');
			$stati = (array) $params->get('status_element');
			$colours = (array) $params->get('colour');

			$query = $db->getQuery(true);
			$query->select('id AS value, label AS text')->from('#__{package}_lists')->where('id IN (' . implode(',', $lists) . ')');
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			for ($i = 0; $i < count($rows); $i++)
			{
				if (!isset($colours[$i]))
				{
					$colours[$i] = '';
				}

				if (!isset($stati[$i]))
				{
					$stati[$i] = '';
				}

				$rows[$i]->startdate_element = $dateFields[$i];
				$rows[$i]->enddate_element = JArrayHelper::getValue($dateFields2, $i);
				$rows[$i]->label_element = $labels[$i];
				$rows[$i]->status = JArrayHelper::getValue($stati, $i, '');
				$rows[$i]->colour = $colours[$i];
			}

			$this->eventLists = $rows;
		}

		return $this->eventLists;
	}

	/**
	 * Get Standard Event Form Info
	 *
	 * @return mixed unknown|NULL
	 */
	public function getAddStandardEventFormInfo()
	{
		$config = JFactory::getConfig();
		$prefix = $config->get('dbprefix');
		$params = $this->getParams();
		$db = FabrikWorker::getDbo();
		$db->setQuery("SELECT form_id, id FROM #__{package}_lists WHERE db_table_name = '{$prefix}fabrik_calendar_events' AND private = '1'");
		$o = $db->loadObject();

		if (is_object($o))
		{
			// There are standard events recorded
			return $o;
		}
		else
		{
			// They aren't any standards events recorded
			return null;
		}
	}

	/**
	 * Save the calendar
	 *
	 * @return  boolean False if not saved, otherwise id of saved calendar
	 */

	public function save()
	{
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$input = $app->input;
		$filter = JFilterInput::getInstance();
		$post = $filter->clean($_POST, 'array');

		if (!$this->bind($post))
		{
			return JError::raiseWarning(500, $this->getError());
		}

		$params = $input->get('params', array(), 'array');
		$this->params = json_encode($params);

		if ($this->id == 0)
		{
			$this->created = date('Y-m-d H:i:s');
			$this->created_by = $user->get('id');
		}
		else
		{
			$this->modified = date('Y-m-d H:i:s');
			$this->modified_by = $user->get('id');
		}

		if (!$this->check())
		{
			return JError::raiseWarning(500, $this->getError());
		}

		if (!$this->store())
		{
			return JError::raiseWarning(500, $this->getError());
		}

		$this->checkin();

		return $this->id;
	}

	/**
	 * Set up calendar events
	 *
	 * @return  array
	 */

	public function setupEvents()
	{
		if (is_null($this->events))
		{
			$params = $this->getParams();
			$tables = (array) $params->get('calendar_table');
			$table_label = (array) $params->get('calendar_label_element');
			$table_startdate = (array) $params->get('calendar_startdate_element');
			$table_enddate = (array) $params->get('calendar_enddate_element');
			$customUrls = (array) $params->get('custom_url');
			$colour = (array) $params->get('colour');
			$legend = (array) $params->get('legendtext');
			$stati = (array) $params->get('status_element');

			$this->events = array();

			for ($i = 0; $i < count($tables); $i++)
			{
				$listModel = JModelLegacy::getInstance('list', 'FabrikFEModel');

				if ($tables[$i] != 'undefined')
				{
					$listModel->setId($tables[$i]);
					$table = $listModel->getTable();
					$endDate = JArrayHelper::getValue($table_enddate, $i, '');
					$startDate = JArrayHelper::getValue($table_startdate, $i, '');

					$startShowTime = true;
					$startDateEl = $listModel->getFormModel()->getElement($startDate);

					if ($startDateEl !== false)
					{
						$startShowTime = $startDateEl->getParams()->get('date_showtime', true);
					}

					$endShowTime = true;

					if ($endDate !== '')
					{
						$endDateEl = $listModel->getFormModel()->getElement($endDate);

						if ($endDateEl !== false)
						{
							$endShowTime = $endDateEl->getParams()->get('date_showtime', true);
						}
					}

					if (!isset($colour[$i]))
					{
						$colour[$i] = '';
					}

					if (!isset($legend[$i]))
					{
						$legend[$i] = '';
					}

					if (!isset($table_label[$i]))
					{
						$table_label[$i] = '';
					}

					$customUrl = JArrayHelper::getValue($customUrls, $i, '');
					$status = JArrayHelper::getValue($stati, $i, '');
					$this->events[$tables[$i]][] = array('startdate' => $startDate, 'enddate' => $endDate, 'startShowTime' => $startShowTime,
						'endShowTime' => $endShowTime, 'label' => $table_label[$i], 'colour' => $colour[$i], 'legendtext' => $legend[$i],
						'formid' => $table->form_id, 'listid' => $tables[$i], 'customUrl' => $customUrl, 'status' => $status);
				}
			}
		}

		return $this->events;
	}

	/**
	 * Get the linked form IDs
	 *
	 * @return array
	 */

	public function getLinkedFormIds()
	{
		$this->setUpEvents();
		$return = array();

		foreach ($this->events as $arr)
		{
			foreach ($arr as $a)
			{
				$return[] = $a['formid'];
			}
		}

		return array_unique($return);
	}

	/**
	 * Go over all the lists whose data is displayed in the calendar
	 * if any element is found in the request data, assign it to the session
	 * This will then be used by the table to filter its data.
	 * nice :)
	 *
	 * @return  void
	 */

	public function setRequestFilters()
	{
		$this->setupEvents();
		$filter = JFilterInput::getInstance();
		$request = $filter->clean($_REQUEST, 'array');
		$listModel = JModelLegacy::getInstance('list', 'FabrikFEModel');

		foreach ($this->events as $listid => $record)
		{
			$listModel->setId($listid);
			$table = $listModel->getTable();
			$formModel = $listModel->getFormModel();

			foreach ($request as $key => $val)
			{
				if ($formModel->hasElement($key))
				{
					$o = new stdClass;
					$o->key = $key;
					$o->val = $val;
					$this->filters[] = $o;
				}
			}
		}
	}

	/**
	 * Can the user add a record into the calendar
	 *
	 * @return  bool
	 */

	public function getCanAdd()
	{
		if (!isset($this->canAdd))
		{
			$params = $this->getParams();
			$lists = (array) $params->get('calendar_table');

			foreach ($lists as $id)
			{
				$listModel = JModelLegacy::getInstance('list', 'FabrikFEModel');
				$listModel->setId($id);

				if (!$listModel->canAdd())
				{
					$this->canAdd = false;

					return false;
				}
			}

			$this->canAdd = true;
		}

		return $this->canAdd;
	}

	/**
	 * Get an arry of list ids for which the current user has delete records rights
	 *
	 * @return  array	List ids.
	 */

	public function getDeleteAccess()
	{
		$deleteables = array();
		$params = $this->getParams();
		$lists = (array) $params->get('calendar_table');

		foreach ($lists as $id)
		{
			$listModel = JModelLegacy::getInstance('list', 'FabrikFEModel');
			$listModel->setId($id);

			if ($listModel->canDelete())
			{
				$deleteables[] = $id;
			}
		}

		return $deleteables;
	}

	/**
	 * Query all tables linked to the calendar and return them
	 *
	 * @return  string	javascript array containg json objects
	 */

	public function getEvents()
	{
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$Itemid = FabrikWorker::itemId();
		$config = JFactory::getConfig();
		$tzoffset = $config->get('offset');
		$tz = new DateTimeZone($tzoffset);
		$w = new FabrikWorker;
		$this->setupEvents();
		$calendar = $this->getRow();
		$aLegend = "$this->calName.addLegend([";
		$jsevents = array();

		foreach ($this->events as $listid => $record)
		{
			$listModel = JModelLegacy::getInstance('list', 'FabrikFEModel');
			$listModel->setId($listid);

			if (!$listModel->canView())
			{
				continue;
			}

			$table = $listModel->getTable();
			$els = $listModel->getElements();

			foreach ($record as $data)
			{
				$db = $listModel->getDb();
				$startdate = trim($data['startdate']) !== '' ? $db->quoteName($data['startdate']) : '\'\'';

				if ($data['startdate'] == '')
				{
					throw new RuntimeException('No start date selected ', 500);

					return;
				}

				$enddate = trim($data['enddate']) !== '' ? $db->quoteName($data['enddate']) : "''";
				$label = trim($data['label']) !== '' ? $db->quoteName($data['label']) : "''";
				$customUrl = $data['customUrl'];
				/**
				 * $$$ hugh @FIXME - $label has already been quoted, so quoting it again meant the array_key_exists
				 * check was never matching, as the name got double quoted.
				 * But ... the code that isn't running is broken, so for now ... If It Ain't Working, Don't Fix It :)
				 */
				$qlabel = $db->quoteName($label);

				if (array_key_exists($qlabel, $els))
				{
					// If db join selected for the label we need to get the label element and not the value
					$label = $db->quoteName($els[$qlabel]->getOrderByName());

					// $$$ hugh @TODO doesn't seem to work for join elements, so adding hack till I can talk
					// to rob about this one.
					if (method_exists($els[$qlabel], 'getJoinLabelColumn'))
					{
						$label = $els[$qlabel]->getJoinLabelColumn();
					}
					else
					{
						$label = $db->quoteName($els[$qlabel]->getOrderByName());
					}
				}

				$pk = $listModel->getTable()->db_primary_key;
				$status = empty($data['status']) ? '""' : $data['status'];
				$query = $db->getQuery(true);
				$status = trim($data['status']) !== '' ? $db->quoteName($data['status']) : "''";
				$query->select($pk . ' AS id, ' . $pk . ' AS rowid, ' . $startdate . ' AS startdate, ' . $enddate . ' AS enddate')
					->select('"" AS link, ' . $label . ' AS label, ' . $db->quote($data['colour']) . ' AS colour, 0 AS formid')
				->select($status . ' AS status')
				->from($table->db_table_name)
				->order($startdate . ' ASC');
				$query = $listModel->buildQueryJoin($query);
				$query = $listModel->buildQueryWhere(true, $query);
				$db->setQuery($query);
				$formdata = $db->loadObjectList();

				if (is_array($formdata))
				{
					foreach ($formdata as $row)
					{
						if ($row->startdate != '')
						{
							$defaultURL = 'index.php?option=com_' . $package . '&Itemid=' . $Itemid . '&view=form&formid='
								. $table->form_id . '&rowid=' . $row->id . '&tmpl=component';
							$thisCustomUrl = $w->parseMessageForPlaceHolder($customUrl, $row);
							$row->link = $thisCustomUrl !== '' ? $thisCustomUrl : $defaultURL;
							$row->custom = $customUrl != '';
							$row->_listid = $table->id;
							$row->_canDelete = (bool) $listModel->canDelete();
							$row->_canEdit = (bool) $listModel->canEdit($row);
							$row->_canView = (bool) $listModel->canViewDetails();

							// Added timezone offset
							if ($row->startdate !== $db->getNullDate() && $data['startShowTime'] == true)
							{
								$date = JFactory::getDate($row->startdate);
								$date->setTimezone($tz);
								$row->startdate = $date->format('Y-m-d H:i:s');
							}

							if ($row->enddate !== $db->getNullDate() && (string) $row->enddate !== '')
							{
								if ($data['endShowTime'] == true)
								{
									$date = JFactory::getDate($row->enddate);
									$date->setTimezone($tz);
									$row->enddate = $date->format('Y-m-d H:i:d');
								}
							}
							else
							{
								$row->enddate = $row->startdate;
							}

							$jsevents[$table->id . '_' . $row->id . '_' . $row->startdate] = clone ($row);
						}
					}
				}
			}
		}

		$params = $this->getParams();
		$addEvent = json_encode($jsevents);

		return $addEvent;
	}

	/**
	 * Get the js code to create the legend
	 *
	 * @return  string
	 */

	public function getLegend()
	{
		$db = FabrikWorker::getDbo();
		$params = $this->getParams();
		$this->setupEvents();
		$tables = (array) $params->get('calendar_table');
		$colour = (array) $params->get('colour');
		$legend = (array) $params->get('legendtext');
		$ref = $this->getJSRenderContext();

		// @TODO: json encode the returned value and move to the view
		$calendar = $this->getRow();
		$aLegend = "$ref.addLegend([";
		$jsevents = array();

		foreach ($this->events as $listid => $record)
		{
			$listModel = JModelLegacy::getInstance('list', 'FabrikFEModel');
			$listModel->setId($listid);
			$table = $listModel->getTable();

			foreach ($record as $data)
			{
				$rubbish = $table->db_table_name . '___';
				$colour = FabrikString::ltrimword($data['colour'], $rubbish);
				$legend = FabrikString::ltrimword($data['legendtext'], $rubbish);
				$label = (empty($legend)) ? $table->label : $legend;
				$aLegend .= "{'label':'" . $label . "','colour':'" . $colour . "'},";
			}
		}

		$aLegend = rtrim($aLegend, ",") . "]);";

		return $aLegend;
	}

	/**
	 * Get calendar js name
	 *
	 * @deprecated  Use getJSRenderContext() instead
	 *
	 * @return NULL
	 */

	public function getCalName()
	{
		if (is_null($this->calName))
		{
			$calendar = $this->getRow();
			$this->calName = 'oCalendar' . $calendar->id;
		}

		return $this->calName;
	}

	/**
	 * Update an event - Not working/used!
	 *
	 * @return  void
	 */

	public function updateevent()
	{
		$oPluginManager = FabrikWorker::getPluginManager();
	}

	/**
	 * Delete an event
	 *
	 * @return  void
	 */

	public function deleteEvent()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$id = $input->getInt('id');
		$listid = $input->getInt('listid');
		$listModel = JModelLegacy::getInstance('list', 'FabrikFEModel');
		$listModel->setId($listid);
		$list = $listModel->getTable();
		$tableDb = $listModel->getDb();
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->select('db_table_name')->from('#__{package}_lists')->where('id = ' . $listid);
		$db->setQuery($query);
		$tablename = $db->loadResult();
		$query = $tableDb->getQuery(true);
		$query->delete(FabrikString::safeColName($tablename))->where($list->db_primary_key . ' = ' . $id);
		$tableDb->setQuery($query);
		$tableDb->execute();
	}
}
