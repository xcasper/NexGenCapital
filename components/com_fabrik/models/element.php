<?php
/**
 * Fabrik Element Model
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');

/**
 * Fabrik Element Model
 *
 * @package  Fabrik
 * @since    3.0
 */

class PlgFabrik_Element extends FabrikPlugin
{
	/**
	 * Element id
	 *
	 * @var int
	 */
	protected $id = null;

	/**
	 * Javascript actions to attach to element
	 *
	 * @var array
	 */
	protected $jsActions = null;

	/**
	 * Editable
	 *
	 * @var bool
	 */
	public $editable = null;

	/**
	 * Is an upload element
	 *
	 * @var bol
	 */
	protected $is_upload = false;

	/**
	 * Does the element's data get recorded in the db
	 *
	 * @var bol
	 */
	protected $recordInDatabase = true;

	/**
	 * Contain access rights
	 *
	 * @var object
	 */
	protected $access = null;

	/**
	 * Validation error
	 *
	 * @var string
	 */
	protected $validationError = null;

	/**
	 *  Stores possible element names to avoid repeat db queries
	 *
	 * @var array
	 */
	public $fullNames = array();

	/**
	 * Group model
	 *
	 * @var object
	 */
	protected $group = null;

	/**
	 * Form model
	 *
	 * @var object
	 */
	protected $form = null;

	/**
	 * List model
	 *
	 * @var object
	 */
	protected $list = null;

	/**
	 * Element object
	 *
	 * @var JTable
	 */
	public $element = null;

	/**
	 * If the element 'Include in search all' option is set to 'default' then this states if the
	 * element should be ignored from search all.
	 *
	 * @var bool  True, ignore in extended search all.
	 */
	protected $ignoreSearchAllDefault = false;

	/**
	 * Does the element have a label
	 *
	 * @var bool
	 */
	protected $hasLabel = true;

	/**
	 * Does the element contain sub elements e.g checkboxes radiobuttons
	 *
	 * @var bool
	 */
	public $hasSubElements = false;

	/**
	 * Valid image extensions
	 *
	 * @var array
	 */
	protected $imageExtensions = array('jpg', 'jpeg', 'gif', 'bmp', 'png');

	/**
	 * Is the element in a detailed view?
	 *
	 * @var bool
	 */
	public $inDetailedView = false;

	/**
	 * Default values
	 *
	 * @var array
	 */
	public $defaults = array();

	/**
	 * The element's HTML ids based on $repeatCounter
	 *
	 * @var array
	 */
	public $HTMLids = null;

	/**
	 * Is the element in a reapeat group
	 *
	 * @var bool
	 */
	public $inRepeatGroup = null;

	/**
	 * Default value
	 *
	 * @var string
	 */
	protected $default = null;

	/**
	 * Join model
	 *
	 * @var object
	 */
	protected $joinModel = null;

	/**
	 * Has the icon been set
	 *
	 * @var bool
	 */
	protected $iconsSet = false;

	/**
	 * Parent element row - if no parent returns element
	 *
	 * @var object
	 */
	protected $parent = null;

	/**
	 * Actual table name (table or joined tables db table name)
	 *
	 * @var string
	 */
	protected $actualTable = null;

	/**
	 * Ensures the query values are only escaped once
	 *
	 * @var bool
	 */
	protected $escapedQueryValue = false;

	/**
	 * Db table field type
	 *
	 * @var  string
	 */
	protected $fieldDesc = 'VARCHAR(%s)';

	/**
	 * Db table field size
	 *
	 * @var  string
	 */
	protected $fieldSize = '255';

	/**
	 * Element error msg
	 *
	 * @var string
	 */
	protected $elementError = '';

	/**
	 * Multi-db join option - can we add duplicate options (set to false in tags element)
	 * @var  bool
	 */
	protected $allowDuplicates = true;
	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An array that holds the plugin configuration
	 *
	 * @since       1.5
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		$this->validator = JModelLegacy::getInstance('ElementValidator', 'FabrikFEModel');
		$this->validator->setElementModel($this);
		$this->access = new stdClass;
	}

	/**
	 * Method to set the element id
	 *
	 * @param   int  $id  element ID number
	 *
	 * @return  void
	 */

	public function setId($id)
	{
		// Set new element ID
		$this->id = $id;
	}

	/**
	 * Get the element id
	 *
	 * @return  int	element id
	 */

	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get the element table object
	 *
	 * @param   bool  $force  default false - force load the element
	 *
	 * @return  object  element table
	 */

	public function &getElement($force = false)
	{
		if (!$this->element || $force)
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fabrik/tables');
			$row = FabTable::getInstance('Element', 'FabrikTable');
			$row->load($this->id);
			$this->element = $row;

			// 3.1 reset the params at the same time. Seems to be required for ajax autocomplete
			if ($force)
			{
				unset($this->params);
				$p = $this->getParams();
			}
		}

		return $this->element;
	}

	/**
	 * Get parent element
	 *
	 * @return  object  element table
	 */

	public function getParent()
	{
		if (!isset($this->parent))
		{
			$element = $this->getElement();

			if ((int) $element->parent_id !== 0)
			{
				$this->parent = FabTable::getInstance('element', 'FabrikTable');
				$this->parent->load($element->parent_id);
			}
			else
			{
				$this->parent = $element;
			}
		}

		return $this->parent;
	}

	/**
	 * Bind data to the _element variable - if possible we should run one query to get all the forms
	 * element data and then iterrate over that, creating an element plugin for each row
	 * and bind each record to that plugins _element. This is instead of using getElement() which
	 * reloads in the element increasing the number of queries run
	 *
	 * @param   mixed  &$row  (object or assoc array)
	 *
	 * @return  object  element table
	 */

	public function bindToElement(&$row)
	{
		if (!$this->element)
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fabrik/tables');
			$this->element = FabTable::getInstance('Element', 'FabrikTable');
		}

		if (is_object($row))
		{
			$row = JArrayHelper::fromObject($row);
		}

		$this->element->bind($row);

		return $this->element;
	}

	/**
	 * Set the context in which the element occurs
	 *
	 * @param   object  &$groupModel  group model
	 * @param   object  &$formModel   form model
	 * @param   object  &$listModel   list model
	 *
	 * @return  void
	 */

	public function setContext(&$groupModel, &$formModel, &$listModel)
	{
		// Dont assign these with &= as they already are when passed into the func
		$this->group = $groupModel;
		$this->form = $formModel;
		$this->list = $listModel;
	}

	/**
	 * Get the element's fabrik list model
	 *
	 * @return  object	list model
	 */

	public function getListModel()
	{
		if (is_null($this->list))
		{
			$groupModel = $this->getGroup();
			$this->list = $groupModel->getListModel();
		}

		return $this->list;
	}

	/**
	 * load in the group model
	 *
	 * @param   int  $groupId  group id
	 *
	 * @return  object	group model
	 */

	public function &getGroup($groupId = null)
	{
		if (is_null($groupId))
		{
			$element = $this->getElement();
			$groupId = $element->group_id;
		}

		if (is_null($this->group) || $this->group->getId() != $groupId)
		{
			$model = JModelLegacy::getInstance('Group', 'FabrikFEModel');
			$model->setId($groupId);
			$model->getGroup();
			$this->group = $model;
		}

		return $this->group;
	}

	/**
	 * Get the elements group model
	 *
	 * @param   int  $group_id  If not set uses elements default group id
	 *
	 * @return  object  group model
	 */

	public function getGroupModel($group_id = null)
	{
		return $this->getGroup($group_id);
	}

	/**
	 * Set the group model
	 *
	 * @param   object  $group  group model
	 *
	 * @since 3.0.6
	 *
	 * @return  null
	 */

	public function setGroupModel($group)
	{
		$this->_group = $group;
	}

	/**
	 * get the elements form model
	 *
	 * @deprecated use getFormModel
	 *
	 * @return  object	form model
	 */

	public function getForm()
	{
		return $this->getFormModel();
	}

	/**
	 * get the element's form model
	 *
	 * @return  object  form model
	 */

	public function getFormModel()
	{
		if (is_null($this->form))
		{
			$listModel = $this->getListModel();
			$table = $listModel->getTable();
			$this->form = JModelLegacy::getInstance('form', 'FabrikFEModel');
			$this->form->setId($table->form_id);
			$this->form->getForm();
		}

		return $this->form;
	}

	/**
	 * Set form model
	 *
	 * @param   object  $model  form model
	 *
	 * @return  void
	 */

	public function setFormModel($model)
	{
		$this->form = $model;
	}

	/**
	 * Shows the RAW list data - can be overwritten in plugin class
	 *
	 * @param   string  $data     element data
	 * @param   object  $thisRow  all the data in the tables current row
	 *
	 * @return  string	formatted value
	 */

	public function renderRawListData($data, $thisRow)
	{
		return $data;
	}

	/**
	 * replace labels shown in table view with icons (if found)
	 *
	 * @param   string  $data  data
	 * @param   string  $view  list/details
	 * @param   string  $tmpl  template
	 *
	 * @since 3.0 - icon_folder is a bool - search through template folders for icons
	 *
	 * @deprecated use replaceWithIcons()
	 * @return  string	data
	 */

	protected function _replaceWithIcons($data, $view = 'list', $tmpl = null)
	{
		return $this->replaceWithIcons($data, $view, $tmpl);
	}

	/**
	 * Replace labels shown in list view with icons (if found)
	 *
	 * @param   string  $data  Data
	 * @param   string  $view  List/details
	 * @param   string  $tmpl  Template
	 *
	 * @since 3.0 - icon_folder is a bool - search through template folders for icons
	 *
	 * @return  string	data
	 */

	protected function replaceWithIcons($data, $view = 'list', $tmpl = null)
	{
		if ($data == '')
		{
			$this->iconsSet = false;

			return $data;
		}

		$params = $this->getParams();
		$listModel = $this->getListModel();
		$iconFile = (string) $params->get('icon_file', '');

		if ((int) $params->get('icon_folder', 0) === 0 && $iconFile === '')
		{
			$this->iconsSet = false;

			return $data;
		}

		if (in_array($listModel->getOutPutFormat(), array('csv', 'rss')))
		{
			$this->iconsSet = false;

			return $data;
		}

		$cleanData = empty($iconfile) ? FabrikString::clean(strip_tags($data)) : $iconfile;
		$cleanDatas = array($this->getElement()->name . '_' . $cleanData, $cleanData);

		foreach ($cleanDatas as $cleanData)
		{
			foreach ($this->imageExtensions as $ex)
			{
				$f = JPath::clean($cleanData . '.' . $ex);
				$opts = array('forceImage' => true);
				$img = FabrikHelperHTML::image($cleanData . '.' . $ex, $view, $tmpl, array(), false, $opts);

				if ($img !== '')
				{
					$this->iconsSet = true;
					$opts = new stdClass;
					$opts->position = 'top';
					$opts = json_encode($opts);
					$data = '<span>' . $data . '</span>';

					// See if data has an <a> tag
					if (class_exists('DOMDocument'))
					{
						$html = new DOMDocument;
						$html->loadXML($data);
						$as = $html->getElementsBytagName('a');
					}

					if ($params->get('icon_hovertext', true))
					{
						$ahref = '#';
						$target = '';

						if (class_exists('DOMDocument') && $as->length)
						{
							// Data already has an <a href="foo"> lets get that for use in hover text
							$a = $as->item(0);
							$ahref = $a->getAttribute('href');
							$target = $a->getAttribute('target');
							$target = 'target="' . $target . '"';
						}

						$data = htmlspecialchars($data, ENT_QUOTES);
						$img = '<a class="fabrikTip" ' . $target . ' href="' . $ahref . '" opts=\'' . $opts . '\' title="' . $data . '">' . $img . '</a>';
					}
					elseif (!empty($iconfile))
					{
						/**
						 * $$$ hugh - kind of a hack, but ... if this is an upload element, it may already be a link, and
						 * we'll need to replace the text in the link with the image
						 * After ages dicking around with a regex to do this, decided to use DOMDocument instead!
						 */

						if (class_exists('DOMDocument') && $as->length)
						{
							$img = $html->createElement('img');
							$img->setAttribute('src', FabrikHelperHTML::image($cleanData . '.' . $ex, $view, $tmpl, array(), true));
							$as->item(0)->nodeValue = '';
							$as->item(0)->appendChild($img);

							return $html->saveHTML();
						}
					}

					return $img;
				}
			}
		}

		return $data;
	}

	/**
	 * Build the sub query which is used when merging in in repeat element
	 * records from their joined table into the one field.
	 * Overwritten in database join element to allow for building the join
	 * to the table containing the stored values required labels
	 *
	 * @param   string  $jkey   key
	 * @param   bool    $addAs  add 'AS' to select sub query
	 *
	 * @return  string  sub query
	 */

	public function buildQueryElementConcat($jkey, $addAs = true)
	{
		$jointable = $this->getJoinModel()->getJoin()->table_join;
		$dbtable = $this->actualTableName();
		$db = JFactory::getDbo();
		$table = $this->getListModel()->getTable();

		// Jaanus: joined group pk? set in groupConcactJoinKey()

		// $pkfeld = $table->db_primary_key;
		$pkfield = $this->groupConcactJoinKey();
		$fullElName = $db->quoteName($dbtable . '___' . $this->element->name);
		$sql = '(SELECT GROUP_CONCAT(' . $jkey . ' SEPARATOR \'' . GROUPSPLITTER . '\') FROM ' . $jointable . ' WHERE parent_id = '
				. $pkfield . ')';

		if ($addAs)
		{
			$sql .= ' AS ' . $fullElName;
		}

		return $sql;
	}

	/**
	 * Build the sub query which is used when merging in
	 * repeat element records from their joined table into the one field.
	 * Overwritten in database join element to allow for building
	 * the join to the talbe containing the stored values required ids
	 *
	 * @since   2.1.1
	 *
	 * @return  string	sub query
	 */

	protected function buildQueryElementConcatId()
	{
		$jointable = $this->getJoinModel()->getJoin()->table_join;
		$dbtable = $this->actualTableName();
		$db = JFactory::getDbo();
		$table = $this->getListModel()->getTable();
		$fullElName = $db->quoteName($dbtable . '___' . $this->element->name . '_raw');
		$pkField = $this->groupConcactJoinKey();

		return '(SELECT GROUP_CONCAT(id SEPARATOR \'' . GROUPSPLITTER . '\') FROM ' . $jointable . ' WHERE parent_id = ' . $pkField
		. ') AS ' . $fullElName;
	}

	/**
	 * Used in form model setJoinData.
	 *
	 * @since 2.1.1
	 *
	 * @return  array  Element names to search data in to create join data array
	 */

	public function getJoinDataNames()
	{
		$group = $this->getGroupModel()->getGroup();
		$name = $this->getFullName(true, false);
		$rawname = $name . '_raw';

		return array($name, $rawname);
	}

	/**
	 * Create the SQL select 'name AS alias' segment for list/form queries
	 *
	 * @param   array  &$aFields    array of element names
	 * @param   array  &$aAsFields  array of 'name AS alias' fields
	 * @param   array  $opts        options
	 *
	 * @return  void
	 */

	public function getAsField_html(&$aFields, &$aAsFields, $opts = array())
	{
		$dbtable = $this->actualTableName();
		$db = FabrikWorker::getDbo();
		$table = $this->getListModel()->getTable();
		$fullElName = JArrayHelper::getValue($opts, 'alias', $db->quoteName($dbtable . '___' . $this->element->name));
		$fName = $dbtable . '.' . $this->element->name;
		$k = $db->quoteName($fName);
		$secret = JFactory::getConfig()->get('secret');

		if ($this->encryptMe())
		{
			$k = 'AES_DECRYPT(' . $k . ', ' . $db->quote($secret) . ')';
		}

		if ($this->isJoin())
		{
			$jkey = $this->element->name;

			if ($this->encryptMe())
			{
				$jkey = 'AES_DECRYPT(' . $jkey . ', ' . $db->quote($secret) . ')';
			}

			$jointable = $this->getJoinModel()->getJoin()->table_join;
			$fullElName = JArrayHelper::getValue($opts, 'alias', $k);
			$str = $this->buildQueryElementConcat($jkey);
		}
		else
		{
			$str = $k . ' AS ' . $fullElName;
		}

		if ($table->db_primary_key == $fullElName)
		{
			array_unshift($aFields, $fullElName);
			array_unshift($aAsFields, $fullElName);
		}
		else
		{
			if (!in_array($str, $aFields))
			{
				$aFields[] = $str;
				$aAsFields[] = $fullElName;
			}

			$k = $db->quoteName($dbtable . '.' . $this->element->name);

			if ($this->encryptMe())
			{
				$k = 'AES_DECRYPT(' . $k . ', ' . $db->quote($secret) . ')';
			}

			if ($this->isJoin())
			{
				$pkField = $this->groupConcactJoinKey();
				$str = $this->buildQueryElementConcatId();
				$aFields[] = $str;
				$aAsFields[] = $fullElName;

				$as = $db->quoteName($dbtable . '___' . $this->element->name . '___params');
				$str = '(SELECT GROUP_CONCAT(params SEPARATOR \'' . GROUPSPLITTER . '\') FROM ' . $jointable . ' WHERE parent_id = '
						. $pkField . ') AS ' . $as;
				$aFields[] = $str;
				$aAsFields[] = $as;
			}
			else
			{
				$fullElName = $db->quoteName($dbtable . '___' . $this->element->name . '_raw');
				$str = $k . ' AS ' . $fullElName;
			}

			if (!in_array($str, $aFields))
			{
				$aFields[] = $str;
				$aAsFields[] = $fullElName;
			}
		}
	}

	/**
	 * OMG! If repeat element inside a repeat group then the group_concat subquery needs to change the key
	 * it selected on - so it could either be the table pk or the joined groups pk.... :D
	 *
	 * @since   3.1rc1
	 *
	 * @return string
	 */

	protected function groupConcactJoinKey()
	{
		$table = $this->getListModel()->getTable();

		if ($this->getGroupModel()->isJoin() && $this->isJoin())
		{
			$groupJoin = $this->getGroupModel()->getJoinModel()->getJoin();
			//$pkField = $groupJoin->table_join . '.' . $groupJoin->table_key;
			$pkField = $groupJoin->params->get('pk');
		}
		else
		{
			$pkField = $table->db_primary_key;
		}

		return $pkField;
	}

	/**
	 * Get raw column name
	 *
	 * @param   bool  $useStep  Use step in name
	 *
	 * @return string
	 */

	public function getRawColumn($useStep = true)
	{
		$n = $this->getFullName($useStep, false);
		$n .= '_raw`';

		return $n;
	}

	/**
	 * Is the element editable - wrapper for _editable property as 3.1 uses editable
	 *
	 * @since  3.0.7
	 *
	 * @return bool
	 */

	public function isEditable()
	{
		return $this->editable;
	}

	/**
	 * Set the element edit state - wrapper for _editable property as 3.1 uses editable
	 *
	 * @param   bool  $editable  Is the element edtiable
	 *
	 * @since 3.0.7
	 *
	 * @return  void
	 */

	public function setEditable($editable)
	{
		$this->editable = $editable;
	}

	/**
	 * Check user can view the read only element OR view in list view
	 *
	 * @param   string  $view  View list/form @since 3.0.7
	 *
	 * @return  bool  can view or not
	 */

	public function canView($view = 'form')
	{
		$default = 1;
		$key = $view == 'form' ? 'view' : 'listview';
		$prop = $view == 'form' ? 'view_access' : 'list_view_access';
		$params = $this->getParams();
		$user = JFactory::getUser();

		if (!is_object($this->access) || !array_key_exists($key, $this->access))
		{
			$groups = $user->getAuthorisedViewLevels();
			$this->access->$key = in_array($params->get($prop, $default), $groups);
		}

		// Override with check on lookup element's value = logged in user id.
		if ($params->get('view_access_user', '') !== '' && $view == 'form')
		{
			$formModel = $this->getFormModel();
			$data = $formModel->getData();

			if (!empty($data) &&  $user->get('id') !== 0)
			{
				$lookUp = $params->get('view_access_user', '');
				$lookUp = $formModel->getElement($lookUp, true);
				$fullName = $lookUp->getFullName(false, true, false);
				$value = $formModel->getElementData($fullName, true);
				$this->access->$key = ($user->get('id') == $value) ? true : false;
			}
		}

		return $this->access->$key;
	}

	/**
	 * Check if the user can use the active element
	 * If location is 'list' then we don't check the group canEdit() option - causes inline edit plugin not to work
	 * when followed by a update_col plugin.
	 *
	 * @param   string  $location  To trigger plugin on form/list for elements
	 * @param   string  $event     To trigger plugin on
	 *
	 * @return  bool can use or not
	 */

	public function canUse($location = null, $event = null)
	{
		$element = $this->getElement();

		// Odd! even though defined in initialize() for confirmation plugin access was not set.
		if (!isset($this->access))
		{
			$this->access = new stdClass;
		}

		if (!is_object($this->access) || !array_key_exists('use', $this->access))
		{
			/**
			 * $$$ hugh - testing new "Option 5" for group show, "Always show read only"
			 * So if element's group show is type 5, then element is de-facto read only.
			 */
			if ($location !== 'list' && !$this->getGroupModel()->canEdit())
			{
				$this->access->use = false;
			}
			else
			{
				$user = JFactory::getUser();
				$groups = $user->getAuthorisedViewLevels();
				$this->access->use = in_array($this->getElement()->access, $groups);
			}
		}

		return $this->access->use;
	}

	/**
	 * Defines if the user can use the filter related to the element
	 *
	 * @return  bool	true if you can use
	 */

	public function canUseFilter()
	{
		$params = $this->getParams();
		$element = $this->getElement();

		if (!is_object($this->access) || !array_key_exists('filter', $this->access))
		{
			$user = JFactory::getUser();
			$groups = $user->getAuthorisedViewLevels();

			// $$$ hugh - fix for where certain elements got created with 0 as the
			// the default for filter_access, which isn't a legal value, should be 1
			$filter_access = $this->getParams()->get('filter_access');
			$filter_access = $filter_access == '0' ? '1' : $filter_access;
			$this->access->filter = in_array($filter_access, $groups);
		}

		return $this->access->filter;
	}

	/**
	 * Set/get if element should record its data in the dabase
	 *
	 * @deprecated - not used
	 *
	 * @return bool
	 */

	public function setIsRecordedInDatabase()
	{
		return true;
	}

	/**
	 * Internal element validation
	 *
	 * @param   array  $data           Form data
	 * @param   int    $repeatCounter  Repeeat group counter
	 *
	 * @return bool
	 */

	public function validate($data, $repeatCounter = 0)
	{
		return true;
	}

	/**
	 * Get validation error - run through JText
	 *
	 * @return  string
	 */

	public function getValidationErr()
	{
		return JText::_($this->validationError);
	}

	/**
	 * Does the element conside the data to be empty
	 * Used in isempty validation rule
	 *
	 * @param   array  $data           Data to test against
	 * @param   int    $repeatCounter  Repeat group #
	 *
	 * @return  bool
	 */

	public function dataConsideredEmpty($data, $repeatCounter)
	{
		return ($data == '') ? true : false;
	}

	/**
	 * Get an array of element html ids and their corresponding
	 * js events which trigger a validation.
	 * Examples of where this would be overwritten include timedate element with time field enabled
	 *
	 * @param   int  $repeatCounter  Repeat group counter
	 *
	 * @return  array  html ids to watch for validation
	 */

	public function getValidationWatchElements($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$ar = array('id' => $id, 'triggerEvent' => 'blur');

		return array($ar);
	}

	/**
	 * Manupulates posted form data for insertion into database
	 *
	 * @param   mixed  $val   This elements posted form data
	 * @param   array  $data  Posted form data
	 *
	 * @return  mixed
	 */

	public function storeDatabaseFormat($val, $data)
	{
		if (is_array($val) && count($val) === 1)
		{
			$val = array_shift($val);
		}

		if (is_array($val) || is_object($val))
		{
			return json_encode($val);
		}
		else
		{
			return $val;
		}
	}

	/**
	 * When importing csv data you can run this function on all the data to
	 * format it into the format that the form would have submitted the date
	 *
	 * @param   array   &$data  To prepare
	 * @param   string  $key    List column heading
	 * @param   bool    $isRaw  Data is raw
	 *
	 * @return  array  data
	 */

	public function prepareCSVData(&$data, $key, $isRaw = false)
	{
		return $data;
	}

	/**
	 * Determines if the data in the form element is used when updating a record
	 *
	 * @param   mixed  $val  Element forrm data
	 *
	 * @return  bool  true if ignored on update, default = false
	 */

	public function ignoreOnUpdate($val)
	{
		return false;
	}

	/**
	 * Determines if the element can contain data used in sending receipts,
	 * e.g. fabrikfield returns true
	 *
	 * @deprecated - not used
	 *
	 * @return  bool
	 */

	public function isReceiptElement()
	{
		return false;
	}

	/**
	 * can be overwritten in adddon class
	 *
	 * checks the posted form data against elements INTERNAL validataion rule - e.g. file upload size / type
	 *
	 * @param   array   $aErrors      Existing errors
	 * @param   object  &$groupModel  Group model
	 * @param   object  &$formModel   Form model
	 * @param   array   $data         Posted data
	 *
	 * @deprecated - not used
	 *
	 * @return  array	updated errors
	 */

	public function validateData($aErrors, &$groupModel, &$formModel, $data)
	{
		return $aErrors;
	}

	/**
	 * Determines the label used for the browser title
	 * in the form/detail views
	 *
	 * @param   array  $data           Form data
	 * @param   int    $repeatCounter  When repeating joinded groups we need to know what part of the array to access
	 * @param   array  $opts           Options
	 *
	 * @return  string	default value
	 */

	public function getTitlePart($data, $repeatCounter = 0, $opts = array())
	{
		return $this->getValue($data, $repeatCounter, $opts);
	}

	/**
	 * This really does get just the default value (as defined in the element's settings)
	 *
	 * @param   array  $data  Form data
	 *
	 * @return mixed
	 */

	public function getDefaultValue($data = array())
	{
		if (!isset($this->default))
		{
			$w = new FabrikWorker;
			$element = $this->getElement();
			$default = $w->parseMessageForPlaceHolder($element->default, $data);

			if ($element->eval == "1" && is_string($default))
			{
				/**
				 * Inline edit with a default eval'd "return FabrikHelperElement::filterValue(290);"
				 * was causing the default to be eval'd twice (no idea y) - add in check for 'return' into eval string
				 * see http://fabrikar.com/forums/showthread.php?t=30859
				 */
				if (!stristr($default, 'return'))
				{
					$this->_default = $default;
				}
				else
				{
					FabrikHelperHTML::debug($default, 'element eval default:' . $element->label);
					$default = stripslashes($default);
					$default = @eval($default);
					FabrikWorker::logEval($default, 'Caught exception on eval of ' . $element->name . ': %s');

					// Test this does stop error
					$this->_default = $default === false ? '' : $default;
				}
			}

			if (is_array($default))
			{
				foreach ($default as &$d)
				{
					$d = JText::_($d);
				}

				$this->default = $default;
			}
			else
			{
				$this->default = JText::_($default);
			}
		}

		return $this->default;
	}

	/**
	 * Called by form model to build an array of values to encrypt
	 *
	 * @param   array  &$values  Previously encrypted values
	 * @param   array  $data     Form data
	 * @param   int    $c        Repeat group counter
	 *
	 * @return  void
	 */

	public function getValuesToEncrypt(&$values, $data, $c)
	{
		$name = $this->getFullName(true, false);
		$opts = array('raw' => true);
		$group = $this->getGroup();

		if ($group->canRepeat())
		{
			if (!array_key_exists($name, $values))
			{
				$values[$name]['data'] = array();
			}

			$values[$name]['data'][$c] = $this->getValue($data, $c, $opts);
		}
		else
		{
			$values[$name]['data'] = $this->getValue($data, $c, $opts);
		}
	}

	/**
	 * Element plugin specific method for setting unecrypted values baack into post data
	 *
	 * @param   array   &$post  Data passed by ref
	 * @param   string  $key    Key
	 * @param   string  $data   Elements unencrypted data
	 *
	 * @return  void
	 */

	public function setValuesFromEncryt(&$post, $key, $data)
	{
		$app = JFactory::getApplication();
		$group = $this->getGroup();
		FArrayHelper::setValue($post, $key, $data);
		FArrayHelper::setValue($_REQUEST, $key, $data);

		// $$$rob even though $post is passed by reference - by adding in the value
		// we arent actually modifiying the $_POST var that post was created from
		$app->input->set($key, $data);
	}

	/**
	 * Determines the value for the element in the form view
	 *
	 * @param   array  $data           Form data
	 * @param   int    $repeatCounter  When repeating joinded groups we need to know what part of the array to access
	 *
	 * @return  string	value
	 */

	public function getROValue($data, $repeatCounter = 0)
	{
		return $this->getValue($data, $repeatCounter);
	}

	/**
	 * Helper method to get the default value used in getValue()
	 * For readonly elements:
	 *    If the form is new we need to get the default value
	 *    If the form is being edited we don't want to get the default value
	 * Otherwise use the 'use_default' value in $opts, defaulting to true
	 *
	 * @param   array  $data  Form data
	 * @param   array  $opts  Options
	 *
	 * @since  3.0.7
	 *
	 * @return  mixed	value
	 */
	protected function getDefaultOnACL($data, $opts)
	{
		// Rob - 31/10/2012 - if readonly and editing an existing record we don't want to show the default label
		if (!$this->isEditable() && JArrayHelper::getValue($data, 'rowid') != 0)
		{
			$opts['use_default'] = false;
		}

		/**
		 * $$$rob - if no search form data submitted for the search element then the default
		 * selection was being applied instead
		 * otherwise get the default value so if we don't find the element's value in $data we fall back on this value
		 */
		return JArrayHelper::getValue($opts, 'use_default', true) == false ? '' : $this->getDefaultValue($data);
	}

	/**
	 * Use in list model storeRow() to determine if data should be stored.
	 * Currently only supported for db join elements whose values are default values
	 * avoids casing '' into 0 for int fields
	 *
	 * @param   array  $data  Data being inserted
	 * @param   mixed  $val   Element value to insert into table
	 *
	 * @since   3.0.7
	 *
	 * @return boolean
	 */

	public function dataIsNull($data, $val)
	{
		return false;
	}

	/**
	 * Determines the value for the element in the form view
	 *
	 * @param   array  $data           Form data
	 * @param   int    $repeatCounter  When repeating joined groups we need to know what part of the array to access
	 * @param   array  $opts           Options, 'raw' = 1/0 use raw value
	 *
	 * @return  string	value
	 */

	public function getValue($data, $repeatCounter = 0, $opts = array())
	{
		$app = JFactory::getApplication();
		$input = $app->input;

		if (!isset($this->defaults))
		{
			$this->defaults = array();
		}

		$key = $repeatCounter . '.' . serialize($opts);

		if (!array_key_exists($key, $this->defaults))
		{
			$groupRepeat = $this->getGroupModel()->canRepeat();
			$default = $this->getDefaultOnACL($data, $opts);
			$name = $this->getFullName(true, false);

			if (JArrayHelper::getValue($opts, 'raw', 0) == 1)
			{
				$name .= '_raw';
			}

			$values = JArrayHelper::getValue($data, $name, $default);

			// Querystring override (seems on http://fabrikar.com/subscribe/form/22 querystring var was not being set into $data)
			if (JArrayHelper::getValue($opts, 'use_querystring', true))
			{
				if ((is_array($values) && empty($values)) || $values === '')
				{
					// Trying to avoid errors if value is an array
					$values = $input->get($name, null, 'array');

					if (is_null($values) || (count($values) === 1 && $values[0] == ''))
					{
						$values = $input->get($name, '', 'string');
					}
				}
			}

			if ($groupRepeat)
			{
				// Wierd bug where stdClass with key 0, when cast to (array) you couldnt access values[0]
				if (is_object($values))
				{
					$values = JArrayHelper::fromObject($values);
				}

				if (!is_array($values))
				{
					$values = (array) $values;
				}

				$values = JArrayHelper::getValue($values, $repeatCounter, '');
			}

			if (JArrayHelper::getValue($opts, 'runplugins', false))
			{
				$formModel = $this->getFormModel();
				FabrikWorker::getPluginManager()->runPlugins('onGetElementDefault', $formModel, 'form', $this);
			}

			$this->defaults[$key] = $values;
		}

		return $this->defaults[$key];
	}

	/**
	 * Is the element hidden or not - if not set then return false
	 *
	 * @return  bool
	 */

	protected function isHidden()
	{
		$element = $this->getElement();

		return ($element->hidden == true) ? true : false;
	}

	/**
	 * Used in things like date when its id is suffixed with _cal
	 * called from getLabel();
	 *
	 * @param   string  &$id  Initial id
	 *
	 * @return  void
	 */

	protected function modHTMLId(&$id)
	{
	}

	/**
	 * Should the element be tipped?
	 *
	 * @param   string  $mode  Form/list render context
	 *
	 * @since	3.0.6
	 *
	 * @return  bool
	 */

	private function isTipped($mode = 'form')
	{
		$formModel = $this->getFormModel();

		if ($formModel->getParams()->get('tiplocation', 'tip') !== 'tip' && $mode === 'form')
		{
			return false;
		}

		$params = $this->getParams();

		if ($params->get('rollover', '') === '')
		{
			return false;
		}

		if ($mode == 'form' && (!$formModel->isEditable() && $params->get('labelindetails', true) == false))
		{
			return false;
		}

		if ($mode === 'list' && $params->get('labelinlist', false) == false)
		{
			return false;
		}

		return true;
	}

	/**
	 * Get list heading label
	 *
	 * @return  string
	 */
	public function getListHeading()
	{
		$params = $this->getParams();
		$element = $this->getElement();
		$label = $params->get('alt_list_heading') == '' ? $element->label : $params->get('alt_list_heading');

		return JText::_($label);
	}

	/**
	 * Get the element's HTML label
	 *
	 * @param   int     $repeatCounter  Group repeat counter
	 * @param   string  $tmpl           Form template
	 *
	 * @return  string  label
	 */

	public function getLabel($repeatCounter, $tmpl = '')
	{
		$config = JComponentHelper::getParams('com_fabrik');
		$app = JFactory::getApplication();
		$bLabel = $this->get('hasLabel');
		$element = $this->getElement();
		$elementHTMLId = $this->getHTMLId($repeatCounter);
		$this->modHTMLId($elementHTMLId);
		$view = $app->input->get('view', 'form');

		if ($view == 'form' && !($this->canUse() || $this->canView()))
		{
			return '';
		}

		if ($view == 'details' && !$this->canView())
		{
			return '';
		}

		$params = $this->getParams();
		$str = '';
		$j3 = FabrikWorker::j3();

		if ($this->canView() || $this->canUse())
		{
			$rollOver = $this->isTipped();
			$labelClass = 'fabrikLabel control-label';

			if (empty($element->label))
			{
				$labelClass .= ' fabrikEmptyLabel';
			}

			if ($rollOver)
			{
				$labelClass .= ' fabrikHover';
			}

			if ($bLabel && !$this->isHidden())
			{
				$model = $this->getFormModel();
				$tip = $this->tipHtml($model->data);

				if ($tip !== '')
				{
					$labelClass .= ' fabrikTip';
				}

				$str .= '<label for="' . $elementHTMLId . '" class="' . $labelClass . '" ' . $tip . '>';
			}
			elseif (!$bLabel && !$this->isHidden())
			{
				$str .= '<span class="' . $labelClass . ' faux-label">';
			}

			$labelText = JText::_($element->label);
			$labelText = $labelText == '' ? '&nbsp;' : $labelText;
			$l = $j3 ? '' : $labelText;
			$iconOpts = array('icon-class' => 'small');

			if ($rollOver)
			{
				$l .= FabrikHelperHTML::image('question-sign.png', 'form', $tmpl, $iconOpts) . ' ';
			}

			if ($this->isEditable())
			{
				$validations = array_unique($this->validator->findAll());

				if (count($validations) > 0)
				{
					$emptyIcon = $this->validator->getIcon();
					$l .= FabrikHelperHTML::image($emptyIcon, 'form', $tmpl, $iconOpts) . ' ';
				}
			}

			$l .= $j3 ? $labelText : '';
			$model = $this->getFormModel();
			$str .= $l;

			if ($bLabel && !$this->isHidden())
			{
				$str .= '</label>';
			}
			elseif (!$bLabel && !$this->isHidden())
			{
				$str .= '</span>';
			}
		}

		return $str;
	}

	/**
	 * Set fabrikErrorMessage div with potential error messages
	 *
	 * @param   int     $repeatCounter  repeat counter
	 * @param   string  $tmpl           template
	 *
	 * @return  string
	 */

	protected function addErrorHTML($repeatCounter, $tmpl = '')
	{
		$err = $this->getErrorMsg($repeatCounter);
		$err = htmlspecialchars($err, ENT_QUOTES);
		$str = '<span class="fabrikErrorMessage">';

		if ($err !== '')
		{
			$err = '<span>' . $err . '</span>';
			$usersConfig = JComponentHelper::getParams('com_fabrik');
			$icon = FabrikWorker::j3() ? $usersConfig->get('error_icon', 'exclamation-sign') . '.png' : 'alert.png';
			$str .= '<a href="#" class="fabrikTip" title="' . $err . '" opts="{notice:true}">' . FabrikHelperHTML::image($icon, 'form', $tmpl)
			. '</a>';
		}

		$str .= '</span>';

		return $str;
	}

	/**
	 * Add tips on element labels
	 * does ACL check on element's label in details setting
	 *
	 * @param   string  $txt   Label
	 * @param   array   $data  Row data
	 * @param   string  $mode  Form/list render context
	 *
	 * @return  string  Label with tip
	 */

	protected function rollover($txt, $data = array(), $mode = 'form')
	{
		if (is_object($data))
		{
			$data = JArrayHelper::fromObject($data);
		}

		$rollOver = $this->tipHtml($data, $mode);

		return $rollOver !== '' ? '<span class="fabrikTip" ' . $rollOver . '">' . $txt . '</span>' : $txt;
	}

	/**
	 * Get the hover tip options
	 *
	 * @return stdClass
	 */

	protected function tipOpts()
	{
		$params = $this->getParams();
		$tmpl = $this->getFormModel()->getTmpl();
		$opts = new stdClass;
		$pos = $params->get('tiplocation', 'top');
		$opts->formTip = true;
		$opts->position = $pos;
		$opts->trigger = 'hover';
		$opts->notice = true;

		if ($this->editable)
		{
			$validations = array_unique($this->validator->findAll());

			if (count($validations) > 0)
			{
				$opts->heading = JText::_('COM_FABRIK_VALIDATION');
			}
		}

		return $opts;
	}

	/**
	 * Get Hover tip text and validation text
	 *
	 * @param   string  $mode  View mode form/list
	 * @param   array   $data  Model data
	 *
	 * @return string
	 */

	protected function tipTextAndValidations($mode, $data = array())
	{
		$lines = array();
		$validations = array();
		$tmpl = $this->getFormModel()->getTmpl();

		if ($this->isEditable() && $mode === 'form')
		{
			$validations = array_unique($this->validator->findAll());
		}

		if (count($validations) == 0 && !$this->isTipped($mode))
		{
			return '';
		}

		$lines[] = '<ul class="validation-notices" style="list-style:none">';

		if ($this->isTipped($mode))
		{
			$lines[] = '<li>' . FabrikHelperHTML::image('question-sign.png', 'form', $tmpl) . ' ' . $this->getTipText($data) . '</li>';
		}

		foreach ($validations as $validation)
		{
			$lines[] = '<li>' . $validation->getHoverText($tmpl) . '</li>';
		}

		if (count($lines) > 0)
		{
			$lines[] = '</ul>';
		}

		$lines = array_unique($lines);
		$rollOver = implode('', $lines);

		// $$$ rob - looks like htmlspecialchars is needed otherwise invalid markup created and pdf output issues.
		$rollOver = htmlspecialchars($rollOver, ENT_QUOTES);

		return $rollOver;
	}

	/**
	 * Get the element tip HTML
	 *
	 * @param   array  $data  to use in parse holders - defaults to form's data
	 *
	 * @return  string  tip HTML
	 */

	protected function getTipText($data = null)
	{
		if (is_null($data))
		{
			$data = $this->getFormModel()->data;
		}

		$model = $this->getFormModel();
		$params = $this->getParams();

		if (!$model->isEditable() && !$params->get('labelindetails'))
		{
			return '';
		}

		$w = new FabrikWorker;
		$tip = $w->parseMessageForPlaceHolder($params->get('rollover'), $data);

		if ($params->get('tipseval'))
		{
			if (FabrikHelperHTML::isDebug())
			{
				$res = eval($tip);
			}
			else
			{
				$res = @eval($tip);
			}

			FabrikWorker::logEval($res, 'Caught exception (%s) on eval of ' . $this->getElement()->name . ' tip: ' . $tip);
			$tip = $res;
		}

		$tip = JText::_($tip);

		return $tip;
	}

	/**
	 * Used for the name of the filter fields
	 * For element this is an alias of getFullName()
	 * Overridden currently only in databasejoin class
	 *
	 * @return  string	element filter name
	 */

	public function getFilterFullName()
	{
		return FabrikString::safeColName($this->getFullName(true, false));
	}

	/**
	 * Get the field name to use in the list's slug url
	 *
	 * @param   bool  $raw  raw
	 *
	 * @since   3.0.6
	 *
	 * @return  string  element slug name
	 */

	public function getSlugName($raw = false)
	{
		return $this->getFilterFullName();
	}

	/**
	 * Set and override element full name (used in pw element)
	 *
	 * @param   string  $name            Element name
	 * @param   bool    $useStep         Cconcat name with form's step element (true) or with '.' (false) default true
	 * @param   bool    $incRepeatGroup  Include '[]' at the end of the name (used for repeat group elements) default true
	 *
	 * @return  void
	 */

	public function setFullName($name = '', $useStep = true, $incRepeatGroup = true)
	{
		$groupModel = $this->getGroup();
		$formModel = $this->getFormModel();
		$element = $this->getElement();
		$key = $element->id . '.' . $groupModel->get('id') . '_' . $formModel->getId() . '_' . $useStep . '_'
				. $incRepeatGroup;
		$this->fullNames[$key] = $name;
	}

	/**
	 * If already run then stored value returned
	 *
	 * @param   bool  $useStep         Concat name with form's step element (true) or with '.' (false) default true
	 * @param   bool  $incRepeatGroup  Include '[]' at the end of the name (used for repeat group elements) default true
	 *
	 * @return  string  element full name
	 */

	public function getFullName($useStep = true, $incRepeatGroup = true)
	{
		$db = FabrikWorker::getDbo();
		$groupModel = $this->getGroup();
		$formModel = $this->getFormModel();
		$listModel = $this->getListModel();
		$element = $this->getElement();

		$key = $element->id . '.' . $groupModel->get('id') . '_' . $formModel->getId() . '_' . $useStep . '_'
				. $incRepeatGroup;

		if (isset($this->fullNames[$key]))
		{
			return $this->fullNames[$key];
		}

		$table = $listModel->getTable();
		$db_table_name = $table->db_table_name;
		$thisStep = ($useStep) ? $formModel->joinTableElementStep : '.';
		$group = $groupModel->getGroup();

		if ($groupModel->isJoin())
		{
			$joinModel = $groupModel->getJoinModel();
			$join = $joinModel->getJoin();
			$fullName = $join->table_join . $thisStep . $element->name;
		}
		else
		{
			$fullName = $db_table_name . $thisStep . $element->name;
		}

		if ($groupModel->canRepeat() == 1 && $incRepeatGroup)
		{
			$fullName .= '[]';
		}

		$this->fullNames[$key] = $fullName;

		return $fullName;
	}

	/**
	 * Get order by full name
	 *
	 * @param   bool  $useStep  Concat name with form's step element (true) or with '.' (false) default true
	 *
	 * @return  string  Order by full name
	 */

	public function getOrderbyFullName($useStep = true)
	{
		return $this->getFullName($useStep);
	}

	/**
	 * When copying elements from an existing table
	 * once a copy of all elements has been made run them through this method
	 * to ensure that things like watched element id's are updated
	 *
	 * @param   array  $newElements  copied element ids (keyed on original element id)
	 *
	 * @return  void
	 */

	public function finalCopyCheck($newElements)
	{
		// Overwritten in element class
	}

	/**
	 * Copy an element table row
	 *
	 * @param   int     $id       element id to copy
	 * @param   string  $copytxt  feedback msg
	 * @param   int     $groupid  group model id
	 * @param   string  $name     new element name
	 *
	 * @return  mixed	error or new row
	 */

	public function copyRow($id, $copytxt = 'Copy of %s', $groupid = null, $name = null)
	{
		$app = JFactory::getApplication();
		$rule = FabTable::getInstance('Element', 'FabrikTable');

		if ($rule->load((int) $id))
		{
			$rule->id = null;
			$rule->label = sprintf($copytxt, $rule->label);

			if (!is_null($groupid))
			{
				$rule->group_id = $groupid;
			}

			if (!is_null($name))
			{
				$rule->name = $name;
			}

			$groupModel = JModelLegacy::getInstance('Group', 'FabrikFEModel');
			$groupModel->setId($groupid);
			$groupListModel = $groupModel->getListModel();

			// $$$ rob - if its a joined group then it can have the same element names
			if ((int) $groupModel->getGroup()->is_join === 0)
			{
				if ($groupListModel->fieldExists($rule->name))
				{
					return JError::raiseWarning(500, JText::_('COM_FABRIK_ELEMENT_NAME_IN_USE'));
				}
			}

			$date = JFactory::getDate();
			$tz = new DateTimeZone($app->getCfg('offset'));
			$date->setTimezone($tz);
			$rule->created = $date->toSql();
			$params = $rule->params == '' ? new stdClass : json_decode($rule->params);
			$params->parent_linked = 1;
			$rule->params = json_encode($params);
			$rule->parent_id = $id;

			if (!$rule->store())
			{
				return JError::raiseWarning($rule->getError());
			}
		}
		else
		{
			return JError::raiseWarning(500, $rule->getError());
		}

		/**
		 * I thought we did this in an overridden element model method, like onCopy?
		 * if its a database join then add in a new join record
		 */
		if (is_a($this, 'PlgFabrik_ElementDatabasejoin'))
		{
			$join = FabTable::getInstance('Join', 'FabrikTable');
			$join->load(array('element_id' => $id));
			$join->id = null;
			$join->element_id = $rule->id;
			$join->group_id = $rule->group_id;
			$join->store();
		}

		// Copy js events
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->select('id')->from('#__{package}_jsactions')->where('element_id = ' . (int) $id);
		$db->setQuery($query);
		$actions = $db->loadColumn();

		foreach ($actions as $id)
		{
			$jscode = FabTable::getInstance('Jsaction', 'FabrikTable');
			$jscode->load($id);
			$jscode->id = 0;
			$jscode->element_id = $rule->id;
			$jscode->store();
		}

		return $rule;
	}

	/**
	 * This was in the views display and _getElement code but seeing as its used
	 * by multiple views its safer to have it here
	 *
	 * @param   int     $c        Repeat group counter
	 * @param   int     $elCount  Order in which the element is shown in the form
	 * @param   string  $tmpl     Template
	 *
	 * @return  mixed	- false if you shouldnt continue to render the element
	 */

	public function preRender($c, $elCount, $tmpl)
	{
		$model = $this->getFormModel();
		$groupModel = $this->getGroup();

		if (!$this->canUse() && !$this->canView())
		{
			return false;
		}

		if (!$this->canUse())
		{
			$this->setEditable(false);
		}
		else
		{
			$editable = $model->isEditable() ? true : false;
			$this->setEditable($editable);
		}

		$params = $this->getParams();

		// Force reload?
		$this->HTMLids = null;
		$elementTable = $this->getElement();
		$element = new stdClass;
		$element->startRow = 0;
		$element->endRow = 0;
		$elHTMLName = $this->getFullName();

		// If the element is in a join AND is the join's foreign key then we don't show the element
		if ($elementTable->name == $this->_foreignKey)
		{
			$element->label = '';
			$element->error = '';
			$this->element->hidden = true;
		}
		else
		{
			$element->error = $this->getErrorMsg($c);
		}

		$element->plugin = $elementTable->plugin;
		$element->hidden = $this->isHidden();
		$element->id = $this->getHTMLId($c);
		$element->className = 'fb_el_' . $element->id;
		$element->containerClass = $this->containerClass($element);
		$element->element = $this->preRenderElement($model->data, $c);

		// Ensure that view data property contains the same html as the group's element

		$model->tmplData[$elHTMLName] = $element->element;
		$element->label_raw = $this->element->label;

		// GetLabel needs to know if the element is editable
		if ($elementTable->name != $this->_foreignKey)
		{
			$l = $this->getLabel($c, $tmpl);
			$w = new FabrikWorker;
			$element->label = $w->parseMessageForPlaceHolder($l, $model->data);
		}

		$element->errorTag = $this->addErrorHTML($c, $tmpl);
		$element->element_ro = $this->getROElement($model->data, $c);
		$element->value = $this->getValue($model->data, $c);

		if (array_key_exists($elHTMLName . '_raw', $model->data))
		{
			$element->element_raw = $model->data[$elHTMLName . '_raw'];
		}
		else
		{
			$element->element_raw = array_key_exists($elHTMLName, $model->data) ? $model->data[$elHTMLName] : $element->value;
		}

		if ($this->dataConsideredEmpty($element->element_ro, $c))
		{
			$element->containerClass .= ' fabrikDataEmpty';
		}

		// Tips (if not rendered as hovers)
		$tip = $this->getTipText();

		if ($tip !== '')
		{
			$tip = FabrikHelperHTML::image('question-sign.png', 'form', $tmpl) . ' ' . $tip;
		}

		switch ($model->getParams()->get('tiplocation'))
		{
			default:
			case 'tip':
				$element->tipAbove = '';
				$element->tipBelow = '';
				$element->tipSide = '';
				break;
			case 'above':
				$element->tipAbove = $tip;
				$element->tipBelow = '';
				$element->tipSide = '';
				break;
			case 'below':
				$element->tipAbove = '';
				$element->tipBelow = $tip;
				$element->tipSide = '';
				break;
			case 'side':
				$element->tipAbove = '';
				$element->tipBelow = '';
				$element->tipSide = $tip;
				break;
		}

		return $element;
	}

	/**
	 * Build the tip HTML
	 *
	 * @param   array   $data  Data
	 * @param   string  $mode  Mode Form/List
	 *
	 * @return string
	 */

	protected function tipHtml($data = array(), $mode = 'form')
	{
		$title = $this->tipTextAndValidations($mode, $data);
		$opts = $this->tipOpts();
		$opts = json_encode($opts);

		return $title !== '' ? 'title="' . $title . '" opts=\'' . $opts . '\'' : '';
	}

	/**
	 * Get the class name for the element wrapping dom object
	 *
	 * @param   object  $element  element row
	 *
	 * @since   3.0
	 *
	 * @return  string	class names
	 */

	protected function containerClass($element)
	{
		$item = $this->getElement();
		$c = array('fabrikElementContainer', 'plg-' . $item->plugin, $element->className, $item->name, $item->name . '_' . $item->group_id);

		if ($element->hidden)
		{
			$c[] = 'fabrikHide';
		}
		else
		{
			/**
			 * $$$ hugh - adding a class name for repeat groups, as per:
			 * http://fabrikar.com/forums/showthread.php?p=165128#post165128
			 * But as per my repsonse on that thread, if this turns out to be a performance
			 * hit, may take it out.  That said, I think having this class will make things
			 * easier for custom styling when the element ID isn't constant.
			 */
			$groupModel = $this->getGroupModel();

			if ($groupModel->canRepeat())
			{
				$c[] = 'fabrikRepeatGroup___' . $this->getFullName(true, false);
			}
		}

		if ($element->error != '')
		{
			$c[] = 'fabrikError';
		}

		return implode(' ', $c);
	}

	/**
	 * Merge the rendered element into the views element storage arrays
	 *
	 * @param   object  $element             to merge
	 * @param   array   &$aElements          element array
	 * @param   array   &$namedData          Form data
	 * @param   array   &$aSubGroupElements  sub group element array
	 *
	 * @return  void
	 */

	public function stockResults($element, &$aElements, &$namedData, &$aSubGroupElements)
	{
		$elHTMLName = $this->getFullName();
		$aElements[$this->getElement()->name] = $element;

		/**
		 * $$$ rob 12/10/2012 - $namedData is the formModels data - commenting out as the form data needs to be consistent
		 * as we loop over elements - this was setting from a string to an object ?!!!???!!
		 * $namedData[$elHTMLName] = $element;
		 */
		if ($elHTMLName)
		{
			// $$$ rob was key'd on int but thats not very useful for templating
			$aSubGroupElements[$this->getElement()->name] = $element;
		}
	}

	/**
	 * Pre-render just the element (no labels etc)
	 * Was _getElement but this was ambiguous with getElement() and method is public
	 *
	 * @param   array  $data           data
	 * @param   int    $repeatCounter  repeat group counter
	 *
	 * @return  string
	 */

	public function preRenderElement($data, $repeatCounter = 0)
	{
		$groupModel = $this->getGroupModel();

		if (!$this->canView() && !$this->canUse())
		{
			return '';
		}
		// Used for working out if the element should behave as if it was in a new form (joined grouped) even when editing a record
		$this->inRepeatGroup = $groupModel->canRepeat();
		$this->_inJoin = $groupModel->isJoin();
		$opts = array('runplugins' => 1);
		$this->getValue($data, $repeatCounter, $opts);

		if ($this->isEditable())
		{
			return $this->render($data, $repeatCounter);
		}
		else
		{
			$htmlid = $this->getHTMLId($repeatCounter);

			// $$$ rob even when not in ajax mode the element update() method may be called in which case we need the span
			// $$$ rob changed from span wrapper to div wrapper as element's content may contain divs which give html error

			// Placeholder to be updated by ajax code
			$v = $this->getROElement($data, $repeatCounter);
			$v = $v == '' ? '&nbsp;' : $v;

			return '<div id="' . $htmlid . '">' . $v . '</div>';
		}
	}

	/**
	 * Get read-only element
	 * Was _getROElement() but is a public method
	 *
	 * @param   array  $data           data
	 * @param   int    $repeatCounter  repeat group counter
	 *
	 * @return  string
	 */

	public function getROElement($data, $repeatCounter = 0)
	{
		$groupModel = $this->getGroup();

		if (!$this->canView() && !$this->canUse())
		{
			return '';
		}

		$editable = $this->isEditable();
		$this->setEditable(false);
		$v = $this->render($data, $repeatCounter);
		$this->addCustomLink($v, $data, $repeatCounter);
		$this->setEditable($editable);

		return $v;
	}

	/**
	 * Add custom link to element - must be uneditable for link to be added
	 *
	 * @param   string  &$v             value
	 * @param   array   $data           row data
	 * @param   int     $repeatCounter  repeat counter
	 *
	 * @return  string
	 */

	protected function addCustomLink(&$v, $data, $repeatCounter = 0)
	{
		if ($this->isEditable())
		{
			return $v;
		}

		$params = $this->getParams();
		$customLink = $params->get('custom_link');

		if ($customLink !== '' && $this->getElement()->link_to_detail == '1' && $params->get('custom_link_indetails', true))
		{
			$w = new FabrikWorker;

			foreach ($data as $k => $val)
			{
				$repData[$k] = $val;
			}

			$customLink = $w->parseMessageForPlaceHolder($customLink, $data);
			$customLink = $this->getListModel()->parseMessageForRowHolder($customLink, $data);

			if (trim($customLink) !== '')
			{
				$v = '<a href="' . $customLink . '">' . $v . '</a>';
			}
		}

		return $v;
	}

	/**
	 * Get any html error messages
	 *
	 * @param   int  $repeatCount  group repeat count
	 *
	 * @return  string	error messages
	 */

	protected function getErrorMsg($repeatCount = 0)
	{
		$arErrors = $this->getFormModel()->errors;
		$parsed_name = $this->getFullName();
		$err_msg = '';
		$parsed_name = FabrikString::rtrimword($parsed_name, '[]');

		if (isset($arErrors[$parsed_name]))
		{
			if (array_key_exists($repeatCount, $arErrors[$parsed_name]))
			{
				if (is_array($arErrors[$parsed_name][$repeatCount]))
				{
					$err_msg = implode('<br />', $arErrors[$parsed_name][$repeatCount]);
				}
				else
				{
					$err_msg .= $arErrors[$parsed_name][$repeatCount];
				}
			}
		}

		return $err_msg;
	}

	/**
	 * Draws the html form element
	 *
	 * @param   array  $data           To preopulate element with
	 * @param   int    $repeatCounter  Repeat group counter
	 *
	 * @return  string	elements html
	 */

	public function render($data, $repeatCounter = 0)
	{
		return 'need to overwrite in element plugin class';
	}

	/**
	 * Get hidden field
	 *
	 * @param   string  $name   Element name
	 * @param   string  $value  Elemenet value
	 * @param   string  $id     Element id
	 * @param   string  $class  Class name
	 *
	 * @return string
	 */

	protected function getHiddenField($name, $value, $id = '', $class = 'fabrikinput inputbox hidden')
	{
		$value = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
		$opts = array('class' => $class, 'type' => 'hidden', 'name' => $name, 'value' => $value, 'id' => $id);

		return $this->buildInput('input', $opts);
	}

	/**
	 * Helper method to build an input field
	 *
	 * @param   string  $node      Input type default 'input'
	 * @param   array   $bits      Input property => value
	 * @param   bool    $shortTag  Is $node a <node/> or <node></node> tag
	 *
	 * @return  string  input
	 */

	protected function buildInput($node = 'input', $bits = array(), $shortTag = true)
	{
		$str = '<' . $node . ' ';

		$value = '';

		foreach ($bits as $key => $val)
		{
			if ($node === 'textarea' && $key === 'value')
			{
				$value = $val;
				continue;
			}

			$str .= $key . '="' . $val . '" ';
		}

		$str .= $shortTag ? '' : '>';

		if (!$shortTag && $value !== '')
		{
			$str .= $value;
		}

		$str .= $shortTag ? '/>' : '</' . $node . '>';

		return $str;
	}

	/**
	 * Helper function to build the property array used in buildInput()
	 *
	 * @param   int    $repeatCounter  Repeat group counter
	 * @param   mixed  $type           Null/string $type property (if null then password/text applied as default)
	 *
	 * @return  array  input properties key/value
	 */

	protected function inputProperties($repeatCounter, $type = null)
	{
		$bits = array();
		$element = $this->getElement();
		$params = $this->getParams();
		$size = (int) $element->width < 0 ? 1 : (int) $element->width;

		if (!isset($type))
		{
			// Changes by JF Questiaux - info@betterliving.be
			switch ($params->get('password')) // Kept the name 'password' for backward compatibility
			{
				case '1' :
					$type = 'password';
					break;
				case '2' :
					$type = 'tel';
					break;
				case '3' :
					$type = 'email';
					break;
				case '4' :
					$type = 'search';
					break;
				case '5' :
					$type = 'url';
					break;
				default :
					$type = 'text';
			}
			// End of changes
		}

		$maxlength = $params->get('maxlength');

		if ($maxlength == "0" or $maxlength == '')
		{
			$maxlength = $size;
		}

		$class = array();
		$bootstrapClass = $params->get('bootstrap_class', '');

		if ($bootstrapClass !== '')
		{
			$class[] = $bootstrapClass;

			// Fall back for old 2.5 sites
			switch ($bootstrapClass)
			{
				case 'input-mini':
					$size = 3;
					break;
				case 'input-small':
					$size = 6;
					break;
				case 'input-medium':
					$size = 10;
					break;
				default:
				case 'input-large':
					$size = 20;
					break;
				case 'input-xlarge':
					$size = 35;
					break;
				case 'input-block-level':
				case 'input-xxlarge':
					$size = 60;
					break;
			}
		}

		if ($this->elementError != '')
		{
			$class[] = ' elementErrorHighlight';
		}

		if ($element->hidden == '1')
		{
			$class[] = ' hidden';
			$type = 'hidden';
		}

		$bits['type'] = $type;
		$bits['id'] = $this->getHTMLId($repeatCounter);
		$bits['name'] = $this->getHTMLName($repeatCounter);

		if (!$element->hidden)
		{
			$bits['size'] = $size;
			$bits['maxlength'] = $maxlength;
		}

		$class[] = 'fabrikinput inputbox';
		$bits['class'] = implode(' ', $class);

		if ($params->get('placeholder', '') !== '')
		{
			$bits['placeholder'] = JText::_($params->get('placeholder'));
		}

		if ($params->get('autocomplete', 1) == 0)
		{
			$bits['autocomplete'] = 'off';
		}
		// Cant be used with hidden element types
		if ($element->hidden != '1')
		{
			if ($params->get('readonly'))
			{
				$bits['readonly'] = "readonly";
				$bits['class'] .= " readonly";
			}

			if ($params->get('disable'))
			{
				$bits['class'] .= " disabled";
				$bits['disabled'] = 'disabled';
			}
		}

		return $bits;
	}

	/**
	 * get the id used in the html element
	 *
	 * @param   int  $repeatCounter  group counter
	 *
	 * @return  string
	 */

	public function getHTMLId($repeatCounter = 0)
	{
		if (!is_array($this->HTMLids))
		{
			$this->HTMLids = array();
		}

		if (!array_key_exists((int) $repeatCounter, $this->HTMLids))
		{
			$groupModel = $this->getGroup();
			$listModel = $this->getListModel();
			$table = $listModel->getTable();
			$groupTable = $groupModel->getGroup();
			$element = $this->getElement();

			if ($groupModel->isJoin())
			{
				$joinModel = $groupModel->getJoinModel();
				$joinTable = $joinModel->getJoin();
				$fullName = $joinTable->table_join . '___' . $element->name;
			}
			else
			{
				$fullName = $table->db_table_name . '___' . $element->name;
			}

			// Change the id for detailed view elements
			if ($this->inDetailedView)
			{
				$fullName .= '_ro';
			}

			if ($groupModel->canRepeat())
			{
				$fullName .= '_' . $repeatCounter;
			}

			$this->HTMLids[$repeatCounter] = $fullName;
		}

		return $this->HTMLids[$repeatCounter];
	}

	/**
	 * get the element html name
	 *
	 * @param   int  $repeatCounter  group counter
	 *
	 * @return  string
	 */

	public function getHTMLName($repeatCounter = 0)
	{
		$groupModel = $this->getGroup();
		$params = $this->getParams();
		$table = $this->getListModel()->getTable();
		$group = $groupModel->getGroup();
		$element = $this->getElement();

		if ($groupModel->isJoin())
		{
			$joinModel = $groupModel->getJoinModel();
			$joinTable = $joinModel->getJoin();
			$fullName = $joinTable->table_join . '___' . $element->name;
		}
		else
		{
			$fullName = $table->db_table_name . '___' . $element->name;
		}

		if ($groupModel->canRepeat())
		{
			// $$$ rob - always use repeatCounter in html names - avoids ajax post issues with mootools1.1
			$fullName .= '[' . $repeatCounter . ']';
		}

		if ($this->hasSubElements)
		{
			$fullName .= '[]';
		}
		// @TODO: check this - repeated elements do need to have something applied to thier id based on their order in the repeated groups

		$this->elementHTMLName = $fullName;

		return $this->elementHTMLName;
	}

	/**
	 * Load element params
	 *
	 * @return  object  default element params
	 */

	public function getParams()
	{
		if (!isset($this->params))
		{
			$this->params = new JRegistry($this->getElement()->params);
		}

		return $this->params;
	}

	/**
	 * Not used
	 *
	 * @deprecated
	 *
	 * @return  mixed
	 */

	protected function loadPluginParams()
	{
		if (isset($this->xmlPath))
		{
			$element = $this->getElement();
			$pluginParams = new JRegistry($element->params);

			return $pluginParams;
		}

		return false;
	}

	/**
	 * Loads in elements validation objects
	 *
	 * @deprecated use $this->validator->findAll()
	 *
	 * @return  array	validation objects
	 */

	public function getValidations()
	{
		return $this->validator->findAll();
	}

	/**
	 * get javasscript actions
	 *
	 * @deprecated ?
	 *
	 * @return  array  js actions
	 */

	public function getJSActions()
	{
		if (!isset($this->jsActions))
		{
			$query = $this->_db->getQuery();
			$query->select('*')->from('#__{package}_jsactions')->where('element_id = ' . (int) $this->id);
			$this->_db->setQuery($query);
			$this->jsActions = $this->_db->loadObjectList();
		}

		return $this->jsActions;
	}

	/**
	 *Create the js code to observe the elements js actions
	 *
	 * @param   string  $jsControllerKey  Either form_ or _details
	 * @param   int     $repeatCount      Counter
	 *
	 * @return  string	js events
	 */

	public function getFormattedJSActions($jsControllerKey, $repeatCount)
	{
		$jsStr = '';
		$allJsActions = $this->getFormModel()->getJsActions();
		/**
		 * $$$ hugh - only needed getParent when we weren't saving changes to parent params to child
		 * which we should now be doing ... and getParent() causes an extra table lookup for every child
		 * element on the form.
		 * $element = $this->getParent();
		*/
		$jsControllerKey = "Fabrik.blocks['" . $jsControllerKey . "']";
		$element = $this->getElement();
		$form = $this->form->getForm();
		$w = new FabrikWorker;

		if (array_key_exists($element->id, $allJsActions))
		{
			$fxadded = array();
			$elId = $this->getHTMLId($repeatCount);

			foreach ($allJsActions[$element->id] as $jsAct)
			{
				$js = $jsAct->code;
				$js = str_replace(array("\n", "\r"), "", $js);

				if ($jsAct->action == 'load')
				{
					// JS code is already stored in the db as htmlspecialchars() 09/08/2013
					$quote = '&#039;';
					$js = preg_replace('#\bthis\b#', 'document.id(' . $quote . $elId . $quote . ')', $js);
				}

				if ($jsAct->action != '' && $js !== '')
				{
					$jsSlashes = addslashes($js);
					$jsStr .= $jsControllerKey . ".dispatchEvent('$element->plugin', '$elId', '$jsAct->action', '$jsSlashes');\n";
				}
				else
				{
					// Build wysiwyg code
					if (isset($jsAct->js_e_event) && $jsAct->js_e_event != '')
					{
						// $$$ rob get the correct element id based on the repeat counter
						$triggerEl = $this->getFormModel()->getElement(str_replace('fabrik_trigger_element_', '', $jsAct->js_e_trigger));
						$triggerid = is_object($triggerEl) ? 'element_' . $triggerEl->getHTMLId($repeatCount) : $jsAct->js_e_trigger;

						if (!array_key_exists($jsAct->js_e_trigger, $fxadded))
						{
							$jsStr .= $jsControllerKey . ".addElementFX('$triggerid', '$jsAct->js_e_event');\n";
							$fxadded[$jsAct->js_e_trigger] = true;
						}

						$f = JFilterInput::getInstance();
						$post = $f->clean($_POST, 'array');
						$jsAct->js_e_value = $w->parseMessageForPlaceHolder(htmlspecialchars_decode($jsAct->js_e_value), $post);

						if ($jsAct->js_e_condition == 'hidden')
						{
							$js = "if (this.getContainer().getStyle('display') === 'none') {";
						}
						elseif ($jsAct->js_e_condition == 'shown')
						{
							$js = "if (this.getContainer().getStyle('display') !== 'none') {";
						}
						elseif ($jsAct->js_e_condition == 'CONTAINS')
						{
							$js = "if (Array.from(this.get('value')).contains('$jsAct->js_e_value')";
							$js .= " || this.get('value').contains('$jsAct->js_e_value')";
							$js .= ") {";
						}
						elseif ($jsAct->js_e_condition == '!CONTAINS')
						{
							$js = "if (!Array.from(this.get('value')).contains('$jsAct->js_e_value')";
							$js .= " || !this.get('value').contains('$jsAct->js_e_value')";
							$js .= ") {";
						}
						// $$$ hugh if we always quote the js_e_value, numeric comparison doesn't work, as '100' < '3'.
						// So let's assume if they use <, <=, > or >= they mean numbers.
						elseif (in_array($jsAct->js_e_condition, array('<', '<=', '>', '>='))) {
							$js .= "if(this.get('value').toFloat() $jsAct->js_e_condition '$jsAct->js_e_value'.toFloat()) {";
						}
						else
						{
							$js = "if (this.get('value') $jsAct->js_e_condition '$jsAct->js_e_value') {";
						}

						// Need to use corrected triggerid here as well
						if (preg_match('#^fabrik_trigger#', $triggerid))
						{
							$js .= $jsControllerKey . ".doElementFX('" . $triggerid . "', '$jsAct->js_e_event', this)";
						}
						else
						{
							$js .= $jsControllerKey . ".doElementFX('fabrik_trigger_" . $triggerid . "', '$jsAct->js_e_event', this)";
						}

						$js .= "}";
						$js = addslashes($js);
						$js = str_replace(array("\n", "\r"), "", $js);
						$jsStr .= $jsControllerKey . ".dispatchEvent('$element->plugin', '$elId', '$jsAct->action', '$js');\n";
					}
				}
			}
		}

		return $jsStr;
	}

	/**
	 * Get the default value for the list filter
	 *
	 * @param   bool  $normal   is the filter a normal or advanced filter
	 * @param   int   $counter  filter order
	 *
	 * @return  string
	 */

	protected function getDefaultFilterVal($normal = true, $counter = 0)
	{
		$app = JFactory::getApplication();

		// Used for update col list plugin - we dont want a default value filled
		if ($app->input->get('fabrikIngoreDefaultFilterVal', false))
		{
			return '';
		}

		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$listModel = $this->getListModel();
		$filters = $listModel->getFilterArray();

		// $$$ rob test for db join fields
		$elName = $this->getFilterFullName();
		$elid = $this->getElement()->id;
		$f = JFilterInput::getInstance();
		$data = $f->clean($_REQUEST, 'array');

		// See if the data is in the request array - can use tablename___elementname=filterval in query string
		$default = '';

		if (array_key_exists($elName, $data))
		{
			if (is_array($data[$elName]))
			{
				$default = @$data[$elName]['value'];
			}
		}

		$context = 'com_' . $package . '.list' . $listModel->getRenderContext() . '.filter.' . $elid;
		$context .= $normal ? '.normal' : '.advanced';

		// We didnt find anything - lets check the filters
		if ($default == '')
		{
			if (empty($filters))
			{
				return '';
			}

			if (array_key_exists('elementid', $filters))
			{
				/**
				 * $$$ hugh - if we have one or more pre-filters on the same element that has a normal filter,
				 * the following line doesn't work. So in 'normal' mode we need to get all the keys,
				 * and find the 'normal' one.
				 * $k = $normal == true ? array_search($elid, $filters['elementid']) : $counter;
				 */
				$k = false;

				if ($normal)
				{
					$keys = array_keys($filters['elementid'], $elid);

					foreach ($keys as $key)
					{
						/**
						 * $$$ rob 05/09/2011 - just testing for 'normal' is not enough as there are several search_types - ie I've added a test for
						 * Querystring filters as without that the search values were not being shown in ranged filter fields
						 */
						if (in_array($filters['search_type'][$key], array('normal', 'querystring', 'jpluginfilters')))
						{
							$k = $key;
							continue;
						}
					}
				}
				else
				{
					$k = $counter;
				}
				// Is there a filter with this elements name
				if ($k !== false)
				{
					$searchType = JArrayHelper::getValue($filters['search_type'], $k);

					// Check element name is the same as the filter (could occur in advanced search when swapping element type)
					if ($searchType <> 'advanced' || $filters['key'][$k] === $app->input->getString('element'))
					{
						/**
						 * if its a search all filter dont use its value.
						 * if we did the next time the filter form is submitted its value is turned
						 * from a search all filter into an element filter
						 */
						if (!is_null($searchType) && $searchType != 'searchall')
						{
							if ($searchType != 'prefilter')
							{
								$default = JArrayHelper::getValue($filters['origvalue'], $k);
							}
						}
					}
				}
			}
		}

		$default = $app->getUserStateFromRequest($context, $elid, $default);
		$fType = $this->getElement()->filter_type;

		if ($this->multiOptionFilter())
		{
			$default = (is_array($default) && array_key_exists('value', $default)) ? $default['value'] : $default;

			if (is_array($default))
			{
				// Hidden querystring filters can be using ranged valued though
				if (!in_array($fType, array('hidden', 'checkbox', 'multiselect', 'range')))
				{
					// Wierd thing on meow where when you first load the task list the id element had a date range filter applied to it????
					$default = '';
				}
			}
			else
			{
				$default = stripslashes($default);
			}
		}

		return $default;
	}

	/**
	 * Is the element filter type a multi-select
	 *
	 * @return boolean
	 */

	protected function multiOptionFilter()
	{
		$fType = $this->getElement()->filter_type;

		return in_array($fType, array('range', 'checkbox', 'multiselect'));
	}

	/**
	 * If the search value isn't what is stored in the database, but rather what the user
	 * sees then switch from the search string to the db value here
	 * overwritten in things like checkbox and radio plugins
	 *
	 * @param   string  $value  FilterVal
	 *
	 * @return  string
	 */

	protected function prepareFilterVal($value)
	{
		return $value;
	}

	/**
	 * Get the filter name
	 *
	 * @param   int   $counter  Filter order
	 * @param   bool  $normal   Do we render as a normal filter or as an advanced search filter
	 *
	 * @return  string
	 */

	protected function filterName($counter = 0, $normal = true)
	{
		$listModel = $this->getListModel();
		$v = 'fabrik___filter[list_' . $listModel->getRenderContext() . '][value]';
		$v .= $normal ? '[' . $counter . ']' : '[]';

		return $v;
	}

	/**
	 * Get the list filter for the element
	 *
	 * @param   int   $counter  Filter order
	 * @param   bool  $normal   Do we render as a normal filter or as an advanced search filter
	 * if normal include the hidden fields as well (default true, use false for advanced filter rendering)
	 *
	 * @return  string	Filter html
	 */

	public function getFilter($counter = 0, $normal = true)
	{
		$listModel = $this->getListModel();
		$formModel = $listModel->getFormModel();
		$dbElName = $this->getFullName(false, false);

		if (!$formModel->hasElement($dbElName))
		{
			return '';
		}

		$table = $listModel->getTable();
		$element = $this->getElement();
		$elName = $this->getFullName(true, false);
		$id = $this->getHTMLId() . 'value';
		$v = $this->filterName($counter, $normal);

		// Corect default got
		$default = $this->getDefaultFilterVal($normal, $counter);
		$return = array();

		if (in_array($element->filter_type, array('range', 'dropdown', 'checkbox', 'multiselect')))
		{
			$rows = $this->filterValueList($normal);
			$this->unmergeFilterSplits($rows);

			if (!in_array($element->filter_type,  array('checkbox', 'multiselect')))
			{
				array_unshift($rows, JHTML::_('select.option', '', $this->filterSelectLabel()));
			}
		}

		$size = (int) $this->getParams()->get('filter_length', 20);
		$class = $this->filterClass();

		switch ($element->filter_type)
		{
			case 'range':
				$this->rangedFilterFields($default, $return, $rows, $v, 'list');
				break;
			case 'checkbox':
				$return[] = $this->checkboxFilter($rows, $default, $v);
				break;
			case 'dropdown':
			case 'multiselect':
				$max = count($rows) < 7 ? count($rows) : 7;
				$size = $element->filter_type === 'multiselect' ? 'multiple="multiple" size="' . $max . '"' : 'size="1"';
				$v = $element->filter_type === 'multiselect' ? $v . '[]' : $v;
				$return[] = JHTML::_('select.genericlist', $rows, $v, 'class="' . $class . '" ' . $size, 'value', 'text', $default, $id);
				break;

			case 'field':
			default:
				// $$$ rob - if searching on "O'Fallon" from querystring filter the string has slashes added regardless
				$default = (string) $default;
				$default = stripslashes($default);
				$default = htmlspecialchars($default);
				$return[] = '<input type="text" name="' . $v . '" class="' . $class . '" size="' . $size . '" value="' . $default . '" id="'
						. $id . '" />';
				break;

			case 'hidden':
				if (is_array($default))
				{
					$this->rangedFilterFields($default, $return, null, $v, 'hidden');
				}
				else
				{
					$default = stripslashes($default);
					$default = htmlspecialchars($default);
					$return[] = '<input type="hidden" name="' . $v . '" class="' . $class . '" value="' . $default . '" id="' . $id . '" />';
				}

				break;

			case 'auto-complete':
				$autoComplete = $this->autoCompleteFilter($default, $v, null, $normal);
				$return = array_merge($return, $autoComplete);
				break;
		}

		$return[] = $normal ? $this->getFilterHiddenFields($counter, $elName) : $this->getAdvancedFilterHiddenFields();

		return implode("\n", $return);
	}

	/**
	 * Get filter classes
	 *
	 * @since 3.1b
	 *
	 * @return  string
	 */

	protected function filterClass()
	{
		$params = $this->getParams();
		$classes = array('inputbox fabrik_filter');
		$bootstrapClass = $params->get('filter_class', 'input-small');
		$classes[] = $bootstrapClass;

		return implode(' ', $classes);
	}

	/**
	 * Checkbox filter
	 *
	 * @param   array   $rows     Filter list options
	 * @param   array   $default  Selected filter values
	 * @param   string  $v        Filter name
	 *
	 * @since 3.0.7
	 *
	 * @return  string  Checkbox filter HTML
	 */

	protected function checkboxFilter($rows, $default, $v)
	{
		$values = array();
		$labels = array();

		foreach ($rows as $row)
		{
			$values[] = $row->value;
			$labels[] = $row->text;
		}

		$default = (array) $default;

		return implode("\n", FabrikHelperHTML::grid($values, $labels, $default, $v, 'checkbox', false, 1, array('input' => array('fabrik_filter'))));
	}

	/**
	 * Build ranged filter fields either as two dropdowns or two hidden fields
	 *
	 * @param   array   $default  Filter values
	 * @param   array   &$return  HTML to return
	 * @param   array   $rows     Filter list options
	 * @param   string  $v        Filter name
	 * @param   string  $type     Show ranged values as a list or hidden
	 *
	 * @since  3.0.7
	 *
	 * @return void
	 */

	protected function rangedFilterFields($default, &$return, $rows, $v, $type = 'list')
	{
		$element = $this->getElement();
		$class = $this->filterClass();
		$attribs = 'class="' . $class . '" size="1" ';
		$default = (array) $default;
		$def0 = array_key_exists('value', $default) ? $default['value'][0] : $default[0];
		$def1 = array_key_exists('value', $default) ? $default['value'][1] : $default[1];

		if ($type === 'list')
		{
			$return[] = JText::_('COM_FABRIK_BETWEEN');
			$return[] = JHTML::_('select.genericlist', $rows, $v . '[0]', $attribs, 'value', 'text', $def0, $element->name . '_filter_range_0');

			$return[] = '<br /> ' . JText::_('COM_FABRIK_AND') . ' ';
			$return[] = JHTML::_('select.genericlist', $rows, $v . '[1]', $attribs, 'value', 'text', $def1, $element->name . '_filter_range_1');
		}
		else
		{
			$return[] = '<input type="hidden" class="' . $class . '" name="' . $v . '[0]" value="' . $def0 . '" id="' . $element->name . '_filter_range_0" />';
			$return[] = '<input type="hidden" class="' . $class . '" name="' . $v . '[1]" value="' . $def1 . '" id="' . $element->name . '_filter_range_1" />';
		}
	}

	/**
	 * Build the HTML for the auto-complete filter
	 *
	 * @param   string  $default     Label
	 * @param   string  $v           Field name
	 * @param   string  $labelValue  Label value
	 * @param   bool    $normal      Do we render as a normal filter or as an advanced search filter
	 * if normal include the hidden fields as well (default true, use false for advanced filter rendering)
	 *
	 * @return  array  HTML bits
	 */

	protected function autoCompleteFilter($default, $v, $labelValue = null, $normal = true)
	{
		if (is_null($labelValue))
		{
			$labelValue = $default;
		}

		$listModel = $this->getListModel();
		$default = stripslashes($default);
		$default = htmlspecialchars($default);
		$id = $this->getHTMLId() . 'value';
		$class = $this->filterClass();
		$size = (int) $this->getParams()->get('filter_length', 20);
		/**
		 * $$$ rob 28/10/2011 using selector rather than element id so we can have n modules with the same filters
		 * showing and not produce invald html & duplicate js calls
		*/
		$return = array();
		$return[] = '<input type="hidden" name="' . $v . '" class="' . $class . ' ' . $id . '" value="' . $default . '" />';
		$return[] = '<input type="text" name="' . 'auto-complete' . $this->getElement()->id . '" class="' . $class . ' autocomplete-trigger '
				. $id . '-auto-complete" size="' . $size . '" value="' . $labelValue . '" />';
		$opts = array();

		if ($normal)
		{
			$opts['menuclass'] = 'auto-complete-container';
			$selector = '#listform_' . $listModel->getRenderContext() . ' .' . $id;
		}
		else
		{
			$selector = '.advancedSeach_' . $listModel->getRenderContext() . ' .' . $id;
			$opts['menuclass'] = 'auto-complete-container advanced';
		}

		$element = $this->getElement();
		$formid = $this->getFormModel()->getId();
		FabrikHelperHTML::autoComplete($selector, $element->id, $formid, $element->plugin, $opts);

		return $return;
	}

	/**
	 * Get dropdown filter select label
	 *
	 * @return  string
	 */

	protected function filterSelectLabel()
	{
		$params = $this->getParams();

		return $params->get('filter_required') == 1 ? JText::_('COM_FABRIK_PLEASE_SELECT') : JText::_('COM_FABRIK_FILTER_PLEASE_SELECT');
	}

	/**
	 * Checks if filter option values are in json format
	 * if so explode those values into new options
	 *
	 * @param   array  &$rows  Filter options
	 *
	 * @return null
	 */

	protected function unmergeFilterSplits(&$rows)
	{
		/*
		 * takes rows which may be in format :
		*
		* [0] => stdClass Object
		(
				[text] => ["1"]
				[value] => ["1"]
		)
		and converts them into
		[0] => JObject Object
		(
				[_errors:protected] => Array
				(
				)
				[value] => 1
				[text] => 1
				[disable] =>
		)
		*/
		$allvalues = array();

		foreach ($rows as $row)
		{
			$allvalues[] = $row->value;
		}

		$c = count($rows) - 1;

		for ($j = $c; $j >= 0; $j--)
		{
			$vals = FabrikWorker::JSONtoData($rows[$j]->value, true);
			$txt = FabrikWorker::JSONtoData($rows[$j]->text, true);

			if (is_array($vals))
			{
				for ($i = 0; $i < count($vals); $i++)
				{
					$vals2 = FabrikWorker::JSONtoData($vals[$i], true);
					$txt2 = FabrikWorker::JSONtoData(JArrayHelper::getValue($txt, $i), true);

					for ($jj = 0; $jj < count($vals2); $jj++)
					{
						if (!in_array($vals2[$jj], $allvalues))
						{
							$allvalues[] = $vals2[$jj];
							$rows[] = JHTML::_('select.option', $vals2[$jj], $txt2[$jj]);
						}
					}
				}

				if (FabrikWorker::isJSON($rows[$j]->value))
				{
					// $$$ rob 01/10/2012 - if not unset then you could get json values in standard dd filter (checkbox)
					unset($rows[$j]);
				}
			}

			if (count($vals) > 1)
			{
				unset($rows[$j]);
			}
		}
	}

	/**
	 * Run after unmergeFilterSplits to ensure filter dropdown labels are correct
	 *
	 * @param   array  &$rows  filter options
	 *
	 * @return  null
	 */

	protected function reapplyFilterLabels(&$rows)
	{
		$element = $this->getElement();
		$values = $this->getSubOptionValues();
		$labels = $this->getSubOptionLabels();

		foreach ($rows as &$row)
		{
			$k = array_search($row->value, $values);

			if ($k !== false)
			{
				$row->text = $labels[$k];
			}
		}

		$rows = array_values($rows);
	}

	/**
	 * Get sub option values
	 *
	 * @return  array
	 */

	protected function getSubOptionValues()
	{
		$phpOpts = $this->getPhpOptions();

		if (!$phpOpts)
		{
			$params = $this->getParams();
			$opts = $params->get('sub_options', '');
			$opts = $opts == '' ? array() : (array) @$opts->sub_values;
		}
		else
		{
			/**
			 * Paul - According to tooltip, $phpOpts should be of form "array(JHTML: :_('select.option', '1', 'one'))"
			 * This is an array of objects with properties text and value.
			 * If user has mis-specified this we should tell them.
			 **/
			if (!is_array($phpOpts) || !$phpOpts[0] || !is_object($phpOpts[0]) || !$phpOpts[0]->value || !$phpOpts[0]->text)
			{
				FabrikWorker::logError(sprintf(JText::_('COM_FABRIK_ELEMENT_SUBOPTION_ERROR'), $this->element->name, var_export($phpOpts, true)), 'error');

				return array();
			}

			$opts = array();

			foreach ($phpOpts as $phpOpt)
			{
				$opts[] = $phpOpt->value;
			}
		}

		return $opts;
	}

	/**
	 * Get sub option labels
	 *
	 * @return  array
	 */

	protected function getSubOptionLabels()
	{
		$phpOpts = $this->getPhpOptions();

		if (!$phpOpts)
		{
			$params = $this->getParams();
			$opts = $params->get('sub_options', '');
			$opts = $opts == '' ? array() : (array) @$opts->sub_labels;
		}
		else
		{
			/**
			 * Paul - According to tooltip, $phpOpts should be of form "array(JHTML: :_('select.option', '1', 'one'))"
			 * This is an array of objects with properties text and value.
			 * If user has mis-specified this we should tell them.
			 **/
			if (!is_array($phpOpts) || !$phpOpts[0] || !is_object($phpOpts[0]) || !$phpOpts[0]->value || !$phpOpts[0]->text)
			{
				FabrikWorker::logError(sprintf(JText::_('COM_FABRIK_ELEMENT_SUBOPTION_ERROR'), $this->element->name, var_export($phpOpts, true)), 'error');

				return array();
			}

			$opts = array();

			foreach ($phpOpts as $phpOpt)
			{
				$opts[] = $phpOpt->text;
			}
		}

		foreach ($opts as &$opt)
		{
			$opt = JText::_($opt);
		}

		return $opts;
	}

	/**
	 * Should we get the elements sub options via the use of eval'd parameter setting
	 *
	 * @since  3.0.7
	 *
	 * @return mixed  false if no, otherwise needs to return array of JHTML::options
	 */

	protected function getPhpOptions()
	{
		$params = $this->getParams();
		$pop = $params->get('dropdown_populate', '');

		if ($pop !== '')
		{
			if (FabrikHelperHTML::isDebug())
			{
				$res = eval($pop);
			}
			else
			{
				$res = @eval($pop);
			}

			FabrikWorker::logEval($res, 'Eval exception : ' . $this->element->name . '::getPhpOptions() : ' . $pop . ' : %s');

			return $res;
		}

		return false;
	}

	/**
	 * Get the radio buttons possible values
	 * needed for inline edit list plugin
	 *
	 * @return  array  of radio button values
	 */

	public function getOptionValues()
	{
		return $this->getSubOptionValues();
	}

	/**
	 * get the radio buttons possible labels
	 * needed for inline edit list plugin
	 *
	 * @return  array  of radio button labels
	 */

	protected function getOptionLabels()
	{
		return $this->getSubOptionLabels();
	}

	/**
	 * Get the flter build method - all (2) or recorded data (1)
	 *
	 * @since   3.0.7
	 *
	 * @return  int
	 */

	protected function getFilterBuildMethod()
	{
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$params = $this->getParams();
		$filter_build = $params->get('filter_build_method', 0);

		if ($filter_build == 0)
		{
			$filter_build = $usersConfig->get('filter_build_method');
		}

		return $filter_build;
	}

	/**
	 * Used by radio and dropdown elements to get a dropdown list of their unique
	 * unique values OR all options - basedon filter_build_method
	 *
	 * @param   bool    $normal     do we render as a normal filter or as an advanced search filter
	 * @param   string  $tableName  table name to use - defaults to element's current table
	 * @param   string  $label      field to use, defaults to element name
	 * @param   string  $id         field to use, defaults to element name
	 * @param   bool    $incjoin    include join
	 *
	 * @return  array  text/value objects
	 */

	public function filterValueList($normal, $tableName = '', $label = '', $id = '', $incjoin = true)
	{
		$filter_build = $this->getFilterBuildMethod();

		if ($filter_build == 2 && $this->hasSubElements)
		{
			return $this->filterValueList_All($normal, $tableName, $label, $id, $incjoin);
		}
		else
		{
			return $this->filterValueList_Exact($normal, $tableName, $label, $id, $incjoin);
		}
	}

	/**
	 * If filterValueList_Exact incjoin value = false, then this method is called
	 * to ensure that the query produced in filterValueList_Exact contains at least the database join element's
	 * join
	 *
	 * @return  string  required join text to ensure exact filter list code produces a valid query.
	 */

	protected function buildFilterJoin()
	{
		return '';
	}

	/**
	 * Create an array of label/values which will be used to populate the elements filter dropdown
	 * returns only data found in the table you are filtering on
	 *
	 * @param   bool    $normal     do we render as a normal filter or as an advanced search filter
	 * @param   string  $tableName  table name to use - defaults to element's current table
	 * @param   string  $label      field to use, defaults to element name
	 * @param   string  $id         field to use, defaults to element name
	 * @param   bool    $incjoin    include join
	 *
	 * @return  array	filter value and labels
	 */

	protected function filterValueList_Exact($normal, $tableName = '', $label = '', $id = '', $incjoin = true)
	{
		$listModel = $this->getListModel();
		$fbConfig = JComponentHelper::getParams('com_fabrik');
		$fabrikDb = $listModel->getDb();
		$table = $listModel->getTable();
		$element = $this->getElement();
		$origTable = $table->db_table_name;
		$elName = $this->getFullName(true, false);
		$params = $this->getParams();
		$elName2 = $this->getFullName(false, false);

		if (!$this->isJoin())
		{
			$ids = $listModel->getColumnData($elName2);

			// For ids that are text with apostrophes in
			for ($x = count($ids) - 1; $x >= 0; $x--)
			{
				if ($ids[$x] == '')
				{
					unset($ids[$x]);
				}
				else
				{
					$ids[$x] = addslashes($ids[$x]);
				}
			}
		}

		$incjoin = $this->isJoin() ? false : $incjoin;
		/**
		 * filter the drop downs lists if the table_view_own_details option is on
		 * other wise the lists contain data the user should not be able to see
		 * note, this should now use the prefilter data to filter the list
		 */

		// Check if the elements group id is on of the table join groups if it is then we swap over the table name
		$fromTable = $this->isJoin() ? $this->getJoinModel()->getJoin()->table_join : $origTable;
		$joinStr = $incjoin ? $listModel->buildQueryJoin() : $this->buildFilterJoin();

		// New option not to order elements - required if you want to use db joins 'Joins where and/or order by statement'
		$groupBy = $this->getOrderBy('filter');

		foreach ($listModel->getJoins() as $aJoin)
		{
			// Not sure why the group id key wasnt found - but put here to remove error
			if (array_key_exists('group_id', $aJoin))
			{
				if ($aJoin->group_id == $element->group_id && $aJoin->element_id == 0)
				{
					$fromTable = $aJoin->table_join;
					$elName = str_replace($origTable . '.', $fromTable . '.', $elName2);
				}
			}
		}

		$elName = FabrikString::safeColName($elName);

		if ($label == '')
		{
			$label = $this->isJoin() ? $this->getElement()->name : $elName;
		}

		if ($id == '')
		{
			$id = $this->isJoin() ? 'id' : $elName;
		}

		if ($this->encryptMe())
		{
			$secret = JFactory::getConfig()->getValue('secret');
			$label = 'AES_DECRYPT(' . $label . ', ' . $fabrikDb->quote($secret) . ')';
			$id = 'AES_DECRYPT(' . $id . ', ' . $fabrikDb->quote($secret) . ')';
		}

		$origTable = $tableName == '' ? $origTable : $tableName;
		/**
		 * $$$ rob - 2nd sql was blowing up for me on my test table - why did we change to it?
		 * http://localhost/fabrik2.0.x/index.php?option=com_fabrik&view=table&listid=12&calculations=0&resetfilters=0&Itemid=255&lang=en
		 * so added test for intial fromtable in join str and if found use origtable
		 */
		if (strstr($joinStr, 'JOIN ' . $fabrikDb->quoteName($fromTable)))
		{
			$sql = 'SELECT DISTINCT(' . $label . ') AS ' . $fabrikDb->quoteName('text') . ', ' . $id . ' AS ' . $fabrikDb->quoteName('value')
			. ' FROM ' . $fabrikDb->quoteName($origTable) . ' ' . $joinStr . "\n";
		}
		else
		{
			$sql = 'SELECT DISTINCT(' . $label . ') AS ' . $fabrikDb->quoteName('text') . ', ' . $id . ' AS ' . $fabrikDb->quoteName('value')
			. ' FROM ' . $fabrikDb->quoteName($fromTable) . ' ' . $joinStr . "\n";
		}

		if (!$this->isJoin())
		{
			$sql .= 'WHERE ' . $id . ' IN (\'' . implode("','", $ids) . '\')';
		}

		// Apply element where/order by statements to the filter (e.g. dbjoins 'Joins where and/or order by statement')
		$elementWhere = $this->buildQueryWhere(array(), true, null, array('mode' => 'filter'));

		if (JString::stristr($sql, 'WHERE ') && JString::stristr($elementWhere, 'WHERE '))
		{
			// $$$ hugh - only replace the WHERE with AND if it's the first word, so we don't munge sub-queries
			// $elementWhere = JString::str_ireplace('WHERE ', 'AND ', $elementWhere);
			$elementWhere = preg_replace("#^(\s*)(WHERE)(.*)#i", "$1AND$3", $elementWhere);
		}

		$sql .= ' ' . $elementWhere;
		$sql .= "\n" . $groupBy;
		$sql = $listModel->pluginQuery($sql);
		$fabrikDb->setQuery($sql, 0, $fbConfig->get('filter_list_max', 100));
		FabrikHelperHTML::debug($fabrikDb->getQuery(), 'element filterValueList_Exact:');

		try
		{
			$rows = $fabrikDb->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			throw new ErrorException('filter query error: ' . $this->getElement()->name . ' ' . $fabrikDb->getErrorMsg(), 500);
		}

		return $rows;
	}

	/**
	 * Get a readonly value for a filter, uses getROElement() to asscertain value, adds between x & y if ranged values
	 *
	 * @param   mixed  $data  String or array of filter value(s)
	 *
	 * @since   3.0.7
	 *
	 * @return  string
	 */

	public function getFilterRO($data)
	{
		if (in_array($this->getFilterType(), array('range', 'range-hidden')))
		{
			$return = array();

			foreach ($data as $d)
			{
				$return[] = $this->getROElement($d);
			}

			return JText::_('COM_FABRIK_BETWEEN') . '<br />' . implode('<br />' . JText::_('COM_FABRIK_AND') . "<br />", $return);
		}

		return $this->getROElement($data);
	}

	/**
	 * Get options order by
	 *
	 * @param   string         $view   Ciew mode '' or 'filter'
	 * @param   JDatabasQuery  $query  Set to false to return a string
	 *
	 * @return  string  order by statement
	 */

	protected function getOrderBy($view = '', $query = false)
	{
		if (isset($this->orderBy))
		{
			return $this->orderBy;
		}
		else
		{
			return "ORDER BY text ASC ";
		}
	}

	/**
	 * Create an array of label/values which will be used to populate the elements filter dropdown
	 * returns all possible options
	 *
	 * @param   bool    $normal     do we render as a normal filter or as an advanced search filter
	 * @param   string  $tableName  table name to use - defaults to element's current table
	 * @param   string  $label      field to use, defaults to element name
	 * @param   string  $id         field to use, defaults to element name
	 * @param   bool    $incjoin    include join
	 *
	 * @return  array	filter value and labels
	 */

	protected function filterValueList_All($normal, $tableName = '', $label = '', $id = '', $incjoin = true)
	{
		$element = $this->getElement();
		$vals = $this->getSubOptionValues();
		$labels = $this->getSubOptionLabels();
		$return = array();

		for ($i = 0; $i < count($vals); $i++)
		{
			$return[] = JHTML::_('select.option', $vals[$i], $labels[$i]);
		}

		return $return;
	}

	/**
	 * Get the hidden fields for a normal filter
	 *
	 * @param   int     $counter  filter counter
	 * @param   string  $elName   full element name will be converted to tablename.elementname format
	 * @param   bool    $hidden   has the filter been added due to a search form value with no corresponding filter set up in the table
	 * if it has we need to know so that when we do a search from a 'fabrik_list_filter_all' field that search term takes prescidence
	 *
	 * @return  string	html hidden fields
	 */

	protected function getFilterHiddenFields($counter, $elName, $hidden = false)
	{
		$params = $this->getParams();
		$element = $this->getElement();
		$class = $this->filterClass();

		// $$$ needs to apply to CDD's as well, so just making this an overideable method.
		if ($this->quoteLabel())
		{
			$elName = FabrikString::safeColName($elName);
		}

		$hidden = $hidden ? 1 : 0;
		$table = $this->getListModel()->getTable();
		$match = $this->isExactMatch(array('match' => $element->filter_exact_match));
		$return = array();
		$filters = $this->getListModel()->getFilterArray();
		$eval = JArrayHelper::getValue($filters, 'eval', array());
		$eval = JArrayHelper::getValue($eval, $counter, FABRIKFILTER_TEXT);

		/**
		 * $$$ hugh - these two lines are preventing the "exact match" setting on an element filter working,
		 * as we always end up with an = condition, so exact match No nev er works.  I've "fixed" it by just using
		 * the element's getFilterCondition(), but I don't know what side effects this might have.
		 * So BOLO for filtering oddities!
		 *
		 * $condition = JArrayHelper::getValue($filters, 'condition', array());
		 * $condition = JArrayHelper::getValue($condition, $counter, $this->getFilterCondition());
		*/
		$condition = $this->getFilterCondition();

		// Need to include class other wise csv export produces incorrect results when exporting
		$prefix = '<input type="hidden" class="' . $class . '" name="fabrik___filter[list_' . $this->getListModel()->getRenderContext() . ']';
		$return[] = $prefix . '[condition][' . $counter . ']" value="' . $condition . '" />';
		$return[] = $prefix . '[join][' . $counter . ']" value="AND" />';
		$return[] = $prefix . '[key][' . $counter . ']" value="' . $elName . '" />';
		$return[] = $prefix . '[search_type][' . $counter . ']" value="normal" />';
		$return[] = $prefix . '[match][' . $counter . ']" value="' . $match . '" />';
		$return[] = $prefix . '[full_words_only][' . $counter . ']" value="' . $params->get('full_words_only', '0') . '" />';
		$return[] = $prefix . '[eval][' . $counter . ']" value="' . $eval . '" />';
		$return[] = $prefix . '[grouped_to_previous][' . $counter . ']" value="0" />';
		$return[] = $prefix . '[hidden][' . $counter . ']" value="' . $hidden . '" />';
		$return[] = $prefix . '[elementid][' . $counter . ']" value="' . $element->id . '" />';

		return implode("\n", $return);
	}

	/**
	 * Get the condition statement to use in the filters hidden field
	 *
	 * @return  string	=, begins or contains
	 */

	protected function getFilterCondition()
	{
		if ($this->getElement()->filter_type == 'auto-complete')
		{
			$cond = 'contains';
		}
		else
		{
			$match = $this->isExactMatch(array('match' => $this->getElement()->filter_exact_match));
			$cond = ($match == 1) ? '=' : 'contains';
		}

		return $cond;
	}

	/**
	 * Get the filter type: the element filter_type property unless a ranged querystring is used
	 *
	 * @since  3.0.7
	 *
	 * @return string
	 */
	protected function getFilterType()
	{
		$element = $this->getElement();
		$type = $element->filter_type;
		$name = $this->getFullName(true, false);
		$app = JFactory::getApplication();
		$qsFilter = $app->input->get($name, array(), 'array');
		$qsValues = JArrayHelper::getValue($qsFilter, 'value', array());

		if (count($qsValues) > 1)
		{
			$type = $type === 'hidden' ? 'range-hidden' : 'range';
		}

		return $type;
	}

	/**
	 * Get the hidden fields for an advanced filter
	 *
	 * @return  string	html hidden fields
	 */

	protected function getAdvancedFilterHiddenFields()
	{
		$element = $this->getElement();
		$elName = $this->getFilterFullName();

		if (!is_a($this, 'PlgFabrik_ElementDatabasejoin'))
		{
			$elName = FabrikString::safeColName($elName);
		}

		$listModel = $this->getListModel();
		$element = $this->getElement();
		$return = array();
		$prefix = '<input type="hidden" name="fabrik___filter[list_' . $this->getListModel()->getRenderContext() . ']';
		$return[] = $prefix . '[elementid][]" value="' . $element->id . '" />';
		/**
		 * already added in advanced filter
		 * $return[] = $prefix . '[key][]" value="'.$elName.'" />';
		 * $return[] = $prefix . '[join][]" value="AND" />';
		 * $return[] = $prefix . '[grouped_to_previous][]" value="0" />';
		 */
		return implode("\n", $return);
	}

	/**
	 * This builds an array containing the filters value and condition
	 * when using a ranged search
	 *
	 * @param   array  $value  Initial values
	 *
	 * @return  array  (value condition)
	 */

	protected function getRangedFilterValue($value)
	{
		$db = FabrikWorker::getDbo();
		$element = $this->getElement();

		if ($element->filter_type === 'range')
		{
			if (is_numeric($value[0]) && is_numeric($value[1]))
			{
				$value = $value[0] . ' AND ' . $value[1];
			}
			else
			{
				$value = $db->quote($value[0]) . ' AND ' . $db->quote($value[1]);
			}

			$condition = 'BETWEEN';
		}
		else
		{
			if (is_array($value) && !empty($value))
			{
				foreach ($value as &$v)
				{
					$v = $db->quote($v);
				}

				$value = ' (' . implode(',', $value) . ')';
			}

			$condition = 'IN';
		}

		return array($value, $condition);
	}

	/**
	 * Esacepes a SINGLE query search string
	 *
	 * @param   string  $condition  filter condition
	 * @param   value   &$value     value to esacpe
	 *
	 * @since   3.0.7
	 *
	 * @return  null
	 */

	private function escapeOneQueryValue($condition, &$value)
	{
		if ($condition == 'REGEXP')
		{
			$value = preg_quote($value);
		}

		/**
		 * If doing a search via a querystring for O'Fallon then the ' is backslahed
		 * in FabrikModelListfilter::getQuerystringFilters()
		 * but the mySQL regexp needs it to be backquoted three times
		 */

		// If searching on '\' then don't double up \'s
		if (strlen(str_replace('\\', '', $value)) > 0)
		{
			$value = str_replace("\\", "\\\\\\", $value);

			// $$$rob check things havent been double quoted twice (occurs now that we are doing preg_quote() above to fix searches on '*'
			$value = str_replace("\\\\\\\\\\\\", "\\\\\\", $value);
		}
	}

	/**
	 * Esacepes the a query search string
	 *
	 * @param   string  $condition  filter condition
	 * @param   value   &$value     value to esacpe
	 *
	 * @return  null
	 */

	private function escapeQueryValue($condition, &$value)
	{
		// $$$ rob 30/06/2011 only escape once !
		if ($this->escapedQueryValue)
		{
			return;
		}

		$this->escapedQueryValue = true;

		if (is_array($value))
		{
			foreach ($value as &$val)
			{
				$this->escapeOneQueryValue($condition, $val);
			}
		}
		else
		{
			$this->escapeOneQueryValue($condition, $value);
		}
	}

	/**
	 * Builds an array containing the filters value and condition
	 *
	 * @param   string  $value      Initial value
	 * @param   string  $condition  Intial $condition
	 * @param   string  $eval       How the value should be handled
	 *
	 * @return  array	(value condition)
	 */

	public function getFilterValue($value, $condition, $eval)
	{
		$this->escapeQueryValue($condition, $value);
		$db = FabrikWorker::getDbo();

		if (is_array($value))
		{
			// Ranged search
			list($value, $condition) = $this->getRangedFilterValue($value);
		}
		else
		{
			switch ($condition)
			{
				case 'notequals':
				case '<>':
					$condition = "<>";

					// 2 = subquery so dont quote
					$value = ($eval == FABRIKFILTER_QUERY) ? '(' . $value . ')' : $db->quote($value);
					break;
				case 'equals':
				case '=':
					$condition = "=";
					$value = ($eval == FABRIKFILTER_QUERY) ? '(' . $value . ')' : $db->quote($value);
					break;
				case 'begins':
				case 'begins with':
					$condition = "LIKE";
					$value = $eval == FABRIKFILTER_QUERY ? '(' . $value . ')' : $db->quote($value . '%');
					break;
				case 'ends':
				case 'ends with':
					// @TODO test this with subsquery
					$condition = "LIKE";
					$value = $eval == FABRIKFILTER_QUERY ? '(' . $value . ')' : $db->quote('%' . $value);
					break;
				case 'contains':
					// @TODO test this with subsquery
					$condition = "LIKE";
					$value = $eval == FABRIKFILTER_QUERY ? '(' . $value . ')' : $db->quote('%' . $value . '%');
					break;
				case '>':
				case '&gt;':
				case 'greaterthan':
					$condition = '>';
					break;
				case '<':
				case '&lt;':
				case 'lessthan':
					$condition = '<';
					break;
				case '>=':
				case '&gt;=':
				case 'greaterthanequals':
					$condition = '>=';
					break;
				case '<=':
				case '&lt;=':
				case 'lessthanequals':
					$condition = '<=';
					break;
				case 'in':
					$condition = 'IN';
					$value = ($eval == FABRIKFILTER_QUERY) ? '(' . $value . ')' : '(' . $value . ')';
					break;
				case 'not_in':
					$condition = 'NOT IN';
					$value = ($eval == FABRIKFILTER_QUERY) ? '(' . $value . ')' : '(' . $value . ')';
					break;
			}

			switch ($condition)
			{
				case '>':
				case '<':
				case '>=':
				case '<=':
					if ($eval == FABRIKFILTER_QUERY)
					{
						$value = '(' . $value . ')';
					}
					else
					{
						if (!is_numeric($value))
						{
							$value = $db->quote($value);
						}
					}
					break;
			}
			// $$$ hugh - if 'noquotes' (3) selected, strip off the quotes again!
			if ($eval == FABRKFILTER_NOQUOTES)
			{
				// $$$ hugh - darn, this is stripping the ' of the end of things like "select & from foo where bar = '123'"
				$value = JString::ltrim($value, "'");
				$value = JString::rtrim($value, "'");
			}

			if ($condition == '=' && $value == "'_null_'")
			{
				$condition = " IS NULL ";
				$value = '';
			}
		}

		return array($value, $condition);
	}

	/**
	 * Build the filter query for the given element.
	 * Can be overwritten in plugin - e.g. see checkbox element which checks for partial matches
	 *
	 * @param   string  $key            element name in format `tablename`.`elementname`
	 * @param   string  $condition      =/like etc
	 * @param   string  $value          search string - already quoted if specified in filter array options
	 * @param   string  $originalValue  original filter value without quotes or %'s applied
	 * @param   string  $type           filter type advanced/normal/prefilter/search/querystring/searchall
	 *
	 * @return  string	sql query part e,g, "key = value"
	 */

	public function getFilterQuery($key, $condition, $value, $originalValue, $type = 'normal')
	{
		$this->encryptFieldName($key);

		switch ($condition)
		{
			case 'earlierthisyear':
				$query = ' DAYOFYEAR(' . $key . ') <= DAYOFYEAR(now()) ';
				break;
			case 'laterthisyear':
				$query = ' DAYOFYEAR(' . $key . ') >= DAYOFYEAR(now()) ';
				break;
			case 'today':
				$query = ' (' . $key . ' >= CURDATE() AND ' . $key . ' < CURDATE() + INTERVAL 1 DAY) ';
				break;
			case 'yesterday':
				$query = ' (' . $key . ' >= CURDATE() - INTERVAL 1 DAY AND ' . $key . ' < CURDATE()) ';
				break;
			case 'tomorrow':
				$query = ' (' . $key . ' >= CURDATE() + INTERVAL 1 DAY  AND ' . $key . ' < CURDATE() + INTERVAL 2 DAY ) ';
				break;
			case 'thismonth':
				$query = ' (' . $key . ' >= DATE_ADD(LAST_DAY(DATE_SUB(now(), INTERVAL 1 MONTH)), INTERVAL 1 DAY)  AND ' . $key
				. ' <= LAST_DAY(NOW()) ) ';
				break;
			case 'lastmonth':
				$query = ' (' . $key . ' >= DATE_ADD(LAST_DAY(DATE_SUB(now(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)  AND ' . $key
				. ' <= LAST_DAY(DATE_SUB(NOW(), INTERVAL 1 MONTH)) ) ';
				break;
			case 'nextmonth':
				$query = ' (' . $key . ' >= DATE_ADD(LAST_DAY(now()), INTERVAL 1 DAY)  AND ' . $key
				. ' <= DATE_ADD(LAST_DAY(NOW()), INTERVAL 1 MONTH) ) ';
				break;
			default:
				if ($this->isJoin())
				{
					// Query the joined table concatanating into one field
					$jointable = $this->getJoinModel()->getJoin()->table_join;
					$pk = $this->getListModel()->getTable()->db_primary_key;
					/**
					 *  Jaanus: joined group pk?
					 *  set in groupConcactJoinKey()
					 */
					// $pk = $this->getListModel()->getTable()->db_primary_key;
					$pk = $this->groupConcactJoinKey();
					$key = "(SELECT GROUP_CONCAT(id SEPARATOR '" . GROUPSPLITTER . "') FROM $jointable WHERE parent_id = $pk)";
					$value = str_replace("'", '', $value);
					$query = "($key = '$value' OR $key LIKE '$value" . GROUPSPLITTER . "%' OR
					$key LIKE '" . GROUPSPLITTER . "$value" . GROUPSPLITTER . "%' OR
					$key LIKE '%" . GROUPSPLITTER . "$value')";
				}
				else
				{
					$query = " $key $condition $value ";
				}

				break;
		}

		return $query;
	}

	/**
	 * Get the AES decrypt sql segment for the element
	 *
	 * @param   string  &$key  field name
	 *
	 * @return  void
	 */

	public function encryptFieldName(&$key)
	{
		if ($this->encryptMe())
		{
			$db = FabrikWorker::getDbo();
			$secret = JFactory::getConfig()->get('secret');
			$key = 'AES_DECRYPT(' . $key . ', ' . $db->quote($secret) . ')';
		}
	}

	/**
	 * If no filter condition supplied (either via querystring or in posted filter data
	 * return the most appropriate filter option for the element.
	 *
	 * @return  string	default filter condition ('=', 'REGEXP' etc)
	 */

	public function getDefaultFilterCondition()
	{
		$params = $this->getParams();
		$fieldDesc = $this->getFieldDescription();

		if (JString::stristr($fieldDesc, 'INT') || $this->getElement()->filter_exact_match == 1)
		{
			return '=';
		}

		return 'REGEXP';
	}

	/**
	 * $$$ rob testing not using this as elements can only be in one group
	 * $$$ hugh - still called from import.php
	 * when adding a new element this will ensure its added to all tables that the
	 * elements group is associated with
	 *
	 * @param   string  $origColName  original column name leave null to ignore
	 *
	 * @TODO Fabrik 3 - loadFromFormId() might need to pass in a package id
	 *
	 * @deprecated
	 *
	 * @return  null
	 */

	public function addToDBTable($origColName = null)
	{
	}

	/**
	 * called from admin element controller when element saved
	 *
	 * @param   array  $data  posted element save data
	 *
	 * @return  bool  save ok or not
	 */

	public function onSave($data)
	{
		$params = $this->getParams();

		if (!$this->canEncrypt() && $params->get('encrypt'))
		{
			throw new RuntimeException('The encryption option is only available for field and text area plugins');
		}
		// Overridden in element plugin if needed
		return true;
	}

	/**
	 * Called from admin element controller when element is removed
	 *
	 * @param   bool  $drop  has the user elected to drop column?
	 *
	 * @return  bool  save ok or not
	 */

	public function onRemove($drop = false)
	{
		// Delete js actions
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$id = (int) $this->getElement()->id;
		$query->delete()->from('#__{package}_jsactions')->where('element_id =' . $id);
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			throw new RuntimeException('didnt delete js actions for element ' . $id);
		}

		return true;
	}

	/**
	 * States if the element contains data which is recorded in the database
	 * some elements (eg buttons) dont
	 *
	 * @param   array  $data  posted data
	 *
	 * @return  bool
	 */

	public function recordInDatabase($data = null)
	{
		return $this->recordInDatabase;
	}

	/**
	 * Used by elements with suboptions, given a value, return its label
	 *
	 * @param   string  $v             Value
	 * @param   string  $defaultLabel  Default label
	 * @param   bool    $forceCheck    Force check even if $v === $defaultLabel
	 *
	 * @return  string	Label
	 */

	public function getLabelForValue($v, $defaultLabel = null, $forceCheck = false)
	{
		/**
		 * $$$ hugh - only needed getParent when we weren't saving changes to parent params to child
		 * which we should now be doing ... and getParent() causes an extra table lookup for every child
		 * element on the form.
		 * $element = $this->getParent();
		 */
		$element = $this->getElement();
		$params = $this->getParams();
		$values = $this->getSubOptionValues();
		$labels = $this->getSubOptionLabels();
		$key = array_search($v, $values);
		/**
		 * $$$ rob if we allow adding to the dropdown but not recording
		 * then there will be no $key set to revert to the $val instead
		*/
		if ($v === $params->get('sub_default_value'))
		{
			$v = $params->get('sub_default_label');
		}

		return ($key === false) ? $v : JArrayHelper::getValue($labels, $key, $defaultLabel);
	}

	/**
	 * Build the query for the avg calculation
	 *
	 * @param   model  &$listModel  list model
	 * @param   array  $labels      Labels
	 *
	 * @return  string	sql statement
	 */

	protected function getAvgQuery(&$listModel, $labels = array())
	{
		if (count($labels) == 0)
		{
			$label = "'calc' AS label";
		}
		else
		{
			$label = 'CONCAT(' . implode(', " & " , ', $labels) . ')  AS label';
		}

		$item = $listModel->getTable();
		$joinSQL = $listModel->buildQueryJoin();
		$whereSQL = $listModel->buildQueryWhere();
		$name = $this->getFullName(false, false);
		$groupModel = $this->getGroup();
		$roundTo = (int) $this->getParams()->get('avg_round');

		if ($groupModel->isJoin())
		{
			// Element is in a joined column - lets presume the user wants to sum all cols, rather than reducing down to the main cols totals
			return "SELECT ROUND(AVG($name), $roundTo) AS value, $label FROM " . FabrikString::safeColName($item->db_table_name)
			. " $joinSQL $whereSQL";
		}
		else
		{
			// Need to do first query to get distinct records as if we are doing left joins the sum is too large
			return "SELECT ROUND(AVG(value), $roundTo) AS value, label
			FROM (SELECT DISTINCT $item->db_primary_key, $name AS value, $label FROM " . FabrikString::safeColName($item->db_table_name)
			. " $joinSQL $whereSQL) AS t";
		}
	}

	/**
	 * Get sum query
	 *
	 * @param   object  &$listModel  List model
	 * @param   array   $labels      Label
	 *
	 * @return string
	 */

	protected function getSumQuery(&$listModel, $labels = array())
	{
		if (count($labels) == 0)
		{
			$label = "'calc' AS label";
		}
		else
		{
			$label = 'CONCAT(' . implode(', " & " , ', $labels) . ')  AS label';
		}

		$item = $listModel->getTable();
		$joinSQL = $listModel->buildQueryJoin();
		$whereSQL = $listModel->buildQueryWhere();
		$name = $this->getFullName(false, false);
		$groupModel = $this->getGroup();

		if ($groupModel->isJoin())
		{
			// Element is in a joined column - lets presume the user wants to sum all cols, rather than reducing down to the main cols totals
			return "SELECT SUM($name) AS value, $label FROM " . FabrikString::safeColName($item->db_table_name) . " $joinSQL $whereSQL";
		}
		else
		{
			// Need to do first query to get distinct records as if we are doing left joins the sum is too large
			return "SELECT SUM(value) AS value, label
			FROM (SELECT DISTINCT $item->db_primary_key, $name AS value, $label FROM " . FabrikString::safeColName($item->db_table_name)
			. " $joinSQL $whereSQL) AS t";
		}
	}

	/**
	 * Get a custom query
	 *
	 * @param   object  &$listModel  list
	 * @param   string  $label       label
	 *
	 * @return  string
	 */

	protected function getCustomQuery(&$listModel, $label = "'calc'")
	{
		$params = $this->getParams();
		$custom_query = $params->get('custom_calc_query', '');
		$item = $listModel->getTable();
		$joinSQL = $listModel->buildQueryJoin();
		$whereSQL = $listModel->buildQueryWhere();
		$name = $this->getFullName(false, false);
		$groupModel = $this->getGroup();

		if ($groupModel->isJoin())
		{
			// Element is in a joined column - lets presume the user wants to sum all cols, rather than reducing down to the main cols totals
			// $custom_query = sprintf($custom_query, $name);
			$custom_query = str_replace('%s', $name, $custom_query);

			return "SELECT $custom_query AS value, $label AS label FROM " . FabrikString::safeColName($item->db_table_name) . " $joinSQL $whereSQL";
		}
		else
		{
			// Need to do first query to get distinct records as if we are doing left joins the sum is too large
			// $custom_query = sprintf($custom_query, 'value');
			$custom_query = str_replace('%s', 'value', $custom_query);

			return "SELECT $custom_query AS value, label FROM (SELECT DISTINCT " . FabrikString::safeColName($item->db_table_name)
			. ".*, $name AS value, $label AS label FROM " . FabrikString::safeColName($item->db_table_name) . " $joinSQL $whereSQL) AS t";
		}
	}

	/**
	 * Get a query for our median query
	 *
	 * @param   object  &$listModel  List
	 * @param   array   $labels      Label
	 *
	 * @return string
	 */

	protected function getMedianQuery(&$listModel, $labels = array())
	{
		if (count($labels) == 0)
		{
			$label = "'calc' AS label";
		}
		else
		{
			$label = 'CONCAT(' . implode(', " & " , ', $labels) . ')  AS label';
		}

		$item = $listModel->getTable();
		$joinSQL = $listModel->buildQueryJoin();
		$whereSQL = $listModel->buildQueryWhere();

		return "SELECT {$this->getFullName(false, false, false)} AS value, $label FROM " . FabrikString::safeColName($item->db_table_name)
		. " $joinSQL $whereSQL ";
	}

	/**
	 * Get a query for our count method
	 *
	 * @param   object  &$listModel  List
	 * @param   array   $labels      Labels
	 *
	 * @return string
	 */

	protected function getCountQuery(&$listModel, $labels = array())
	{
		if (count($labels) == 0)
		{
			$label = "'calc' AS label";
		}
		else
		{
			$label = 'CONCAT(' . implode(', " & " , ', $labels) . ')  AS label';
		}

		$db = FabrikWorker::getDbo();
		$item = $listModel->getTable();
		$joinSQL = $listModel->buildQueryJoin();
		$whereSQL = $listModel->buildQueryWhere();
		$name = $this->getFullName(false, false);

		// $$$ hugh - need to account for 'count value' here!
		$params = $this->getParams();
		$count_condition = $params->get('count_condition', '');

		if (!empty($count_condition))
		{
			if (!empty($whereSQL))
			{
				$whereSQL .= " AND $name = " . $db->quote($count_condition);
			}
			else
			{
				$whereSQL = "WHERE $name = " . $db->quote($count_condition);
			}
		}

		$groupModel = $this->getGroup();

		if ($groupModel->isJoin())
		{
			// Element is in a joined column - lets presume the user wants to sum all cols, rather than reducing down to the main cols totals
			return "SELECT COUNT($name) AS value, $label FROM " . FabrikString::safeColName($item->db_table_name) . " $joinSQL $whereSQL";
		}
		else
		{
			// Need to do first query to get distinct records as if we are doing left joins the sum is too large
			$query = "SELECT COUNT(value) AS value, label
			FROM (SELECT DISTINCT $item->db_primary_key, $name AS value, $label FROM " . FabrikString::safeColName($item->db_table_name)
			. " $joinSQL $whereSQL) AS t";
		}

		return $query;
	}

	/**
	 * Work out the calculation group by's to apply:
	 *
	 * - If group_by is assigned in the app input
	 * - If no group_by request then check the list models group by and add that
	 *
	 * @param   string  $splitParam  Element parameter name containing the calculation split option
	 * @param   object  $listModel   List model
	 *
	 * @since   3.0.8
	 *
	 * @return  array  Group by element names
	 */
	protected function calcGroupBys($splitParam, $listModel)
	{
		$app = JFactory::getApplication();
		$pluginManager = FabrikWorker::getPluginManager();
		$requestGroupBy = $app->input->get('group_by', '');

		if ($requestGroupBy == '0')
		{
			$requestGroupBy = '';
		}

		$groupBys = array();
		$splitName = array();

		if ($requestGroupBy !== '')
		{
			$formModel = $this->getFormModel();
			$requestGroupBy = $formModel->getElement($requestGroupBy)->getElement()->id;
			$groupBys[] = $requestGroupBy;
		}
		else
		{
			$listGroupBy = $listModel->getTable()->group_by;

			if ($listGroupBy !== '')
			{
				$groupBys[] = $listGroupBy;
			}
		}

		$params = $this->getParams();
		$splitSum = $params->get($splitParam, null);

		if (!is_null($splitSum))
		{
			$groupBys[] = $splitSum;
		}

		foreach ($groupBys as &$gById)
		{
			$plugin = $pluginManager->getElementPlugin($gById);
			$sName = method_exists($plugin, 'getJoinLabelColumn') ? $plugin->getJoinLabelColumn() : $plugin->getFullName(false, false, false);

			if (!stristr($sName, 'CONCAT'))
			{
				$gById = FabrikString::safeColName($sName);
			}
			else
			{
				// If its a concat - can we use the key value as the group by name
				if (method_exists($plugin, 'getJoinValueColumn'))
				{
					$sName = $plugin->getJoinValueColumn();
					$gById = FabrikString::safeColName($sName);
				}
			}
		}

		return $groupBys;
	}

	/**
	 * If the calculation query has had to convert the data to a machine format, use
	 * this function to convert back to human readable format. E.g. time element
	 * calcs in seconds but we'd want to convert back into h:m:s
	 *
	 * @param   array  &$rows  Calculaton values
	 *
	 * @return  void
	 */

	protected function formatCalValues(&$rows)
	{
	}

	/**
	 * Calculation: sum
	 * can be overridden in element class
	 *
	 * @param   object  &$listModel  List model
	 *
	 * @return  array
	 */

	public function sum(&$listModel)
	{
		$db = $listModel->getDb();
		$app = JFactory::getApplication();
		$params = $this->getParams();
		$item = $listModel->getTable();
		$splitSum = $params->get('sum_split', '');
		$groupBys = $this->calcGroupBys('sum_split', $listModel);
		$split = empty($groupBys) ? false : true;
		$calcLabel = $params->get('sum_label', JText::_('COM_FABRIK_SUM'));

		if ($split)
		{
			$pluginManager = FabrikWorker::getPluginManager();
			$plugin = $pluginManager->getElementPlugin($splitSum);
			$sql = $this->getSumQuery($listModel, $groupBys) . ' GROUP BY label';

			$sql = $listModel->pluginQuery($sql);
			$db->setQuery($sql);
			$results2 = $db->loadObjectList('label');
			$this->formatCalValues($results2);
			$uberTotal = 0;

			foreach ($results2 as $pair)
			{
				$uberTotal += $pair->value;
			}

			$uberObject = new stdClass;
			$uberObject->value = $uberTotal;
			$uberObject->label = JText::_('COM_FABRIK_TOTAL');
			$uberObject->class = 'splittotal';
			$uberObject->special = true;
			$results2[] = $uberObject;
			$results = $this->formatCalcSplitLabels($results2, $plugin, 'sum');
		}
		else
		{
			// Need to add a group by here as well as if the ONLY_FULL_GROUP_BY SQL mode is enabled an error is produced
			$sql = $this->getSumQuery($listModel) . ' GROUP BY label';
			$sql = $listModel->pluginQuery($sql);
			$db->setQuery($sql);
			$results = $db->loadObjectList('label');
			$this->formatCalValues($results);
		}

		$res = $this->formatCalcs($results, $calcLabel, $split);

		return array($res, $results);
	}

	/**
	 * calculation: avarage
	 * can be overridden in element class
	 *
	 * @param   object  &$listModel  list model
	 *
	 * @return  string	result
	 */

	public function avg(&$listModel)
	{
		$db = $listModel->getDb();
		$params = $this->getParams();
		$splitAvg = $params->get('avg_split', '');
		$item = $listModel->getTable();
		$calcLabel = $params->get('avg_label', JText::_('COM_FABRIK_AVERAGE'));
		$groupBys = $this->calcGroupBys('avg_split', $listModel);

		$split = empty($groupBys) ? false : true;

		if ($split)
		{
			$pluginManager = FabrikWorker::getPluginManager();
			$plugin = $pluginManager->getElementPlugin($splitAvg);
			$sql = $this->getAvgQuery($listModel, $groupBys) . " GROUP BY label";
			$sql = $listModel->pluginQuery($sql);
			$db->setQuery($sql);
			$results2 = $db->loadObjectList('label');
			$this->formatCalValues($results2);
			$uberTotal = 0;

			foreach ($results2 as $pair)
			{
				$uberTotal += $pair->value;
			}

			$uberObject = new stdClass;
			$uberObject->value = $uberTotal / count($results2);
			$uberObject->label = JText::_('COM_FABRIK_AVERAGE');
			$uberObject->special = true;
			$uberObject->class = 'splittotal';
			$results2[] = $uberObject;

			$results = $this->formatCalcSplitLabels($results2, $plugin, 'avg');
		}
		else
		{
			// Need to add a group by here as well as if the ONLY_FULL_GROUP_BY SQL mode is enabled an error is produced
			$sql = $this->getAvgQuery($listModel) . " GROUP BY label";
			$sql = $listModel->pluginQuery($sql);
			$db->setQuery($sql);
			$results = $db->loadObjectList('label');
			$this->formatCalValues($results);
		}

		$res = $this->formatCalcs($results, $calcLabel, $split);

		return array($res, $results);
	}

	/**
	 * Get the sprintf format string
	 *
	 * @since 3.0.4
	 *
	 * @return string
	 */

	public function getFormatString()
	{
		$params = $this->getParams();

		return $params->get('text_format_string');
	}

	/**
	 * calculation: median
	 * can be overridden in element class
	 *
	 * @param   object  &$listModel  list model
	 *
	 * @return  string 	result
	 */

	public function median(&$listModel)
	{
		$db = $listModel->getDb();
		$item = $listModel->getTable();
		$element = $this->getElement();
		$joinSQL = $listModel->buildQueryJoin();
		$whereSQL = $listModel->buildQueryWhere();
		$params = $this->getParams();
		$splitMedian = $params->get('median_split', '');
		$groupBys = $this->calcGroupBys('sum_split', $listModel);
		$split = empty($groupBys) ? false : true;
		$format = $this->getFormatString();
		$res = '';
		$calcLabel = $params->get('median_label', JText::_('COM_FABRIK_MEDIAN'));
		$results = array();

		if ($split)
		{
			$pluginManager = FabrikWorker::getPluginManager();
			$plugin = $pluginManager->getElementPlugin($splitMedian);
			$sql = $this->getMedianQuery($listModel, $groupBys) . ' GROUP BY label ';
			$sql = $listModel->pluginQuery($sql);
			$db->setQuery($sql);
			$results2 = $db->loadObjectList();
			$results = $this->formatCalcSplitLabels($results2, $plugin, 'median');
		}
		else
		{
			$sql = $this->getMedianQuery($listModel);
			$sql = $listModel->pluginQuery($sql);
			$db->setQuery($sql);
			$res = $this->_median($db->loadColumn());
			$o = new stdClass;

			if ($format != '')
			{
				$res = sprintf($format, $res);
			}

			$o->value = $res;
			$label = $this->getListHeading();
			$o->elLabel = $label;
			$o->calLabel = $calcLabel;
			$o->label = 'calc';
			$results = array('calc' => $o);
		}

		$res = $this->formatCalcs($results, $calcLabel, $split, true, false);

		return array($res, $results);
	}

	/**
	 * calculation: count
	 * can be overridden in element class
	 *
	 * @param   object  &$listModel  list model
	 *
	 * @return  string	result
	 */

	public function count(&$listModel)
	{
		$db = $listModel->getDb();
		$listModel->clearTable();
		$item = $listModel->getTable();
		$element = $this->getElement();
		$params = $this->getParams();
		$calcLabel = $params->get('count_label', JText::_('COM_FABRIK_COUNT'));
		$splitCount = $params->get('count_split', '');

		$groupBys = $this->calcGroupBys('count_split', $listModel);
		$split = empty($groupBys) ? false : true;

		if ($split)
		{
			$pluginManager = FabrikWorker::getPluginManager();
			$plugin = $pluginManager->getElementPlugin($splitCount);
			$sql = $this->getCountQuery($listModel, $groupBys) . " GROUP BY label ";
			$sql = $listModel->pluginQuery($sql);
			$db->setQuery($sql);
			$results2 = $db->loadObjectList('label');
			$uberTotal = 0;
			/*
			 * Removes values from display when split on used:
			* see http://www.fabrikar.com/forums/index.php?threads/calculation-split-on-problem.32035/
			foreach ($results2 as $k => &$r)
			{
			if ($k == '')
			{
			unset($results2[$k]);
			}
			}
			*/
			foreach ($results2 as $pair)
			{
				$uberTotal += $pair->value;
			}

			$uberObject = new stdClass;
			$uberObject->value = count($results2) == 0 ? 0 : $uberTotal;
			$uberObject->label = JText::_('COM_FABRIK_TOTAL');
			$uberObject->class = 'splittotal';
			$uberObject->special = true;
			$results = $this->formatCalcSplitLabels($results2, $plugin, 'count');
			$results[JText::_('COM_FABRIK_TOTAL')] = $uberObject;
		}
		else
		{
			// Need to add a group by here as well as if the ONLY_FULL_GROUP_BY SQL mode is enabled an error is produced
			$sql = $this->getCountQuery($listModel) . ' GROUP BY label ';
			$sql = $listModel->pluginQuery($sql);
			$db->setQuery($sql);
			$results = $db->loadObjectList('label');
		}

		$res = $this->formatCalcs($results, $calcLabel, $split, false, false);

		return array($res, $results);
	}

	/**
	 * calculation: custom_calc
	 * can be overridden in element class
	 *
	 * @param   object  &$listModel  list model
	 *
	 * @return  array
	 */

	public function custom_calc(&$listModel)
	{
		$db = $listModel->getDb();
		$params = $this->getParams();
		$item = $listModel->getTable();
		$splitCustom = $params->get('custom_calc_split', '');
		$split = $splitCustom == '' ? false : true;
		$calcLabel = $params->get('custom_calc_label', JText::_('COM_FABRIK_CUSTOM'));

		if ($split)
		{
			$pluginManager = FabrikWorker::getPluginManager();
			$plugin = $pluginManager->getElementPlugin($splitCustom);
			$splitName = method_exists($plugin, 'getJoinLabelColumn') ? $plugin->getJoinLabelColumn() : $plugin->getFullName(false, false);
			$splitName = FabrikString::safeColName($splitName);
			$sql = $this->getCustomQuery($listModel, $splitName) . ' GROUP BY label';
			$sql = $listModel->pluginQuery($sql);
			$db->setQuery($sql);
			$results2 = $db->loadObjectList('label');
			$results = $this->formatCalcSplitLabels($results2, $plugin, 'custom_calc');
		}
		else
		{
			// Need to add a group by here as well as if the ONLY_FULL_GROUP_BY SQL mode is enabled an error is produced
			$sql = $this->getCustomQuery($listModel) . ' GROUP BY label';
			$sql = $listModel->pluginQuery($sql);
			$db->setQuery($sql);
			$results = $db->loadObjectList('label');
		}

		$res = $this->formatCalcs($results, $calcLabel, $split);

		return array($res, $results);
	}

	/**
	 * Format the labels for calculations when they are split
	 *
	 * @param   array   &$results2  calculation results
	 * @param   object  &$plugin    element that the data is SPLIT on
	 * @param   string  $type       of calculation
	 *
	 * @return  unknown_type
	 */

	protected function formatCalcSplitLabels(&$results2, &$plugin, $type = '')
	{
		$results = array();
		$tomerge = array();
		$name = $plugin->getFullName(true, false);

		// $$$ hugh - avoid PHP warning if $results2 is NULL
		if (empty($results2))
		{
			return $results;
		}

		foreach ($results2 as $key => $val)
		{
			if (isset($val->special) && $val->special)
			{
				// Don't inlcude special values (ubers) in $tomerge, otherwise total sum added to first value
				$results[$val->label] = $val;
				continue;
			}

			if ($plugin->hasSubElements)
			{
				$val->label = ($type == 'median') ? $plugin->getLabelForValue($val->label) : $plugin->getLabelForValue($key, $key);
			}
			else
			{
				$d = new stdClass;
				$d->$name = $val->label;
				$val->label = $plugin->renderListData($val->label, $d);
			}

			if (array_key_exists($val->label, $results))
			{
				/** $$$ rob the $result data is keyed on the raw database result - however, we are intrested in
				 * keying on the formatted table result (e.g. allows us to group date entries by year)
				 */
				if ($results[$val->label] !== '')
				{
					$tomerge[$val->label][] = $results[$val->label]->value;
				}

				$results[$val->label] = '';
				$tomerge[$val->label][] = $val->value;
			}
			else
			{
				$results[$val->label] = $val;
			}
		}

		foreach ($tomerge as $label => $data)
		{
			$o = new stdClass;

			switch ($type)
			{
				case 'avg':
					$o->value = $this->simpleAvg($data);
					break;
				case 'sum':
					$o->value = $this->simpleSum($data);
					break;
				case 'median':
					$o->value = $this->_median($data);
					break;
				case 'count':
					$o->value = count($data);
					break;
				case 'custom_calc':
					$params = $this->getParams();
					$custom_calc_php = $params->get('custom_calc_php', '');

					if (!empty($custom_calc_php))
					{
						$o->value = @eval((string) stripslashes($custom_calc_php));
						FabrikWorker::logEval($custom_calc_php, 'Caught exception on eval of ' . $name . ': %s');
					}
					else
					{
						$o->value = $data;
					}

					break;
				default:
					$o->value = $data;
					break;
			}

			$o->label = $label;
			$results[$label] = $o;
		}

		return $results;
	}

	/**
	 * find an average from a set of data
	 * can be overwritten in plugin - see date for example of averaging dates
	 *
	 * @param   array  $data  to average
	 *
	 * @return  string  average result
	 */

	public function simpleAvg($data)
	{
		return $this->simpleSum($data) / count($data);
	}

	/**
	 * find the sum from a set of data
	 * can be overwritten in plugin - see date for example of averaging dates
	 *
	 * @param   array  $data  to sum
	 *
	 * @return  string  sum result
	 */

	public function simpleSum($data)
	{
		return array_sum($data);
	}

	/**
	 * Take the results form a calc and create the string that can be used to summarize them
	 *
	 * @param   array   &$results       calculation results
	 * @param   string  $calcLabel      calc label
	 * @param   bool    $split          is the data split
	 * @param   bool    $numberFormat   should we applpy any number formatting
	 * @param   bool    $sprintFFormat  should we apply the text_format_string ?
	 *
	 * @return  string
	 */

	protected function formatCalcs(&$results, $calcLabel, $split = false, $numberFormat = true, $sprintFFormat = true)
	{
		settype($results, 'array');
		$res = array();
		$res[] = $split ? '<dl>' : '<ul class="fabrikRepeatData">';
		$l = '<span class="calclabel">' . $calcLabel . '</span>';
		$res[] = $split ? '<dt>' . $l . '</dt>' : '<li>' . $l;
		$element = $this->getElement();
		$format = $this->getFormatString();
		$label = $this->getListHeading();

		foreach ($results as $key => $o)
		{
			$o->label = ($o->label == 'calc') ? '' : $o->label;
			$o->elLabel = $label . ' ' . $o->label;

			if ($numberFormat)
			{
				$o->value = $this->numberFormat($o->value);
			}

			if ($format != '' && $sprintFFormat)
			{
				$o->value = sprintf($format, $o->value);
			}

			$o->calLabel = $calcLabel;
			$class = isset($o->class) ? ' class="' . $o->class . '"' : '';

			if ($split)
			{
				$res[] = '<dd' . $class . '><span class="calclabel">' . $o->label . ':</span> ' . $o->value . '</dd>';
			}
			else
			{
				$res[] = $o->value . '</li>';
			}
		}

		ksort($results);
		$res[] = $split ? '</dl>' : '</ul>';

		return implode("\n", $res);
	}

	/**
	 * Get median
	 *
	 * @param   array  $results  set of results to get median from
	 *
	 * @return  string	median value
	 */

	private function _median($results)
	{
		$results = (array) $results;
		sort($results);

		if ((count($results) % 2) == 1)
		{
			/* odd */
			$midKey = floor(count($results) / 2);

			return $results[$midKey];
		}
		else
		{
			$midKey = floor(count($results) / 2) - 1;
			$midKey2 = floor(count($results) / 2);

			return $this->simpleAvg(array(JArrayHelper::getValue($results, $midKey), JArrayHelper::getValue($results, $midKey2)));
		}
	}

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 *
	 * @param   int  $repeatCounter  Repeat group counter
	 *
	 * @return  array
	 */

	public function elementJavascript($repeatCounter)
	{
		return array();
	}

	/**
	 * Get JS code for ini element list js
	 * Overwritten in plugin classes
	 *
	 * @return string
	 */

	public function elementListJavascript()
	{
		return '';
	}

	/**
	 * Create a class for the elements default javascript options
	 *
	 * @param   int  $repeatCounter  repeat group counter
	 *
	 * @return  object	options
	 */

	public function getElementJSOptions($repeatCounter)
	{
		$element = $this->getElement();
		$opts = new stdClass;
		$data = $this->getFormModel()->data;
		$opts->repeatCounter = $repeatCounter;
		$opts->editable = ($this->canView() && !$this->canUse()) ? false : $this->isEditable();
		$opts->value = $this->getValue($data, $repeatCounter);
		$opts->label = $element->label;
		$opts->defaultVal = $this->getDefaultValue($data);
		$opts->inRepeatGroup = $this->getGroup()->canRepeat() == 1;
		$opts->fullName = $this->getFullName(true, false);
		$opts->watchElements = $this->validator->jsWatchElements($repeatCounter);
		$groupModel = $this->getGroup();
		$opts->canRepeat = (bool) $groupModel->canRepeat();
		$opts->isGroupJoin = (bool) $groupModel->isJoin();
		$validations = $this->validator->findAll();
		$opts->validations = empty($validations) ? false : true;

		if ($this->isJoin())
		{
			$opts->joinid = (int) $this->getJoinModel()->getJoin()->id;
		}
		else
		{
			$opts->joinid = (int) $groupModel->getGroup()->join_id;
		}

		return $opts;
	}

	/**
	 * Does the element use the WYSWYG editor
	 *
	 * @return  bool	use wysiwyg editor
	 */

	public function useEditor()
	{
		return false;
	}

	/**
	 * Processes uploaded data
	 *
	 * @return  void
	 */

	public function processUpload()
	{
	}

	/**
	 * Get the class to manage the form element
	 * to ensure that the file is loaded only once
	 *
	 * @param   array   &$srcs   Scripts previously loaded
	 * @param   string  $script  Script to load once class has loaded
	 * @param   array   &$shim   Dependant class names to load before loading the class - put in requirejs.config shim
	 *
	 * @return void
	 */

	public function formJavascriptClass(&$srcs, $script = '', &$shim = array())
	{
		$name = $this->getElement()->plugin;
		$ext = FabrikHelperHTML::isDebug() ? '.js' : '-min.js';
		$shimKey = 'element/' . $name . '/' . $name;

		if (!array_key_exists($shimKey, $shim))
		{
			$s = new stdClass;
			$s->deps = array('fab/element');
			$shim['element/' . $name . '/' . $name] = $s;
		}

		static $elementclasses;

		if (!isset($elementclasses))
		{
			$elementclasses = array();
		}
		// Load up the default scipt
		if ($script == '')
		{
			$script = 'plugins/fabrik_element/' . $name . '/' . $name . $ext;
		}

		if (empty($elementclasses[$script]))
		{
			$srcs[] = $script;
			$elementclasses[$script] = 1;
		}
	}

	/**
	 * load js file for element when in list view
	 *
	 * @param   array  &$srcs  JS scripts to load
	 *
	 * @return  null
	 */

	public function tableJavascriptClass(&$srcs)
	{
		$p = $this->getElement()->plugin;
		$src = 'plugins/fabrik_element/' . $p . '/list-' . $p . '.js';

		if (JFile::exists($src))
		{
			$srcs[] = $src;
		}
	}

	/**
	 * Can be overwritten in plugin classes
	 * eg if changing from db join to field we need to remove the join
	 * entry from the #__{package}_joins table
	 *
	 * @param   object  &$row  that is going to be updated
	 *
	 * @return null
	 */

	public function beforeSave(&$row)
	{
		$safeHtmlFilter = JFilterInput::getInstance(null, null, 1, 1);
		$post = $safeHtmlFilter->clean($_POST, 'array');
		$post = $post['jform'];
		$dbjoinEl = (is_subclass_of($this, 'PlgFabrik_ElementDatabasejoin') || get_class($this) == 'PlgFabrik_ElementDatabasejoin');
		/**
		 * $$$ hugh - added test for empty id, i.e. new element, otherwise we try and delete a crapload of join table rows
		 * we shouldn't be deleting!  Also adding defensive code to deleteJoins() to test for empty ID.
		*/

		if (!empty($post['id']) && !$this->isJoin() && !$dbjoinEl)
		{
			$this->deleteJoins((int) $post['id']);
		}
	}

	/**
	 * Delete joins
	 *
	 * @param   int  $id  element id
	 *
	 * @return  null
	 */

	protected function deleteJoins($id)
	{
		// $$$ hugh - bail if no $id specified
		if (empty($id))
		{
			return;
		}

		$element = $this->getElement();
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->delete('#__{package}_joins')->where('element_id = ' . $id);
		$db->setQuery($query);
		$db->execute();

		$query->clear();
		$query->select('j.id AS jid')->from('#__{package}_elements AS e')->join('INNER', ' #__{package}_joins AS j ON j.element_id = e.id')
		->where('e.parent_id = ' . $id);
		$db->setQuery($query);
		$join_ids = $db->loadColumn();

		if (!empty($join_ids))
		{
			$query->clear();
			$query->delete('#__{package}_joins')->where('id IN (' . implode(',', $join_ids) . ')');
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * If your element risks not to post anything in the form (e.g. check boxes with none checked)
	 * the this function will insert a default value into the database
	 *
	 * @param   array  &$data  form data
	 *
	 * @return  array  form data
	 */

	public function getEmptyDataValue(&$data)
	{
	}

	/**
	 * Used to format the data when shown in the form's email
	 *
	 * @param   mixed  $value          element's data
	 * @param   array  $data           form records data
	 * @param   int    $repeatCounter  repeat group counter
	 *
	 * @return  string	formatted value
	 */

	public function getEmailValue($value, $data = array(), $repeatCounter = 0)
	{
		if ($this->inRepeatGroup && is_array($value))
		{
			$val = array();

			foreach ($value as $v2)
			{
				$val[] = $this->getIndEmailValue($v2, $data, $repeatCounter);
			}
		}
		else
		{
			$val = $this->getIndEmailValue($value, $data, $repeatCounter);
		}

		return $val;
	}

	/**
	 * Turn form value into email formatted value
	 *
	 * @param   mixed  $value          Element value
	 * @param   array  $data           Form data
	 * @param   int    $repeatCounter  Group repeat counter
	 *
	 * @return  string  email formatted value
	 */

	protected function getIndEmailValue($value, $data = array(), $repeatCounter = 0)
	{
		return $value;
	}

	/**
	 * Is the element an upload element
	 *
	 * @return boolean
	 */

	public function isUpload()
	{
		return $this->is_upload;
	}

	/**
	 * If a database join element's value field points to the same db field as this element
	 * then this element can, within modifyJoinQuery, update the query.
	 * E.g. if the database join element points to a file upload element then you can replace
	 * the file path that is the standard $val with the html to create the image
	 *
	 * @param   string  $val   value
	 * @param   string  $view  form or list
	 *
	 * @deprecated - doesn't seem to be used
	 *
	 * @return  string	modified val
	 */

	protected function modifyJoinQuery($val, $view = 'form')
	{
		return $val;
	}

	/**
	 * not used
	 *
	 * @deprecated
	 *
	 * @return void
	 */

	public function ajax_loadTableFields()
	{
		$db = FabrikWorker::getDbo();
		$listModel = JModelLegacy::getInstance('List', 'FabrikFEModel');
		$app = JFactory::getApplication();
		$input = $app->input;
		$this->_cnnId = $input->getInt('cid', 0);
		$tbl = $db->quoteName($input->get('table'));
		$fieldDropDown = $listModel->getFieldsDropDown($this->_cnnId, $tbl, '-', false, 'params[join_val_column]');
		$fieldDropDown2 = $listModel->getFieldsDropDown($this->_cnnId, $tbl, '-', false, 'params[join_key_column]');
		echo "$('addJoinVal').innerHTML = '$fieldDropDown';";
		echo "$('addJoinKey').innerHTML = '$fieldDropDown2';";
	}

	/**
	 * Get join row
	 *
	 * @return  JTable	join table or false if not loaded
	 */

	protected function getJoin()
	{
		if ($this->isJoin())
		{
			return $this->getJoinModel()->getJoin();
		}

		return false;
	}

	/**
	 * Get database field description
	 *
	 * @return  string  db field type
	 */

	public function getFieldDescription()
	{
		$element = strtolower(str_ireplace('PlgFabrik_Element', '', get_class($this)));
		$plugin = JPluginHelper::getPlugin('fabrik_element', $element);
		$fparams = new JRegistry($plugin->params);
		$p = $this->getParams();

		if ($this->encryptMe())
		{
			return 'BLOB';
		}

		$group = $this->getGroup();

		if ($group->isJoin() == 0 && $group->canRepeat())
		{
			return "TEXT";
		}
		else
		{
			$size = $p->get('maxlength', $this->fieldSize);
			$objtype = sprintf($this->fieldDesc, $size);
		}

		$objtype = $fparams->get('defaultFieldType', $objtype);

		return $objtype;
	}

	/**
	 * Trigger called when a row is deleted, can be used to delete images previously uploaded
	 *
	 * @param   array  $groups  grouped data of rows to delete
	 *
	 * @return  void
	 */

	public function onDeleteRows($groups)
	{
	}

	/**
	 * Trigger called when a form or group row is stored.
	 * Ignores the element if it is a join chx/multi select
	 *
	 * @param   array  &$data          Data to store
	 * @param   int    $repeatCounter  Repeat group index
	 *
	 * @return  bool  If false, data should not be added.
	 */

	public function onStoreRow(&$data, $repeatCounter = 0)
	{
		if ($this->isJoin())
		{
			return false;
		}

		$element = $this->getElement();

		// We should not process this element if it is unpublished
		// Unpublished elements may not be in a valid state and may cause an error (white-screen)
		if (!$element->published)
		{
			return false;
		}

		$shortName = $element->name;
		$listModel = $this->getListModel();

		if ($this->encryptMe())
		{
			$listModel->encrypt[] = $shortName;
		}

		$formModel = $this->getFormModel();
		$name = $this->getFullName(true, false);

		/**
		 * @TODO - fix this to use formData instead of formDataWithTableName,
		 * which we need to deprecate.
		 */
		if (!array_key_exists($name, $formModel->formDataWithTableName))
		{
			$this->getEmptyDataValue($data);
		}

		$v = $this->getValue($formModel->formDataWithTableName, $repeatCounter);

		if (!$this->ignoreOnUpdate($v))
		{
			$data[$shortName] = $v;
		}

		return true;
	}

	/**
	 * Shows the data formatted for the list view
	 *
	 * @param   string    $data      elements data
	 * @param   stdClass  &$thisRow  all the data in the lists current row
	 *
	 * @return  string	formatted value
	 */

	public function renderListData($data, stdClass &$thisRow)
	{
		$params = $this->getParams();
		$listModel = $this->getListModel();
		$data = FabrikWorker::JSONtoData($data, true);

		foreach ($data as $i => &$d)
		{
			if ($params->get('icon_folder') == '1')
			{
				// $$$ rob was returning here but that stoped us being able to use links and icons together
				$d = $this->replaceWithIcons($d, 'list', $listModel->getTmpl());
			}

			$d = $this->rollover($d, $thisRow, 'list');
			$d = $listModel->_addLink($d, $this, $thisRow, $i);
		}

		return $this->renderListDataFinal($data);
	}

	/**
	 * Final prepare data function called from renderListData(), converts data to string and if needed
	 * encases in <ul> (for repeating data)
	 *
	 * @param   array  $data  list cell data
	 *
	 * @return  string	cell data
	 */

	protected function renderListDataFinal($data)
	{
		if (is_array($data) && count($data) > 1)
		{
			if (!array_key_exists(0, $data))
			{
				// Occurs if we have created a list from an exisitng table whose data contains json objects (e.g. #__users.params)
				$obj = JArrayHelper::toObject($data);
				$data = array();
				$data[0] = $obj;
			}
			// If we are storing info as json the data will contain an array of objects
			if (is_object($data[0]))
			{
				foreach ($data as &$o)
				{
					$this->convertDataToString($o);
				}
			}

			$r = '<ul class="fabrikRepeatData"><li>' . implode('</li><li>', $data) . '</li></ul>';
		}
		else
		{
			$r = empty($data) ? '' : array_shift($data);
		}

		return $r;
	}

	/**
	 * Convert an object or array into a <ul>
	 *
	 * @param   mixed  &$o  data to convert
	 *
	 * @return  void
	 */

	protected function convertDataToString(&$o)
	{
		if (is_object($o))
		{
			$s = '<ul>';

			foreach ($o as $k => $v)
			{
				if (!is_string($v))
				{
					$v = json_encode($v);
				}

				$s .= '<li>' . $v . '</li>';
			}

			$s .= '</ul>';
			$o = $s;
		}
	}

	/**
	 * Prepares the element data for CSV export
	 *
	 * @param   string  $data      Element data
	 * @param   object  &$thisRow  All the data in the lists current row
	 *
	 * @return  string	Formatted CSV export value
	 */

	public function renderListData_csv($data, &$thisRow)
	{
		return $data;
	}

	/**
	 * Builds some html to allow certain elements to display the option to add in new options
	 * e.g. pciklists, dropdowns radiobuttons
	 *
	 * @param   bool  $repeatCounter  repeat group counter
	 * @param   bool  $onlylabel      only show the label - overrides standard element settings
	 *
	 * @return  string
	 */

	protected function getAddOptionFields($repeatCounter, $onlylabel = false)
	{
		$params = $this->getParams();

		if (!$params->get('allow_frontend_addto'))
		{
			return;
		}

		$id = $this->getHTMLId($repeatCounter);
		$valueid = $id . '_ddVal';
		$labelid = $id . '_ddLabel';
		$value = '<input class="inputbox text" id="' . $valueid . '" name="addPicklistValue" />';
		$label = '<input class="inputbox text" id="' . $labelid . '" name="addPicklistLabel" />';
		$str[] = '<a href="#" title="' . JText::_('COM_FABRIK_ADD') . '" class="btn btn-info toggle-addoption">';
		$str[] = FabrikHelperHTML::image('plus.png', 'form', @$this->tmpl, array('alt' => JText::_('COM_FABRIK_ADD')));
		$str[] = '</a>';
		$str[] = '<div style="clear:left">';
		$str[] = '<div class="addoption"><div>' . JText::_('COM_FABRIK_ADD_A_NEW_OPTION_TO_THOSE_ABOVE') . '</div>';

		if (!$params->get('allowadd-onlylabel') && $params->get('savenewadditions'))
		{
			// $$$ rob dont wrap in <dl> as the html is munged when rendered inside form tab template
			$str[] = '<label for="' . $valueid . '">' . JText::_('COM_FABRIK_VALUE') . '</label>';
			$str[] = $value;

			if (!$onlylabel)
			{
				$str[] = '<label for="' . $labelid . '">' . JText::_('COM_FABRIK_LABEL') . '</label>';
				$str[] = $label;
			}
		}
		else
		{
			$str[] = $label;
		}

		$str[] = '<input class="button btn btn-success" type="button" id="' . $id . '_dd_add_entry" value="' . JText::_('COM_FABRIK_ADD') . '" />';
		$str[] = $this->getHiddenField($id . "_additions", '', $id . "_additions");
		$str[] = '</div>';
		$str[] = '</div>';

		return implode("\n", $str);
	}

	/**
	 * Does the element force the form to submit via AJAX
	 *
	 * @deprecated - not used
	 *
	 * @return  bool	true if the element type forces the form to
	 */

	public function requiresAJAXSubmit()
	{
		return false;
	}

	/**
	 * Determine if the element should run its validation plugins on form submission
	 *
	 * @return  bool	default true
	 */

	public function mustValidate()
	{
		return true;
	}

	/**
	 * Get the name of the field to order the table data by
	 * can be overwritten in plugin class - but not currently done so
	 *
	 * @return string column to order by tablename___elementname and yes you can use aliases in the order by clause
	 */

	public function getOrderByName()
	{
		return $this->getFullName(true, false);
	}

	/**
	 * Not used
	 *
	 * @param   string  $rawval  raw value
	 *
	 * @deprecated - not used
	 *
	 * @return string
	 */

	public function getFilterLabel($rawval)
	{
		return $rawval;
	}

	/**
	 * Store the element params
	 *
	 * @return  bool
	 */

	public function storeAttribs()
	{
		$element = $this->getElement();

		if (!$element)
		{
			return false;
		}

		$db = FabrikWorker::getDbo(true);
		$element->params = $this->getParams()->toString();
		$query = $db->getQuery(true);
		$query->update('#__{package}_elements')->set('params = ' . $db->quote($element->params))->where('id = ' . (int) $element->id);
		$db->setQuery($query);
		$res = $db->execute();

		return $res;
	}

	/**
	 * load a new set of default properites and params for the element
	 * can be overridden in plugin class
	 *
	 * @return  object	element (id = 0)
	 */

	public function getDefaultProperties()
	{
		$user = JFactory::getUser();
		$now = JFactory::getDate()->toSql();
		$this->setId(0);
		$item = $this->getElement();
		$item->plugin = $this->_name;
		$item->params = $this->getDefaultAttribs();
		$item->created = $now;
		$item->created_by = $user->get('id');
		$item->created_by_alias = $user->get('username');
		$item->published = '1';
		$item->show_in_list_summary = '1';
		$item->link_to_detail = '1';

		return $item;
	}

	/**
	 * Get a json encoded string of the element default parameters
	 *
	 * @return  string
	 */

	public function getDefaultAttribs()
	{
		$o = new stdClass;
		$o->rollover = '';
		$o->comment = '';
		$o->sub_default_value = '';
		$o->sub_default_label = '';
		$o->element_before_label = 1;
		$o->allow_frontend_addtocheckbox = 0;
		$o->database_join_display_type = 'dropdown';
		$o->joinType = 'simple';
		$o->join_conn_id = -1;
		$o->date_table_format = 'Y-m-d';
		$o->date_form_format = 'Y-m-d H:i:s';
		$o->date_showtime = 0;
		$o->date_time_format = 'H:i';
		$o->date_defaulttotoday = 1;
		$o->date_firstday = 0;
		$o->multiple = 0;
		$o->allow_frontend_addtodropdown = 0;
		$o->password = 0;
		$o->maxlength = 255;
		$o->text_format = 'text';
		$o->integer_length = 6;
		$o->decimal_length = 2;
		$o->guess_linktype = 0;
		$o->disable = 0;
		$o->readonly = 0;
		$o->ul_max_file_size = 16000;
		$o->ul_email_file = 0;
		$o->ul_file_increment = 0;
		$o->upload_allow_folderselect = 1;
		$o->fu_fancy_upload = 0;
		$o->upload_delete_image = 1;
		$o->make_link = 0;
		$o->fu_show_image_in_table = 0;
		$o->image_library = 'gd2';
		$o->make_thumbnail = 0;
		$o->imagepath = '/';
		$o->selectImage_root_folder = '/';
		$o->image_front_end_select = 0;
		$o->show_image_in_table = 0;
		$o->image_float = 'none';
		$o->link_target = '_self';
		$o->radio_element_before_label = 0;
		$o->options_per_row = 4;
		$o->ck_options_per_row = 4;
		$o->allow_frontend_addtoradio = 0;
		$o->use_wysiwyg = 0;
		$o->my_table_data = 'id';
		$o->update_on_edit = 0;
		$o->view_access = 1;
		$o->show_in_rss_feed = 0;
		$o->show_label_in_rss_feed = 0;
		$o->icon_folder = -1;
		$o->use_as_row_class = 0;
		$o->filter_access = 1;
		$o->full_words_only = 0;
		$o->inc_in_adv_search = 1;
		$o->sum_on = 0;
		$o->sum_access = 0;
		$o->avg_on = 0;
		$o->avg_access = 0;
		$o->median_on = 0;
		$o->median_access = 0;
		$o->count_on = 0;
		$o->count_access = 0;

		return json_encode($o);
	}

	/**
	 * Do we need to include the lighbox js code
	 *
	 * @return  bool
	 */

	public function requiresLightBox()
	{
		return false;
	}

	/**
	 * Do we need to include the slideshow js code
	 *
	 * @return  bool
	 */

	public function requiresSlideshow()
	{
		return false;
	}

	/**
	 * Get Joomfish options
	 *
	 * @deprecated - not supporting joomfish
	 *
	 * @return  array	key=>value options
	 */

	public function getJoomfishOptions()
	{
		return array();
	}

	/**
	 * When filtering a table determine if the element's filter should be an exact match
	 * should take into account if the element is in a non-joined repeat group
	 *
	 * @param   string  $val  element value
	 *
	 * @return  bool
	 */

	public function isExactMatch($val)
	{
		$element = $this->getElement();
		$filterExactMatch = isset($val['match']) ? $val['match'] : $element->filter_exact_match;
		$group = $this->getGroup();

		if (!$group->isJoin() && $group->canRepeat())
		{
			$filterExactMatch = false;
		}

		return $filterExactMatch;
	}

	/**
	 * Not used
	 *
	 * @deprecated - not used
	 *
	 * @return boolean
	 */

	public function onAjax_getFolders()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$rDir = $input->get('dir');
		$folders = JFolder::folders($rDir);

		if ($folders === false)
		{
			// $$$ hugh - need to echo empty JSON array otherwise we break JS which assumes an array
			echo json_encode(array());

			return false;
		}

		sort($folders);
		echo json_encode($folders);
	}

	/**
	 * If used as a filter add in some JS code to watch observed filter element's changes
	 * when it changes update the contents of this elements dd filter's options
	 *
	 * @param   bool    $normal     is the filter a normal (true) or advanced filter
	 * @param   string  $container  container
	 *
	 * @return  void
	 */

	public function filterJS($normal, $container)
	{
		// Overwritten in plugin
	}

	/**
	 * Should the element's data be returned in the search all?
	 * Looks at the lists selected options, if its there looks at what search mode the list is using
	 * and determines if the selected element can be used.
	 *
	 * @param   bool    $advancedMode  Is the elements' list is extended search all mode?
	 * @param   string  $search        Search string
	 *
	 * @return  bool	true
	 */

	public function includeInSearchAll($advancedMode = false, $search = '')
	{
		if ($this->isJoin() && $advancedMode)
		{
			return false;
		}

		$listModel = $this->getListModel();
		$listParams = $listModel->getParams();
		$searchElements = $listParams->get('list_search_elements', '');

		if ($searchElements === '')
		{
			return false;
		}

		$searchElements = json_decode($searchElements);

		if (!isset($searchElements->search_elements))
		{
			return false;
		}

		if (in_array($this->getId(), $searchElements->search_elements))
		{
			$advancedMode = $listParams->get('search-mode-advanced');

			return $this->canIncludeInSearchAll($advancedMode);
		}
	}

	/**
	 * Is it possible to include the element in the  Search all query?
	 * true if basic search
	 * true/false if advanced search
	 *
	 * @param   bool  $advancedMode  Is the list using advanced search
	 *
	 * @since  3.1b
	 *
	 * @return boolean
	 */

	public function canIncludeInSearchAll($advancedMode)
	{
		$params = $this->getParams();

		if (!$advancedMode)
		{
			return true;
		}

		if ($this->ignoreSearchAllDefault)
		{
			return false;
		}

		$format = $params->get('text_format');

		if ($format == 'integer' || $format == 'decimal')
		{
			return false;
		}

		return true;
	}

	/**
	 * Modify the label for admin list - filter elements.
	 * Adds a '*' if the element is not available in advanced search
	 *
	 * @param   string  &$label  Element label
	 *
	 * @return  void
	 */

	public function availableInAdvancedSearchLabel(&$label)
	{
		$label = $this->canIncludeInSearchAll(true) ? $label : $label . '*';
	}

	/**
	 * Get the value to use for graph calculations
	 * see timer which converts the value into seconds
	 *
	 * @param   string  $v  standard value
	 *
	 * @return  mixed calculation value
	 */

	public function getCalculationValue($v)
	{
		return (float) $v;
	}

	/**
	 * run on formModel::setFormData()
	 *
	 * @param   int  $c  repeat group counter
	 *
	 * @return void
	 */

	public function preProcess($c)
	{
	}

	/**
	 * Called when copy row list plugin called
	 *
	 * @param   mixed  $val  value to copy into new record
	 *
	 * @return mixed value to copy into new record
	 */

	public function onCopyRow($val)
	{
		return $val;
	}

	/**
	 * Called when save as copy form button clicked
	 *
	 * @param   mixed  $val  value to copy into new record
	 *
	 * @return  mixed  value to copy into new record
	 */

	public function onSaveAsCopy($val)
	{
		return $val;
	}

	/**
	 * Ajax call to get auto complete options (now caches results)
	 *
	 * @return  string  json encoded options
	 */

	public function onAutocomplete_options()
	{
		// Needed for ajax update (since we are calling this method via dispatcher element is not set)
		$app = JFactory::getApplication();
		$input = $app->input;
		$this->setId($input->getInt('element_id'));
		$this->loadMeForAjax();
		$cache = FabrikWorker::getCache();
		$search = $input->get('value', '', 'string');
		echo $cache->call(array(get_class($this), 'cacheAutoCompleteOptions'), $this, $search);
	}

	/**
	 * Cache method to populate autocomplete options
	 *
	 * @param   plgFabrik_Element  $elementModel  element model
	 * @param   string             $search        search string
	 * @param   array              $opts          options, 'label' => field to use for label (db join)
	 *
	 * @since   3.0.7
	 *
	 * @return string  json encoded search results
	 */

	public static function cacheAutoCompleteOptions($elementModel, $search, $opts = array())
	{
		$name = $elementModel->getFullName(false, false);
		$elementModel->encryptFieldName($name);
		$listModel = $elementModel->getListModel();
		$db = $listModel->getDb();
		$query = $db->getQuery(true);
		$tableName = $listModel->getTable()->db_table_name;
		$query->select('DISTINCT(' . $name . ') AS value, ' . $name . ' AS text')->from($tableName);
		$query->where($name . ' LIKE ' . $db->quote(addslashes('%' . $search . '%')));
		$query = $listModel->buildQueryJoin($query);
		$query = $listModel->buildQueryWhere(false, $query);
		$query = $listModel->pluginQuery($query);
		$db->setQuery($query);
		$tmp = $db->loadObjectList();

		foreach ($tmp as &$t)
		{
			$elementModel->toLabel($t->text);
			$t->text = strip_tags($t->text);
		}

		return json_encode($tmp);
	}

	/**
	 * Get the table name that the element stores to
	 * can be the main table name or the joined table name
	 *
	 * @return  string
	 */

	protected function getTableName()
	{
		$listModel = $this->getListModel();
		$table = $listModel->getTable();
		$groupModel = $this->getGroup();

		if ($groupModel->isJoin())
		{
			$joinModel = $groupModel->getJoinModel();
			$join = $joinModel->getJoin();
			$name = $join->table_join;
		}
		else
		{
			$name = $table->db_table_name;
		}

		return $name;
	}

	/**
	 * Converts a raw value into its label equivalent
	 *
	 * @param   string  &$v  raw value
	 *
	 * @return  void
	 */

	protected function toLabel(&$v)
	{
	}

	/**
	 * Build group by query to append to list query
	 *
	 * @return  stringgetGroupByQuery
	 */

	public function getGroupByQuery()
	{
		return '';
	}

	/**
	 * Append element where statement to lists where array
	 *
	 * @param   array  &$whereArray  list models where statements
	 *
	 * @return  void
	 */

	public function appendTableWhere(&$whereArray)
	{
		$params = $this->getParams();
		$where = '';

		if ($params->get('append_table_where', false))
		{
			if (method_exists($this, 'buildQueryWhere'))
			{
				$where = trim($this->buildQueryWhere(array()));

				if ($where != '')
				{
					$where = JString::substr($where, 5, JString::strlen($where) - 5);

					if (!in_array($where, $whereArray))
					{
						$whereArray[] = $where;
					}
				}
			}
		}
	}

	/**
	 * Used by validations
	 *
	 * @param   string  $data     this elements data
	 * @param   string  $cond     what condiion to apply
	 * @param   string  $compare  data to compare element's data to
	 *
	 * @return bool
	 */

	public function greaterOrLessThan($data, $cond, $compare)
	{
		if ($cond == '>')
		{
			return $data > $compare;
		}
		elseif ($cond == '>=')
		{
			return $data >= $compare;
		}
		elseif ($cond == '<')
		{
			return $data < $compare;
		}
		elseif ($cond == '<=')
		{
			return $data <= $compare;
		}
		elseif ($cond == '==')
		{
			return $data == $compare;
		}

		return false;
	}

	/**
	 * Can the element plugin encrypt data
	 *
	 * @return  bool
	 */

	public function canEncrypt()
	{
		return false;
	}

	/**
	 * Should the element's data be encrypted
	 *
	 * @return  bool
	 */

	public function encryptMe()
	{
		$params = $this->getParams();

		return ($this->canEncrypt() && $params->get('encrypt', false));
	}

	/**
	 * Format a number value
	 *
	 * @param   mixed  $data  (double/int)
	 *
	 * @return  string	formatted number
	 */

	protected function numberFormat($data)
	{
		$params = $this->getParams();

		if (!$params->get('field_use_number_format', false))
		{
			return $data;
		}

		$decimal_length = (int) $params->get('decimal_length', 2);
		$decimal_sep = $params->get('field_decimal_sep', '.');
		$thousand_sep = $params->get('field_thousand_sep', ',');

		// Workaround for params not letting us save just a space!
		if ($thousand_sep == '#32')
		{
			$thousand_sep = ' ';
		}

		return number_format((float) $data, $decimal_length, $decimal_sep, $thousand_sep);
	}

	/**
	 * Strip number format from a number value
	 *
	 * @param   mixed  $val  (double/int)
	 *
	 * @return  string	formatted number
	 */

	public function unNumberFormat($val)
	{
		$params = $this->getParams();

		if (!$params->get('field_use_number_format', false))
		{
			return $val;
		}
		// Might think about rounding to decimal_length, but for now let MySQL do it
		$decimal_length = (int) $params->get('decimal_length', 2);

		// Swap dec and thousand seps back to Normal People Decimal Format!
		$decimal_sep = $params->get('field_decimal_sep', '.');
		$thousand_sep = $params->get('field_thousand_sep', ',');
		$val = str_replace($thousand_sep, '', $val);
		$val = str_replace($decimal_sep, '.', $val);

		return $val;
	}

	/**
	 * Recursively get all linked children of an element
	 *
	 * @param   int  $id  element id
	 *
	 * @return  array
	 */

	protected function getElementDescendents($id = 0)
	{
		if (empty($id))
		{
			$id = $this->id;
		}

		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->select('id')->from('#__{package}_elements')->where('parent_id = ' . (int) $id);
		$db->setQuery($query);
		$kids = $db->loadObjectList();
		$all_kids = array();

		foreach ($kids as $kid)
		{
			$all_kids[] = $kid->id;
			$all_kids = array_merge($this->getElementDescendents($kid->id), $all_kids);
		}

		return $all_kids;
	}

	/**
	 * Get the actual table name to use when building select queries
	 * so if in a joined group get the joined to table's name otherwise return the
	 * table's db table name
	 *
	 * @return  string
	 */

	protected function actualTableName()
	{
		if (isset($this->actualTable))
		{
			return $this->actualTable;
		}

		$groupModel = $this->getGroup();

		if ($groupModel->isJoin())
		{
			$joinModel = $groupModel->getJoinModel();

			return $joinModel->getJoin()->table_join;
		}

		$listModel = $this->getListModel();
		$this->actualTable = $listModel->getTable()->db_table_name;

		return $this->actualTable;
	}

	/**
	 * When creating crud query in tableModel::storeRow() each element has the chance
	 * to alter the row id - used by sugarid plugin to fudge rowid
	 *
	 * @param   string  &$rowId  row id
	 *
	 * @deprecated - not used
	 *
	 * @return  void
	 */

	public function updateRowId(&$rowId)
	{
	}

	/**
	 * Fabrik3: moved to Admin Element Model
	 *
	 * @deprecated - not used
	 *
	 * @return  string	table name
	 */

	protected function getRepeatElementTableName()
	{
	}

	/**
	 * Is the element a repeating element
	 *
	 * @return  bool
	 */

	public function isRepeatElement()
	{
		return $this->isJoin();
	}

	/**
	 * fabrik3: moved to Admin Element Model
	 * if repeated element we need to make a joined db table to store repeated data in
	 *
	 * @depreciated
	 *
	 * @return  void
	 */

	public function createRepeatElement()
	{
	}

	/**
	 * get the element's associated join model
	 *
	 * @return  object	join model
	 */

	public function getJoinModel()
	{
		if (is_null($this->joinModel))
		{
			$this->joinModel = JModelLegacy::getInstance('Join', 'FabrikFEModel');

			// $$$ rob ensure we load the join by asking for the parents id, but then ensure we set the element id back to this elements id
			$this->joinModel->getJoinFromKey('element_id', $this->getParent()->id);
			$this->joinModel->getJoin()->element_id = $this->getElement()->id;
		}

		return $this->joinModel;
	}

	/**
	 * when saving an element pk we need to update any join which has the same params->pk
	 *
	 * @param   string  $oldName  (prevoius element name)
	 * @param   string  $newName  (new element name)
	 *
	 * @since	3.0.6
	 *
	 * @return  void
	 */

	public function updateJoinedPks($oldName, $newName)
	{
		$db = $this->getListModel()->getDb();
		$item = $this->getListModel()->getTable();
		$query = $db->getQuery(true);

		// Update linked lists id.
		$query->update('#__{package}_joins')->set('table_key = ' . $db->quote($newName))
		->where('join_from_table = ' . $db->quote($item->db_table_name))->where('table_key = ' . $db->quote($oldName));
		$db->setQuery($query);
		$db->execute();

		// Update join pk parameter
		$query->clear();
		$query->select('id')->from('#__{package}_joins')->where('table_join = ' . $db->quote($item->db_table_name));
		$db->setQuery($query);
		$ids = $db->loadColumn();
		$teskPk = $db->quoteName($item->db_table_name . '.' . $oldName);
		$newPk = $db->quoteName($item->db_table_name . '.' . $newName);

		foreach ($ids as $id)
		{
			$join = FabTable::getInstance('Join', 'FabrikTable');
			$join->load($id);
			$params = new JRegistry($join->params);

			if ($params->get('pk') === $teskPk)
			{
				$params->set('pk', $newPk);
				$join->params = (string) $params;
				$join->store();
			}
		}
	}

	/**
	 * Is the element a join
	 *
	 * @return  bool
	 */

	public function isJoin()
	{
		return $this->getParams()->get('repeat', false);
	}

	/**
	 * Used by inline edit table plugin
	 * If returns yes then it means that there are only two possible options for the
	 * ajax edit, so we should simply toggle to the alternative value and show the
	 * element rendered with that new value (used for yes/no element)
	 *
	 * @deprecated - only called in a deprecated element method
	 *
	 * @return  bool
	 */

	protected function canToggleValue()
	{
		return false;
	}

	/**
	 * Encrypt an enitre columns worth of data, used when updating an element to encrypted
	 * with existing data in the column
	 *
	 * @return  null
	 */

	public function encryptColumn()
	{
		$secret = JFactory::getConfig()->get('secret');
		$listModel = $this->getListModel();
		$db = $listModel->getDb();
		$tbl = $this->actualTableName();
		$name = $this->getElement()->name;
		$db->setQuery("UPDATE $tbl SET " . $name . " = AES_ENCRYPT(" . $name . ", " . $db->quote($secret) . ")");
		$db->execute();
	}

	/**
	 * Decrypt an enitre columns worth of data, used when updating an element from encrypted to decrypted
	 * with existing data in the column
	 *
	 * @return  null
	 */

	public function decryptColumn()
	{
		// @TODO this query looks right but when going from encrypted blob to decrypted field the values are set to null
		$secret = JFactory::getConfig()->get('secret');
		$listModel = $this->getListModel();
		$db = $listModel->getDb();
		$tbl = $this->actualTableName();
		$name = $this->getElement()->name;
		$db->setQuery("UPDATE $tbl SET " . $name . " = AES_DECRYPT(" . $name . ", " . $db->quote($secret) . ")");
		$db->execute();
	}

	/**
	 * PN 19-Jun-11: Construct an element error string.
	 *
	 * @return  string
	 */

	public function selfDiagnose()
	{
		$retStr = '';
		$this->_db->setQuery("SELECT COUNT(*) FROM #__fabrik_groups " . "WHERE (id = " . $this->element->group_id . ");");
		$group_id = $this->_db->loadResult();

		if (!$group_id)
		{
			$retStr = 'No valid group assignment';
		}
		elseif (!$this->element->plugin)
		{
			$retStr = 'No plugin';
		}
		elseif (!$this->element->label)
		{
			$retStr = 'No element label';
		}
		else
		{
			$retStr = '';
		}

		return $retStr;
	}

	/**
	 * Shortcut to get plugin manager
	 *
	 * @since 3.0b
	 *
	 * @deprecated
	 *
	 * @return  object  plugin manager
	 */

	public function getPluginManager()
	{
		return FabrikWorker::getPluginManager();
	}

	/**
	 * When the element is a repeatble join (e.g. db join checkbox) then figure out how many
	 * records have been selected
	 *
	 * @param   array   $data   data
	 * @param   object  $oJoin  join current join
	 *
	 * @since 3.0rc1
	 *
	 * @return  int  number of records selected
	 */

	public function getJoinRepeatCount($data, $oJoin)
	{
		return count(JArrayHelper::getValue($data, $oJoin->table_join . '___id', array()));
	}

	/**
	 * When we do ajax requests from the element - as the plugin controller uses the J dispatcher
	 * the element hasnt loaded up itself, so any time you have a function onAjax_doSomething() call this
	 * helper function first to load up the element. Otherwise things like parameters will not be loaded
	 *
	 * @since 3.0rc1
	 *
	 * @return  null
	 */

	protected function loadMeForAjax()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$this->form = JModelLegacy::getInstance('form', 'FabrikFEModel');
		$formId = $input->getInt('formid');
		$this->form->setId($formId);
		$this->setId($input->getInt('element_id'));
		$this->list = JModelLegacy::getInstance('list', 'FabrikFEModel');
		$this->list->loadFromFormId($formId);
		$table = $this->list->getTable(true);
		$table->form_id = $formId;
		$element = $this->getElement(true);
	}

	/**
	 * get the element's cell class
	 *
	 * @since 3.0.4
	 *
	 * @return  string	css classes
	 */

	public function getCellClass()
	{
		$params = $this->getParams();
		$classes = array();
		$classes[] = $this->getFullName(true, false);
		$classes[] = 'fabrik_element';
		$classes[] = 'fabrik_list_' . $this->getListModel()->getId() . '_group_' . $this->getGroupModel()->getId();
		$c = $params->get('tablecss_cell_class', '');

		if ($c !== '')
		{
			$classes[] = $c;
		}

		return implode(' ', $classes);
	}

	/**
	 * get the elements list heading class
	 *
	 * @since 3.0.4
	 *
	 * @return  string	css classes
	 */

	public function getHeadingClass()
	{
		$params = $this->getParams();
		$classes = array();
		$classes[] = 'fabrik_ordercell';
		$classes[] = $this->getFullName(true, false);
		$classes[] = $this->getElement()->id . '_order';
		$classes[] = 'fabrik_list_' . $this->getListModel()->getId() . '_group_' . $this->getGroupModel()->getId();
		$classes[] = $this->getParams()->get('tablecss_header_class');

		return implode(' ', $classes);
	}

	/**
	 * convert XML format data into fabrik data (used by web services)
	 *
	 * @param   mixed  $v  data
	 *
	 * @return  mixed  data
	 */

	public function fromXMLFormat($v)
	{
		return $v;
	}

	/**
	 * Allows the element to pre-process a rows data before and join mergeing of rows
	 * occurs. Used in calc element to do cals on actual row rather than merged row
	 *
	 * @param   string  $data  elements data for the current row
	 * @param   object  $row   current row's data
	 *
	 * @since	3.0.5
	 *
	 * @return  string	formatted value
	 */

	public function preFormatFormJoins($data, $row)
	{
		return $data;
	}

	/**
	 * Return an array of parameter names which should not get updated if a linked element's parent is saved
	 * notably any paramter which references another element id should be returned in this array
	 * called from admin element model updateChildIds()
	 * see cascadingdropdown element for example
	 *
	 * @return  array	parameter names to not alter
	 */

	public function getFixedChildParameters()
	{
		return array();
	}

	/**
	 * Set row class
	 *
	 * @param   array  &$data  row data to set class for
	 *
	 * @return  null
	 */

	public function setRowClass(&$data)
	{
		$rowclass = $this->getParams()->get('use_as_row_class');

		if ($rowclass == 1)
		{
			$col = $this->getFullName(true, false);
			$rawcol = $col . '_raw';

			foreach ($data as $groupk => $group)
			{
				for ($i = 0; $i < count($group); $i++)
				{
					$c = false;

					if (isset($data[$groupk][$i]->data->$rawcol))
					{
						$c = $data[$groupk][$i]->data->$rawcol;
					}
					elseif (isset($data[$groupk][$i]->data->$col))
					{
						$c = $data[$groupk][$i]->data->$col;
					}

					if ($c !== false)
					{
						$c = preg_replace('/[^A-Z|a-z|0-9]/', '-', $c);
						$c = FabrikString::ltrim($c, '-');
						$c = FabrikString::rtrim($c, '-');

						// $$$ rob 24/02/2011 can't have numeric class names so prefix with element name
						if (is_numeric($c))
						{
							$c = $this->getElement()->name . $c;
						}

						$data[$groupk][$i]->class .= ' ' . $c;
					}
				}
			}
		}
	}

	/**
	 * Unset the element models access
	 *
	 * @return  null
	 */

	public function clearAccess()
	{
		unset($this->access);
	}

	/**
	 * Forces reset of defaults, etc.
	 *
	 * @return  null
	 */

	public function reset()
	{
		$this->defaults = null;
	}

	/**
	 * Should the 'label' field be quoted.  Overridden by databasejoin and extended classes,
	 * which may use a CONCAT'ed label which musn't be quoted.
	 *
	 * @since	3.0.6
	 *
	 * @return boolean
	 */

	protected function quoteLabel()
	{
		return true;
	}

	/**
	 * Create the where part for the query that selects the list options
	 *
	 * @param   array           $data            Current row data to use in placeholder replacements
	 * @param   bool            $incWhere        Should the additional user defined WHERE statement be included
	 * @param   string          $thisTableAlias  Db table alais
	 * @param   array           $opts            Options
	 * @param   JDatabaseQuery  $query           Append where to JDatabaseQuery object or return string (false)
	 *
	 * @return string|JDatabaseQuery
	 */

	protected function buildQueryWhere($data = array(), $incWhere = true, $thisTableAlias = null, $opts = array(), $query = false)
	{
		return '';
	}

	/**
	 * Is the element set to always render in list contexts
	 *
	 * @param   bool  $not_shown_only  Not sure???
	 *
	 * @return   bool
	 */

	public function isAlwaysRender($not_shown_only = true)
	{
		$params = $this->getParams();
		$element = $this->getElement();
		$alwaysRender = $params->get('always_render', '0');

		return $not_shown_only ? $element->show_in_list_summary == 0 && $alwaysRender == '1' : $alwaysRender == '1';
	}

	/**
	 * Called at end of form record save. Used for many-many join elements to save their data
	 *
	 * @param   array  &$data  Form data
	 *
	 * @since  3.1rc1
	 *
	 * @return  void
	 */

	public function onFinalStoreRow(&$data)
	{
		if (!$this->isJoin())
		{
			return;
		}

		$groupModel = $this->getGroupModel();
		$listModel = $this->getListModel();
		$joinModel = $this->getJoinModel();
		$db = $listModel->getDb();
		$query = $db->getQuery(true);
		$formData =& $this->getFormModel()->formDataWithTableName;
		$tableName = $listModel->getTable()->db_table_name;

		// I set this to raw for cdd.
		$name = $this->getFullName(true, false) . '_raw';
		$shortName = $this->getElement()->name;

		$join = $this->getJoin();

		// The submitted element's values
		$allJoinValues = (array) $formData[$name];

		if ($groupModel->isJoin())
		{
			$groupJoinModel = $groupModel->getJoinModel();
			$idKey = $join->table_join . '___id';
			$paramsKey = $join->table_join . '___params';
			$k = str_replace('`', '', str_replace('.', '___', $groupJoinModel->getJoin()->params->get('pk')));
			$parentIds = (array) $formData[$k];
		}
		else
		{
			$k = 'rowid';
			$idKey = $name . '___id';
			$paramsKey = $name . '___params';
			$parentIds = empty($allJoinValues) ? array() : array_fill(0, count($allJoinValues), $formData[$k]);
		}

		$allJoinIds = JArrayHelper::getValue($formData, $idKey, array());
		$allParams = array_values(JArrayHelper::getValue($formData, $paramsKey, array()));
		$i = 0;
		$idsToKeep = array();

		foreach ($parentIds as $parentId)
		{
			if (!array_key_exists($parentId, $idsToKeep))
			{
				$idsToKeep[$parentId] = array();
			}

			if ($groupModel->canRepeat())
			{
				$joinValues = JArrayHelper::getValue($allJoinValues, $i, array());
			}
			else
			{
				$joinValues = $allJoinValues;
			}

			$joinValues = (array) $joinValues;

			// Get existing records
			if ($parentId == '')
			{
				$ids = array();
			}
			else
			{
				$query->clear();
				$query->select('id, ' . $shortName)->from($join->table_join)->where('parent_id = ' . $parentId);
				$db->setQuery($query);
				$ids = (array) $db->loadObjectList($shortName);
			}

			foreach ($joinValues as $jIndex => $jid)
			{
				$record = new stdClass;
				$record->parent_id = $parentId;
				$fkVal = JArrayHelper::getValue($joinValues, $jIndex);
				$record->$shortName = $fkVal;
				$record->params = JArrayHelper::getValue($allParams, $jIndex);

				// Stop notice with fileupload where fkVal is an array
				if (array_key_exists($fkVal, $ids))
				{
					$record->id = $ids[$fkVal]->id;
					$idsToKeep[$parentId][] = $record->id;
				}
				else
				{
					$record->id = 0;
				}

				if ($record->id == 0)
				{
					$ok = $listModel->insertObject($join->table_join, $record);
					$lastInsertId = $listModel->getDb()->insertid();

					if (!$this->allowDuplicates)
					{
						$newid = new stdClass;
						$newid->id = $lastInsertId;
						$newid->$shortName = $record->$shortName;
						$ids[$record->$shortName] = $newid;
					}

					$idsToKeep[$parentId][] = $lastInsertId;
				}
				else
				{
					$ok = $listModel->updateObject($join->table_join, $record, 'id');
				}

				if (!$ok)
				{
					throw new RuntimeException('didnt save db joined repeat element');
				}
			}

			$i ++;
		}

		// Delete any records that were unselected.
		$this->deleteDeselectedItems($idsToKeep, $k);
	}

	/**
	 * Delete any deselected items from the cross-reference table
	 *
	 * @param   array   $idsToKeep  List of ids to keep
	 * @param   string  $k          Parent record key name
	 *
	 * @return  void
	 */

	protected function deleteDeselectedItems($idsToKeep, $k)
	{
		$listModel = $this->getListModel();
		$join = $this->getJoin();
		$db = $listModel->getDb();
		$query = $db->getQuery(true);

		if (empty($idsToKeep))
		{
			$formData = $this->getFormModel()->formDataWithTableName;
			$parentId = $formData[$k];
			$query->delete($join->table_join)->where('parent_id = ' . $db->quote($parentId));
			$db->setQuery($query);
			$db->execute();
		}

		foreach ($idsToKeep as $parentId => $ids)
		{
			$query->clear();
			$query->delete($join->table_join)->where('parent_id = ' . $parentId);

			if (!empty($ids))
			{
				$query->where('id NOT IN ( ' . implode($ids, ',') . ')');
			}

			$db->setQuery($query);
			$db->execute();
		}
	}
}
