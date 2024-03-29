<?php
/**
 * Fabrik List Model
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modelform');

require_once COM_FABRIK_FRONTEND . '/helpers/pagination.php';
require_once COM_FABRIK_FRONTEND . '/helpers/list.php';

/**
 * Fabrik List Model
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       3.0
 */

class FabrikFEModelList extends JModelForm
{
	/**
	 * List id
	 *
	 * @var int
	 */
	public $id = null;

	/**
	 * package id
	 *
	 * @var int
	 */
	public $packageId = null;

	/**
	 * Lists connection object
	 *
	 * @var object
	 */
	protected $connection = null;

	/**
	 * List item
	 *
	 * @var JTable
	 */
	protected $table = null;

	/**
	 * List's form model
	 *
	 * @var FabrikFEModelForm
	 */
	protected $formModel = null;

	/**
	 * Joins
	 *
	 * @var array
	 */
	private $joins = null;

	/**
	 * Column calculations
	 * @var array
	 */
	protected $runCalculations = array();

	/**
	 * List output format - set to rss to collect correct element data within function getData()
	 *
	 * @var string
	 */
	protected $outputFormat = 'html';

	/**
	 * Is rendered as a J content plugin
	 *
	 * @var bool
	 */
	public $isMambot = false;

	/**
	 * Contain access rights
	 *
	 * @var object
	 */
	protected $access = null;

	/**
	 * Id of the last inserted record (or if updated the last record updated)
	 *
	 * @var int
	 */
	public $lastInsertId = null;

	/**
	 * Database fields
	 *
	 * @var array
	 */
	protected $dbFields = null;

	/**
	 * Used when making custom links to determine if we need to append the rowid to the url
	 *
	 * @var bool
	 */
	protected $rowIdentifierAdded = false;

	/**
	 * Is ajax used
	 *
	 * @var bool
	 */
	public $ajax = null;

	/**
	 * Join sql
	 *
	 * @var string
	 */
	protected $joinsSQL = null;

	/**
	 * Order by column names
	 *
	 * @var array
	 */
	protected $orderByFields = null;

	protected $joinsToThisKey = null;

	protected $linksToThisKey = null;

	/**
	 * Used to determine which filter action to use.
	 * If a filter is a range then override lists setting with onsubmit
	 *
	 * @var string
	 */
	protected $real_filter_action = null;

	/**
	 * Internally used when using parseMessageForRowHolder();
	 *
	 * @var array
	 */
	protected $aRow = null;

	/**
	 * Rows to delete
	 *
	 * @var array
	 */
	public $rowsToDelete = null;

	/**
	 * Original list data BEFORE form saved - used to ensure uneditable data is stored
	 *
	 * @var array
	 */
	protected $origData = null;

	/**
	 * Set to true to load records STARTING from a random id (used in the getPageNav func)
	 *
	 * @var bool
	 */
	public $randomRecords = false;

	/**
	 * List data
	 *
	 * @var array
	 */
	protected $data = null;

	/**
	 * Template name
	 *
	 * @var string
	 */
	protected $tmpl = null;

	/**
	 * Pagination
	 *
	 * @var FPagination
	 */
	protected $nav = null;

	/**
	 * List field names
	 *
	 * @var array
	 */
	protected $fields = null;

	/**
	 * Prefilters
	 *
	 * @var array
	 */
	protected $prefilters = null;

	/**
	 * Filters
	 *
	 * @var array
	 */
	public $filters = null;

	/**
	 * Can rows be selected
	 *
	 * @var bool
	 */
	protected $canSelectRows = null;

	/**
	 * As fields - used in query to build list data
	 *
	 * @var array
	 */
	public $asfields = null;

	/**
	 * Has an element, which is the db key, already been added to the list of fields to select
	 *
	 * @var bool
	 */
	public $temp_db_key_addded = false;

	/**
	 * Has the group by statement been added to the list query
	 *
	 * @var bool
	 */
	protected $group_by_added = false;

	/**
	 * List of where conditions added by list plugins
	 *
	 * @var array
	 */
	protected $pluginQueryWhere = array();

	/**
	 * List of group by statements added by list plugins
	 *
	 * @var array
	 */
	protected $pluginQueryGroupBy = array();

	/**
	 * Used in views for rendering
	 *
	 * @var array
	 */
	public $groupTemplates = array();

	/**
	 * Is the list a view
	 *
	 * @var bool
	 */
	protected $isView = null;

	/**
	 * Index objects
	 *
	 * @var array
	 */
	protected $indexes = null;

	/**
	 * Previously submitted advanced search data
	 *
	 * @var array
	 */
	protected $advancedSearchRows = null;

	/**
	 * List action url
	 *
	 * @var string
	 */
	protected $tableAction = null;

	/**
	 * Doing CSV import
	 *
	 * @var bool
	 */
	public $importingCSV = false;

	/**
	 * Element names to encrypt
	 *
	 * @var array
	 */
	public $encrypt = array();

	/**
	 * Which record number to start showing from
	 *
	 * @var int
	 */
	public $limitStart = null;

	/**
	 * Number of records per page
	 *
	 * @var int
	 */
	public $limitLength = null;

	/**
	 * List rows
	 *
	 * @var array
	 */
	protected $rows = null;

	/**
	 * Should a heading be added for action buttons (returns true if at least one row has buttons)
	 *
	 * @deprecated (since 3.0.7)
	 *
	 * @var bool
	 */
	protected $actionHeading = false;

	/**
	 * List of column data - used for filters
	 *
	 * @var array
	 */
	protected $columnData = array();

	/**
	 * Render context used for defining custom css variable for tmpl rendering e.g. module_1
	 *
	 * @var string
	 */
	protected $renderContext = '';

	/**
	 * Tthe max number of buttons that is shown in a row
	 *
	 * @var int
	 */
	protected $rowActionCount = 0;

	/**
	 * Do any of the elements have a required filter, only used through method of same name
	 *
	 * @var bool
	 */
	protected $hasRequiredElementFilters = null;

	/**
	 * Elements which have a required filter
	 *
	 * @var array
	 */
	protected $elementsWithRequiredFilters = array();

	/**
	 * Force formatData() to format all elements, uses formatAll() accessor method
	 *
	 * @var bool
	 */
	protected $format_all = false;

	/**
	 * Array of order by elements
	 *
	 * @var array
	 */
	public $orderEls = array();

	/**
	 * Cached order by statement
	 *
	 * @since 3.0.7
	 *
	 * @var mixed - string or JQueryBuilder section
	 */
	public $orderBy = null;

	/**
	 * Tabs field to use
	 *
	 * @since 3.1
	 *
	 * @var string
	 */
	protected $tabsField = null;

	/**
	 * Tabs to display
	 *
	 * @since 3.1
	 *
	 * @var array
	 */
	protected $tabs = null;

	/**
	 * Load form
	 *
	 * @param   array  $data      form data
	 * @param   bool   $loadData  load in the data
	 *
	 * @since       1.5
	 *
	 * @return  mixed  false or form.
	 */

	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_fabrik.list', 'view', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Constructor
	 */

	public function __construct()
	{
		parent::__construct();
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$app = JFactory::getApplication();
		$input = $app->input;
		$id = $input->getInt('listid', $usersConfig->get('listid'));
		$this->packageId = (int) $input->getInt('packageId', $usersConfig->get('packageId'));
		$this->setId($id);
		$this->access = new stdClass;
	}

	/**
	 * Process the lists plug-ins
	 *
	 * @return  array	of list plug-in result messages
	 */

	public function processPlugin()
	{
		$pluginManager = FabrikWorker::getPluginManager();
		$pluginManager->runPlugins('process', $this, 'list');

		return $pluginManager->data;
	}

	/**
	 * Code to enable plugins to add a button to the top of the list
	 *
	 * @return  array	button html
	 */

	public function getPluginTopButtons()
	{
		$pluginManager = FabrikWorker::getPluginManager();
		$pluginManager->runPlugins('topButton', $this, 'list');
		$buttons = $pluginManager->data;

		return $buttons;
	}

	/**
	 * Get the html that is outputted by list plug-in buttons
	 *
	 * @return  array  buttons
	 */

	public function getPluginButtons()
	{
		$pluginManager = FabrikWorker::getPluginManager();
		$pluginManager->getPlugInGroup('list');
		$pluginManager->runPlugins('button', $this, 'list');
		$buttons = $pluginManager->data;

		return $buttons;
	}

	/**
	 * Get an array of plugin js classes to load
	 *
	 * @param   array  &$r     Previously loaded classes
	 * @param   array  &$shim  Shim object to ini require.js
	 *
	 * @return  array
	 */

	public function getPluginJsClasses(&$r = array(), &$shim = array())
	{
		$r = (array) $r;
		$pluginManager = FabrikWorker::getPluginManager();
		$pluginManager->getPlugInGroup('list');
		$src = array();
		$pluginManager->runPlugins('loadJavascriptClass', $this, 'list', $src);

		foreach ($pluginManager->data as $f)
		{
			if (is_null($f) || $f == '')
			{
				continue;
			}

			if (is_array($f))
			{
				$r = array_merge($r, $f);
			}
			else
			{
				$r[] = $f;
			}
		}

		$pluginManager->runPlugins('requireJSShim', $this, 'list');

		foreach ($pluginManager->data as $ashim)
		{
			$shim = array_merge($shim, $ashim);
		}

		return $r;
	}

	/**
	 * Get plugin js objects
	 *
	 * @param   string  $container  list container HTML id
	 *
	 * @return  mixed
	 */

	public function getPluginJsObjects($container = null)
	{
		if (is_null($container))
		{
			$container = 'listform_' . $this->getId();
		}

		$pluginManager = FabrikWorker::getPluginManager();
		$pluginManager->getPlugInGroup('list');
		$pluginManager->runPlugins('onLoadJavascriptInstance', $this, 'list', $container);

		return $pluginManager->data;
	}

	/**
	 * Main query to build table
	 *
	 * @return  array  list data
	 */

	public function render()
	{
		FabrikHelperHTML::debug($_POST, 'render:post');
		$app = JFactory::getApplication();
		$input = $app->input;
		$profiler = JProfiler::getInstance('Application');
		$id = $this->getId();
		$this->outputFormat = $input->get('format', 'html');

		if (is_null($id) || $id == '0')
		{
			throw new RuntimeException(JText::_('COM_FABRIK_INCORRECT_LIST_ID'), 500);
		}

		if ($this->outputFormat == 'fabrikfeed')
		{
			$this->outputFormat = 'feed';
		}

		$item = $this->getTable();

		if ($item->db_table_name == '')
		{
			throw new RuntimeException(JText::_('COM_FABRIK_INCORRECT_LIST_ID'), 500);
		}

		// Cant set time limit in safe mode so suppress warning
		@set_time_limit(60);
		JDEBUG ? $profiler->mark('About to get table filter') : null;
		$filters = $this->getFilterArray();
		JDEBUG ? $profiler->mark('Got filters') : null;
		$this->setLimits();
		$this->setElementTmpl();
		$data = $this->getData();
		JDEBUG ? $profiler->mark('got data') : null;

		// Think we really have to do these as the calc isnt updated when the list is filtered
		$this->doCalculations();
		JDEBUG ? $profiler->mark('done calcs') : null;
		$this->getCalculations();
		JDEBUG ? $profiler->mark('got cacls') : null;
		$item->hit();

		return $data;
	}

	/**
	 * Set the navigation limit and limitstart
	 *
	 * @param   int  $limitstart_override   Specific limitstart to use, if both start and length are specified
	 * @param   int  $limitlength_override  Specific limitlength to use, if both start and length are specified
	 *
	 * @return  void
	 */

	public function setLimits($limitstart_override = null, $limitlength_override = null)
	{
		$app = JFactory::getApplication();
		$input = $app->input;

		// Plugins using setLimits - these limits would get overwritten by render() or getData() calls
		if (isset($this->limitLength) && isset($this->limitStart) && is_null($limitstart_override) && is_null($limitlength_override))
		{
			return;
		}
		/*
		 * $$$ hugh - added the overrides, so things like visualizations can just turn
		 * limits off, by passing 0's, without having to go round the houses setting
		 * the request array before calling this method.
		 */
		if (!is_null($limitstart_override) && !is_null($limitlength_override))
		{
			// Might want to set the request vars here?
			$limitStart = $limitstart_override;
			$limitLength = $limitlength_override;
		}
		else
		{
			$package = $app->getUserState('com_fabrik.package', 'fabrik');
			$item = $this->getTable();
			$params = $this->getParams();
			$id = $this->getId();
			$this->randomRecords = $input->get('fabrik_random', $this->randomRecords);

			// $$$ rob dont make the key list.X as the registry doesnt seem to like keys with just '1' a
			$context = 'com_' . $package . '.list' . $this->getRenderContext() . '.';
			$limitStart = $this->randomRecords ? $this->getRandomLimitStart() : 0;

			// Deal with the fact that you can have more than one list on a page so limitstart has to be  specfic per table

			// If list is rendered as a content plugin dont set the limits in the session
			if ($app->scope == 'com_content')
			{
				$limitLength = $input->getInt('limit' . $id, $item->rows_per_page);

				if (!$this->randomRecords)
				{
					$limitStart = $input->getInt('limitstart' . $id, $limitStart);
				}
			}
			else
			{
				// If a list (assoc with a menu item) loads a form, with db join & front end select - dont use the orig menu's rows_per_page value.
				$mambot = $this->isMambot || ($input->get('tmpl') === 'component' && $input->getInt('ajax') === 1);
				$rowsPerPage = FabrikWorker::getMenuOrRequestVar('rows_per_page', $item->rows_per_page, $mambot);
				$limitLength = $app->getUserStateFromRequest($context . 'limitlength', 'limit' . $id, $rowsPerPage);

				if (!$this->randomRecords)
				{
					$limitStart = $app->getUserStateFromRequest($context . 'limitstart', 'limitstart' . $id, $limitStart, 'int');
				}
			}

			if ($this->outputFormat == 'feed')
			{
				$limitLength = $input->getInt('limit', $params->get('rsslimit', 150));
				$maxLimit = $params->get('rsslimitmax', 2500);

				if ($limitLength > $maxLimit)
				{
					$limitLength = $maxLimit;
				}
			}

			if ($limitStart < 0)
			{
				$limitStart = 0;
			}
		}

		$this->limitLength = $limitLength;
		$this->limitStart = $limitStart;
	}

	/**
	 * This merges session data for the fromForm with any request data
	 * allowing us to filter data results from both search forms and filters
	 *
	 * @return  array
	 */

	public function getRequestData()
	{
		$profiler = JProfiler::getInstance('Application');
		JDEBUG ? $profiler->mark('start get Request data') : null;
		$f = $this->getFilterModel()->getFilters();
		JDEBUG ? $profiler->mark('end get Request data') : null;

		return $f;
	}

	/**
	 * Get the table's filter model
	 *
	 * @return  model	filter model
	 */

	public function &getFilterModel()
	{
		if (!isset($this->filterModel))
		{
			$this->filterModel = JModelLegacy::getInstance('Listfilter', 'FabrikFEModel');
			$this->filterModel->setListModel($this);
		}

		return $this->filterModel;
	}

	/**
	 * Once we have a few table joins, our select statements are
	 * getting big enough to hit default select length max in MySQL.  Added per-list
	 * setting to enable_big_selects.
	 *
	 * 03/10/2012 - Should preserve any old list settings, but this is now set in the global config
	 * We set it on the main J db in the system plugin setBigSelects() but should do here as well as we
	 * may not be dealing with the same db.
	 *
	 * 2012-10-19 - $$$ hugh - trouble with preserving old list settings is there is no way to change them, without
	 * directly poking around in the params in the database.  Commenting out the per-list checking.
	 *
	 * @deprecated   now handled in FabrikHelper::getDbo(), as it needs to apply to all queruies, including internal / default connection ones.
	 * @since   3/16/2010
	 *
	 * @return  void
	 */

	public function setBigSelects()
	{
		$fbConfig = JComponentHelper::getParams('com_fabrik');
		$bigSelects = $fbConfig->get('enable_big_selects', 0);
		/*
		 $fabrikDb = $this->getDb();
		$params = $this->getParams();
		if ($params->get('enable_big_selects', $bigSelects))
		 */
		if ($bigSelects)
		{
			$fabrikDb = $this->getDb();

			// $$$ hugh - added bumping up GROUP_CONCAT_MAX_LEN here, rather than adding YAFO for it
			$fabrikDb->setQuery("SET OPTION SQL_BIG_SELECTS=1, GROUP_CONCAT_MAX_LEN=10240");
			$fabrikDb->execute();
		}
	}

	/**
	 * Append to the list's data
	 *
	 * @param   string  $groupRef  Group by reference (0 for non-grouped)
	 * @param   object  $row       Row to append to the list
	 *
	 * @return  array  $this->data
	 */

	public function appendData($groupRef, $row)
	{
		$data = $this->getData();

		if (array_key_exists($groupRef, $data))
		{
			$data[$groupRef][] = $row;
			$this->data = $data;
		}

		return $this->data;
	}
	/**
	 * Get the table's data
	 *
	 * @return  array	of objects (rows)
	 */

	public function getData()
	{
		if (isset($this->data) && !is_null($this->data))
		{
			return $this->data;
		}

		$profiler = JProfiler::getInstance('Application');
		$pluginManager = FabrikWorker::getPluginManager();
		$fbConfig = JComponentHelper::getParams('com_fabrik');
		$pluginManager->runPlugins('onPreLoadData', $this, 'list');

		// Needs to be off for FOUND_ROWS() to work
		ini_set('mysql.trace_mode', 'off');
		$fabrikDb = $this->getDb();
		JDEBUG ? $profiler->mark('query build start') : null;

		// Ajax call needs to recall this - not sure why
		$this->setLimits();
		$query = $this->buildQuery();
		JDEBUG ? $profiler->mark('query build end') : null;
		$cache = FabrikWorker::getCache($this);
		$item = $this->getTable();

		try
		{
			$results = $cache->call(
				array(get_class($this), 'finesseData'), $this->getId(), $query,
				$this->limitStart, $this->limitLength, $this->outputFormat
				);
		}
		catch (Exception $e)
		{
			$msg = 'Fabrik has generated an incorrect query for the list ' . $item->label . ': <br /><br /><pre>' . $e->getMessage() . '</pre>';
			throw new RuntimeException($msg, 500);
		}

		$this->totalRecords = $results[0];
		$this->data = $results[1];
		$this->groupTemplates = $results[2];
		$nav = $this->getPagination($this->totalRecords, $this->limitStart, $this->limitLength);
		$pluginManager->runPlugins('onLoadData', $this, 'list', $this->data);

		return $this->data;
	}

	/**
	 * Cached Method to run the getData select query and do our Fabrik magikin'
	 *
	 * @param   int     $listId        list id
	 * @param   string  $query         sql query
	 * @param   int     $start         start of limit
	 * @param   int     $length        limit length
	 * @param   string  $outputFormat  output format csv/html/rss etc
	 *
	 * @return array (total records, data set)
	 */

	public static function finesseData($listId, $query, $start, $length, $outputFormat)
	{
		$profiler = JProfiler::getInstance('Application');
		$traceModel = ini_get('mysql.trace_mode');
		$listModel = JModelLegacy::getInstance('List', 'FabrikFEModel');
		$listModel->setId($listId);
		$listModel->setOutputFormat($outputFormat);
		$fabrikDb = $listModel->getDb();
		$listModel->setBigSelects();

		// $$$ rob - if merging joined data then we don't want to limit
		// the query as we have already done so in buildQuery()
		if ($listModel->mergeJoinedData())
		{
			$fabrikDb->setQuery($query);
		}
		else
		{
			$fabrikDb->setQuery($query, $start, $length);
		}

		FabrikHelperHTML::debug($fabrikDb->getQuery(), 'list GetData:' . $listModel->getTable()->label);
		JDEBUG ? $profiler->mark('before query run') : null;

		/* set 2nd param to false in attempt to stop joomfish db adaptor from translating the orignal query
		 * fabrik3 - 2nd param in j16 is now used - guessing that joomfish now uses the third param for the false switch?
		* $$$ rob 26/09/2011 note Joomfish not currently released for J1.7
		*/
		$listModel->data = $fabrikDb->loadObjectList('', 'stdClass', false);

		// $$$ rob better way of getting total records
		if ($listModel->mergeJoinedData())
		{
			$listModel->totalRecords = $listModel->getTotalRecords();
		}
		else
		{
			$fabrikDb->setQuery("SELECT FOUND_ROWS()");
			$listModel->totalRecords = $fabrikDb->loadResult();
		}

		if ($listModel->randomRecords)
		{
			shuffle($listModel->data);
		}

		ini_set('mysql.trace_mode', $traceModel);
		JDEBUG ? $profiler->mark('query run and data loaded') : null;
		$listModel->translateData($listModel->data);

		// Add labels before preformatting - otherwise calc elements on dropdown elements show raw data for {list___element}
		$listModel->addLabels($listModel->data);

		// Run calculations
		$listModel->preFormatFormJoins($listModel->data);
		JDEBUG ? $profiler->mark('start format for joins') : null;
		$listModel->formatForJoins($listModel->data);
		JDEBUG ? $profiler->mark('start format data') : null;
		$listModel->formatData($listModel->data);
		JDEBUG ? $profiler->mark('data formatted') : null;

		return array($listModel->totalRecords, $listModel->data, $listModel->groupTemplates);
	}

	/**
	 * Part of list model finesseData() replace list___element values with labels for
	 * things like dropdowns, needed so that calc elements in preFormatFormJoins() have access to the element
	 * label
	 *
	 * @param   array  &$data  List data
	 *
	 * @return  void
	 */
	protected function addLabels(&$data)
	{
		$form = $this->getFormModel();
		$groups = $form->getGroupsHiarachy();
		$ec = count($data);

		foreach ($groups as $groupModel)
		{
			$elementModels = $this->activeContextElements($groupModel);

			foreach ($elementModels as $elementModel)
			{
				$elementModel->setContext($groupModel, $form, $this);
				$col = $elementModel->getFullName(false, true, false);

				if (!empty($data) && array_key_exists($col, $data[0]))
				{
					for ($i = 0; $i < $ec; $i++)
					{
						$thisRow = $data[$i];
						$coldata = $thisRow->$col;
						$data[$i]->$col = $elementModel->getLabelForValue($coldata, $coldata);
					}
				}
			}
		}
	}

	/**
	 * $$$ rob pointless getting elemetsnnot shown in the table view?
	 * $$$ hugh - oops, they might be using elements in group-by template not shown in table
	 * http://fabrikar.com/forums/showthread.php?p=102600#post102600
	 * $$$ rob in that case lets test that rather than loading blindly
	 * $$$ rob 15/02/2011 or out put may be csv in which we want to format any fields not shown in the form
	 * $$$ hugh 06/05/2012 added formatAll() mechanism, so plugins can force formatting of all elements
	 *
	 * @param   JModel  $groupModel  Group model
	 *
	 * @return array element models
	 */
	private function activeContextElements($groupModel)
	{
		$tableParams = $this->getParams();

		if ($this->formatAll() || ($tableParams->get('group_by_template') !== '' && $this->getGroupBy() != '') || $this->outputFormat == 'csv'
			|| $this->outputFormat == 'feed')
		{
			$elementModels = $groupModel->getPublishedElements();
		}
		else
		{
			/* $$$ hugh - added 'always render' option to elements, and methods to grab those.
			 * Could probably do this in getPublishedListElements(), but for now just grab a list
			 * of elements with 'always render' set to Yes, and "show in list" set to No,
			 * then merge that with the getPublishedListElements.  This is to work around issues
			 * where things like plugin bubble templates use placeholders for elements not shown in the list.
			 */
			$alwaysRenderElements = $this->getAlwaysRenderElements(true);
			$elementModels = $groupModel->getPublishedListElements();
			$elementModels = array_merge($elementModels, $alwaysRenderElements);
		}

		return $elementModels;
	}

	/**
	 * Translate data
	 *
	 * @param   array  &$data  data
	 *
	 * @deprecated Joomfish not available in J1.7
	 *
	 * @return  void
	 */

	protected function translateData(&$data)
	{
		return;
		$params = $this->getParams();

		if (!JPluginHelper::isEnabled('system', 'jfdatabase'))
		{
			return;
		}

		if (defined('JOOMFISH_PATH') && $params->get('allow-data-translation'))
		{
			$table = $this->getTable();
			$db = FabrikWorker::getDbo();
			$jf = JoomFishManager::getInstance();
			$config = JFactory::getConfig();
			$tableName = str_replace($config->get('dbprefix'), '', $table->db_table_name);
			$contentElement = $jf->getContentElement($tableName);

			if (!is_object($contentElement))
			{
				return;
			}

			$title = Fabrikstring::shortColName($params->get('joomfish-title'));
			$activelangs = $jf->getActiveLanguages();
			$registry = JFactory::getConfig();
			$langid = $activelangs[$registry->get("jflang")]->id;
			$db->setQuery($contentElement->createContentSQL($langid));

			if ($title == '')
			{
				$contentTable = $contentElement->getTable();

				foreach ($contentTable->Fields as $tableField)
				{
					if ($tableField->Type == 'titletext')
					{
						$title = $tableField->Name;
					}
				}
			}

			$longKey = FabrikString::safeColNameToArrayKey($table->db_primary_key);
			$res = $db->loadObjectList(FabrikString::shortColName($table->db_primary_key));

			// $$$ hugh - if no JF results, bail out, otherwise we pitch warnings in the foreach loop.
			if (empty($res))
			{
				return;
			}

			foreach ($data as &$row)
			{
				// $$$ rob if the id isnt published fall back to __pk_val
				$translateRow = array_key_exists($longKey, $row) ? $res[$row->$longKey] : $res[$row->__pk_val];

				foreach ($row as $key => $val)
				{
					$shortkey = array_pop(explode('___', $key));

					if ($shortkey === $title)
					{
						$row->$key = $translateRow->titleTranslation;
						$key = $key . '_raw';
						$row->$key = $translateRow->titleTranslation;
					}
					else
					{
						if (array_key_exists($shortkey, $translateRow))
						{
							$row->$key = $translateRow->$shortkey;
							$key = $key . '_raw';

							if (array_key_exists($key, $row))
							{
								$row->$key = $translateRow->$shortkey;
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Run the list data through element filters
	 *
	 * @param   array  &$data  list data
	 *
	 * @return  void
	 */

	protected function formatData(&$data)
	{
		$profiler = JProfiler::getInstance('Application');
		$app = JFactory::getApplication();
		$input = $app->input;
		jimport('joomla.filesystem.file');
		$form = $this->getFormModel();
		$tableParams = $this->getParams();
		$table = $this->getTable();
		$pluginManager = FabrikWorker::getPluginManager();
		$method = 'renderListData_' . $this->outputFormat;
		$this->_aLinkElements = array();

		// $$$ hugh - temp foreach fix
		$groups = $form->getGroupsHiarachy();
		$ec = count($data);

		foreach ($groups as $groupModel)
		{
			$elementModels = $this->activeContextElements($groupModel);

			foreach ($elementModels as $elementModel)
			{
				$e = $elementModel->getElement();
				$elementModel->setContext($groupModel, $form, $this);
				$params = $elementModel->getParams();
				$col = $elementModel->getFullName(true, false);

				// Check if there is  a custom out put handler for the tables format
				// Currently supports "renderListData_csv", "renderListData_rss", "renderListData_html", "renderListData_json"
				if (!empty($data) && array_key_exists($col, $data[0]))
				{
					if (method_exists($elementModel, $method))
					{
						for ($i = 0; $i < count($data); $i++)
						{
							$thisRow = $data[$i];
							$coldata = $thisRow->$col;
							$data[$i]->$col = $elementModel->$method($coldata, $thisRow);
						}
					}
					else
					{
						JDEBUG ? $profiler->mark('elements renderListData: ' . "($ec)" . " talbeid = $table->id " . $col) : null;

						for ($i = 0; $i < $ec; $i++)
						{
							$thisRow = $data[$i];
							$coldata = $thisRow->$col;
							$data[$i]->$col = $elementModel->renderListData($coldata, $thisRow);
							$rawCol = $col . '_raw';

							// Rendering of accented characters in DomPDF
							$data[$i]->$col = htmlspecialchars_decode(htmlentities($data[$i]->$col, ENT_NOQUOTES, 'UTF-8'), ENT_NOQUOTES);

							/* Not sure if this works, as far as I can tell _raw will always exist, even if
							 * the element model hasn't explicitly done anything with it (except mayeb unsetting it?)
							* For instance, the calc element needs to set _raw.  For now, I changed $thisRow above to
							* be a = reference to $data[$i], and in renderListData() the calc element modifies
							* the _raw entry in $thisRow.  I guess it could simply unset the _raw in $thisRow and
							* then implement a renderRawListData.  Anyway, just sayin'.
							*/
							if (!array_key_exists($rawCol, $thisRow))
							{
								$data[$i]->$rawCol = $elementModel->renderRawListData($coldata, $thisRow);
								$data[$i]->$rawCol = htmlspecialchars_decode(htmlentities($data[$i]->$rawCol, ENT_NOQUOTES, 'UTF-8'), ENT_NOQUOTES);
							}
						}
					}
				}
			}
		}

		JDEBUG ? $profiler->mark('elements rendered for table data') : null;
		$this->_aGroupInfo = array();
		$groupTitle = array();
		$this->groupTemplates = array();

		// Check if the data has a group by applied to it
		$groupBy = $this->getGroupBy();

		if ($groupBy != '' && $this->outputFormat != 'csv')
		{
			$w = new FabrikWorker;

			// 3.0 if not group by template spec'd by group but assigned in qs then use that as the group by tmpl
			$requestGroupBy = $input->get('group_by', '');

			if ($requestGroupBy == '')
			{
				$groupTemplate = $tableParams->get('group_by_template');

				if ($groupTemplate == '')
				{
					$groupTemplate = '{' . $groupBy . '}';
				}
			}
			else
			{
				$groupTemplate = '{' . $requestGroupBy . '}';
			}

			$groupedData = array();
			$thisGroupedData = array();
			$groupBy = FabrikString::safeColNameToArrayKey($groupBy);
			$groupBy .= '_raw';
			$groupTitle = null;
			$aGroupTitles = array();
			$groupId = 0;
			$gKey = 0;

			for ($i = 0; $i < count($data); $i++)
			{
				if (isset($data[$i]->$groupBy))
				{
					// Get rid of & as it blows up SimpleXMLElement, and dont want to use htmlspecialchars as don't want to mess with <, >, etc.
					$sdata = str_replace('&', '&amp;', str_replace('&amp;', '&', $data[$i]->$groupBy));

					// Test if its just an <a>*</a> tag - if so allow HTML (enables use of icons)
					$xml = new SimpleXMLElement('<div>' . $sdata . '</div>');
					$children = $xml->children();

					// Not working in PHP5.2	if (!($xml->count() === 1 && $children[0]->getName() == 'a'))
					if (!(count($xml->children()) === 1 && $children[0]->getName() == 'a'))
					{
						$sdata = strip_tags($sdata);
					}

					if (!in_array($sdata, $aGroupTitles))
					{
						$aGroupTitles[] = $sdata;
						$grouptemplate = ($w->parseMessageForPlaceHolder($groupTemplate, JArrayHelper::fromObject($data[$i])));
						$this->groupTemplates[$sdata] = nl2br($grouptemplate);
						$groupedData[$sdata] = array();
					}

					$data[$i]->_groupId = $sdata;
					$gKey = $sdata;

					// If the group_by was added in in getAsFields remove it from the returned data set (to avoid mess in package view)

					if ($this->group_by_added)
					{
						unset($data[$i]->$groupBy);
					}

					if ($this->temp_db_key_addded)
					{
						$k = $table->db_primary_key;
					}
				}

				$groupedData[$gKey][] = $data[$i];
			}

			$data = $groupedData;
		}
		else
		{
			for ($i = 0; $i < count($data); $i++)
			{
				if ($this->temp_db_key_addded)
				{
					$k = $table->db_primary_key;
				}
			}
			// Make sure that the none grouped data is in the same format
			$data = array($data);
		}

		JDEBUG ? $profiler->mark('table groupd by applied') : null;

		if ($this->outputFormat != 'pdf' && $this->outputFormat != 'csv' && $this->outputFormat != 'feed')
		{
			$this->addSelectBoxAndLinks($data);
			FabrikHelperHTML::debug($data, 'table:data');
		}

		JDEBUG ? $profiler->mark('end format data') : null;
	}

	/**
	 * Add the select box and various links into the data array
	 *
	 * @param   array  &$data  list's row objects
	 *
	 * @return  void
	 */

	protected function addSelectBoxAndLinks(&$data)
	{
		$j3 = FabrikWorker::j3();
		$item = $this->getTable();
		$app = JFactory::getApplication();
		$db = FabrikWorker::getDbo(true);
		$params = $this->getParams();
		$buttonAction = $this->actionMethod();
		$nextview = $this->canEdit() ? 'form' : 'details';
		$tmpKey = '__pk_val';
		$facted = $params->get('factedlinks');

		// Get a list of fabrik lists and ids for view list and form links
		$oldLinksToForms = $this->getLinksToThisKey();
		$linksToForms = array();

		foreach ($oldLinksToForms as $join)
		{
			if ($join !== false)
			{
				$k = $join->list_id . '-' . $join->form_id . '-' . $join->element_id;
				$linksToForms[$k] = $join;
			}
		}

		$action = $app->isAdmin() ? 'task' : 'view';
		$query = $db->getQuery(true);
		$query->select('id, label, db_table_name')->from('#__{package}_lists');
		$db->setQuery($query);
		$aTableNames = $db->loadObjectList('label');
		$cx = count($data);
		$viewLinkAdded = false;

		// Get pk values
		$pks = array();

		foreach ($data as $groupKey => $group)
		{
			$cg = count($group);

			for ($i = 0; $i < $cg; $i++)
			{
				$pks[] = @$data[$groupKey][$i]->$tmpKey;
			}
		}

		$joins = $this->getJoins();

		foreach ($data as $groupKey => $group)
		{
			// $group = $data[$key]; //Messed up in php 5.1 group positioning in data became ambiguous
			$cg = count($group);

			for ($i = 0; $i < $cg; $i++)
			{
				$row = $data[$groupKey][$i];
				$viewLinkAdded = false;

				// Done each row as its result can change
				$canEdit = $this->canEdit($row);
				$canView = $this->canView($row);
				$canDelete = $this->canDelete($row);
				$nextview = $canEdit ? 'form' : 'details';
				$pKeyVal = array_key_exists($tmpKey, $row) ? $row->$tmpKey : '';
				$pkcheck = array();
				$pkcheck[] = '<div style="display:none">';

				foreach ($joins as $join)
				{
					if ($join->list_id !== '0')
					{
						// $$$ rob 22/02/2011 was not using _raw before which was intserting html into the value for image elements
						$fkey = $join->table_join_alias . '___' . $join->table_key . '_raw';

						if (isset($row->$fkey))
						{
							$fKeyVal = $row->$fkey;
							$pkcheck[] = '<input type="checkbox" class="fabrik_joinedkey" value="' . htmlspecialchars($fKeyVal, ENT_COMPAT, 'UTF-8')
							. '" name="' . $join->table_join_alias . '[' . $row->__pk_val . ']" />';
						}
					}
				}

				$pkcheck[] = '</div>';
				$pkcheck = implode("\n", $pkcheck);
				$row->fabrik_select = $this->canSelectRow($row)
				? '<input type="checkbox" id="id_' . $row->__pk_val . '" name="ids[' . $row->__pk_val . ']" value="'
						. htmlspecialchars($pKeyVal, ENT_COMPAT, 'UTF-8') . '" />' . $pkcheck : '';

				// Add in some default links if no element choosen to be a link
				$link = $this->viewDetailsLink($data[$groupKey][$i]);
				$edit_link = $this->editLink($data[$groupKey][$i]);
				$row->fabrik_view_url = $link;
				$row->fabrik_edit_url = $edit_link;

				$editAttribs = $this->getCustomLink('attribs', 'edit');
				$detailsAttribs = $this->getCustomLink('attribs', 'details');

				$row->fabrik_view = '';
				$row->fabrik_edit = '';

				$editLabel = $this->editLabel();
				$editText = $buttonAction == 'dropdown' ? $editLabel : '<span class="hidden">' . $editLabel . '</span>';

				$btnClass = ($j3 && $buttonAction != 'dropdown') ? 'btn ' : '';
				$class = $j3 ? $btnClass . 'fabrik_edit fabrik__rowlink' : 'btn fabrik__rowlink';
				$dataList = 'list_' . $this->getRenderContext();
				$loadMethod = $params->get('editurl', '') == '' ? 'xhr' : 'iframe';
				$img = FabrikHelperHTML::image('edit.png', 'list', '', array('alt' => $editLabel));
				$editLink = '<a data-loadmethod="' . $loadMethod . '" class="' . $class . '" ' . $editAttribs
						. 'data-list="' . $dataList . '" href="' . $edit_link . '" title="' . $editLabel . '">' . $img
						. ' ' . $editText . '</a>';

				$viewLabel = $this->viewLabel();
				$viewText = $buttonAction == 'dropdown' ? $viewLabel : '<span class="hidden">' . $viewLabel . '</span>';
				$class = $j3 ? $btnClass . 'fabrik_view fabrik__rowlink' : 'btn fabrik__rowlink';

				$loadMethod = $params->get('detailurl', '') == '' ? 'xhr' : 'iframe';

				$img = FabrikHelperHTML::image('search.png', 'list', '', array('alt' => $viewLabel));
				$viewLink = '<a data-loadmethod="' . $loadMethod . '" class="' . $class . '" ' . $detailsAttribs
						. 'data-list="' . $dataList . '" href="' . $link . '" title="' . $viewLabel . '">' . $img
						. ' ' . $viewText . '</a>';

				// 3.0 actions now in list in one cell
				$row->fabrik_actions = array();
				$actionMethod = $this->actionMethod();

				if ($canView || $canEdit)
				{
					if ($canEdit == 1)
					{
						if ($params->get('editlink') || ($actionMethod == 'floating' || $j3))
						{
							$row->fabrik_edit = $editLink;
							$row->fabrik_actions['fabrik_edit'] = $j3 ? $row->fabrik_edit : '<li class="fabrik_edit">' . $row->fabrik_edit . '</li>';
						}

						$row->fabrik_edit_url = $edit_link;

						if ($this->canViewDetails() && $this->floatingDetailLink())
						{
							$row->fabrik_view = $viewLink;
							$row->fabrik_actions['fabrik_view'] = $j3 ? $row->fabrik_view : '<li class="fabrik_view">' . $row->fabrik_view . '</li>';
						}
					}
					else
					{
						if ($this->canViewDetails() && $this->floatingDetailLink())
						{
							if (empty($this->_aLinkElements))
							{
								$viewLinkAdded = true;
								$row->fabrik_view = $viewLink;
								$row->fabrik_actions['fabrik_view'] = $j3 ? $row->fabrik_view : '<li class="fabrik_view">' . $row->fabrik_view . '</li>';
							}
						}
						else
						{
							$row->fabrik_edit = '';
						}
					}
				}

				if ($this->canViewDetails() && !$viewLinkAdded && $this->floatingDetailLink())
				{
					$link = $this->viewDetailsLink($row, 'details');
					$row->fabrik_view_url = $link;
					$row->fabrik_view = $viewLink;
					$row->fabrik_actions['fabrik_view'] = $j3 ? $row->fabrik_view : '<li class="fabrik_view">' . $row->fabrik_view . '</li>';
				}

				if ($this->canDelete($row))
				{
					if ($buttonAction == 'dropdown')
					{
						$row->fabrik_actions['delete_divider'] = $j3 ? '' : '<li class="divider"></li>';
					}

					$row->fabrik_actions['fabrik_delete'] = $this->deleteButton();
				}
				// Create columns containing links which point to tables associated with this table
				$oldJoinsToThisKey = $this->getJoinsToThisKey();
				$joinsToThisKey = array();

				foreach ($oldJoinsToThisKey as $join)
				{
					$k = $join->list_id . '-' . $join->form_id . '-' . $join->element_id;
					$joinsToThisKey[$k] = $join;
				}

				foreach ($joinsToThisKey as $f => $join)
				{
					// $$$ hugh - for reasons I don't understand, $joinsToThisKey now contains entries
					// which aren't in $facted->linkedlist, so added this sanity check.
					if (isset($facted->linkedlist->$f))
					{
						$linkedTable = $facted->linkedlist->$f;
						$popupLink = $facted->linkedlist_linktype->$f;
						$linkedListText = $facted->linkedlisttext->$f;

						if ($linkedTable != '0')
						{
							$recordKey = $join->element_id . '___' . $linkedTable;
							$key = $recordKey . "_list_heading";
							$val = $pKeyVal;
							$recordCounts = $this->getRecordCounts($join, $pks);
							$count = 0;
							$linkKey = $recordCounts['linkKey'];

							if (is_array($recordCounts))
							{
								if (array_key_exists($val, $recordCounts))
								{
									$count = $recordCounts[$val]->total;
									$linkKey = $recordCounts[$val]->linkKey;
								}
								else
								{
									if (array_key_exists((int) $val, $recordCounts) && (int) $val !== 0)
									{
										$count = $recordCounts[(int) $val]->total;
										$linkKey = $recordCounts[$val]->linkKey;
									}
								}
							}

							$join->list_id = array_key_exists($join->listlabel, $aTableNames) ? $aTableNames[$join->listlabel]->id : '';
							$group[$i]->$key = $this->viewDataLink($popupLink, $join, $row, $linkKey, $val, $count, $f);
						}
						// $$$ hugh - pretty sure we don't need to be doing this
						// $f++;
					}
				}

				// Create columns containing links which point to forms assosciated with this table
				foreach ($linksToForms as $f => $join)
				{
					$linkedForm = $facted->linkedform->$f;
					$popupLink = $facted->linkedform_linktype->$f;
					/* $$$ hugh @TODO - rob, can you check this, I added this line,
					 * but the logic applied for $val in the linked table code above seems to be needed?
					* http://fabrikar.com/forums/showthread.php?t=9535
					*/
					$val = $pKeyVal;

					if ($linkedForm !== '0')
					{
						if (is_object($join))
						{
							// $$$rob moved these two lines here as there were giving warnings since Hugh commented out the if ($element != '') {
							$linkKey = @$join->db_table_name . '___' . @$join->name;
							$gkey = $linkKey . '_form_heading';
							$row2 = JArrayHelper::fromObject($row);
							$linkLabel = $this->parseMessageForRowHolder($facted->linkedformtext->$f, $row2);
							$group[$i]->$gkey = $this->viewFormLink($popupLink, $join, $row, $linkKey, $val, false, $f);
						}
					}
				}
			}
		}

		$args['data'] = &$data;
		$pluginButtons = $this->getPluginButtons();

		foreach ($data as $groupKey => $group)
		{
			$cg = count($group);

			for ($i = 0; $i < $cg; $i++)
			{
				$row = $data[$groupKey][$i];

				if (!empty($pluginButtons))
				{
					if ($buttonAction == 'dropdown')
					{
						$row->fabrik_actions[] = $j3 ? '' : '<li class="divider"></li>';
					}
				}

				foreach ($pluginButtons as $b)
				{
					if (trim($b) !== '')
					{
						$row->fabrik_actions[] = $j3 ? $b : '<li>' . $b . '</li>';
					}
				}

				if (!empty($row->fabrik_actions))
				{
					if (count($row->fabrik_actions) > $this->rowActionCount)
					{
						$this->rowActionCount = count($row->fabrik_actions);
					}

					if ($j3)
					{
						$align = $params->get('checkboxLocation', 'end') == 'end' ? 'right' : 'left';

						if ($buttonAction == 'dropdown')
						{
							$row->fabrik_actions = FabrikHelperHTML::bootStrapDropDown($row->fabrik_actions, $align);
						}
						else
						{
							$row->fabrik_actions = FabrikHelperHTML::bootStrapButtonGroup($row->fabrik_actions);
						}
					}
					else
					{
						$row->fabrik_actions = '<ul class="fabrik_action">' . implode("\n", $row->fabrik_actions) . '</ul>';
					}
				}
				else
				{
					$row->fabrik_actions = '';
				}
			}
		}
	}

	/**
	 * Helper method to decide if a detail link should be added to the row.
	 *
	 * If in Fabrik 3.1 return true (just use the default acl to control the link)
	 *
	 * If in Fabrik 3.0 return true if detail link option on and action method is floating
	 *
	 * @return boolean
	 */
	protected function floatingDetailLink()
	{
		if (FabrikWorker::j3())
		{
			return true;
		}

		$params = $this->getParams();
		$actionMethod = $this->actionMethod();

		return $params->get('detaillink') == '1' || $actionMethod == 'floating';
	}

	/**
	 * Get the way row buttons are rendered floating/inline
	 * Can be set either by global config or list options
	 *
	 * In Fabrik 3.1 we've deprecated the floating action code - should always return inline
	 *
	 * @since   3.0.7
	 *
	 * @return  string
	 */

	public function actionMethod()
	{
		$params = $this->getParams();
		$fbConfig = JComponentHelper::getParams('com_fabrik');

		if ($params->get('actionMethod', 'default') == 'default')
		{
			// Use global
			$fbConfig = JComponentHelper::getParams('com_fabrik');
			$globalDefault = $fbConfig->get('actionMethod', 'floating');

			// Floating depreacted in J3
			if (FabrikWorker::j3() && $globalDefault === 'floating')
			{
				return 'inline';
			}

			return $globalDefault;
		}
		else
		{
			$default = $params->get('actionMethod', 'floating');
		}
		// Floating depreacted in J3
		if (FabrikWorker::j3() && $default === 'floating')
		{
			return 'inline';
		}

		return $default;
	}

	/**
	 * Get delete button
	 *
	 * @param   string  $tpl      Template
	 * @param   bool    $heading  Is this the check all delete button
	 *
	 * @since 3.0
	 *
	 * @return	string	delete button wrapped in <li>
	 */

	protected function deleteButton($tpl = '', $heading = false)
	{
		$params = $this->getParams();
		$label = JText::_('COM_FABRIK_DELETE');
		$buttonAction = $this->actionMethod();
		$tpl = $this->getTmpl();
		$j3 = FabrikWorker::j3();
		$text = $buttonAction == 'dropdown' ? $label : '<span class="hidden">' . $label . '</span>';
		$btnClass = ($j3 && $buttonAction != 'dropdown') ? 'btn ' : '';
		$iconClass = $j3 ? 'icon-remove' : 'icon-minus';
		$label = $j3 ? ' ' . JText::_('COM_FABRIK_DELETE') : '<span>' . JText::_('COM_FABRIK_DELETE') . '</span>';
		$btn = '<a href="#" class="' . $btnClass . 'delete" data-listRef="list_' . $this->getRenderContext()
				. '" title="' . JText::_('COM_FABRIK_DELETE') . '">'
				. FabrikHelperHTML::image('delete.png', 'list', $tpl, array('alt' => $label, 'icon-class' => $iconClass)) . ' ' . $text . '</a>';

		return $j3 ? $btn : '<li class="fabrik_delete">' . $btn . '</li>';
	}

	/**
	 * Get a list of possible menus
	 * USED TO BUILD RELTED TABLE LNKS WITH CORRECT iTEMD AND TEMPLATE
	 *
	 * @since   2.0.4
	 *
	 * @return  array  linked table menu items
	 */

	protected function getTableLinks()
	{
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');

		if (isset($this->tableLinks))
		{
			return $this->tableLinks;
		}

		$db = JFactory::getDbo();
		$joinsToThisKey = $this->getJoinsToThisKey();

		if (empty($joinsToThisKey))
		{
			$this->tableLinks = array();
		}
		else
		{
			$query = $db->getQuery(true);
			$query->select('*')->from('#__menu');

			foreach ($joinsToThisKey as $element)
			{
				$linkWhere[] = 'link LIKE "index.php?option=com_' . $package . '&view=list&listid=' . (int) $element->list_id . '%"';
			}

			$where = 'type = "component" AND (' . implode(' OR ', $linkWhere) . ')';
			$query->where($where);
			$db->setQuery($query);
			$this->tableLinks = $db->loadObjectList();
		}

		return $this->tableLinks;
	}

	/**
	 * For related table links get the record count for each of the table's rows
	 *
	 * @param   object  &$element  element
	 * @param   array   $pks       primary keys to count on
	 *
	 * @return  array  counts key'd on element primary key
	 */

	public function getRecordCounts(&$element, $pks = array())
	{
		if (!isset($this->recordCounts))
		{
			$this->recordCounts = array();
		}

		$app = JFactory::getApplication();
		$input = $app->input;
		$k = $element->element_id;

		if (array_key_exists($k, $this->recordCounts))
		{
			return $this->recordCounts[$k];
		}

		$listModel = JModelLegacy::getInstance('List', 'FabrikFEModel');
		$listModel->setId($element->list_id);
		$db = $listModel->getDb();
		$elementModel = $listModel->getFormModel()->getElement($element->element_id, true);
		$key = $elementModel->getFullName(false, false);
		$linkKey = FabrikString::safeColName($key);
		$fparams = $listModel->getParams();

		// Ensure that the facted list's "require filters" option is set to false
		$fparams->set('require-filter', false);

		// Ignore facted lists session filters
		$origIncSesssionFilters = $input->get('fabrik_incsessionfilters', true);
		$input->set('fabrik_incsessionfilters', false);
		$query = $db->getQuery(true);
		$query = $listModel->buildQueryWhere($input->getInt('incfilters', 0), $query);

		if (!empty($pks))
		{
			// Only load the current record sets record counts
			$query->where($linkKey . ' IN (' . implode(',', $pks) . ')');
		}
		// Force reload of join sql
		$listModel->set('joinsSQL', null);

		// Trigger load of joins without cdd elements - seems to mess up count otherwise
		$listModel->set('includeCddInJoin', false);
		$k2 = $db->quote(FabrikString::safeColNameToArrayKey($key));

		// $$$ Jannus - see http://fabrikar.com/forums/showthread.php?t=20751
		$distinct = $listModel->mergeJoinedData() ? 'DISTINCT ' : '';
		$item = $listModel->getTable();
		$query->select($k2 . ' AS linkKey, ' . $linkKey . ' AS id, COUNT(' . $distinct . $item->db_primary_key . ') AS total')->from($item->db_table_name);
		$query = $listModel->buildQueryJoin($query);
		$listModel->set('includeCddInJoin', true);
		$query->group($linkKey);
		$db->setQuery($query);
		$this->recordCounts[$k] = $db->loadObjectList('id');
		$this->recordCounts[$k]['linkKey'] = FabrikString::safeColNameToArrayKey($key);
		FabrikHelperHTML::debug($query->dump(), 'getRecordCounts query: ' . $linkKey);
		FabrikHelperHTML::debug($this->recordCounts[$k], 'getRecordCounts data: ' . $linkKey);
		$input->set('fabrik_incsessionfilters', $origIncSesssionFilters);

		return $this->recordCounts[$k];
	}

	/**
	 * Creates the html <a> link allowing you to edit other forms from the list
	 * E.g. Faceted browsing: those specified in the list's "Form's whose primary keys link to this table"
	 *
	 * @param   bool    $popUp    is popup link
	 * @param   object  $element  27/06/2011 - changed to passing in element
	 * @param   object  $row      current list row
	 * @param   string  $key      key
	 * @param   string  $val      value
	 * @param   bool    $usekey   use the key
	 * @param   int     $f        repeat value 27/11/2011
	 *
	 * @return  string	<a> html part
	 */

	public function viewFormLink($popUp = false, $element = null, $row = null, $key = '', $val = '', $usekey = false, $f = 0)
	{
		$elKey = $element->list_id . '-' . $element->form_id . '-' . $element->element_id;
		$params = $this->getParams();
		$listid = $element->list_id;
		$formid = $element->form_id;
		$linkedFormText = $params->get('linkedformtext');
		$facted = $params->get('factedlinks');
		$linkedFormText = JArrayHelper::fromObject($facted->linkedformtext);
		$msg = JArrayHelper::getValue($linkedFormText, $elKey);
		$row2 = JArrayHelper::fromObject($row);
		$label = $this->parseMessageForRowHolder($msg, $row2);
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$Itemid = FabrikWorker::itemId();

		if (is_null($listid))
		{
			$list = $this->getTable();
			$listid = $list->id;
		}

		if (is_null($formid))
		{
			$form = $this->getFormModel()->getForm();
			$formid = $form->id;
		}

		$facetTable = $this->facetedTable($listid);

		if (!$facetTable->canAdd())
		{
			return '<div style="text-align:center"><a title="' . JText::_('JERROR_ALERTNOAUTHOR')
			. '"><img src="' . COM_FABRIK_LIVESITE . 'media/com_fabrik/images/login.png" alt="' . JText::_('JERROR_ALERTNOAUTHOR') . '" /></a></div>';
		}

		if ($app->isAdmin())
		{
			$bits[] = 'task=form.view';
			$bits[] = 'cid=' . $formid;
		}
		else
		{
			$bits[] = 'view=form';
			$bits[] = 'Itemid=' . $Itemid;
		}

		$bits[] = 'formid=' . $formid;
		$bits[] = 'referring_table=' . $this->getTable()->id;

		// $$$ hugh - change in databasejoin getValue() means we have to append _raw to key name
		if ($key != '')
		{
			$bits[] = $key . '_raw=' . $val;
		}

		if ($usekey and $key != '' and !is_null($row))
		{
			$bits[] = 'usekey=' . FabrikString::shortColName($key);
			$bits[] = 'rowid=' . $row->slug;
		}

		$url = 'index.php?option=com_' . $package . '&' . implode('&', $bits);
		$url = JRoute::_($url);

		if (is_null($label) || $label == '')
		{
			$label = JText::_('COM_FABRIK_LINKED_FORM_ADD');
		}

		$icon = '<i class="icon-plus"></i> ';
		$trigger = $popUp ? 'data-fabrik-view="form"' : '';
		$link = '<a ' . $trigger . ' href="' . $url . '" title="' . $label . '">' . $icon . $label . '</a>';
		$url = '<span class="addbutton">' . $link . '</span></a>';

		return $url;
	}

	/**
	 * Get one of the current tables facet tables
	 *(used in tables that link to this lists links)
	 *
	 * @param   int  $id  list id
	 *
	 * @return  object	table
	 */

	protected function facetedTable($id)
	{
		if (!isset($this->facettables))
		{
			$this->facettables = array();
		}

		if (!array_key_exists($id, $this->facettables))
		{
			$this->facettables[$id] = JModelLegacy::getInstance('List', 'FabrikFEModel');
			$this->facettables[$id]->setId($id);
		}

		return $this->facettables[$id];
	}

	/**
	 * Build the link (<a href..>) for viewing list data
	 *
	 * @param   bool    $popUp    is the link to generated a popup to show
	 * @param   object  $element  27/06/2011
	 * @param   object  $row      current list row data
	 * @param   string  $key      28/06/2011 - do longer passed in with _raw appended (done in this method)
	 * @param   string  $val      value
	 * @param   int     $count    number of related records
	 * @param   int     $f        ref to related data admin info 27/16/2011
	 *
	 * @return  string
	 */

	public function viewDataLink($popUp = false, $element = null, $row = null, $key = '', $val = '', $count = 0, $f = null)
	{
		$count = (int) $count;
		$elKey = $element->list_id . '-' . $element->form_id . '-' . $element->element_id;
		$listid = $element->list_id;
		$html = array();
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$params = $this->getParams();
		$facted = $params->get('factedlinks');

		/* $$$ hugh - we are getting element keys that aren't in the linkedlisttext.
		 * not sure why, so added this defensive code.  Should probably find out
		* why though!  I just needed to make this error go away NAO!
		*/
		$linkedListText = isset($facted->linkedlisttext->$elKey) ? $facted->linkedlisttext->$elKey : '';
		$row2 = JArrayHelper::fromObject($row);
		$label = $this->parseMessageForRowHolder($linkedListText, $row2);

		$action = $app->isAdmin() ? 'task' : 'view';

		if (is_null($listid))
		{
			$list = $this->getTable();
			$listid = $list->id;
		}

		$facetTable = $this->facetedTable($listid);

		if (!$facetTable->canView())
		{
			return '<div style="text-align:center"><a title="' . JText::_('COM_FABRIK_NO_ACCESS_PLEASE_LOGIN')
			. '"><img src="' . COM_FABRIK_LIVESITE . 'media/com_fabrik/images/login.png"
				alt="' . JText::_('COM_FABRIK_NO_ACCESS_PLEASE_LOGIN') . '" /></a></div>';
		}

		$showRelated = (int) $params->get('show_related_info', 0);
		$emptyLabel = $showRelated === 1 ? JText::_('COM_FABRIK_NO_RECORDS') : '';
		$tlabel = ($count === 0) ? $emptyLabel : '(0) ' . $label;
		$showRelatedAdd = (int) $params->get('show_related_add', 0);
		$aExisitngLinkedForms = (array) $params->get('linkedform');
		$linkedForm = JArrayHelper::getValue($aExisitngLinkedForms, $f, false);
		$addLink = $linkedForm == '0' ? $this->viewFormLink($popUp, $element, $row, $key, $val, false, $f) : '';

		if ($count === 0)
		{
			$html[] = '<div style="text-align:center" class="related_data_norecords">' . $tlabel . '</div>';
		}

		$key .= '_raw';

		if ($label === '')
		{
			$label = JText::_('COM_FABRIK_VIEW');
		}

		$title = $label;
		$label = '<span class="fabrik_related_data_count">(' . $count . ')</span> ' . $label;
		$icon = '<i class="icon-list-view"></i> ';
		$url = $this->releatedDataURL($key, $val, $listid);

		if ($showRelated == 0 || ($showRelated == 2  && $count))
		{
			$trigger = $popUp ? 'data-fabrik-view="list"' : '';
			$html[] = '<a class="related_data" ' . $trigger . ' href="' . $url . '" title="' . $title . '">' . $icon . $label . '</a>';
		}

		if ($addLink != '' && ($showRelatedAdd === 1 || ($showRelatedAdd === 2 && $count === 0)))
		{
			$html[] = '<div>' . $addLink . '</div>';
		}

		return implode("\n", $html);
	}

	/**
	 * Make related data URL
	 *
	 * @param   string  $key     Releated link key
	 * @param   string  $val     Related link value
	 * @param   int     $listid  List id
	 *
	 * @since   3.0.8
	 *
	 * @return  string  URL
	 */

	protected function releatedDataURL($key, $val, $listid)
	{
		$app = JFactory::getApplication();
		$Itemid = FabrikWorker::itemId();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$url = 'index.php?option=com_' . $package . '&';

		if ($app->isAdmin())
		{
			$bits[] = 'task=list.view';
			$bits[] = 'cid=' . $listid;
		}
		else
		{
			$bits[] = 'view=list';
			$bits[] = 'listid=' . $listid;
			$listLinks = $this->getTableLinks();

			// $$$ rob 01/03/2011 find at matching itemid in another menu item for the related data link
			foreach ($listLinks as $listLink)
			{
				if (strstr($listLink->link, 'index.php?option=com_' . $package . '&view=list&listid=' . $listid))
				{
					$bits[] = 'Itemid=' . $listLink->id;
					$Itemid = $listLink->id;
					break;
				}
			}

			$bits[] = 'Itemid=' . $Itemid;
		}

		if ($key != '')
		{
			$bits[] = $key . '=' . $val;
		}

		$bits[] = 'limitstart' . $listid . '=0';
		$bits[] = 'resetfilters=1';

		// Nope stops url filter form workin on related data :(
		// $bits[] = 'clearfilters=1';

		// Test for releated data, filter once, go backt o main list re-filter -
		$bits[] = 'fabrik_incsessionfilters=0';
		$url .= implode('&', $bits);
		$url = JRoute::_($url);

		return $url;
	}

	/**
	 * Add a normal/custom link to the element data
	 *
	 * @param   string  $data           Element data
	 * @param   object  &$elementModel  Element model
	 * @param   object  $row            All row data
	 * @param   int     $repeatCounter  Repeat group counter
	 *
	 * @return  string	element data with link added if specified
	 */

	public function _addLink($data, &$elementModel, $row, $repeatCounter = 0)
	{
		$element = $elementModel->getElement();
		$params = $elementModel->getParams();
		$customLink = trim($params->get('custom_link', ''));

		if ($this->outputFormat == 'csv' || ($element->link_to_detail == 0 && $customLink == ''))
		{
			return $data;
		}

		// $$$ rob if its a custom link then we aren't linking to the details view so we should
		// ignore the view details access settings
		if (!($this->canViewDetails($row) || $this->canEdit()) && $customLink == '')
		{
			return $data;
		}

		$list = $this->getTable();
		$primaryKeyVal = $this->getKeyIndetifier($row);
		$link = $this->linkHref($elementModel, $row, $repeatCounter);

		if ($link == '')
		{
			return $data;
		}
		// Try to remove any previously entered links
		$data = preg_replace('/<a(.*?)>|<\/a>/', '', $data);
		$class = '';

		if ($this->canViewDetails($row))
		{
			$class = ' fabrik_view';
		}

		if ($this->canEdit($row))
		{
			$class = ' fabrik_edit';
		}

		$loadMethod = $params->get('custom_link', '') == '' ? 'xhr' : 'iframe';
		$class = 'fabrik___rowlink ' . $class;
		$dataList = 'list_' . $this->getRenderContext();
		$data = '<a data-loadmethod="' . $loadMethod . '" data-list="' . $dataList . '" class="' . $class . '" href="' . $link . '">' . $data
		. '</a>';

		return $data;
	}

	/**
	 * Get the href for the edit/details link
	 *
	 * @param   object  $elementModel   Element model
	 * @param   array   $row            Lists current row data
	 * @param   int     $repeatCounter  Repeat group counter
	 *
	 * @since   2.0.4
	 *
	 * @return  string	link href
	 */

	public function linkHref($elementModel, $row, $repeatCounter = 0)
	{
		$element = $elementModel->getElement();
		$table = $this->getTable();
		$params = $elementModel->getParams();
		$customLink = $params->get('custom_link');
		$link = '';

		if ($customLink == '')
		{
			// $$$ rob only test canEdit and canView on stardard edit links - if custom we should always use them,
			// 3.0 get either edit or view link - as viewDetailslInk now always returns the view details link
			if ($this->canEdit($row))
			{
				$this->_aLinkElements[] = $element->name;
				$link = $this->editLink($row);
			}
			elseif ($this->canViewDetails($row))
			{
				$this->_aLinkElements[] = $element->name;
				$link = $this->viewDetailsLink($row);
			}
		}
		else
		{
			$array = JArrayHelper::fromObject($row);

			foreach ($array as $k => &$v)
			{
				/* $$$ hugh - not everything is JSON, some stuff is just plain strings.
				 * So we need to see if JSON encoding failed, and only use result if it didn't.
				* $v = json_decode($v, true);
				*/
				if (is_array($v))
				{
					$v = JArrayHelper::getValue($v, $repeatCounter);
				}
				else
				{
					$v2 = json_decode($v, true);

					if ($v2 !== null)
					{
						if (is_array($v2))
						{
							$v = JArrayHelper::getValue($v2, $repeatCounter);
						}
						else
						{
							$v = $v2;
						}
					}
				}
			}

			$array['rowid'] = $this->getSlug($row);
			$array['listid'] = $table->id;
			$link = JRoute::_($this->parseMessageForRowHolder($customLink, $array));
		}

		return $link;
	}

	/**
	 * get query to make records
	 *
	 * @return  string	sql
	 */

	public function buildQuery()
	{
		$profiler = JProfiler::getInstance('Application');
		$app = JFactory::getApplication();
		$input = $app->input;
		JDEBUG ? $profiler->mark('buildQuery: start') : null;
		$query = array();
		$table = $this->getTable();

		if ($this->mergeJoinedData())
		{
			/* $$$ rob - get a list of the main table's ids limited on the navigation
			 * this will then be used to filter the main query,
			* by modifying the where part of the query
			*/
			$db = $this->getDb();
			$table = $this->getTable();

			/* $$$ rob 23/05/2012 if the search data is in the joined records we want to get the id's for the joined records and not the master record
			 see http://fabrikar.com/forums/showthread.php?t=26400. This is a partial hack as I can't see how we know which joined record is really last
			$$$ rob 25/05/2012 - slight change so that we work our way up the pk/fk list until we find some ids.
			$$$ hugh, later in the day 25/05/2012 - big OOOOPS, see comment below about table_key vs table_join_key!
			erm no not a mistake!?! reverted as no example of what was wrong with original code
			*/
			$joins = $this->getJoins();

			// Default to the primary key as before this fix
			$lookupC = 0;
			$tmpPks = array();

			foreach ($joins as $join)
			{
				// $$$ hugh - added repeatElement, as _makeJoinAliases() is going to set canUse to false for those,
				// so they won't get included in the query ... so will blow up if we reference them with __pk_calX selection
				if ($join->params->get('type') !== 'element' && $join->params->get('type') !== 'repeatElement')
				{
					// $$$ hugh - need to be $lookupC + 1, otherwise we end up with two 0's, 'cos we added main table above

					/**
					 * [non-merged data]
					 *
					 * country	towm
					 * ------------------------------
					 * france	la rochelle
					 * france	paris
					 * france	bordeaux
					 *
					 * [merged data]
					 *
					 * country	town
					 * -------------------------------
					 * france	la rochelle
					 * 			paris
					 * 			bordeaux
					 *
					 * [now search on town = 'la rochelle']
					 *
					 * If we dont use this new code then the search results show all three towns.
					 * By getting the lowest set of complete primary keys (in this example the town ids) we set our query to be:
					 *
					 * where town_id IN (1)
					 *
					 * which gives a search result of
					 *
					 * country	town
					 * -------------------------------
					 * france	la rochelle
					 *
					 */
					$pk = $join->params->get('pk');

					if (!array_key_exists($pk, $tmpPks) || !is_array($tmpPks[$pk]))
					{
						$tmpPks[$pk] = array($pk);
					}
					else
					{
						if (count($tmpPks[$pk]) == 1)
						{
							$v = str_replace('`', '', $tmpPks[$pk][0]);
							$v = explode('.', $v);
							$v[0] = $v[0] . '_0';
							$tmpPks[$pk][0] = $db->quoteName($v[0] . '.' . $v[1]);
						}

						$v = str_replace('`', '', $pk);
						$v = explode('.', $v);
						$v[0] = $v[0] . '_' . count($tmpPks[$pk]);
						$tmpPks[$pk][] = $db->quoteName($v[0] . '.' . $v[1]);
					}
				}
			}
			// Check for duplicate pks if so we can presume that they are aliased with _X in from query
			$lookupC = 0;
			$lookUps = array('DISTINCT ' . $table->db_primary_key . ' AS __pk_val' . $lookupC);
			$lookUpNames = array($table->db_primary_key);

			foreach ($tmpPks as $pks)
			{
				foreach ($pks as $pk)
				{
					$lookUps[] = $pk . ' AS __pk_val' . ($lookupC + 1);
					$lookUpNames[] = $pk;
					$lookupC++;
				}
			}

			// $$$ rob if no ordering applied i had results where main record (e.g. UK) was shown in 2 lines not next to each other
			// causing them not to be merged and a 6 rows shown when limit set to 5. So below, if no order by set then order by main pk asc
			$by = trim($table->order_by) === '' ? array() : (array) json_decode($table->order_by);

			if (empty($by))
			{
				$dir = (array) json_decode($table->order_dir);
				array_unshift($dir, 'ASC');
				$table->order_dir = json_encode($dir);

				$by = (array) json_decode($table->order_by);
				array_unshift($by, $table->db_primary_key);
				$table->order_by = json_encode($by);
			}

			// $$$ rob build order first so that we know of any elemenets we need to include in the select statement
			$order = $this->buildQueryOrder();
			$this->selectedOrderFields = (array) $this->selectedOrderFields;
			$this->selectedOrderFields = array_unique(array_merge($lookUps, $this->selectedOrderFields));

			$query['select'] = 'SELECT  ' . implode(', ', $this->selectedOrderFields) . ' FROM ' . $db->quoteName($table->db_table_name);

			$query['join'] = $this->buildQueryJoin();
			$query['where'] = $this->buildQueryWhere($input->get('incfilters', 1));
			$query['groupby'] = $this->buildQueryGroupBy();
			$query['order'] = $order;

			// Check that the order by fields are in the select statement
			$squery = implode(' ', $query);

			// Can't limit the query here as this gives incorrect _data array.
			// $db->setQuery($squery, $this->limitStart, $this->limitLength);
			$db->setQuery($squery);
			FabrikHelperHTML::debug($squery, 'table:mergeJoinedData get ids');
			$ids = array();
			$idRows = $db->loadObjectList();
			$maxPossibleIds = count($idRows);

			// An array of the lists pk values
			$mainKeys = array();

			foreach ($idRows as $r)
			{
				$mainKeys[] = $db->quote($r->__pk_val0);
			}
			// Chop up main keys for list limitstart, length to cull the data down to the correct length as defined by the page nav/ list settings
			$mainKeys = array_slice(array_unique($mainKeys), $this->limitStart, $this->limitLength);
			/**
			 * $$$ rob get an array containing the PRIMARY key values for each joined tables data.
			 * Stop as soon as we have a set of ids totaling the sum of records contained in $idRows
			*/

			while (count($ids) < $maxPossibleIds && $lookupC >= 0)
			{
				$ids = JArrayHelper::getColumn($idRows, '__pk_val' . $lookupC);

				for ($idx = count($ids) - 1; $idx >= 0; $idx--)
				{
					if ($ids[$idx] == '')
					{
						unset($ids[$idx]);
					}
					else
					{
						$ids[$idx] = $db->quote($ids[$idx]);
					}
				}

				if (count($ids) < $maxPossibleIds)
				{
					$lookupC--;
				}
			}
		}

		// Now lets actually construct the query that will get the required records:
		$query = array();
		$query['select'] = $this->buildQuerySelect();
		JDEBUG ? $profiler->mark('queryselect: got') : null;
		$query['join'] = $this->buildQueryJoin();
		JDEBUG ? $profiler->mark('queryjoin: got') : null;

		if ($this->mergeJoinedData())
		{
			/* $$$ rob We've already used buildQueryWhere to get our list of main pk ids.
			 * so lets use that list of ids to create the where statement. This will return 5/10/20 etc
			* records from our main table, as per our page nav, even if a main record has 3 rows of joined
			* data. If no ids found then do where 1 = -1 to return no records
			*/
			if (!empty($ids))
			{
				$query['where'] = ' WHERE ' . $lookUpNames[$lookupC] . ' IN (' . implode(array_unique($ids), ',') . ')';

				if (!empty($mainKeys))
				{
					// Limit to the current page
					$query['where'] .= ' AND ' . $table->db_primary_key . ' IN (' . implode($mainKeys, ',') . ')';
				}
			}
			else
			{
				$query['where'] = ' WHERE 1 = -1';
			}
		}
		else
		{
			// $$$ rob we aren't merging joined records so lets just add the standard where query
			// Incfilters set when exporting as CSV
			$query['where'] = $this->buildQueryWhere($input->get('incfilters', 1));
		}

		$query['groupby'] = $this->buildQueryGroupBy();
		$query['order'] = $this->buildQueryOrder();
		$query = $this->pluginQuery($query);
		$query = implode(' ', $query);
		$this->mainQuery = $query;

		return $query;
	}

	/**
	 * Pass an sql query through the table plug-ins
	 *
	 * @param   string  $query  sql query
	 *
	 * @return  string	altered query.
	 */

	public function pluginQuery($query)
	{
		// Pass the query as an object property so it can be updated via reference
		$args = new stdClass;
		$args->query = $query;
		FabrikWorker::getPluginManager()->runPlugins('onQueryBuilt', $this, 'list', $args);
		$query = $args->query;

		return $query;
	}

	/**
	 * Add the slug field to the select fields, called from buildQuerySelect()
	 *
	 * @param   array  &$fields  fields
	 *
	 * @since 3.0.6
	 *
	 * @return  void
	 */

	private function selectSlug(&$fields)
	{
		$formModel = $this->getFormModel();
		$item = $this->getTable();
		$pk = FabrikString::safeColName($item->db_primary_key);
		$params = $this->getParams();

		if (in_array($this->outputFormat, array('raw', 'html', 'feed', 'pdf', 'phocapdf')))
		{
			$slug = $params->get('sef-slug');
			$raw = JString::substr($slug, JString::strlen($slug) - 4, 4) == '_raw' ? true : false;
			$slug = FabrikString::rtrimword($slug, '_raw');
			$slugElement = $formModel->getElement($slug);

			if ($slugElement)
			{
				$slug = $slugElement->getSlugName($raw);
			}

			// Test slug is not ``.``
			if (preg_match('/[A-Z|a-z][0-9]/', $slug))
			{
				$slug = FabrikString::safeColName($slug);
				$fields[] = "CONCAT_WS(':', $pk, $slug) AS slug";
			}
			else
			{
				if ($pk !== '``')
				{
					$fields[] = $pk . ' AS slug';
				}
			}
		}
	}

	/**
	 * Get the select part of the query
	 *
	 * @param   string  $mode  list/form - effects which elements are selected
	 *
	 * @return  string
	 */

	public function buildQuerySelect($mode = 'list')
	{
		$profiler = JProfiler::getInstance('Application');
		JDEBUG ? $profiler->mark('queryselect: start') : null;
		$db = $this->getDb();
		$form = $this->getFormModel();
		$table = $this->getTable();
		$form->getGroupsHiarachy();
		JDEBUG ? $profiler->mark('queryselect: fields load start') : null;
		$fields = $this->getAsFields($mode);
		$pk = FabrikString::safeColName($table->db_primary_key);
		$params = $this->getParams();
		$this->selectSlug($fields);
		JDEBUG ? $profiler->mark('queryselect: fields loaded') : null;
		$sfields = (empty($fields)) ? '' : implode(", \n ", $fields) . "\n ";

		/**
		 * Testing potential fix for FOUND_ROWS performance issue on large tables.  If merging,
		 * we never do a SELECT FOUND_ROWS(), so no need to use SQL_CALC_FOUND_ROWS.
		 */
		$calc_found_rows = $this->mergeJoinedData() ? '' : 'SQL_CALC_FOUND_ROWS';

		// $$$rob added raw as an option to fix issue in saving calendar data
		if (trim($table->db_primary_key) != '' && (in_array($this->outputFormat, array('raw', 'html', 'feed', 'pdf', 'phocapdf', 'csv', 'word', 'yql'))))
		{
			$sfields .= ', ';
			$strPKey = $pk . ' AS ' . $db->quoteName('__pk_val') . "\n";
			$query = 'SELECT ' . $calc_found_rows . ' DISTINCT ' . $sfields . $strPKey;
		}
		else
		{
			$query = 'SELECT ' . $calc_found_rows . ' DISTINCT ' . trim($sfields, ", \n") . "\n";
		}

		$query .= ' FROM ' . $db->quoteName($table->db_table_name) . " \n";

		return $query;
	}

	/**
	 * Get the part of the sql statement that orders the table data
	 * Since 3.0.7 caches the results as calling orderBy twice when using single ordering in admin module anules the user selected order by
	 *
	 * @param   mixed  $query  False or a query object
	 *
	 * @return  mixed  string or query object - Ordering part of sql statement
	 */

	public function buildQueryOrder($query = false)
	{
		if (isset($this->orderBy))
		{
			return $query ? $query : $this->orderBy;
		}

		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$params = $this->getParams();
		$app = JFactory::getApplication();
		$input = $app->input;
		$formModel = $this->getFormModel();
		$table = $this->getTable();
		$db = $this->getDb();
		$this->selectedOrderFields = array();

		if ($this->outputFormat == 'fabrikfeed' || $this->outputFormat == 'feed')
		{
			$dateColId = (int) $params->get('feed_date', 0);
			$dateColElement = $formModel->getElement($dateColId, true);
			$dateCol = $db->quoteName($dateColElement->getFullName(false, false, false));

			if ($dateColId !== 0)
			{
				$this->order_dir = 'DESC';
				$this->order_by = $dateCol;

				if (!$query)
				{
					return "\n" . ' ORDER BY ' . $dateCol . ' DESC';
				}
				else
				{
					$query->order($dateCol . ' DESC');

					return $query;
				}
			}
		}

		$session = JFactory::getSession();

		/**
		 * When list reordered the controller runs order() and
		 * stores the order settings in the session by calling setOrderByAndDir()
		 * it then redirects to the list view and here all we need to do it get
		 * those order settings from the session
		*/

		$elements = $this->getElements();

		// Build the order by statement from the session
		$strOrder = '';
		$clearOrdering = (bool) $input->getInt('clearordering', false) && $input->get('task') !== 'order';
		$singleOrdering = $this->singleOrdering();
		$id = $this->getId();

		foreach ($elements as $element)
		{
			$context = 'com_' . $package . '.list' . $this->getRenderContext() . '.order.' . $element->getElement()->id;

			if ($clearOrdering)
			{
				$session->set($context, '');
			}
			else
			{
				// $$$tom Added single-ordering option
				if (!$singleOrdering || ($singleOrdering && $element->getElement()->id == $input->getInt('orderby')))
				{
					$dir = $session->get($context);

					if ($dir != '' && $dir != '-' && trim($dir) != 'Array')
					{
						$strOrder == '' ? $strOrder = "\n ORDER BY " : $strOrder .= ',';
						$strOrder .= $element->getOrderByName() . ' ' . $dir;
						$this->orderEls[] = $element->getOrderByName();
						$this->orderDirs[] = $dir;
						$element->getAsField_html($this->selectedOrderFields, $aAsFields);
					}
				}
				else
				{
					$session->set($context, '');
				}
			}
		}
		// If nothing found in session use default ordering (or that set by querystring)
		if ($strOrder == '')
		{
			$orderbys = explode(',', $input->get('order_by', ''));

			if ($orderbys[0] == '')
			{
				$orderbys = json_decode($table->order_by, true);
			}
			// $$$ not sure why, but sometimes $orderbys is NULL at this point.
			if (!isset($orderbys))
			{
				$orderbys = array();
			}
			// Covert ids to names (were stored as names but then stored as ids)
			foreach ($orderbys as &$orderby)
			{
				if (is_numeric($orderby))
				{
					$elementModel = $formModel->getElement($orderby, true);
					$orderby = $elementModel ? $elementModel->getOrderByName() : $orderby;
				}
			}

			$orderdirs = explode(',',  $input->get('order_dir', ''));

			if ($orderdirs[0] == '')
			{
				$orderdirs = json_decode($table->order_dir, true);
			}

			$els = $this->getElements('filtername');

			if (!empty($orderbys))
			{
				$bits = array();
				$o = 0;

				foreach ($orderbys as $orderbyRaw)
				{
					$dir = JArrayHelper::getValue($orderdirs, $o, 'desc');

					if ($orderbyRaw !== '')
					{
						// $$$ hugh - getOrderByName can return a CONCAT, ie join element ...

						/*
						 * $$$ hugh - OK, we need to test for this twice, because older elements
						 * which get converted form names to ids above have already been run through
						 * getOrderByName().  So first check here ...
						 */
						if (!JString::stristr($orderbyRaw, 'CONCAT(') && !JString::stristr($orderbyRaw, 'CONCAT_WS('))
						{
							$orderbyRaw = FabrikString::safeColName($orderbyRaw);

							if (array_key_exists($orderbyRaw, $els))
							{
								$field = $els[$orderbyRaw]->getOrderByName();
								/*
								 * $$$ hugh - ... second check for CONCAT, see comment above
								 * $$$ @TODO why don't we just embed this logic in safeColName(), so
								 * it recognizes a CONCAT and treats it accordingly?
								 */
								if (!JString::stristr($field, 'CONCAT(') && !JString::stristr($field, 'CONCAT_WS('))
								{
									$field = FabrikString::safeColName($field);
								}

								$bits[] = " $field $dir";
								$this->orderEls[] = $field;
								$this->orderDirs[] = $dir;
							}
							else
							{
								if (strstr($orderbyRaw, '_raw`'))
								{
									$orderbyRaw = FabrikString::safeColNameToArrayKey($orderbyRaw);
								}

								$bits[] = " $orderbyRaw $dir";
								$this->orderEls[] = $orderbyRaw;
								$this->orderDirs[] = $dir;
							}
						}
						else
						{
							// If it was a CONCAT(), just add it with no other checks or processing
							$bits[] = " $orderbyRaw $dir";
							$this->orderEls[] = $orderbyRaw;
							$this->orderDirs[] = $dir;
						}
					}

					$o ++;
				}

				if (!empty($bits))
				{
					if (!$query)
					{
						$strOrder = "\n ORDER BY" . implode(',', $bits);
					}
					else
					{
						$query->order(implode(',', $bits));
					}
				}
			}
		}
		/* apply group ordering
		 * @TODO - explain something to hugh!  Why is this "group ordering"?  AFAICT, it's just a secondary
		* order by, isn't specific to the Group By feature in any way?  So why not just put this option in
		*/
		$groupOrderBy = $params->get('group_by_order');

		if ($groupOrderBy != '')
		{
			$groupOrderDir = $params->get('group_by_order_dir');
			$strOrder == '' ? $strOrder = "\n ORDER BY " : $strOrder .= ',';
			$orderby = strstr($groupOrderBy, '_raw`') ? FabrikString::safeColNameToArrayKey($groupOrderBy) : FabrikString::safeColName($groupOrderBy);

			if (!$query)
			{
				$strOrder .= $orderby . ' ' . $groupOrderDir;
			}
			else
			{
				$query->order($orderby . ' ' . $groupOrderDir);
			}

			$this->orderEls[] = $orderby;
			$this->orderDirs[] = $groupOrderDir;
		}

		$this->orderBy = $query === false ? $strOrder : $query;

		return $this->orderBy;
	}

	/**
	 * Should we order on multiple elements or one
	 *
	 * @since   3.0.7 (refractored from buildQueryOrder())
	 *
	 * @return  bool
	 */

	public function singleOrdering()
	{
		$params = $this->getParams();

		if ($params->get('enable_single_sorting', 'default') == 'default')
		{
			// Use global
			$fbConfig = JComponentHelper::getParams('com_fabrik');
			$singleOrdering = $fbConfig->get('enable_single_sorting', false);
		}
		else
		{
			$singleOrdering = $params->get('enable_single_sorting', false);
		}

		return $singleOrdering;
	}

	/**
	 * Called when the table column order by is clicked
	 * store order options in session
	 *
	 * @return  void
	 */

	public function setOrderByAndDir()
	{
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$session = JFactory::getSession();
		$app = JFactory::getApplication();
		$input = $app->input;
		$postOrderBy = $input->getInt('orderby', '');
		$postOrderDir = $input->get('orderdir', '');
		$arOrderVals = array('asc', 'desc', '-');
		$id = $this->getRenderContext();

		if (in_array($postOrderDir, $arOrderVals))
		{
			$context = 'com_' . $package . '.list' . $id . '.order.' . $postOrderBy;
			$session->set($context, $postOrderDir);
		}
	}

	/**
	 * Get the part of the sql query that creates the joins
	 * used when building the table's data
	 *
	 * @param   mixed  $query  JQuery object or false
	 *
	 * @return  mixed  string or join query - join sql
	 */

	public function buildQueryJoin($query = false)
	{
		$db = FabrikWorker::getDbo();
		$ref = $query ? '1' : '0';

		if (isset($this->joinsSQL[$ref]))
		{
			return $this->joinsSQL[$ref];
		}

		$statements = array();
		$table = $this->getTable();
		$selectedTables[] = $table->db_table_name;
		$return = array();
		$joins = ($this->get('includeCddInJoin', true) === false) ? $this->getJoinsNoCdd() : $this->getJoins();
		$tableGroups = array();

		foreach ($joins as $join)
		{
			// Used to bypass user joins if the table connect isnt the Joomla connection
			if ((int) $join->canUse === 0)
			{
				continue;
			}

			if ($join->join_type == '')
			{
				$join->join_type = 'LEFT';
			}

			$sql = JString::strtoupper($join->join_type) . ' JOIN ' . $db->quoteName($join->table_join);
			$k = FabrikString::safeColName($join->keytable . '.' . $join->table_key);

			// Check we only get the field name
			$join->table_join_key = explode('.',  $join->table_join_key);
			$join->table_join_key = array_pop($join->table_join_key);

			if ($join->table_join_alias == '')
			{
				$on = FabrikString::safeColName($join->table_join . '.' . $join->table_join_key);
				$sql .= ' ON ' . $on . ' = ' . $k;
			}
			else
			{
				$on = FabrikString::safeColName($join->table_join_alias . '.' . $join->table_join_key);
				$sql .= ' AS ' . FabrikString::safeColName($join->table_join_alias) . ' ON ' . $on . ' = ' . $k . "\n";
			}
			/* Try to order join statements to ensure that you are selecting from tables that have
			 * already been included (either via a previous join statement or the table select statement)
			*/
			if (in_array($join->keytable, $selectedTables))
			{
				$return[] = $sql;
				$selectedTables[] = $join->table_join;
			}
			else
			{
				// Didn't find anything so defer it till later

				/* $statements[$join->keytable] = $sql;
				 * $$$rob - sometimes the keytable is the same for 2 deferred joins
				* in this case the first join is incorrectly overwritten in the $statements array
				* keying on join->id should solve this
				*/
				$statements[$join->id] = array($join->keytable, $sql);
			}

			// Go through the deferred join statements and see if their table has now been selected
			foreach ($statements as $joinid => $ar)
			{
				$t = $ar[0];
				$s = $ar[1];

				if (in_array($t, $selectedTables))
				{
					if (!in_array($s, $return))
					{
						// $$$rob test to avoid duplicate join queries
						$return[] = $s;
						unset($statements[$t]);
					}
				}
			}
		}
		// $$$rob test for bug #376
		foreach ($statements as $joinid => $ar)
		{
			$s = $ar[1];

			if (!in_array($s, $return))
			{
				$return[] = $s;
			}
		}
		// 3.0 not really tested
		if ($query !== false)
		{
			foreach ($return as $r)
			{
				$words = explode(' ', trim($r));
				$type = array_shift($words);
				$statement = str_replace('JOIN', '', implode(' ', $words));
				$query->join($type, $statement);
			}

			return $query;
		}
		else
		{
			$return = implode(' ', $return);
			$this->joinsSQL[$ref] = $return;
		}

		return $query == false ? $return : $query;
	}

	/**
	 * Build query prefilter where part
	 *
	 * @param   object  $element  model
	 *
	 * @return  string
	 */

	public function buildQueryPrefilterWhere($element)
	{
		$elementName = FabrikString::safeColName($element->getFullName(false, false));
		$filters = $this->getFilterArray();
		$keys = array_keys($filters);
		$vkeys = array_keys(JArrayHelper::getValue($filters, 'value', array()));

		foreach ($vkeys as $i)
		{
			if ($filters['search_type'][$i] != 'prefilter' || $filters['key'][$i] != $elementName)
			{
				foreach ($keys as $key)
				{
					unset($filters[$key][$i]);
				}
			}
		}

		list($sqlNoFilter, $sql) = $this->_filtersToSQL($filters);
		$where = str_replace('WHERE', '', $sql);

		if ($where != '')
		{
			$where = ' AND ' . $where;
		}

		return $where;
	}

	/**
	 * Get the part of the main query that provides a group by statement
	 * only added by 'count' element plug-in at the moment
	 *
	 * @param   mixed  $query  false to return a mySQL string, JQuery object to append group statement to.
	 *
	 * @return  mixed  string if $query false, else JQuery object
	 */

	public function buildQueryGroupBy($query = false)
	{
		$groups = $this->getFormModel()->getGroupsHiarachy();

		foreach ($groups as $groupModel)
		{
			$elementModels = $groupModel->getPublishedElements();

			foreach ($elementModels as $elementModel)
			{
				$res = $elementModel->getGroupByQuery();

				if ($res != '')
				{
					$this->pluginQueryGroupBy[] = $res;
				}
			}
		}

		if (!empty($this->pluginQueryGroupBy))
		{
			if ($query === false)
			{
				return ' GROUP BY ' . implode(', ', $this->pluginQueryGroupBy);
			}
			else
			{
				$query->group($this->pluginQueryGroupBy);

				return $query;
			}
		}

		return $query === false ? '' : $query;
	}

	/**
	 * Get the part of the sql query that relates to the where statement
	 *
	 * @param   bool  $incFilters  if true the SQL contains any filters
	 *                             if false only contains prefilter sql
	 * @param   bool  $query       if false return the where as a string
	 *                             if a db query object, set the where clause
	 * Paul 2013-07-20 Add join parameter to limit where clause to main table if needed
	 * @param   bool  $doJoins     include where clauses for joins?
	 *
	 * @return  mixed	string if $query false, else JQuery object
	 */

	public function buildQueryWhere($incFilters = true, $query = false, $doJoins = true)
	{
		$pluginManager = FabrikWorker::getPluginManager();
		$pluginManager->runPlugins('onBuildQueryWhere', $this, 'list');

		$sig = !$query ? 'string' : 'query';
		$sig .= (int) $incFilters;
		$db = FabrikWorker::getDbo();

		if (isset($this->_whereSQL[$sig]))
		{
			return $this->_whereSQL[$sig][$incFilters];
		}

		$filters = $this->getFilterArray();
		$params = $this->getParams();

		/* $$$ hugh - added option to 'require filtering', so if no filters specified
		 * we return an empty table.  Only do this where $inFilters is set, so we're only doing this
		* on the main row count and data fetch, and things like
		* filter dropdowns still get built.
		*/

		if ($incFilters && !$this->gotAllRequiredFilters())
		{
			if (!$query)
			{
				return 'WHERE 1 = -1 ';
			}
			else
			{
				$query->where('1 = -1');

				return $query;
			}
		}

		$groups = $this->getFormModel()->getGroupsHiarachy();

		foreach ($groups as $groupModel)
		{
			if ($doJoins || (!$doJoins && $groupModel->isJoin()))
			{
				$elementModels = $groupModel->getPublishedElements();

				foreach ($elementModels as $elementModel)
				{
					$elementModel->appendTableWhere($this->pluginQueryWhere);
				}
			}
		}

		if (empty($filters))
		{
			// $$$ hugh - testing hack for plugins to add WHERE clauses
			if (!empty($this->pluginQueryWhere))
			{
				if (!$query)
				{
					return 'WHERE ' . implode(' AND ', $this->pluginQueryWhere);
				}
				else
				{
					$query->where(implode(' AND ', $this->pluginQueryWhere));

					return $query;
				}
			}
			else
			{
				return $query ? $query : '';
			}
		}

		$addWhere = $query == false ? true : false;
		list($sqlNoFilter, $sql) = $this->_filtersToSQL($filters, $addWhere);
		$this->_whereSQL[$sig] = array('0' => $sqlNoFilter, '1' => $sql);

		if (!$query)
		{
			return $this->_whereSQL[$sig][$incFilters];
		}
		else
		{
			if (!empty($this->_whereSQL[$sig][$incFilters]))
			{
				$query->where($this->_whereSQL[$sig][$incFilters]);
			}

			return $query;
		}
	}

	/**
	 * Used by _buildWhereQuery and buildQueryPrefilterWhere
	 * takes a filter array and returns the SQL
	 *
	 * @param   array  &$filters        filters
	 * @param   bool   $startWithWhere  start the statement with 'where' (true is for j1.5 way of making queries, false for j1.6+)
	 *
	 * @return  array	nofilter, filter sql
	 */

	private function _filtersToSQL(&$filters, $startWithWhere = true)
	{
		$prefilters = $this->groupFilterSQL($filters, 'prefilter');
		$postfilers = $this->groupFilterSQL($filters);

		if (!empty($prefilters) && !empty($postfilers))
		{
			array_unshift($postfilers, 'AND');
		}

		$sql = array_merge($prefilters, $postfilers);
		$pluginQueryWhere = trim(implode(' AND ', $this->pluginQueryWhere));

		if ($pluginQueryWhere !== '')
		{
			$pluginQueryWhere = '(' . $pluginQueryWhere . ')';

			if (!empty($sql))
			{
				$sql[] = ' AND ';
			}

			if (!empty($prefilters))
			{
				$prefilters[] = ' AND ';
			}

			$sql[] = $pluginQueryWhere;
			$prefilters[] = $pluginQueryWhere;
		}
		// Add in the where to the query
		if (!empty($sql) && $startWithWhere)
		{
			array_unshift($sql, 'WHERE');
		}

		if (!empty($prefilters) && $startWithWhere)
		{
			array_unshift($prefilters, 'WHERE');
		}

		$sql = implode($sql, ' ');
		$prefilters = implode($prefilters, ' ');

		return array($prefilters, $sql);
	}

	/**
	 * Parse the filter array and return an array of words that will make up part of the filter query
	 *
	 * @param   array   &$filters  filters
	 * @param   string  $type      * = filters, 'prefilter' = get prefilter only
	 *
	 * @return  array	words making up sql query.
	 */

	private function groupFilterSQL(&$filters, $type = '*')
	{
		$groupedCount = 0;
		$ingroup = false;
		$sql = array();

		// $$$ rob keys may no longer be in asc order as we may have filtered out some in buildQueryPrefilterWhere()
		$vkeys = array_keys(JArrayHelper::getValue($filters, 'key', array()));
		$last_i = false;
		$nullElementConditions = array('IS NULL', 'IS NOT NULL');

		while (list($vkey, $i) = each($vkeys))
		{
			// $$$rob - prefilter with element that is not published so ignore
			$condition = JString::strtoupper(JArrayHelper::getValue($filters['condition'], $i, ''));

			if (JArrayHelper::getValue($filters['sqlCond'], $i, '') == '' && !in_array($condition, $nullElementConditions))
			{
				$last_i = $i;
				continue;
			}

			if ($filters['search_type'][$i] == 'prefilter' && $type == '*')
			{
				$last_i = $i;
				continue;
			}

			if ($filters['search_type'][$i] != 'prefilter' && $type == 'prefilter')
			{
				$last_i = $i;
				continue;
			}

			$n = current($vkeys);

			if ($n === false)
			{
				// End of array
				$n = -1;
			}

			$gstart = '';
			$gend = '';

			if (!in_array($condition, $nullElementConditions))
			{
				$filters['origvalue'][$i] = 'this is ignoerd i hope';
			}
			// $$$ rob added $filters['sqlCond'][$i] test so that you can test for an empty string
			if ($filters['origvalue'][$i] != '' || $filters['sqlCond'][$i] != '')
			{
				if (array_key_exists($n, $filters['grouped_to_previous']))
				{
					if ($filters['grouped_to_previous'][$n] == 1)
					{
						if (!$ingroup)
						{
							// Search all filter after a prefilter - alter 'join' value to 'AND'
							$gstart = '(';
							$groupedCount++;
						}

						$ingroup = true;
					}
					else
					{
						if ($ingroup)
						{
							$gend = ')';
							$groupedCount--;
							$ingroup = false;
						}
					}
				}
				else
				{
					if ($ingroup)
					{
						$gend = ')';
						$groupedCount--;
						$ingroup = false;
					}
				}

				$glue = JArrayHelper::getValue($filters['join'], $i, 'AND');
				$sql[] = empty($sql) ? $gstart : $glue . ' ' . $gstart;
				$sql[] = $filters['sqlCond'][$i] . $gend;
			}

			$last_i = $i;
		}
		// $$$rob ensure opening and closing parathethis for prefilters are equal
		// Seems to occur if you have 3 prefilters with 2nd = grouped/AND and 3rd grouped/OR

		if ($groupedCount > 0)
		{
			$sql[] = str_pad('', (int) $groupedCount, ")");
		}
		// Wrap in brackets
		if (!empty($sql))
		{
			array_unshift($sql, '(');
			$sql[] = ')';
		}

		return $sql;
	}

	/**
	 * Get a list of the tables columns' order by field names
	 *
	 * @deprecated - dont think its used
	 *
	 * @return  array	order by names
	 */

	public function getOrderByFields()
	{
		if (is_null($this->orderByFields))
		{
			$this->orderByFields = array();
		}

		$form = $this->getFormModel();
		$groups = $form->getGroupsHiarachy();

		foreach ($groups as $groupModel)
		{
			$elementModels = $groupModel->getPublishedElements();

			foreach ($elementModels as $elementModel)
			{
				$this->orderByFields[] = $elementModel->getOrderByName();
			}
		}

		return $this->orderByFields;
	}

	/**
	 * Get the elements that are included in the search all query
	 *
	 * @return  array  search all fields
	 */

	public function getSearchAllFields()
	{
		$profiler = JProfiler::getInstance('Application');

		if (isset($this->searchAllAsFields))
		{
			return $this->searchAllAsFields;
		}

		$searchAllFields = array();
		$this->searchAllAsFields = array();
		$form = $this->getFormModel();
		$table = $this->getTable();
		$aJoinObjs = $this->getJoins();
		$groups = $form->getGroupsHiarachy();
		$gkeys = array_keys($groups);
		$opts = array('inc_raw' => false);
		$mode = $this->getParams()->get('search-mode-advanced');

		foreach ($gkeys as $x)
		{
			$groupModel = $groups[$x];
			$elementModels = $groupModel->getPublishedElements();

			for ($ek = 0; $ek < count($elementModels); $ek++)
			{
				$elementModel = $elementModels[$ek];

				if ($elementModel->includeInSearchAll($mode))
				{
					// Boolean search doesnt seem possible on encrypted fields.
					$p = $elementModel->getParams();
					$o = $p->get('encrypt');
					$p->set('encrypt', false);
					$elementModel->getAsField_html($this->searchAllAsFields, $searchAllFields, $opts);
					$p->set('encrypt', $o);
				}
			}
		}

		$db = FabrikWorker::getDbo();

		// If the group by element isnt in the fields (IE its not published) add it (otherwise group by wont work)
		$longGroupBy = $db->quoteName($this->getGroupBy());

		if (!in_array($longGroupBy, $searchAllFields) && trim($table->group_by) != '')
		{
			$this->searchAllAsFields[] = FabrikString::safeColName($this->getGroupBy()) . ' AS ' . $longGroupBy;
			$searchAllFields[] = $longGroupBy;
		}

		for ($x = 0; $x < count($this->searchAllAsFields); $x++)
		{
			$match = ' AS ' . $searchAllFields[$x];

			if (array_key_exists($x, $this->searchAllAsFields))
			{
				$this->searchAllAsFields[$x] = trim(str_replace($match, '', $this->searchAllAsFields[$x]));
			}
		}

		$this->searchAllAsFields = array_unique($this->searchAllAsFields);

		return $this->searchAllAsFields;
	}

	/**
	 * Get the part of the table sql statement that selects which fields to load
	 *
	 * @param   string  $mode  list/form - effects which elements are selected
	 *
	 * @return  array	field names to select in getelement data sql query
	 */

	protected function &getAsFields($mode = 'list')
	{
		$profiler = JProfiler::getInstance('Application');

		if (isset($this->asfields))
		{
			return $this->asfields;
		}

		$this->fields = array();
		$this->asfields = array();
		$db = FabrikWorker::getDbo(true);
		$form = $this->getFormModel();
		$table = $this->getTable();
		$aJoinObjs = $this->getJoins();
		$this->temp_db_key_addded = false;
		$groups = $form->getGroupsHiarachy();
		$gkeys = array_keys($groups);

		foreach ($gkeys as $x)
		{
			$groupModel = $groups[$x];

			if ($groupModel->canView($mode) !== false)
			{
				$elementModels = $mode === 'list' ? $groupModel->getListQueryElements() : $groupModel->getPublishedElements();

				foreach ($elementModels as $elementModel)
				{
					$method = 'getAsField_' . $this->outputFormat;

					if (!method_exists($elementModel, $method))
					{
						$method = 'getAsField_html';
					}

					$elementModel->$method($this->asfields, $this->fields);
				}
			}
		}

		/**
		 * temporaraily add in the db key so that the edit links work, must remove it before final return
		 of getData();
		*/
		JDEBUG ? $profiler->mark('getAsFields: starting to test if a view') : null;

		if (!$this->isView())
		{
			if (!$this->temp_db_key_addded && $table->db_primary_key != '')
			{
				$str = FabrikString::safeColName($table->db_primary_key) . ' AS ' . FabrikString::safeColNameToArrayKey($table->db_primary_key);
				$this->fields[] = $db->quoteName(FabrikString::safeColNameToArrayKey($table->db_primary_key));
			}
		}

		JDEBUG ? $profiler->mark('getAsFields: end of view test') : null;

		// For raw data in packages
		if ($this->outputFormat == 'raw')
		{
			$str = FabrikString::safeColName($table->db_primary_key) . ' AS __pk_val';
			$this->fields[] = $str;
		}

		$this->group_by_added = false;

		// If the group by element isnt in the fields (IE its not published) add it (otherwise group by wont work)
		$longGroupBy = $this->getGroupByName();

		if (!in_array($longGroupBy, $this->fields) && trim($longGroupBy) != '')
		{
			$this->asfields[] = FabrikString::safeColName($longGroupBy) . ' AS ' . $longGroupBy;
			$this->fields = $longGroupBy;
			$this->group_by_added = true;
		}

		return $this->asfields;
	}

	/**
	 * Get the group by element regardless of wheter it was stored as id or string
	 *
	 * @since 3.0.7
	 *
	 * @return  plgFabrik_Element
	 */
	protected function getGroupByElement()
	{
		$app = JFactory::getApplication();
		$item = $this->getTable();
		$formModel = $this->getFormModel();
		$groupBy = $app->input->get('group_by', $item->group_by, 'string');

		return $formModel->getElement($groupBy, true);
	}

	/**
	 * Get group by field name
	 *
	 * @since 3.0.7
	 *
	 * @return mixed false or name
	 */

	protected function getGroupByName()
	{
		$db = $this->getDb();
		$elementModel = $this->getGroupByElement();

		if (!$elementModel)
		{
			return false;
		}

		$groupBy = $elementModel->getFullName(true, false);

		return $db->quoteName(FabrikString::safeColNameToArrayKey($groupBy));
	}

	/**
	 * Checks if the params object has been created and if not creates and returns it
	 *
	 * @return  object	params
	 */

	public function getParams()
	{
		$item = $this->getTable();

		if (!isset($this->params))
		{
			$this->params = new JRegistry($item->params);
		}

		return $this->params;
	}

	/**
	 * Method to set the list id
	 *
	 * @param   int  $id  list ID
	 *
	 * @return  void
	 */

	public function setId($id)
	{
		$this->setState('list.id', $id);
		$this->renderContext = '';

		// $$$ rob not sure why but we need this getState() here when assinging id from admin view
		$this->setRenderContext($id);
		$this->getState();
	}

	/**
	 * Get the list id
	 *
	 * @return  int  list id
	 */

	public function getId()
	{
		return $this->getState('list.id');
	}

	/**
	 * Get the table object for the models _id
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return   object	table
	 */

	public function getTable($name = '', $prefix = 'Table', $options = array())
	{
		if ($name === true)
		{
			$this->clearTable();
		}

		if (!isset($this->table) || !is_object($this->table))
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fabrik/tables');
			$this->table = FabTable::getInstance('List', 'FabrikTable');
			$id = $this->getId();

			if ($id !== 0)
			{
				$this->table->load($id);
			}

			if (trim($this->table->db_primary_key) !== '')
			{
				$this->table->db_primary_key = FabrikString::safeColName($this->table->db_primary_key);
			}
		}

		return $this->table;
	}

	/**
	 * Set the table object
	 *
	 * @param   object  $table  db row
	 *
	 * @return   void
	 */

	public function setTable($table)
	{
		$this->table = $table;
	}

	/**
	 * unset the table object
	 *
	 * @return void
	 */

	public function clearTable()
	{
		unset($this->table);
	}

	/**
	 * Load the database object associated with the list
	 *
	 * @return  object	database
	 */

	public function &getDb()
	{
		return FabrikWorker::getConnection($this->getTable())->getDb();
	}

	/**
	 * Get the lists connection object
	 * sets $this->connection to the lists connection
	 *
	 * @deprecated since 3.0b use FabrikWorker::getConnection() instead
	 *
	 * @return  object	connection
	 */

	public function &getConnection()
	{
		$this->connection = FabrikWorker::getConnection($this->getTable());

		return $this->connection;
	}

	/**
	 *Is the table published
	 * Dates are stored as UTC so we can compare them against a date with no offset applied
	 *
	 * @return  bool	published state
	 */

	public function canPublish()
	{
		$item = $this->getTable();
		$db = FabrikWorker::getDbo();
		$nullDate = $db->getNullDate();
		$publishup = JFactory::getDate($item->publish_up);
		$publishup = $publishup->toUnix();
		$publishdown = JFactory::getDate($item->publish_down);
		$publishdown = $publishdown->toUnix();
		$jnow = JFactory::getDate();
		$now = $jnow->toUnix();

		if ($item->published == '1')
		{
			if ($now >= $publishup || $item->publish_up == '' || $item->publish_up == $nullDate)
			{
				if ($now <= $publishdown || $item->publish_down == '' || $item->publish_down == $nullDate)
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Access control to determine if the current user has rights to drop data
	 * from the table
	 *
	 * @return  bool	yes/no
	 */

	public function canEmpty()
	{
		$params = $this->getParams();

		if (!array_key_exists('allow_drop', $this->access))
		{
			$groups = JFactory::getUser()->getAuthorisedViewLevels();
			$this->access->allow_drop = in_array($this->getParams()->get('allow_drop'), $groups);
		}

		return $this->access->allow_drop;
	}

	/**
	 * Check if the user can view the detailed records
	 *
	 * @return  bool
	 */

	public function canViewDetails()
	{
		$params = $this->getParams();

		if (!array_key_exists('viewdetails', $this->access))
		{
			$groups = JFactory::getUser()->getAuthorisedViewLevels();
			$this->access->viewdetails = in_array($this->getParams()->get('allow_view_details'), $groups);
		}

		return $this->access->viewdetails;
	}

	/**
	 * Checks user access for editing records
	 *
	 * @param   object  $row  of data currently active
	 *
	 * @return  bool	access allowed
	 */

	public function canEdit($row = null)
	{
		$params = $this->getParams();

		/**
		 * $$$ hugh - FIXME - we really need to split out a onCanEditRow method, rather than overloading
		 * onCanEdit for both table and per-row contexts.  At the moment, we calling per-row plugins with
		 * null $row when canEdit() is called in a table context.
		 */

		/**
		* Find out what any plugins have to say
		*/

		$pluginCanEdit = FabrikWorker::getPluginManager()->runPlugins('onCanEdit', $this, 'list', $row);

		// At least one plugin run, so plugin results take precedence over anything else.
		if (!empty($pluginCanEdit))
		{
			// If one plugin returns false then return false.
			return in_array(false, $pluginCanEdit) ? false : true;
		}

		$canUserDo = $this->canUserDo($row, 'allow_edit_details2');

		if ($canUserDo !== -1)
		{
			// $canUserDo expressed a boolean preference, so use that
			return $canUserDo;
		}

		if (!array_key_exists('edit', $this->access))
		{
			$user = JFactory::getUser();
			$groups = $user->getAuthorisedViewLevels();
			$this->access->edit = in_array($this->getParams()->get('allow_edit_details'), $groups);
		}
		// Plugins didn't override, canuserDo() didn't express a preference, so return standard ACL
		return $this->access->edit;
	}

	/**
	 * Checks if any one row is editable = used to get the correct headings
	 *
	 * @return  bool
	 */

	protected function canEditARow()
	{
		$data = $this->getData();

		foreach ($data as $rows)
		{
			foreach ($rows as $row)
			{
				if ($this->canEdit($row))
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Access control function for determining if the user can perform
	 * a designated function on a specific row
	 *
	 * @param   object  $row  data
	 * @param   string  $col  access control setting to compare against
	 *
	 * @return  mixed	- if ACL setting defined here return bool, otherwise return -1 to contiune with default acl setting
	 */

	protected function canUserDo($row, $col)
	{
		$params = $this->getParams();

		return FabrikWorker::canUserDo($params, $row, $col);
	}

	/**
	 * Checks user access for deleting records.
	 *
	 * @param   object  $row  of data currently active
	 *
	 * @return  bool	access allowed
	 */

	public function canDelete($row = null)
	{
		/**
		 * Find out if any plugins deny delete.  We then allow a plugin to override with 'false' if
		 * if useDo or group ACL allows edit.  But we don't allow plugin to allow, if userDo or group ACL
		 * deny access.
		 */
		$pluginCanDelete = FabrikWorker::getPluginManager()->runPlugins('onCanDelete', $this, 'list', $row);
		$pluginCanDelete = !in_array(false, $pluginCanDelete);
		$canUserDo = $this->canUserDo($row, 'allow_delete2');

		if ($canUserDo !== -1)
		{
			// If userDo allows delete, let plugin override
			return $canUserDo ? $pluginCanDelete : $canUserDo;
		}

		if (!array_key_exists('delete', $this->access))
		{
			$groups = JFactory::getUser()->getAuthorisedViewLevels();
			$this->access->delete = in_array($this->getParams()->get('allow_delete'), $groups);
		}
		// If group access allows delete, then let plugin override
		return $this->access->delete ? $pluginCanDelete : $this->access->delete;
	}

	/**
	 * Determine if any record can be deleted - used to see if we include the
	 * delete button in the list view
	 *
	 * @return  bool
	 */

	public function deletePossible()
	{
		$data = $this->getData();

		foreach ($data as $rows)
		{
			foreach ($rows as $row)
			{
				if ($this->canDelete($row))
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Checks user access for importing csv
	 *
	 * @return  bool  access allowed
	 */

	public function canCSVImport()
	{
		if (!array_key_exists('csvimport', $this->access))
		{
			$groups = JFactory::getUser()->getAuthorisedViewLevels();
			$this->access->csvimport = in_array($this->getParams()->get('csv_import_frontend'), $groups);
		}

		return $this->access->csvimport;
	}

	/**
	 * Checks user access for exporting csv
	 *
	 * @return  bool  access allowed
	 */

	public function canCSVExport()
	{
		if (!array_key_exists('csvexport', $this->access))
		{
			$groups = JFactory::getUser()->getAuthorisedViewLevels();
			$this->access->csvexport = in_array($this->getParams()->get('csv_export_frontend'), $groups);
		}

		return $this->access->csvexport;
	}

	/**
	 * Checks user access for front end group by
	 *
	 * @return  bool  access allowed
	 */

	public function canGroupBy()
	{
		if (!array_key_exists('groupby', $this->access))
		{
			$groups = JFactory::getUser()->getAuthorisedViewLevels();
			$this->access->groupby = in_array($this->getParams()->get('group_by_access'), $groups);
		}

		return $this->access->groupby;
	}

	/**
	 * Checks user access for adding records
	 *
	 * @return  bool  access allowed
	 */

	public function canAdd()
	{
		$params = $this->getParams();

		if (!array_key_exists('add', $this->access))
		{
			$groups = JFactory::getUser()->getAuthorisedViewLevels();
			$this->access->add = in_array($this->getParams()->get('allow_add'), $groups);
		}

		return $this->access->add;
	}

	/**
	 * Check use can view the list
	 *
	 * @return  bool  can view or not
	 */

	public function canView()
	{
		if (!array_key_exists('view', $this->access))
		{
			$groups = JFactory::getUser()->getAuthorisedViewLevels();
			$this->access->view = in_array($this->getTable()->access, $groups);
		}

		return $this->access->view;
	}

	/**
	 * Load the table from the form_id value
	 *
	 * @param   int  $formId  (jos_fabrik_forms.id)
	 *
	 * @return  object	table row
	 */

	public function loadFromFormId($formId)
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fabrik/table');
		$row = FabTable::getInstance('List', 'FabrikTable');
		$row->load(array('form_id' => $formId));
		$this->table = $row;
		$this->setId($row->id);
		$this->setState('list.id', $row->id);

		return $row;
	}

	/**
	 * Like getJoins() but exclude cascading dropdown joins
	 * seems to be needed when calculating related table's record counts.
	 * This is called from within buildQueryJoin()
	 * and fired if this is done:
	 * $listModel->set('includeCddInJoin', false);
	 * as in tableModel::getRecordCounts()
	 *
	 * @return  array  join objects (table rows - not table objects or models)
	 */

	protected function getJoinsNoCdd()
	{
		if (!isset($this->_joinsNoCdd))
		{
			$form = $this->getFormModel();
			$form->getGroupsHiarachy();
			$ignore = array('PlgFabrik_ElementCascadingdropdown');
			$ids = $form->getElementIds($ignore);
			$db = FabrikWorker::getDbo(true);
			$id = (int) $this->getId();
			$query = $db->getQuery(true);
			$query->select('*')->from('#__{package}_joins')->where('list_id = ' . $id, 'OR');

			if (!empty($ids))
			{
				$query->where('element_id IN (' . implode(", ", $ids) . ')');
			}
			/* maybe we will have to order by element_id asc to ensure that table joins are loaded
			 * before element joins (if an element join is in a table join then its 'join_from_table' key needs to be updated
			 		*/
			$query->order('id');
			$db->setQuery($query);
			$this->_joinsNoCdd = $db->loadObjectList();
			$this->_makeJoinAliases($this->_joinsNoCdd);
		}

		return $this->_joinsNoCdd;
	}

	/**
	 * Get joins
	 *
	 * @return array join objects (table rows - not table objects or models)
	 */

	public function &getJoins()
	{
		if (!isset($this->joins))
		{
			$form = $this->getFormModel();
			$form->getGroupsHiarachy();

			// Force loading of join elements
			$ids = $form->getElementIds(array(), array('includePublised' => false, 'loadPrefilters' => true));
			$db = FabrikWorker::getDbo(true);
			$id = (int) $this->getId();
			$query = $db->getQuery(true);
			$query->select('*')->from('#__{package}_joins')->where('(element_id = 0 AND list_id = ' . $id . ')', 'OR');

			if (!empty($ids))
			{
				$query->where('element_id IN ( ' . implode(', ', $ids) . ')');
			}
			/* maybe we will have to order by element_id asc to ensure that table joins are loaded
			 * before element joins (if an element join is in a table join then its 'join_from_table' key needs to be updated
			 		*/
			$query->order('id');
			$db->setQuery($query);
			$this->joins = $db->loadObjectList();
			$this->_makeJoinAliases($this->joins);

			foreach ($this->joins as &$join)
			{
				if (is_string($join->params))
				{
					$join->params = new JRegistry($join->params);
					$this->setJoinPk($join);
				}
			}
		}

		return $this->joins;
	}

	/**
	 * Merged data queries need to know the joined tables primary key value
	 *
	 * @param   object  &$join  join
	 *
	 * @since	3.0.6
	 *
	 * @return  void
	 */

	protected function setJoinPk(&$join)
	{
		$pk = $join->params->get('pk');

		if (!isset($pk))
		{
			$fabrikDb = $this->getDb();
			$db = FabrikWorker::getDbo(true);
			$query = $db->getQuery(true);
			$pk = $this->getPrimaryKeyAndExtra($join->table_join);
			$pks = $join->table_join;
			$pks .= '.' . $pk[0]['colname'];
			$join->params->set('pk', $fabrikDb->quoteName($pks));
			$query->update('#__{package}_joins')->set('params = ' . $db->quote((string) $join->params))->where('id = ' . (int) $join->id);
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
			}

			$join->params = new JRegistry($join->params);
		}
	}

	/**
	 * As you may be joining to multiple versions of the same db table we need
	 * to set the various database name alaises that our SQL query will use
	 *
	 * @param   array  &$joins  joins
	 *
	 * @return  void
	 */
	protected function _makeJoinAliases(&$joins)
	{
		$app = JFactory::getApplication();
		$prefix = $app->getCfg('dbprefix');
		$table = $this->getTable();
		$db = FabrikWorker::getDbo(true);
		$aliases = array($table->db_table_name);
		$tableGroups = array();

		// Build up the alias and $tableGroups array first
		foreach ($joins as &$join)
		{
			$join->canUse = true;

			if ($join->table_join == '#__users' || $join->table_join == $prefix . 'users')
			{
				$conf = JFactory::getConfig();

				if (!$this->inJDb())
				{
					/* $$$ hugh - changed this to pitch an error and bang out, otherwise if we just set canUse to false, our getData query
					 * is just going to blow up, with no useful warning msg.
					* This is basically a bandaid for corner case where user has (say) host name in J!'s config, and IP address in
					* our connection details, or vice versa, which is not uncommon for 'locahost' setups,
					* so at least I'll know what the problem is when they post in the forums!
					*/

					$join->canUse = false;
				}
			}
			// $$$ rob = check for repeat elements In table view we dont need to add the join
			// as the element data is concatenated into one row. see elementModel::getAsField_html()
			$opts = json_decode($join->params);

			if (isset($opts->type) && $opts->type == 'repeatElement')
			{
				// If ($join->list_id != 0 && $join->element_id != 0) {
				$join->canUse = false;
			}

			$tablejoin = str_replace('#__', $prefix, $join->table_join);

			if (in_array($tablejoin, $aliases))
			{
				$base = $tablejoin;
				$a = $base;
				$c = 0;

				while (in_array($a, $aliases))
				{
					$a = $base . '_' . $c;
					$c++;
				}

				$join->table_join_alias = $a;
			}
			else
			{
				$join->table_join_alias = $tablejoin;
			}

			$aliases[] = str_replace('#__', $prefix, $join->table_join_alias);

			if (!array_key_exists($join->group_id, $tableGroups))
			{
				if ($join->element_id == 0)
				{
					$tableGroups[$join->group_id] = $join->table_join_alias;
				}
			}
		}

		foreach ($joins as &$join)
		{
			// If they are element joins add in this tables name as the calling joining table.
			if ($join->join_from_table == '')
			{
				$join->join_from_table = $table->db_table_name;
			}

			/*
			 * Test case:
			* you have a table that joins to a 2nd table
			* in that 2nd table there is a database join element
			* that 2nd elements key needs to point to the 2nd tables name and not the first
			*
			* e.g. when you want to create a n-n relationship
			*
			* events -> (table join) events_artists -> (element join) artist
			*/

			$join->keytable = $join->join_from_table;

			if (!array_key_exists($join->group_id, $tableGroups))
			{
			}
			else
			{
				if ($join->element_id != 0)
				{
					$join->keytable = $tableGroups[$join->group_id];
					$join->join_from_table = $join->keytable;
				}
			}
		}

		FabrikHelperHTML::debug($joins, 'joins');
	}

	/**
	 * Gets the field names for the given table
	 * $$$ hugh - copies this to backend model, so remember to modify that as well, if
	 * you make changes to this one.  Better yet, make it a Helper func that requires
	 * the $tbl arg, as that's the only thing that makes it list model specific.
	 *
	 * @param   string  $tbl  table name
	 * @param   string  $key  field to key return array on
	 *
	 * @return  array	table fields
	 */

	public function getDBFields($tbl = null, $key = null)
	{
		if (is_null($tbl))
		{
			$table = $this->getTable();
			$tbl = $table->db_table_name;
		}

		if ($tbl == '')
		{
			return array();
		}

		$sig = $tbl . $key;
		$tbl = FabrikString::safeColName($tbl);

		if (!isset($this->dbFields[$sig]))
		{
			$db = $this->getDb();
			$tbl = FabrikString::safeColName($tbl);
			$db->setQuery("DESCRIBE " . $tbl);

			try
			{
				$this->dbFields[$sig] = $db->loadObjectList($key);
			}
			catch (RuntimeException $e)
			{
				// List may be in second connection but we might try to get #__user fields for join
				$this->dbFields[$sig] = array();
			}

			foreach ($this->dbFields[$sig] as &$row)
			{
				/**
				 * Boil the type down to just the base type, so "INT(11) UNSIGNED" becomes just "INT"
				 * I'm sure there's other cases than just UNSIGNED I need to deal with, but for now that's
				 * what I most care about, as this stuff is being written handle being more specific about
				 * the elements the list PK can be selected from.
				 */
				$row->BaseType = strtoupper(preg_replace('#(\(\d+\))$#', '', $row->Type));
				$row->BaseType = preg_replace('#(\s+SIGNED|\s+UNSIGNED)#', '', $row->BaseType);
			}
		}

		return $this->dbFields[$sig];
	}

	/**
	 * Called at the end of saving an element
	 * if a new element it will run the sql to add to field,
	 * if existing element and name changed will create query to be used later
	 *
	 * @param   object  &$elementModel  element model
	 * @param   string  $origColName    original column name
	 *
	 * @return  array($update, $q, $oldName, $newdesc, $origDesc, $dropKey)
	 */

	public function shouldUpdateElement(&$elementModel, $origColName = null)
	{
		$app = JFactory::getApplication();
		$db = FabrikWorker::getDbo();
		$return = array(false, '', '', '', '', false);
		$element = $elementModel->getElement();
		$pluginManager = FabrikWorker::getPluginManager();
		$basePlugIn = $pluginManager->getPlugIn($element->plugin, 'element');
		$fbConfig = JComponentHelper::getParams('com_fabrik');
		$fabrikDb = $this->getDb();
		$group = $elementModel->getGroup();
		$dropKey = false;
		/*$$$ rob - replaced this with getting the table from the group as if we moved the element
		 *from one group to another $this->getTable gives you the old group's table, where as we want
		* the new group's table
		*/
		$table = $group->getlistModel()->getTable();

		// $$$ hugh - if this is a table-less form ... not much point going any
		// further 'cos things will go BANG
		if (empty($table->id))
		{
			return $return;
		}

		if ($this->isView())
		{
			return $return;
		}

		if ($group->isJoin())
		{
			$tableName = $group->getJoinModel()->getJoin()->table_join;
			$keydata = $this->getPrimaryKeyAndExtra($tableName);
			$primaryKey = $keydata[0]['colname'];
		}
		else
		{
			$keydata = $this->getPrimaryKeyAndExtra();
			$tableName = $table->db_table_name;
			$primaryKey = $table->db_primary_key;
		}

		// $$$ rob base plugin needs to know group info for date fields in non-join repeat groups
		$basePlugIn->setGroupModel($elementModel->getGroupModel());

		// The element type AFTER saving
		$objtype = $elementModel->getFieldDescription();
		$dbdescriptions = $this->getDBFields($tableName, 'Field');

		if (!$this->canAlterFields() && !$this->canAddFields())
		{
			$objtype = $dbdescriptions[$origColName]->Type;
		}

		if (is_null($objtype))
		{
			return $return;
		}

		$existingfields = array_keys($dbdescriptions);
		$lastfield = $existingfields[count($existingfields) - 1];
		$tableName = FabrikString::safeColName($tableName);
		$lastfield = FabrikString::safeColName($lastfield);
		$altered = false;

		if (!array_key_exists($element->name, $dbdescriptions))
		{
			if ($origColName == '')
			{
				if ($this->canAddFields())
				{
					$fabrikDb
					->setQuery("ALTER TABLE $tableName ADD COLUMN " . FabrikString::safeColName($element->name) . " $objtype AFTER $lastfield");

					try
					{
						$fabrikDb->execute();
						$altered = true;
					}
					catch (Exception $e)
					{
						throw new ErrorException('alter structure: ' . $fabrikDb->getErrorMsg(), 500);
						$altered = false;
					}
				}
			}
			// Commented out as it stops the update when changing an element name
			// return $return;
		}

		$thisFieldDesc = JArrayHelper::getValue($dbdescriptions, $origColName, new stdClass);

		/* $$$ rob the Default property for timestamps when they are set to CURRENT_TIMESTAMP
		 * doesn't show up from getDBFields()  - so presuming a timestamp field will always default
		* to the current timestamp (update of the field's data controller in the Extra property (on update CURRENT_TIMESTAMP)
				*/
		$existingDef = '';

		if (isset($thisFieldDesc->Type))
		{
			$existingDef = $thisFieldDesc->Type;

			if ($thisFieldDesc->Type == 'timestamp')
			{
				$existingDef .= $thisFieldDesc->Null = 'YES' ? ' NULL' : ' NOT NULL';
				$existingDef .= ' DEFAULT CURRENT_TIMESTAMP';
				$existingDef .= ' ' . $thisFieldDesc->Extra;
			}
		}

		// If its the primary 3.0
		for ($k = 0; $k < count($keydata); $k++)
		{
			if ($keydata[$k]['colname'] == $origColName)
			{
				$existingDef .= ' ' . $keydata[$k]['extra'];
			}
		}
		/* $$$ hugh 2012/05/13 - tweaking things a little so we don't care about certain differences in type.
		 * Initally, just integer types and signed vs unsigned.  So if the existing column is TINYINT(3) UNSIGNED
		* and we think it's INT(3), i.e. that's what getFieldDescription() returns, let's treat those as functionally
		* the same, and not change anything.  Ideally we should turn this into some kind of element model method, so
		* we would do something like $base_existingDef = $elementModel->baseFieldDescription($existingDef), and (say) the
		* field element, if passed "TINYINT(3) UNSIGNED" would return "INT(3)".  But for now, just tweak it here.
		*/
		$objtypeUpper = ' ' . JString::strtoupper(trim($objtype)) . ' ';
		$objtypeUpper = str_replace(' NOT NULL ', ' ', $objtypeUpper);
		$objtypeUpper = str_replace(' UNSIGNED ', ' ', $objtypeUpper);
		$objtypeUpper = trim($objtypeUpper);
		$existingDef = ' ' . JString::strtoupper(trim($existingDef)) . ' ';
		$existingDef = str_replace(' UNSIGNED ', ' ', $existingDef);
		$existingDef = str_replace(array(' INTEGER', ' TINYINT', ' SMALLINT', ' MEDIUMINT', ' BIGINT'), ' INT', $existingDef);
		$existingDef = trim($existingDef);

		if ($element->name == $origColName && $existingDef == $objtypeUpper)
		{
			// No changes to the element name or field type
			return $return;
		}
		elseif ($this->canAlterFields() === false)
		{
			// Give a notice if the user cant alter the field type but selections he has made would normally do so:
			$app->enqueueMessage(JText::_('COM_FABRIK_NOTICE_ELEMENT_SAVED_BUT_STRUCTUAL_CHANGES_NOT_APPLIED'), 'notice');

			return $return;
		}

		$return[4] = $existingDef;
		$existingfields = array_keys($dbdescriptions);
		$lastfield = $existingfields[count($existingfields) - 1];
		$tableName = FabrikString::safeColName($tableName);
		$lastfield = FabrikString::safeColName($lastfield);

		if (empty($origColName) || !in_array($origColName, $existingfields) || ($app->input->get('task') === 'save2copy' && $this->canAddFields()))
		{
			if (!$altered)
			{
				if (!in_array($element->name, $existingfields))
				{
					$fabrikDb->setQuery("ALTER TABLE $tableName ADD COLUMN " . FabrikString::safeColName($element->name) . " $objtype AFTER $lastfield");

					try
					{
						$fabrikDb->execute();
					}
					catch (RuntimeException $e)
					{
						// Don't throw error for attempting to re-add an existing db column
						if (!array_key_exists($element->name, $dbdescriptions))
						{
							throw new ErrorException('alter structure: ' . $e->getErrorMsg(), 500);
						}
					}
				}
			}
		}
		else
		{
			// $$$ rob don't alter it yet - lets defer this and give the user the choice if they
			// really want to do this
			if ($this->canAlterFields())
			{
				$origColName = $origColName == null ? $fabrikDb->quoteName($element->name) : $fabrikDb->quoteName($origColName);

				if (JString::strtolower($objtype) == 'blob')
				{
					$dropKey = true;
				}

				$q = 'ALTER TABLE ' . $tableName . ' CHANGE ' . $origColName . ' ' . FabrikString::safeColName($element->name) . ' ' . $objtype . ' ';
				$testColName = $tableName . '.' . FabrikString::safeColName($element->name);

				if (FabrikString::safeColName($primaryKey) == $tableName . '.' . FabrikString::safeColName($element->name) && $table->auto_inc)
				{
					if (!strpos($q, ' NOT NULL AUTO_INCREMENT'))
					{
						$q .= ' NOT NULL AUTO_INCREMENT ';
					}
				}

				$origColName = FabrikString::safeColName($origColName);
				$return[0] = true;
				$return[1] = $q;
				$return[2] = $origColName;
				$return[3] = $objtypeUpper;
				$return[5] = $dropKey;

				return $return;
			}
		}

		return $return;
	}

	/**
	 * Add or update a database column via sql
	 *
	 * @param   object  &$elementModel  element plugin
	 * @param   string  $origColName    origional field name
	 *
	 * @return  bool
	 */

	public function alterStructure(&$elementModel, $origColName = null)
	{
		$db = FabrikWorker::getDbo();
		$element = $elementModel->getElement();
		$pluginManager = FabrikWorker::getPluginManager();
		$basePlugIn = $pluginManager->getPlugIn($element->plugin, 'element');
		$fbConfig = JComponentHelper::getParams('com_fabrik');
		$fabrikDb = $this->getDb();
		$table = $this->getTable();
		$tableName = $table->db_table_name;

		// $$$ rob base plugin needs to know group info for date fields in non-join repeat groups
		$basePlugIn->setGroupModel($elementModel->getGroupModel());
		$objtype = $elementModel->getFieldDescription();
		$dbdescriptions = $this->getDBFields($tableName);

		if (!$this->canAlterFields())
		{
			foreach ($dbdescriptions as $f)
			{
				if ($f->Field == $origColName)
				{
					$objtype = $f->Type;
				}
			}
		}

		if (!is_null($objtype))
		{
			foreach ($dbdescriptions as $dbdescription)
			{
				$fieldname = JString::strtolower($dbdescription->Field);

				if (JString::strtolower($element->name) == $fieldname && JString::strtolower($dbdescription->Type) == JString::strtolower($objtype))
				{
					return;
				}

				$existingfields[] = $fieldname;
			}

			$lastfield = $fieldname;
			$element->name = FabrikString::safeColName($element->name);
			$tableName = FabrikString::safeColName($tableName);
			$lastfield = FabrikString::safeColName($lastfield);

			if (empty($origColName) || !in_array(JString::strtolower($origColName), $existingfields))
			{
				$fabrikDb->setQuery("ALTER TABLE $tableName ADD COLUMN $element->name $objtype AFTER $lastfield");

				try
				{
					$fabrikDb->execute();
				}
				catch (Exception $e)
				{
					throw new RuntimeException('alter structure: ' . $e->getMessage());
				}
			}
			else
			{
				if ($this->canAlterFields())
				{
					if ($origColName == null)
					{
						$origColName = $element->name;
					}

					$origColName = FabrikString::safeColName($origColName);
					$fabrikDb->setQuery("ALTER TABLE $tableName CHANGE $origColName $element->name $objtype");

					try
					{
						$fabrikDb->execute();
					}
					catch (Exception $e)
					{
						throw new RuntimeException('alter structure: ' . $e->getMessage());
					}
				}
			}
		}

		return true;
	}

	/**
	 * Can we alter this tables fields structure?
	 *
	 * @return  bool
	 */

	public function canAlterFields()
	{
		$listid = $this->getId();

		if (empty($listid))
		{
			return false;
		}

		$state = $this->alterExisting();

		return $state == 1;
	}

	/**
	 * Get the alter fields setting
	 *
	 * @since	3.0.6
	 *
	 * @return  string	alter fields setting
	 */

	private function alterExisting()
	{
		$params = $this->getParams();
		$fbConfig = JComponentHelper::getParams('com_fabrik');
		$alter = $params->get('alter_existing_db_cols', 'default');

		if ($alter === 'default')
		{
			$alter = $fbConfig->get('fbConf_alter_existing_db_cols', true);
		}

		return $alter;
	}

	/**
	 * Can we add fields to the list?
	 *
	 * @since	3.0.6
	 *
	 * @return  bool
	 */

	public function canAddFields()
	{
		$state = $this->alterExisting();

		return ($state == 1 || $state == 'addonly');
	}

	/**
	 * If not loaded this loads in the table's form model
	 * also binds a reference of the table to the form.
	 *
	 * @return  object	form model with form table loaded
	 */

	public function &getFormModel()
	{
		if (!isset($this->formModel))
		{
			$this->formModel = JModelLegacy::getInstance('Form', 'FabrikFEModel');
			$table = $this->getTable();
			$this->formModel->setId($table->form_id);
			$this->formModel->getForm();
			$this->formModel->setListModel($this);
		}

		return $this->formModel;
	}

	/**
	 * Set the form model
	 *
	 * @param   object  $model  form model
	 *
	 * @return  void
	 */

	public function setFormModel($model)
	{
		$this->formModel = $model;
	}

	/**
	 * Tests if the table is in fact a view
	 *
	 * @return  bool	true if table is a view
	 */

	public function isView()
	{
		$params = $this->getParams();
		$isView = $params->get('isview', null);

		if (!is_null($isView) && (int) $isView >= 0)
		{
			return $isView;
		}

		/* $$$ hugh - because querying INFORMATION_SCHEMA can be very slow (like minutes!) on
		 * a shared host, I made a small change.  The edit table view now adds a hidden 'isview'
		* param, defaulting to -1 on new tables.  So the following code should only ever execute
		* one time, when a new table is saved.  Before this change, because 'isview' wasn't
		* included on the edit view (because it's not a "real" user settable param), so didn't
		* exist when we picked up the params from the submitted data, this code was running (twice!)
		* every time a table was saved.
		* http://fabrikar.com/forums/showthread.php?t=16622&page=6
		*/

		if (isset($this->isView))
		{
			return $this->isView;
		}

		$db = FabrikWorker::getDbo();
		$table = $this->getTable();
		$cn = $this->getConnection();

		$c = $cn->getConnection();
		$dbname = $c->database;

		if ($table->db_table_name == '')
		{
			return;
		}

		$sql = " SELECT table_name, table_type, engine FROM INFORMATION_SCHEMA.tables " . "WHERE table_name = " . $db->quote($table->db_table_name)
		. " AND table_type = 'view' AND table_schema = " . $db->quote($dbname);
		$db->setQuery($sql);
		$row = $db->loadObjectList();
		$this->isView = empty($row) ? 0 : 1;

		// Store and save param for following tests
		$params->set('isview', $this->isView);
		$table->params = (string) $params;
		$table->store();

		return $this->isView;
	}

	/**
	 * Store filters in the registry
	 *
	 * @param   array  $request  filters to store
	 *
	 * @return  void
	 */

	public function storeRequestData($request)
	{
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$input = $app->input;
		$session = JFactory::getSession();
		$registry = $session->get('registry');
		$option = 'com_' . $package;
		$tid = 'list' . $this->getRenderContext();

		// Make sure that we only store data thats been entered from this page first test we aren't in a plugin
		if ($input->get('option') == $option && is_object($registry))
		{
			// Don't do this when you are viewing a form or details page as it wipes out the table filters
			$reg = $registry->get('_registry');

			if (isset($reg[$option]) && !in_array($input->get('view'), array('form', 'details')))
			{
				unset($reg[$option]['data']->$tid->filter);
			}
		}

		$context = $option . '.' . $tid . '.filter';

		// @TODO test for _clear_ in values and if so delete session data
		foreach ($request as $key => $val)
		{
			if (is_array($val))
			{
				$key = $context . '.' . $key;
				$app->setUserState($key, array_values($val));
			}
		}
	}

	/**
	 * Creates filter array (return existing if exists)
	 *
	 * @return  array	filters
	 */

	public function &getFilterArray()
	{
		if (isset($this->filters))
		{
			return $this->filters;
		}

		$filterModel = $this->getFilterModel();
		$db = FabrikWorker::getDbo();
		$this->filters = array();
		$user = JFactory::getUser();
		$request = $this->getRequestData();
		$this->storeRequestData($request);
		FabrikHelperHTML::debug($request, 'filter:request');

		$params = $this->getParams();
		$elements = $this->getElements('id');

		/* $$$ rob prefilters loaded before anything to avoid issues where you filter on something and
		 * you have 2 prefilters with joined by an OR - this was incorrectly giving SQL of
		* WHERE normal filter = x OR ( prefilter1 = y OR prefilter2 = x)
		* this change changes the SQL to
		* WHERE ( prefilter1 = y OR prefilter2 = x) AND normal filter = x
		*/
		$this->getPrefilterArray($this->filters);

		// These are filters created from a search form or normal search
		$keys = array_keys($request);
		$indexStep = count(JArrayHelper::getValue($this->filters, 'key', array()));
		FabrikHelperHTML::debug($keys, 'filter:request keys');

		foreach ($keys as $key)
		{
			if (is_array($request[$key]))
			{
				foreach ($request[$key] as $kk => $v)
				{
					if (!array_key_exists($key, $this->filters) || !is_array($this->filters[$key]))
					{
						$this->filters[$key] = array();
					}

					$this->filters[$key][$kk + $indexStep] = $v;
				}
			}
		}

		FabrikHelperHTML::debug($this->filters, 'tablemodel::getFilterArray middle');
		$readOnlyValues = array();
		$w = new FabrikWorker;
		$noFiltersSetup = JArrayHelper::getValue($this->filters, 'no-filter-setup', array());

		if (count($this->filters) == 0)
		{
			FabrikWorker::getPluginManager()->runPlugins('onFiltersGot', $this, 'list');

			return $this->filters;
		}

		// Get a list of plugins
		$pluginKeys = $filterModel->getPluginFilterKeys();
		$elementids = JArrayHelper::getValue($this->filters, 'elementid', array());
		$sqlCond = JArrayHelper::getValue($this->filters, 'sqlCond', array());
		$raws = JArrayHelper::getValue($this->filters, 'raw', array());

		foreach ($this->filters['key'] as $i => $keyval)
		{
			$value = $this->filters['value'][$i];
			$condition = JString::strtolower($this->filters['condition'][$i]);
			$key = $this->filters['key'][$i];
			$filterEval = $this->filters['eval'][$i];
			$elid = JArrayHelper::getValue($elementids, $i);
			$key2 = array_key_exists('key2', $this->filters) ? JArrayHelper::getValue($this->filters['key2'], $i, '') : '';

			/* $$$ rob see if the key is a raw filter
			 * 20/12/2010 - think $key is never with _raw now as it is unset in tablefilter::getQuerystringFilters() although may  be set elsewhere
			* - if it is make a note and remove the _raw from the name
			*/
			$raw = JArrayHelper::getValue($raws, $i, false);

			if (JString::substr($key, -5, 5) == '_raw`')
			{
				$key = JString::substr($key, 0, JString::strlen($key) - 5) . '`';
				$raw = true;
			}

			if ($elid == -1)
			{
				// Bool match
				$this->filters['origvalue'][$i] = $value;
				$this->filters['sqlCond'][$i] = $key . ' ' . $condition . ' (' . $db->quote($value) . ' IN BOOLEAN MODE)';
				continue;
			}

			// List plug-in filter found - it should have set its own sql in onGetPostFilter();
			if (in_array($elid, $pluginKeys))
			{
				$this->filters['origvalue'][$i] = $value;
				$this->filters['sqlCond'][$i] = $this->filters['sqlCond'][$i];
				continue;
			}

			$elementModel = JArrayHelper::getValue($elements, $elid);

			// $$$ rob key2 if set is in format  `countries_0`.`label` rather than  `countries`.`label`
			// used for search all filter on 2nd db join element pointing to the same table
			if (strval($key2) !== '')
			{
				$key = $key2;
			}

			$eval = $this->filters['eval'][$i];
			$fullWordsOnly = $this->filters['full_words_only'][$i];
			$exactMatch = $this->filters['match'][$i];

			if (!is_a($elementModel, 'PlgFabrik_Element'))
			{
				if ($this->filters['condition'][$i] == 'exists')
				{
					$this->filters['sqlCond'][$i] = 'EXISTS (' . $this->filters['value'][$i] . ')';
				}

				continue;
			}

			$elementModel->_rawFilter = $raw;

			// $$ hugh - testing allowing {QS} replacements in pre-filter values
			$w->replaceRequest($value);
			$value = $this->prefilterParse($value);
			$value = $w->parseMessageForPlaceHolder($value);

			if ($filterEval == '1')
			{
				// $$$ rob hehe if you set $i in the eval'd code all sorts of chaos ensues
				$origi = $i;
				$value = stripslashes(htmlspecialchars_decode($value, ENT_QUOTES));
				$value = @eval($value);
				FabrikWorker::logEval($value, 'Caught exception on eval of tableModel::getFilterArray() ' . $key . ': %s');
				$i = $origi;
			}

			if ($condition == 'regexp')
			{
				$condition = 'REGEXP';

				// $$$ 30/06/2011 rob dont escape the search as it may contain \\\ from preg_escape (e.g. search all on 'c+b)

				// $$$ 14/11/2012 - Lower case search value - as accented characters e.g. Ö are case sensetive in regex. Key already lower cased in filter model

				// $value = 'LOWER(' . $db->quote($value, false) . ')';
			}
			elseif ($condition == 'like')
			{
				$condition = 'LIKE';
				$value = $db->quote($value);
			}
			elseif ($condition == 'laterthisyear' || $condition == 'earlierthisyear')
			{
				$value = $db->quote($value);
			}

			if ($fullWordsOnly == '1')
			{
				$condition = 'REGEXP';
			}

			$originalValue = $this->filters['value'][$i];

			if ($value == '' && $eval == FABRIKFILTER_QUERY)
			{
				throw new RuntimeException(JText::_('COM_FABRIK_QUERY_PREFILTER_WITH_NO_VALUE'), 500);
			}

			list($value, $condition) = $elementModel->getFilterValue($value, $condition, $eval);

			if ($fullWordsOnly == '1')
			{
				if (is_array($value))
				{
					foreach ($value as &$v)
					{
						$v = "\"[[:<:]]" . $v . "[[:>:]]\"";
					}
				}
				else
				{
					$value = "\"[[:<:]]" . $value . "[[:>:]]\"";
				}
			}

			if ($condition === 'REGEXP')
			{
				// $$$ 15/11/2012 - moved from before getFilterValue() to after as otherwise date filters in querystrings created wonky query
				$value = 'LOWER(' . $db->quote($value, false) . ')';
			}

			if (!array_key_exists($i, $sqlCond) || $sqlCond[$i] == '')
			{
				$query = $elementModel->getFilterQuery($key, $condition, $value, $originalValue, $this->filters['search_type'][$i]);
				$this->filters['sqlCond'][$i] = $query;
			}

			$this->filters['condition'][$i] = $condition;

			// Used when getting the selected dropdown filter value
			$this->filters['origvalue'][$i] = $originalValue;
			$this->filters['value'][$i] = $value;

			if (!array_key_exists($i, $noFiltersSetup))
			{
				$this->filters['no-filter-setup'][$i] = 0;
			}

			if ($this->filters['no-filter-setup'][$i] == 1)
			{
				$tmpName = $elementModel->getFullName(true, false);
				$tmpData = array($tmpName => $originalValue, $tmpName . '_raw' => $originalValue);

				// Set defaults to null to ensure we get correct value for 2nd dropdown search value (mutli dropdown from search form)
				$elementModel->defaults = null;

				if (!array_key_exists($key, $readOnlyValues))
				{
					$readOnlyValues[$key] = array();
				}

				$readOnlyValues[$key][] = $elementModel->getFilterRO($tmpData);

				// Set it back to null again so that in form view we dont return this value.
				$elementModel->defaults = null;

				// Filter value assigned in readOnlyValues foreach loop towards end of this function
				$this->filters['filter'][$i] = '';
			}
			else
			{
				/*$$$rob not sure $value is the right var to put in here - or if its actually used
				 * but without this line you get warnings about missing variable in the filter array
				*/
				$this->filters['filter'][$i] = $value;
			}
		}

		FabrikHelperHTML::debug($this->filters, 'end filters');

		foreach ($readOnlyValues as $key => $val)
		{
			foreach ($this->filters['key'] as $i => $fkey)
			{
				if ($fkey === $key)
				{
					$this->filters['filter'][$i] = implode("<br>", $val);
				}
			}
		}

		FabrikWorker::getPluginManager()->runPlugins('onFiltersGot', $this, 'list');
		FabrikHelperHTML::debug($this->filters, 'after plugins:onFiltersGot');

		return $this->filters;
	}

	/**
	 * Get the elements to show in the list view
	 *
	 * @since 3.1b2
	 *
	 * @return array
	 */
	private function showInList()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$showInList = array();
		$listels = json_decode(FabrikWorker::getMenuOrRequestVar('list_elements', '', $this->isMambot));

		if (isset($listels->show_in_list))
		{
			$showInList = $listels->show_in_list;
		}

		$showInList = (array) $input->get('fabrik_show_in_list', $showInList, 'array');

		// Set it for use by groupModel->getPublishedListElements()
		$input->set('fabrik_show_in_list', $showInList);

		return $showInList;
	}

	/**
	 * Get the prefilter settings from list/module/menu options
	 * Use in listModel::getPrefilterArray() and formModel::getElementIds()
	 *
	 * @return multitype:array
	 */
	public function prefilterSetting()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$params = $this->getParams();
		$showInList = $this->showInList();

		// Are we coming from a post request via a module?
		$moduleid = 0;
		$requestRef = $input->get('listref', '', 'string');

		if ($requestRef !== '' && !strstr($requestRef, 'com_' . $package))
		{
			// If so we need to load in the modules parameters
			$ref = explode('_', $requestRef);

			if (count($ref) > 1)
			{
				$moduleid = (int) array_pop($ref);
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);

				if ($moduleid !== 0)
				{
					$this->setRenderContext($moduleid);
					$query->select('params')->from('#__modules')->where('id = ' . $moduleid);
					$db->setQuery($query);
					$obj = json_decode($db->loadResult());

					if (is_object($obj) && isset($obj->prefilters))
					{
						$properties = $obj->prefilters;
					}
				}
			}
		}

		// List prefilter properties
		$elements = $this->getElements('filtername');
		$afilterFields = (array) $params->get('filter-fields');
		$afilterConditions = (array) $params->get('filter-conditions');
		$afilterValues = (array) $params->get('filter-value');
		$afilterAccess = (array) $params->get('filter-access');
		$afilterEval = (array) $params->get('filter-eval');
		$afilterJoins = (array) $params->get('filter-join');
		$afilterGrouped = (array) $params->get('filter-grouped');

		/* If we are rendering as a module dont pick up the menu item options (parmas already set in list module)
		 * so first statement when rendenering a module, 2nd when posting to the component from a module.
		*/
		if (!strstr($this->getRenderContext(), 'mod_fabrik_list') && $moduleid === 0)
		{
			$properties = FabrikWorker::getMenuOrRequestVar('prefilters', '', $this->isMambot);
		}

		if (isset($properties))
		{
			$prefilters = JArrayHelper::fromObject(json_decode($properties));
			$conditions = (array) $prefilters['filter-conditions'];

			if (!empty($conditions))
			{
				$afilterFields = JArrayHelper::getValue($prefilters, 'filter-fields', array());
				$afilterConditions = JArrayHelper::getValue($prefilters, 'filter-conditions', array());
				$afilterValues = JArrayHelper::getValue($prefilters, 'filter-value', array());
				$afilterAccess = JArrayHelper::getValue($prefilters, 'filter-access', array());
				$afilterEval = JArrayHelper::getValue($prefilters, 'filter-eval', array());
				$afilterJoins = JArrayHelper::getValue($prefilters, 'filter-join', array());
			}
		}

		return array($afilterFields, $afilterConditions, $afilterValues, $afilterAccess, $afilterEval, $afilterJoins);
	}

	/**
	 * Creates array of prefilters
	 * Set to public 15/04/2013
	 *
	 * @param   array  &$filters  filters
	 *
	 * @return  array	prefilters combinde with filters
	 */

	public function getPrefilterArray(&$filters)
	{
		if (!isset($this->prefilters))
		{
			$elements = $this->getElements('filtername');
			$params = $this->getParams();
			list($afilterFields, $afilterConditions, $afilterValues, $afilterAccess, $afilterEval, $afilterJoins) = $this->prefilterSetting();
			$join = 'WHERE';
			$w = new FabrikWorker;

			for ($i = 0; $i < count($afilterFields); $i++)
			{
				if (!array_key_exists(0, $afilterJoins) || $afilterJoins[0] == '')
				{
					$afilterJoins[0] = 'AND';
				}

				$join = JArrayHelper::getValue($afilterJoins, $i, 'AND');

				if (trim(JString::strtolower($join)) == 'where')
				{
					$join = 'AND';
				}

				$filter = $afilterFields[$i];
				$condition = $afilterConditions[$i];
				$selValue = JArrayHelper::getValue($afilterValues, $i, '');
				$filterEval = JArrayHelper::getValue($afilterEval, $i, false);
				$filterGrouped = JArrayHelper::getValue($afilterGrouped, $i, false);
				$selAccess = $afilterAccess[$i];

				if (!$this->mustApplyFilter($selAccess))
				{
					continue;
				}
				// $tmpfilter = strstr($filter, '_raw') ? FabrikString::rtrimword( $filter, '_raw') : $filter;
				$raw = preg_match("/_raw$/", $filter) > 0;
				$tmpfilter = $raw ? FabrikString::rtrimword($filter, '_raw') : $filter;
				$elementModel = JArrayHelper::getValue($elements, FabrikString::safeColName($tmpfilter), false);

				if ($elementModel === false && $condition !== 'exists')
				{
					// Include the JLog class.
					jimport('joomla.log.log');

					// Add the logger.
					JLog::addLogger(array('text_file' => 'fabrik.log.php'));

					// Start logging...
					JLog::add(
					'A prefilter has been set up on an unpublished element, and will not be applied:' . FabrikString::safeColName($tmpfilter),
					JLog::NOTICE, 'com_fabrik');
					continue;
				}

				$filters['join'][] = $join;
				$filters['search_type'][] = 'prefilter';
				$filters['key'][] = $tmpfilter;
				$filters['value'][] = $selValue;
				$filters['origvalue'][] = $selValue;
				$filters['sqlCond'][] = '';
				$filters['no-filter-setup'][] = null;
				$filters['condition'][] = $condition;
				$filters['grouped_to_previous'][] = $filterGrouped;
				$filters['eval'][] = $filterEval;
				$filters['match'][] = ($condition == 'equals') ? 1 : 0;
				$filters['full_words_only'][] = 0;
				$filters['label'][] = '';
				$filters['access'][] = '';
				$filters['key2'][] = '';
				$filters['required'][] = 0;
				$filters['hidden'][] = false;
				$filters['elementid'][] = $elementModel !== false ? $elementModel->getElement()->id : 0;
				$filters['raw'][] = $raw;
				$this->prefilters = true;
			}
		}

		FabrikHelperHTML::debug($filters, 'prefilters');
	}

	/**
	 * Get the total number of records in the table
	 *
	 * @return  int		total number of records
	 */

	public function getTotalRecords()
	{
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');

		// $$$ rob ensure that the limits are set - otherwise can create monster query
		$this->setLimits();
		$session = JFactory::getSession();
		$context = 'com_' . $package . '.list' . $this->getRenderContext() . '.total';

		if (isset($this->totalRecords))
		{
			$session->set($context, $this->totalRecords);

			return $this->totalRecords;
		}
		// $$$ rob getData() should always be run first
		if (is_null($this->data))
		{
			$this->getData();

			return $this->totalRecords;
		}

		if ($this->mergeJoinedData())
		{
			$this->totalRecords = $this->getJoinMergeTotalRecords();
			$session->set($context, $this->totalRecords);

			return $this->totalRecords;
		}
	}

	/**
	 * Modified version of getTotalRecords() for use when the table join data
	 * is to be merged on the main table's primary key
	 *
	 * @return int total records
	 */

	protected function getJoinMergeTotalRecords()
	{
		$db = $this->getDb();
		$app = JFactory::getApplication();
		$table = $this->getTable();
		$count = 'DISTINCT ' . $table->db_primary_key;
		$totalSql = 'SELECT COUNT(' . $count . ') AS t FROM ' . $table->db_table_name . ' ' . $this->buildQueryJoin();
		$totalSql .= ' ' . $this->buildQueryWhere($app->input->get('incfilters', 1));
		$totalSql .= ' ' . $this->buildQueryGroupBy();
		$totalSql = $this->pluginQuery($totalSql);
		$db->setQuery($totalSql);
		FabrikHelperHTML::debug($totalSql, 'table getJoinMergeTotalRecords');
		$total = $db->loadResult();

		return $total;
	}

	/**
	 * Load in the elements for the table's form
	 * If no form loaded for the list object then one is loaded
	 *
	 * @return  array	element objects
	 */

	public function getFormGroupElementData()
	{
		return $this->getFormModel()->getGroupsHiarachy();
	}

	/**
	 * Require the correct pagenav class based on template
	 *
	 * @param   int  $total       total
	 * @param   int  $limitstart  start
	 * @param   int  $limit       length of records to return
	 *
	 * @return  object	pageNav
	 */

	public function &getPagination($total = 0, $limitstart = 0, $limit = 0)
	{
		$db = FabrikWorker::getDbo();

		if (!isset($this->nav))
		{
			if ($this->randomRecords)
			{
				$limitstart = $this->getRandomLimitStart();
			}

			$params = $this->getParams();
			$this->nav = new FPagination($total, $limitstart, $limit);

			if ($limit == -1)
			{
				$this->nav->viewAll = true;
			}

			// $$$ rob set the nav link urls to the table action to avoid messed up url links when  doing ranged filters via the querystring
			$this->nav->url = $this->getTableAction();
			$this->nav->showAllOption = $params->get('showall-records', false);
			$this->nav->setId($this->getId());
			$this->nav->showTotal = $params->get('show-total', false);
			$item = $this->getTable();
			$this->nav->startLimit = FabrikWorker::getMenuOrRequestVar('rows_per_page', $item->rows_per_page, $this->isMambot);
			$this->nav->showDisplayNum = $params->get('show_displaynum', true);
		}

		return $this->nav;
	}

	/**
	 * Get the random lmit start val
	 *
	 * @return  int	 limit start
	 */

	protected function getRandomLimitStart()
	{
		if (isset($this->randomLimitStart))
		{
			return $this->randomLimitStart;
		}

		$db = $this->getDb();
		$table = $this->getTable();
		/* $$$ rob @todo - do we need to add the join in here as well?
		 * added + 1 as with 4 records to show 3 4th was not shown
		*/
		$query = $db->getQuery(true);
		$query->select('FLOOR(RAND() * COUNT(*) + 1) AS ' . $db->quoteName('offset'))->from($db->quoteName($table->db_table_name));
		$query = $this->buildQueryWhere($query);
		$db->setQuery($query);
		/* $db
		 ->setQuery(
		 		'SELECT FLOOR(RAND() * COUNT(*) + 1) AS ' . $db->quoteName('offset') . ' FROM ' . $db->quoteName($table->db_table_name) . ' '
		 		. $this->buildQueryWhere()); */
		$limitstart = $db->loadResult();
		/*$$$ rob 11/01/2011 cant do this as we dont know what the total is yet
		 $$$ rob ensure that the limitstart + limit isn't greater than the total
		if ($limitstart + $limit > $total) {
		$limitstart = $total - $limit;
		}
		$$$ rob 25/02/2011 if you only have say 3 reocrds then above random will show 1 2 or 3 records
		so decrease the random start num by the table row dispaly num
		going to favour records at the beginning of the table though
		*/
		$limitstart -= $table->rows_per_page;

		if ($limitstart < 0)
		{
			$limitstart = 0;
		}

		$this->randomLimitStart = $limitstart;

		return $limitstart;
	}

	/**
	 * Used to determine which filter action to use.
	 * If a filter is a range then override lists setting with onsubmit
	 *
	 * @return  string
	 */

	public function getFilterAction()
	{
		if (!isset($this->real_filter_action))
		{
			// First, grab the list's setting as the default
			$table = $this->getTable();
			$this->real_filter_action = $table->filter_action;

			// Check to see if any list filter plugins require a Go button, like radius search
			$pluginManager = FabrikWorker::getPluginManager();
			$listPlugins = $pluginManager->getPlugInGroup('list');

			$pluginManager->runPlugins('requireFilterSubmit', $this, 'list');
			$res = $pluginManager->data;

			if (!empty($res))
			{
				if (in_array(1, $res))
				{
					// We've got at least one plugin which needs the button, so set action and bail
					$this->real_filter_action = 'submitform';

					return $this->real_filter_action;
				}
			}

			// No list plugins expressed a preference, so check for range filters
			$form = $this->getFormModel();
			$groups = $form->getGroupsHiarachy();

			foreach ($groups as $groupModel)
			{
				$elementModels = $groupModel->getPublishedElements();

				foreach ($elementModels as $elementModel)
				{
					$element = $elementModel->getElement();

					if (isset($element->filter_type) && $element->filter_type <> '')
					{
						if ($elementModel->canView() && $elementModel->canUseFilter() && $element->show_in_list_summary == '1')
						{
							// $$$ rob does need to check auto-compelte otherwise submission occurs without the value selected.
							if ($element->filter_type == 'range' || $element->filter_type == 'auto-complete')
							{
								$this->real_filter_action = 'submitform';

								return $this->real_filter_action;
							}
						}
					}
				}
			}
		}

		return $this->real_filter_action;
	}

	/**
	 * Gets the part of a url to describe the key that the link links to
	 * if a table this is rowid=x
	 * if a view this is view_primary_key={where statement}
	 *
	 * @param   object  $data  current list row
	 *
	 * @return  string
	 */

	protected function getKeyIndetifier($data)
	{
		return '&rowid=' . $this->getSlug($data);
	}

	/**
	 * Format the row id slug
	 *
	 * @param   object  $row  current list row data
	 *
	 * @return  string	formatted slug
	 */

	protected function getSlug($row)
	{
		if (!isset($row->slug))
		{
			return '';
		}

		$row->slug = str_replace(':', '-', $row->slug);
		$row->slug = JApplication::stringURLSafe($row->slug);

		return $row->slug;
	}

	/**
	 * Get other lists who have joins to the list db tables pk
	 *
	 * @return array of element objects that are database joins and that
	 * use this table's key as their foregin key
	 */

	public function getJoinsToThisKey()
	{
		if (is_null($this->joinsToThisKey))
		{
			$this->joinsToThisKey = array();
			$db = FabrikWorker::getDbo(true);
			$table = $this->getTable();

			if ($table->id == 0)
			{
				$this->joinsToThisKey = array();
			}
			else
			{
				$usersConfig = JComponentHelper::getParams('com_fabrik');
				$query = $db->getQuery(true);

				// Select the required fields from the table.
				$query
				->select(
						"l.db_table_name,
						el.name, el.plugin, l.label AS listlabel, l.id as list_id, \n
						el.id AS element_id, el.label AS element_label, f.id AS form_id,
						el.params AS element_params");
				$query->from('#__{package}_elements AS el');
				$query->join('LEFT', '#__{package}_formgroup AS fg ON fg.group_id = el.group_id');
				$query->join('LEFT', '#__{package}_forms AS f ON f.id = fg.form_id');
				$query->join('LEFT', '#__{package}_lists AS l ON l.form_id = f.id');
				$query->join('LEFT', '#__{package}_groups AS g ON g.id = fg.group_id');
				$query->where('el.published = 1 AND g.published = 1');
				$query
				->where(
						"(plugin = 'databasejoin' AND el.params like '%\"join_db_name\":\"" . $table->db_table_name
						. "\"%'
						AND el.params like  '%\"join_conn_id\":\"" . $table->connection_id . "%') OR (plugin = 'cascadingdropdown' AND \n"
						. " el.params like '\"%cascadingdropdown_table\":\"" . $table->id . "\"%' \n"
						. "AND el.params like '\"%cascadingdropdown_connection\":\"" . $table->connection_id . "\"%') ", "OR");

				// Load in user element links as well
				// $$$rob - not convinced this is a good idea
				if ($usersConfig->get('user_elements_as_related_data', false) == true)
				{
					$query->where("(plugin = 'user' AND
							el.params like '%\"join_conn_id\":\"" . $table->connection_id . "%\"' )", "OR");
				}

				$db->setQuery($query);

				try
				{
					$this->joinsToThisKey = $db->loadObjectList();

					foreach ($this->joinsToThisKey as $join)
					{
						$element_params = json_decode($join->element_params);
						$join->join_key_column = $element_params->join_key_column;
					}
				}
				catch (RuntimeException $e)
				{
					throw new ErrorException('getJoinsToThisKey: ' . $e->getMessage(), 500);
				}
			}
		}

		return $this->joinsToThisKey;
	}

	/**
	 * Get an array of elements that point to a form where their data will be filtered
	 *
	 * @return  array
	 */

	public function getLinksToThisKey()
	{
		if (!is_null($this->linksToThisKey))
		{
			return $this->linksToThisKey;
		}

		$params = $this->getParams();
		$this->linksToThisKey = array();
		$facted = $params->get('factedlinks', new stdClass);

		if (!isset($facted->linkedform))
		{
			return $this->linksToThisKey;
		}

		$linkedForms = $facted->linkedform;
		$aAllJoinsToThisKey = $this->getJoinsToThisKey();

		foreach ($aAllJoinsToThisKey as $join)
		{
			$key = "{$join->list_id}-{$join->form_id}-{$join->element_id}";

			// $$$ rob required for releated form links. otherwise links for forms not listed first in the admin options wherent being rendered
			$this->linksToThisKey[] = isset($linkedForms->$key) ? $join : false;
		}

		return $this->linksToThisKey;
	}

	/**
	 * Get empty data message
	 *
	 * @return string
	 */

	public function getEmptyDataMsg()
	{
		if (isset($this->emptyMsg))
		{
			return $this->emptyMsg;
		}

		$params = $this->getParams();

		return $params->get('empty_data_msg', JText::_('COM_FABRIK_LIST_NO_DATA_MSG'));
	}

	/**
	 * Get the message telling the user that all required filters must be selected
	 *
	 * @return  string
	 */

	public function getRequiredMsg()
	{
		if (isset($this->emptyMsg))
		{
			return $this->emptyMsg;
		}

		return '';
	}

	/**
	 * Do we have all required filters, by both list level and element level settings.
	 *
	 * @return  bool
	 */

	public function gotAllRequiredFilters()
	{
		if ($this->listRequiresFiltering() && !$this->gotOptionalFilters())
		{
			$this->emptyMsg = JText::_('COM_FABRIK_SELECT_AT_LEAST_ONE_FILTER');

			return false;
		}

		if ($this->hasRequiredElementFilters() && !$this->getRequiredFiltersFound())
		{
			$this->emptyMsg = JText::_('COM_FABRIK_PLEASE_SELECT_ALL_REQUIRED_FILTERS');

			return false;
		}

		return true;
	}

	/**
	 * Does a filter have to be appled before we show any list data
	 *
	 * @return bool
	 */

	protected function listRequiresFiltering()
	{
		$app = JFactory::getApplication();
		$params = $this->getParams();
		/*
		 if (!$this->getRequiredFiltersFound()) {
		return true;
		}
		*/
		switch ($params->get('require-filter', 0))
		{
			case 0:
			default:
				return false;
				break;
			case 1:
				return true;
				break;
			case 2:
				return $app->isAdmin() ? false : true;
				break;
		}
	}

	/**
	 * Have all the required filters been met?
	 *
	 * @return  bool  true if they have if false we shouldnt show the table data
	 */

	protected function hasRequiredElementFilters()
	{
		if (isset($this->hasRequiredElementFilters))
		{
			return $this->hasRequiredElementFilters;
		}

		$filters = $this->getFilterArray();
		$elements = $this->getElements();
		$this->hasRequiredElementFilters = false;

		foreach ($elements as $kk => $val2)
		{
			// Don't do with = as this foobars up the last elementModel
			$elementModel = $elements[$kk];
			$element = $elementModel->getElement();

			if ($element->filter_type <> '' && $element->filter_type != 'null')
			{
				if ($elementModel->canView() && $elementModel->canUseFilter())
				{
					if ($elementModel->getParams()->get('filter_required') == 1)
					{
						$this->elementsWithRequiredFilters[] = $elementModel;
						$this->hasRequiredElementFilters = true;
					}
				}
			}
		}

		return $this->hasRequiredElementFilters;
	}

	/**
	 * Do we have any filters that aren't pre-filters
	 *
	 * @return  bool
	 */

	protected function gotOptionalFilters()
	{
		$filters = $this->getFilterArray();
		$ftypes = JArrayHelper::getValue($filters, 'search_type', array());

		foreach ($ftypes as $i => $ftype)
		{
			if ($ftype != 'prefilter')
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Have all the required filters been met?
	 *
	 * @return  bool  true if they have if false we shouldnt show the table data
	 */

	public function getRequiredFiltersFound()
	{
		if (isset($this->requiredFilterFound))
		{
			return $this->requiredFilterFound;
		}

		$filters = $this->getFilterArray();
		$elements = $this->getElements();
		$required = array();

		// If no required filters, then by definition we have them all
		if (!$this->hasRequiredElementFilters())
		{
			return true;
		}
		// If no filter keys, by definition we don't have required ones
		if (!array_key_exists('key', $filters) || !is_array($filters['key']))
		{
			$this->emptyMsg = JText::_('COM_FABRIK_PLEASE_SELECT_ALL_REQUIRED_FILTERS');

			return false;
		}

		foreach ($this->elementsWithRequiredFilters as $elementModel)
		{
			if ($elementModel->getParams()->get('filter_required') == 1)
			{
				$name = FabrikString::safeColName($elementModel->getFullName(false, false));
				reset($filters['key']);
				$found = false;

				while (list($key, $val) = each($filters['key']))
				{
					if ($val == $name)
					{
						$found = true;
						break;
					}
				}

				if (!$found || $filters['origvalue'][$key] == '')
				{
					$this->emptyMsg = JText::_('COM_FABRIK_PLEASE_SELECT_ALL_REQUIRED_FILTERS');

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Get filters for display in html view
	 *
	 * @param   string  $container  List container
	 * @param   string  $type       Type
	 * @param   string  $id         Html id, only used if called from viz plugin
	 * @param   string  $ref        Js ref used when filters set for visualizations
	 *
	 * @return array filters
	 */

	public function getFilters($container = 'listform_1', $type = 'list', $id = '', $ref = '')
	{
		if (!isset($this->viewfilters))
		{
			$profiler = JProfiler::getInstance('Application');
			$params = $this->getParams();
			$this->viewfilters = array();
			JDEBUG ? $profiler->mark('fabrik makeFilters start') : null;
			$modelFilters = $this->makeFilters($container, $type, $id, $ref);
			JDEBUG ? $profiler->mark('fabrik makeFilters end') : null;

			foreach ($modelFilters as $name => $filter)
			{
				$f = new stdClass;
				$f->label = $filter->label;
				$f->element = $filter->filter;
				$f->required = array_key_exists('required', $filter) ? $filter->required : '';
				$this->viewfilters[$filter->name] = $f;
			}

			FabrikWorker::getPluginManager()->runPlugins('onMakeFilters', $this, 'list');
		}

		return $this->viewfilters;
	}

	/**
	 * Creates an array of HTML code for each filter
	 * Also adds in JS code to manage filters
	 *
	 * @param   string  $container  container
	 * @param   string  $type       type listviz
	 * @param   int     $id         html id, only used if called from viz plugin
	 * @param   string  $ref        js filter ref, used when rendering filters for visualizations
	 *
	 * @return  array	of html code for each filter
	 */

	protected function &makeFilters($container = 'listform_1', $type = 'list', $id = '', $ref = '')
	{
		$aFilters = array();
		$table = $this->getTable();
		$opts = new stdClass;
		$opts->container = $container;
		$opts->type = $type;
		$opts->id = $type === 'list' ? $this->getId() : $id;
		$opts->ref = $this->getRenderContext();
		$opts->advancedSearch = $this->getAdvancedSearchOpts();
		$opts->advancedSearch->controller = $type;
		$opts = json_encode($opts);
		$fscript = "\tFabrik.filter_{$container} = new FbListFilter($opts);\n";
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$filters = $this->getFilterArray();
		$params = $this->getParams();

		// Paul Switch to 0/1 for NO/YES from AND/OR so that bootstrap classes work but support legacy values
		if (($params->get('search-mode', '0') == '1')
			|| ($params->get('search-mode', '0') == 'OR'))
		{
			// One field to search them all (and in the darkness bind them)
			$requestKey = $this->getFilterModel()->getSearchAllRequestKey();
			$v = $this->getFilterModel()->getSearchAllValue('html');
			$o = new stdClass;
			$searchLabel = $params->get('search-all-label', JText::_('COM_FABRIK_SEARCH'));
			$class = FabrikWorker::j3() ? 'fabrik_filter search-query input-medium' : 'fabrik_filter';
			$o->filter = '<input type="search" size="20" placeholder="' . $searchLabel . '" value="' . $v
			. '" class="' . $class . '" name="' . $requestKey . '" />';

			if ($params->get('search-mode-advanced') == 1)
			{
				$opts = array();
				$opts[] = JHTML::_('select.option', 'all', JText::_('COM_FABRIK_ALL_OF_THESE_TERMS'));
				$opts[] = JHTML::_('select.option', 'any', JText::_('COM_FABRIK_ANY_OF_THESE_TERMS'));
				$opts[] = JHTML::_('select.option', 'exact', JText::_('COM_FABRIK_EXACT_TERMS'));
				$opts[] = JHTML::_('select.option', 'none', JText::_('COM_FABRIK_NONE_OF_THESE_TERMS'));
				$mode = $app->getUserStateFromRequest('com_' . $package . '.list' . $this->getRenderContext() . '.searchallmode', 'search-mode-advanced');
				$o->filter .= '&nbsp;'
						. JHTML::_('select.genericList', $opts, 'search-mode-advanced', "class='fabrik_filter'", 'value', 'text', $mode);
			}

			$o->name = 'all';
			$o->label = $searchLabel;
			$aFilters[] = $o;
		}

		$counter = 0;
		/* $$$ hugh - another one of those weird ones where if we use = the foreach loop
		 * will sometimes skip a group
		* $groups = $this->getFormGroupElementData();
		*/
		$groups = $this->getFormGroupElementData();

		foreach ($groups as $groupModel)
		{
			$g = $groupModel->getGroup();
			$elementModels = null;
			$elementModels = $groupModel->getPublishedElements();

			foreach ($elementModels as $elementModel)
			{
				$element = $elementModel->getElement();

				/*$$ rob added as some filter_types were null, have to double check that this doesnt
				 * mess with showing the readonly values from search forms
				*/
				if (isset($element->filter_type) && $element->filter_type <> '' && $element->filter_type != 'null')
				{
					if ($elementModel->canView() && $elementModel->canUseFilter())
					{
						/* $$$ rob in facted browsing somehow (not sure how!) some elements from the facted table get inserted into elementModels
						 * with their form id set - so test if its been set and if its not the same as the current form id
						* if so then ignore
						*/
						if (isset($element->form_id) && (int) $element->form_id !== 0 && $element->form_id !== $this->getFormModel()->getId())
						{
							continue;
						}
						// Force the correct group model into the element model to ensure no wierdness in getting the element name
						$elementModel->setGroupModel($groupModel);
						$o = new stdClass;
						$o->name = $elementModel->getFullName(true, false);
						$o->filter = $elementModel->getFilter($counter, true);
						$fscript .= $elementModel->filterJS(true, $container);
						$o->required = $elementModel->getParams()->get('filter_required');
						$o->label = $elementModel->getListHeading();
						$aFilters[] = $o;
						$counter++;
					}
				}
			}
		}

		$fscript .= 'Fabrik.filter_' . $container . ".update();\n";
		$this->filterJs = $fscript;

		// Check for search form filters - if they exists create hidden elements for them
		$keys = JArrayHelper::getValue($filters, 'key', array());

		foreach ($keys as $i => $key)
		{
			if ($filters['no-filter-setup'][$i] == '1' && !in_array($filters['search_type'][$i], array('searchall', 'advanced', 'jpluginfilters')))
			{
				$o = new stdClass;
				/* $$$ rob - we are now setting read only filters 'filter' var to the elements read only
				 * label for the passed in filter value
				*$o->filter = $value;
				*/
				$elementModel = $this->getFormModel()->getElement(str_replace('`', '', $key));
				$o->filter = JArrayHelper::getValue($filters['filter'], $i);

				if ($elementModel)
				{
					$elementModel->getElement()->filter_type = 'hidden';
					$o->filter .= $elementModel->getFilter(0, true);
				}

				$o->name = $filters['key'][$i];
				$o->label = $filters['label'][$i];
				$aFilters[] = $o;
			}
		}

		return $aFilters;
	}

	/**
	 * Build the advanced search link
	 *
	 * @return  string  <a href...> link
	 */

	public function getAdvancedSearchLink()
	{
		$params = $this->getParams();

		if ($params->get('advanced-filter', '0'))
		{
			$tmpl = $this->getTmpl();
			$url = $this->getAdvancedSearchURL();
			$title = '<span>' . JText::_('COM_FABRIK_ADVANCED_SEARCH') . '</span>';
			$opts = array('alt' => JText::_('COM_FABRIK_ADVANCED_SEARCH'), 'class' => 'fabrikTip', 'opts' => "{notice:true}", 'title' => $title);
			$img = FabrikHelperHTML::image('find.png', 'list', $tmpl, $opts);

			return '<a href="' . $url . '" class="advanced-search-link">' . $img . '</a>';
		}
		else
		{
			return '';
		}
	}

	/**
	 * Get the URL used to open the advanced search window
	 *
	 * @return  string
	 */

	public function getAdvancedSearchURL()
	{
		$app = JFactory::getApplication();
		$table = $this->getTable();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$url = COM_FABRIK_LIVESITE . 'index.php?option=com_' . $package . '&amp;view=list&amp;layout=_advancedsearch&amp;tmpl=component&amp;listid='
				. $table->id . '&amp;nextview=' . $app->input->get('view', 'list');

		// Defines if we are in a module or in the component.
		$url .= '&amp;scope=' . $app->scope;
		$url .= '&amp;tkn=' . JSession::getFormToken();

		return $url;
	}

	/**
	 * Called from index.php?option=com_fabrik&view=list&layout=_advancedsearch&tmpl=component&listid=4
	 * advanced serach popup view
	 *
	 * @return  object	advanced search options
	 */

	public function getAdvancedSearchOpts()
	{
		$params = $this->getParams();
		$opts = new stdClass;

		// $$$ rob - 20/208/2012 if list advanced search off return nothing
		if ($params->get('advanced-filter') == 0)
		{
			return $opts;
		}

		$list = $this->getTable();
		$listRef = $this->getRenderContext();
		$opts->conditionList = FabrikHelperHTML::conditonList($listRef, '');
		list($fieldNames, $firstFilter) = $this->getAdvancedSearchElementList();
		$statements = $this->getStatementsOpts();
		$opts->elementList = JHTML::_('select.genericlist', $fieldNames, 'fabrik___filter[list_' . $listRef . '][key][]',
				'class="inputbox key input-small" size="1" ', 'value', 'text');
		$opts->statementList = JHTML::_('select.genericlist', $statements, 'fabrik___filter[list_' . $listRef . '][condition][]',
				'class="inputbox input-small" size="1" ', 'value', 'text');
		$opts->listid = $list->id;
		$opts->listref = $listRef;
		$opts->ajax = $this->isAjax();
		$opts->counter = count($this->getadvancedSearchRows()) - 1;
		$elements = $this->getElements();
		$arr = array();

		foreach ($elements as $e)
		{
			$key = $e->getFilterFullName();
			$arr[$key] = array('id' => $e->getId(), 'plugin' => $e->getElement()->plugin);
		}

		$opts->elementMap = $arr;

		return $opts;
	}

	/**
	 * Get a list of elements that are included in the advacned search dropdown list
	 *
	 * @return  array  list of fields names and which is the first filter
	 */

	private function getAdvancedSearchElementList()
	{
		$first = false;
		$firstFilter = false;
		$fieldNames[] = JHTML::_('select.option', '', JText::_('COM_FABRIK_PLEASE_SELECT'));
		$elementModels = $this->getElements();

		foreach ($elementModels as $elementModel)
		{
			$element = $elementModel->getElement();
			$elParams = $elementModel->getParams();

			if ($elParams->get('inc_in_adv_search', 1))
			{
				$elName = $elementModel->getFilterFullName();

				if (!$first)
				{
					$first = true;
					$firstFilter = $elementModel->getFilter(0, false);
				}

				$fieldNames[] = JHTML::_('select.option', $elName, strip_tags($element->label));
			}
		}

		return array($fieldNames, $firstFilter);
	}

	/**
	 * Get a list of advanced search options
	 *
	 * @return array of JHTML options
	 */

	private function getStatementsOpts()
	{
		$statements = array();
		$statements[] = JHTML::_('select.option', '=', JText::_('COM_FABRIK_EQUALS'));
		$statements[] = JHTML::_('select.option', '<>', JText::_('COM_FABRIK_NOT_EQUALS'));
		$statements[] = JHTML::_('select.option', 'BEGINS WITH', JText::_('COM_FABRIK_BEGINS_WITH'));
		$statements[] = JHTML::_('select.option', 'CONTAINS', JText::_('COM_FABRIK_CONTAINS'));
		$statements[] = JHTML::_('select.option', 'ENDS WITH', JText::_('COM_FABRIK_ENDS_WITH'));
		$statements[] = JHTML::_('select.option', '>', JText::_('COM_FABRIK_GREATER_THAN'));
		$statements[] = JHTML::_('select.option', '<', JText::_('COM_FABRIK_LESS_THAN'));
		$statements[] = JHTML::_('select.option', 'EMPTY', JText::_('COM_FABRIK_IS_EMPTY'));

		return $statements;
	}

	/**
	 * Get a list of submitted advanced filters
	 *
	 * @return array advanced filter values
	 */

	private function getAdvancedFilterValues()
	{
		$filters = $this->getFilterArray();
		$advanced = array();
		$iKeys = array_keys(JArrayHelper::getValue($filters, 'key', array()));

		foreach ($iKeys as $i)
		{
			$searchType = JArrayHelper::getValue($filters['search_type'], $i);

			if (!is_null($searchType) && $searchType == 'advanced')
			{
				$tmp = array();

				foreach (array_keys($filters) as $k)
				{
					if (array_key_exists($k, $advanced))
					{
						$advanced[$k][] = JArrayHelper::getValue($filters[$k], $i, '');
					}
					else
					{
						$advanced[$k] = array_key_exists($i, $filters[$k]) ? array(($filters[$k][$i])) : '';
					}
				}
			}
		}

		return $advanced;
	}

	/**
	 * Build an array of html data that gets inserted into the advanced search popup view
	 *
	 * @return  array	html lists/fields
	 */

	public function getAdvancedSearchRows()
	{
		if (isset($this->advancedSearchRows))
		{
			return $this->advancedSearchRows;
		}

		$statements = $this->getStatementsOpts();
		$app = JFactory::getApplication();
		$input = $app->input;
		$rows = array();
		$first = false;
		$elementModels = $this->getElements();
		list($fieldNames, $firstFilter) = $this->getAdvancedSearchElementList();
		$prefix = 'fabrik___filter[list_' . $this->getRenderContext() . '][';
		$type = '<input type="hidden" name="' . $prefix . 'search_type][]" value="advanced" />';
		$grouped = '<input type="hidden" name="' . $prefix . 'grouped_to_previous][]" value="0" />';
		$filters = $this->getAdvancedFilterValues();
		$counter = 0;

		if (array_key_exists('key', $filters))
		{
			foreach ($filters['key'] as $key)
			{
				foreach ($elementModels as $elementModel)
				{
					$testkey = FabrikString::safeColName($elementModel->getFullName(false, false));

					if ($testkey == $key)
					{
						break;
					}
				}

				$join = $filters['join'][$counter];
				$condition = $filters['condition'][$counter];
				$value = $filters['origvalue'][$counter];
				$v2 = $filters['value'][$counter];
				$jsSel = '=';

				switch ($condition)
				{
					case 'EMPTY':
						$jsSel = 'EMPTY';
						break;
					case "<>":
						$jsSel = '<>';
						break;
					case "=":
						$jsSel = 'EQUALS';
						break;
					case "<":
						$jsSel = '<';
						break;
					case ">":
						$jsSel = '>';
						break;
					default:
						$firstChar = JString::substr($v2, 1, 1);
						$lastChar = JString::substr($v2, -2, 1);

						switch ($firstChar)
						{
							case "%":
								$jsSel = ($lastChar == "%") ? 'CONTAINS' : $jsSel = 'ENDS WITH';
								break;
							default:
								if ($lastChar == "%")
								{
									$jsSel = 'BEGINS WITH';
								}
								break;
						}
						break;
				}

				if (is_string($value))
				{
					$value = trim(trim($value, '"'), "%");
				}

				if ($counter == 0)
				{
					$join = JText::_('COM_FABRIK_WHERE') . '<input type="hidden" value="WHERE" name="' . $prefix . 'join][]" />';
				}
				else
				{
					$join = FabrikHelperHTML::conditonList($this->getRenderContext(), $join);
				}

				$lineElname = FabrikString::safeColName($elementModel->getFullName(true, false));
				$orig = $input->get($lineElname);
				$input->set($lineElname, array('value' => $value));
				$filter = $elementModel->getFilter($counter, false);
				$input->set($lineElname, $orig);
				$key = JHTML::_('select.genericlist', $fieldNames, $prefix . 'key][]', 'class="inputbox key input-small" size="1" ', 'value', 'text', $key);
				$jsSel = JHTML::_('select.genericlist', $statements, $prefix . 'condition][]', 'class="inputbox input-small" size="1" ', 'value', 'text', $jsSel);
				$rows[] = array('join' => $join, 'element' => $key, 'condition' => $jsSel, 'filter' => $filter, 'type' => $type,
						'grouped' => $grouped);
				$counter++;
			}
		}

		if ($counter == 0)
		{
			$join = JText::_('COM_FABRIK_WHERE') . '<input type="hidden" name="' . $prefix . 'join][]" value="WHERE" />';
			$key = JHTML::_('select.genericlist', $fieldNames, $prefix . 'key][]', 'class="inputbox key" size="1" ', 'value', 'text', '');
			$jsSel = JHTML::_('select.genericlist', $statements, $prefix . 'condition][]', 'class="inputbox" size="1" ', 'value', 'text', '');
			$rows[] = array('join' => $join, 'element' => $key, 'condition' => $jsSel, 'filter' => $firstFilter, 'type' => $type,
					'grouped' => $grouped);
		}

		$this->advancedSearchRows = $rows;

		return $rows;
	}

	/**
	 * Fet the headings that should be shown in the csv export file
	 *
	 * @param   array  $headings  to use (key is element name value must be 1 for it to be added)
	 *
	 * @return  void
	 */

	public function setHeadingsForCSV($headings)
	{
		$asfields = $this->getAsFields();
		$newfields = array();
		$db = $this->getDb();
		$this->temp_db_key_addded = false;
		/* $$$ rob if no fields specified presume we are requesting CSV file from URL and return
		 * all fields otherwise set the fields to be those selected in fabrik window
		* or defined in the lists csv export settings
		*/
		if (!empty($headings))
		{
			foreach ($headings as $name => $val)
			{
				if ($val != 1)
				{
					continue;
				}

				$elModel = $this->getFormModel()->getElement($name);

				if (is_object($elModel))
				{
					$name = $elModel->getFullName(true, false);
					$pName = $elModel->isJoin() ? $db->quoteName($elModel->getJoinModel()->getJoin()->table_join . '___params') : '';

					foreach ($asfields as $f)
					{
						if ((strstr($f, $db->quoteName($name)) || strstr($f, $db->quoteName($name . '_raw'))
							|| ($elModel->isJoin() && strstr($f, $pName))))
						{
							$newfields[] = $f;
						}
					}
				}
			}

			$this->asfields = $newfields;
		}
	}

	/**
	 * returns the table headings, seperated from writetable function as
	 * when group_by is selected mutliple tables are written
	 * 09/07/2011 moved headingClass into arry rather than string
	 *
	 * @return  array  (table headings, array columns, $aLinkElements)
	 */

	public function getHeadings()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$item = $this->getTable();
		$item->order_dir = JString::strtolower($item->order_dir);
		$aTableHeadings = array();
		$headingClass = array();
		$cellClass = array();
		$params = $this->getParams();
		$w = new FabrikWorker;
		$session = JFactory::getSession();
		$formModel = $this->getFormModel();
		$oldLinksToForms = $this->getLinksToThisKey();
		$linksToForms = array();

		foreach ($oldLinksToForms as $join)
		{
			// $$$ hugh - anoher issue with getLinksTothisKey() now returning false for some joins.
			if ($join)
			{
				$k = $join->list_id . '-' . $join->form_id . '-' . $join->element_id;
				$linksToForms[$k] = $join;
			}
		}

		$groups = $formModel->getGroupsHiarachy();
		$groupHeadings = array();

		$orderbys = json_decode($item->order_by, true);

		// Responsive element classes
		$listClasses = json_decode($params->get('list_responsive_elements'));

		if (!isset($listClasses->responsive_elements))
		{
			$listClasses = new stdClass;
			$listClasses->responsive_elements = array();
		}

		$showInList = $this->showInList();

		if (!in_array($this->outputFormat, array('pdf', 'csv')))
		{
			if ($this->canSelectRows() && $params->get('checkboxLocation', 'end') !== 'end')
			{
				$this->addCheckBox($aTableHeadings, $headingClass, $cellClass);
			}

			if ($params->get('checkboxLocation', 'end') !== 'end')
			{
				$this->actionHeading($aTableHeadings, $headingClass, $cellClass);
			}
		}

		foreach ($groups as $groupModel)
		{
			$groupHeadingKey = $w->parseMessageForPlaceHolder($groupModel->getGroup()->label, array(), false);
			$groupHeadings[$groupHeadingKey] = 0;
			$elementModels = $groupModel->getPublishedListElements();

			if ($groupModel->canView() === false)
			{
				continue;
			}

			foreach ($elementModels as $key => $elementModel)
			{
				$element = $elementModel->getElement();

				// If we define the elements to show in the list - e.g in admin list module then only show those elements
				if (!empty($showInList) && !in_array($element->id, $showInList))
				{
					continue;
				}

				$viewLinkAdded = false;
				$groupHeadings[$groupHeadingKey]++;
				$key = $elementModel->getFullName(true, false);
				$compsitKey = !empty($showInList) ? array_search($element->id, $showInList) . ':' . $key : $key;
				$orderKey = $elementModel->getOrderbyFullName(false);
				$elementParams = $elementModel->getParams();
				$label = $elementModel->getListHeading();
				$label = $w->parseMessageForPlaceHolder($label, array());

				if ($elementParams->get('can_order') == '1' && $this->outputFormat != 'csv')
				{
					$context = 'com_' . $package . '.list' . $this->getRenderContext() . '.order.' . $element->id;
					$orderDir = $session->get($context);
					$class = "";
					$currentOrderDir = $orderDir;
					$tmpl = $this->getTmpl();

					switch ($orderDir)
					{
						case "desc":
							$orderDir = "-";
							$class = 'class="fabrikorder-desc"';
							$img = FabrikHelperHTML::image('arrow-up.png', 'list', $tmpl, array('alt' => JText::_('COM_FABRIK_ORDER')));
							break;
						case "asc":
							$orderDir = "desc";
							$class = 'class="fabrikorder-asc"';
							$img = FabrikHelperHTML::image('arrow-down.png', 'list', $tmpl, array('alt' => JText::_('COM_FABRIK_ORDER')));
							break;
						case "":
						case "-":
							$orderDir = "asc";
							$class = 'class="fabrikorder"';
							$img = FabrikHelperHTML::image('menu-2.png', 'list', $tmpl, array('alt' => JText::_('COM_FABRIK_ORDER')));
							break;
					}

					if ($class === '')
					{
						if (in_array($key, $orderbys))
						{
							if ($item->order_dir === 'desc')
							{
								$class = 'class="fabrikorder-desc"';
								$img = FabrikHelperHTML::image('orderdesc.png', 'list', $tmpl, array('alt' => JText::_('COM_FABRIK_ORDER')));
							}
						}
					}

					$heading = '<a ' . $class . ' href="#">' . $img . ' ' . $label . '</a>';
				}
				else
				{
					$heading = $label;
				}

				$aTableHeadings[$compsitKey] = $heading;

				// Check responsive class
				$responsiveKey = array_search($element->id, $listClasses->responsive_elements);
				$responsiveClass = $responsiveKey !== false ? JArrayHelper::getValue($listClasses->responsive_class, $responsiveKey, '') : '';

				if ($responsiveClass !== '')
				{
					$responsiveClass .= ' ';
				}

				$headingClass[$compsitKey] = array('class' => $responsiveClass . $elementModel->getHeadingClass(),
						'style' => $elementParams->get('tablecss_header'));
				$cellClass[$compsitKey] = array('class' => $responsiveClass . $elementModel->getCellClass(), 'style' => $elementParams->get('tablecss_cell'));

				// Add in classes for repeat/merge data
				if ($groupModel->canRepeat())
				{
					$cellClass[$compsitKey]['class'] .= ' repeat';
					$dis = $params->get('join-display');

					if ($dis != 'default')
					{
						$cellClass[$compsitKey]['class'] .= '-' . $dis;
					}
				}
			}

			if ($groupHeadings[$groupHeadingKey] == 0)
			{
				unset($groupHeadings[$groupHeadingKey]);
			}
		}

		if (!empty($showInList))
		{
			$aTableHeadings = $this->removeHeadingCompositKey($aTableHeadings);
			$headingClass = $this->removeHeadingCompositKey($headingClass);
			$cellClass = $this->removeHeadingCompositKey($cellClass);
		}

		if (!in_array($this->outputFormat, array('pdf', 'csv')))
		{
			// @TODO check if any plugins need to use the selector as well!
			if ($this->canSelectRows() && $params->get('checkboxLocation', 'end') === 'end')
			{
				$this->addCheckBox($aTableHeadings, $headingClass, $cellClass);
			}

			$viewLinkAdded = false;

			// If no elements linking to the edit form add in a edit column (only if we have the right to edit/view of course!)
			if ($params->get('checkboxLocation', 'end') === 'end')
			{
				$this->actionHeading($aTableHeadings, $headingClass, $cellClass);
			}
			// Create columns containing links which point to lists associated with this list
			$facted = $params->get('factedlinks');
			$joinsToThisKey = $this->getJoinsToThisKey();
			$listOrder = json_decode($params->get('faceted_list_order'));
			$formOrder = json_decode($params->get('faceted_form_order'));

			if (is_null($listOrder))
			{
				// Not yet saved with order
				$listOrder = is_object($facted) && is_object($facted->linkedlist) ? array_keys(JArrayHelper::fromObject($facted->linkedlist)) : array();
			}

			if (is_null($formOrder))
			{
				// Not yet saved with order
				$formOrder = is_object($facted) && is_object($facted->linkedform) ? array_keys(JArrayHelper::fromObject($facted->linkedform)) : array();
			}

			foreach ($listOrder as $key)
			{
				$join = $this->facetedJoin($key);

				if ($join === false)
				{
					continue;
				}

				if (is_object($join) && isset($facted->linkedlist->$key))
				{
					$linkedTable = $facted->linkedlist->$key;
					$heading = $facted->linkedlistheader->$key;

					if ($linkedTable != '0')
					{
						$prefix = $join->element_id . '___' . $linkedTable . '_list_heading';
						$aTableHeadings[$prefix] = empty($heading) ? $join->listlabel . ' ' . JText::_('COM_FABRIK_LIST') : $heading;
						$headingClass[$prefix] = array('class' => 'fabrik_ordercell related ' . $prefix,
								'style' => '');
						$cellClass[$prefix] = array('class' => $prefix . ' fabrik_element related');
					}
				}
			}

			foreach ($formOrder as $key)
			{
				$join = $this->facetedJoin($key);

				if ($join === false)
				{
					continue;
				}

				$linkedForm = $facted->linkedform->$key;

				if ($linkedForm != '0')
				{
					$heading = $facted->linkedformheader->$key;
					$prefix = $join->db_table_name . '___' . $join->name . '_form_heading';
					$aTableHeadings[$prefix] = empty($heading) ? $join->listlabel . ' ' . JText::_('COM_FABRIK_FORM') : $heading;
					$headingClass[$prefix] = array('class' => 'fabrik_ordercell related ' . $prefix,
							'style' => '');
					$cellClass[$prefix] = array('class' => $prefix . ' fabrik_element related');
				}
			}
		}

		if ($this->canSelectRows())
		{
			$groupHeadings[''] = '';
		}

		$args['tableHeadings'] = $aTableHeadings;
		$args['groupHeadings'] = $groupHeadings;
		$args['headingClass'] = $headingClass;
		$args['cellClass'] = $cellClass;
		FabrikWorker::getPluginManager()->runPlugins('onGetPluginRowHeadings', $this, 'list', $args);

		return array($aTableHeadings, $groupHeadings, $headingClass, $cellClass);
	}

	/**
	 * Find a faceted join based on composite key
	 *
	 * @param   string  $searchKey  Key
	 *
	 * @return  mixed   False if not found, join object if found
	 */

	protected function facetedJoin($searchKey)
	{
		$facetedJoins = $this->getJoinsToThisKey();

		foreach ($facetedJoins as $join)
		{
			$key = $join->list_id . '-' . $join->form_id . '-' . $join->element_id;

			if ($searchKey === $key)
			{
				return $join;
			}
		}

		return false;
	}

	/**
	 * Put the actions in the headings array - separated to here to enable it to be added at the end or beginning
	 *
	 * @param   array  &$aTableHeadings  Table headings
	 * @param   array  &$headingClass    Heading classes
	 * @param   array  &$cellClass       Cell classes
	 *
	 * @return  void
	 */

	protected function actionHeading(&$aTableHeadings, &$headingClass, &$cellClass)
	{
		$params = $this->getParams();
		$filterMethod = $params->get('show-table-filters');
		$filters = $this->getFilters('listform_' . $this->getRenderContext(), 'list');
		$filtersUnderHeadingsAndGo = ($this->getFilterAction() === 'submitform' && !empty($filters) && $filterMethod > 2) ? true : false;

		// Check for conditions in https://github.com/Fabrik/fabrik/issues/621
		$details = $this->canViewDetails();

		if ($params->get('detaillink', 1) == 0)
		{
			$details = false;
		}

		$edit = $this->canEdit();

		if ($params->get('editlink', 1) == 0)
		{
			$edit = false;
		}

		if ($this->canSelectRows() || $this->canEditARow() || $details || $edit || $filtersUnderHeadingsAndGo)
		{
			// 3.0 actions now go in one column
			$pluginManager = FabrikWorker::getPluginManager();
			$params = $this->getParams();
			$headingButtons = array();

			if ($this->deletePossible())
			{
				$headingButtons[] = $this->deleteButton('', true);
			}

			$return = $pluginManager->runPlugins('button', $this, 'list', array('heading' => true));
			$res = $pluginManager->data;

			foreach ($res as &$r)
			{
				$r = $this->actionMethod() == 'dropdown' ? '<li>' . $r . '</li>' : $r;
			}

			$headingButtons = array_merge($headingButtons, $res);

			if (FabrikWorker::j3())
			{
				if (empty($headingButtons))
				{
					$aTableHeadings['fabrik_actions'] = '';
				}
				else
				{
					if ($this->actionMethod() == 'dropdown')
					{
						$aTableHeadings['fabrik_actions'] = FabrikHelperHTML::bootStrapDropDown($headingButtons);
					}
					else
					{
						$aTableHeadings['fabrik_actions'] = FabrikHelperHTML::bootStrapButtonGroup($headingButtons);
					}
				}
			}
			else
			{
				$aTableHeadings['fabrik_actions'] = empty($headingButtons) ? '' : '<ul class="fabrik_action">' . implode("\n", $headingButtons) . '</ul>';
			}

			$headingClass['fabrik_actions'] = array('class' => 'fabrik_ordercell fabrik_actions', 'style' => '');

			// Needed for ajax filter/nav
			$cellClass['fabrik_actions'] = array('class' => 'fabrik_actions fabrik_element');
		}
	}

	/**
	 * Put the checkbox in the headings array - separated to here to enable it to be added at the end or beginning
	 *
	 * @param   array  &$aTableHeadings  table headings
	 * @param   array  &$headingClass    heading classes
	 * @param   array  &$cellClass       cell classes
	 *
	 * @return  void
	 */

	protected function addCheckBox(&$aTableHeadings, &$headingClass, &$cellClass)
	{
		$id = 'list_' . $this->getId() . '_checkAll';
		$select = '<input type="checkbox" name="checkAll" class="' . $id . '" id="' . $id . '" />';
		$aTableHeadings['fabrik_select'] = $select;
		$headingClass['fabrik_select'] = array('class' => 'fabrik_ordercell fabrik_select', 'style' => '');

		// Needed for ajax filter/nav
		$cellClass['fabrik_select'] = array('class' => 'fabrik_select fabrik_element');
	}

	/**
	 * Enter description here ...
	 *
	 * @param   array  $arr  array
	 *
	 * @return  array
	 */

	protected function removeHeadingCompositKey($arr)
	{
		/* $$$ hugh - horrible hack, but if we just ksort as-is, once we have more than 9 elements,
		 * it'll start sort 0,1,10,11,2,3 etc.  There's no doubt a cleaner way to do this,
		* but for now ... rekey with a 0 padded prefix before we ksort
		*/
		foreach ($arr as $key => $val)
		{
			if (strstr($key, ':'))
			{
				list($part1, $part2) = explode(':', $key);
				$part1 = sprintf('%03d', $part1);
				$newkey = $part1 . ':' . $part2;
				$arr[$newkey] = $arr[$key];
				unset($arr[$key]);
			}
		}

		ksort($arr);

		foreach ($arr as $key => $val)
		{
			if (strstr($key, ':'))
			{
				$bits = explode(':', $key);
				$newkey = array_pop($bits);
				$arr[$newkey] = $arr[$key];
				unset($arr[$key]);
			}
		}

		return $arr;
	}

	/**
	 * Can the user select the specified row
	 *
	 * Needs to return true to insert a checkbox in the row.
	 *
	 * @param   object  $row  row of list data
	 *
	 * @return  bool
	 */

	public function canSelectRow($row)
	{
		$canSelect = FabrikWorker::getPluginManager()->runPlugins('onCanSelectRow', $this, 'list', $row);

		if (in_array(false, $canSelect))
		{
			return false;
		}

		if ($this->canDelete($row))
		{
			$this->canSelectRows = true;

			return true;
		}

		$params = $this->getParams();
		$usedPlugins = (array) $params->get('plugins');

		if (empty($usedPlugins))
		{
			return false;
		}

		$pluginManager = FabrikWorker::getPluginManager();
		$listplugins = $pluginManager->getPlugInGroup('list');
		$v = in_array(true, $pluginManager->runPlugins('canSelectRows', $this, 'list'));

		if ($v)
		{
			$this->canSelectRows = true;
		}

		return $v;
	}

	/**
	 * Can the user select ANY row?
	 *
	 * Should the checkbox be shown in the list
	 * If you can delete then true returned, if not then check
	 * available list plugins to see if they allow for row selection
	 * if so a checkbox column appears in the table
	 *
	 * @return  bool
	 */

	public function canSelectRows()
	{
		if (!is_null($this->canSelectRows))
		{
			return $this->canSelectRows;
		}

		if ($this->canDelete() || $this->deletePossible())
		{
			$this->canSelectRows = true;

			return $this->canSelectRows;
		}

		$params = $this->getParams();
		$usedPlugins = (array) $params->get('plugins');

		if (empty($usedPlugins))
		{
			$this->canSelectRows = false;

			return $this->canSelectRows;
		}

		$pluginManager = FabrikWorker::getPluginManager();
		$pluginManager->getPlugInGroup('list');
		$this->canSelectRows = in_array(true, $pluginManager->runPlugins('canSelectRows', $this, 'list'));

		return $this->canSelectRows;
	}

	/**
	 * Clear the calculations
	 *
	 * @return  void
	 */

	public function clearCalculations()
	{
		unset($this->runCalculations);
	}

	/**
	 * return mathematical column calculations (run at doCalculations() on for submission)
	 *
	 * @return  array  calculations
	 */

	public function getCalculations()
	{
		if (!empty($this->runCalculations))
		{
			return $this->runCalculations;
		}

		$user = JFactory::getUser();
		$aclGroups = $user->getAuthorisedViewLevels();
		$aCalculations = array();
		$formModel = $this->getFormModel();
		$aAvgs = array();
		$aSums = array();
		$aMedians = array();
		$aCounts = array();
		$aCustoms = array();
		$groups = $formModel->getGroupsHiarachy();

		foreach ($groups as $groupModel)
		{
			$elementModels = $groupModel->getPublishedElements();

			foreach ($elementModels as $elementModel)
			{
				$params = $elementModel->getParams();
				$elName = $elementModel->getFullName(true, false);
				$sumOn = $params->get('sum_on', '0');
				$avgOn = $params->get('avg_on', '0');
				$medianOn = $params->get('median_on', '0');
				$countOn = $params->get('count_on', '0');
				$customOn = $params->get('custom_calc_on', '0');
				$sumAccess = $params->get('sum_access', 0);
				$avgAccess = $params->get('avg_access', 0);
				$medianAccess = $params->get('median_access', 0);
				$countAccess = $params->get('count_access', 0);
				$customAccess = $params->get('custom_calc_access', 0);

				if ($sumOn && in_array($sumAccess, $aclGroups) && $params->get('sum_value', '') != '')
				{
					$aSums[$elName] = $params->get('sum_value', '');
					$ser = $params->get('sum_value_serialized');

					if (is_string($ser))
					{
						// If group gone from repeat to none repeat could be array
						$aSums[$elName . '_obj'] = unserialize($ser);
					}
				}

				if ($avgOn && in_array($avgAccess, $aclGroups) && $params->get('avg_value', '') != '')
				{
					$aAvgs[$elName] = $params->get('avg_value', '');
					$ser = $params->get('avg_value_serialized');

					if (is_string($ser))
					{
						$aAvgs[$elName . '_obj'] = unserialize($ser);
					}
				}

				if ($medianOn && in_array($medianAccess, $aclGroups) && $params->get('median_value', '') != '')
				{
					$aMedians[$elName] = $params->get('median_value', '');
					$ser = $params->get('median_value_serialized', '');

					if (is_string($ser))
					{
						$aMedians[$elName . '_obj'] = unserialize($ser);
					}
				}

				if ($countOn && in_array($countAccess, $aclGroups) && $params->get('count_value', '') != '')
				{
					$aCounts[$elName] = $params->get('count_value', '');
					$ser = $params->get('count_value_serialized');

					if (is_string($ser))
					{
						$aCounts[$elName . '_obj'] = unserialize($ser);
					}
				}

				if ($customOn && in_array($customAccess, $aclGroups) && $params->get('custom_calc_value', '') != '')
				{
					$aCustoms[$elName] = $params->get('custom_calc_value', '');
					$ser = $params->get('custom_calc_value_serialized');

					if (is_string($ser))
					{
						$aCounts[$elName . '_obj'] = unserialize($ser);
					}
				}
			}
		}

		$aCalculations['sums'] = $aSums;
		$aCalculations['avgs'] = $aAvgs;
		$aCalculations['medians'] = $aMedians;
		$aCalculations['count'] = $aCounts;
		$aCalculations['custom_calc'] = $aCustoms;
		$this->runCalculations = $aCalculations;

		return $aCalculations;
	}

	/**
	 * Get list headings to pass into list js oject
	 *
	 * @return  string	headings tablename___name
	 */

	public function jsonHeadings()
	{
		$aHeadings = array();
		$table = $this->getTable();
		$formModel = $this->getFormModel();
		$groups = $formModel->getGroupsHiarachy();

		foreach ($groups as $groupModel)
		{
			$elementModels = $groupModel->getPublishedElements();

			foreach ($elementModels as $elementModel)
			{
				$element = $elementModel->getElement();

				if ($element->show_in_list_summary)
				{
					$aHeadings[] = $table->db_table_name . '___' . $element->name;
				}
			}
		}

		return "['" . implode("','", $aHeadings) . "']";
	}

	/**
	 * Strip the table names from the front of the key
	 *
	 * @param   array   $data   data to strip
	 * @param   string  $split  string splitter ___ or .
	 *
	 * @return  array stripped data
	 */

	public function removeTableNameFromSaveData($data, $split = '___')
	{
		foreach ($data as $key => $val)
		{
			$akey = explode($split, $key);

			if (count($akey) > 1)
			{
				$newKey = $akey[1];
				unset($data[$key]);
			}
			else
			{
				$newKey = $akey[0];
			}

			$data[$newKey] = $val;
		}

		return $data;
	}

	/**
	 * Saves posted form data into a table
	 * data should be keyed on short name
	 *
	 * @param   array   $data            To save
	 * @param   int     $rowId           Row id to edit/updated
	 * @param   bool    $isJoin          Is the data being saved into a join table
	 * @param   JTable  $joinGroupTable  Joined group table
	 *
	 * @return  bool	int  Insert id
	 */

	public function storeRow($data, $rowId, $isJoin = false, $joinGroupTable = null)
	{
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$origRowId = $rowId;

		// Don't save a record if no data collected
		if ($isJoin && empty($data))
		{
			return;
		}

		$input = $app->input;
		$fabrikDb = $this->getDb();
		$table = $this->getTable();
		$formModel = $this->getFormModel();

		if ($isJoin)
		{
			$this->getFormGroupElementData();
		}

		$oRecord = new stdClass;
		$aBindData = array();
		$noRepeatFields = array();
		$c = 0;
		$groups = $formModel->getGroupsHiarachy();

		foreach ($groups as $groupModel)
		{
			$group = $groupModel->getGroup();
			/*
			 * $$$rob this following if statement avoids this scenario from happening:
			* you have a form with joins to two other tables
			* each joined group has a field called 'password'
			* first group's password is set to password plugin, second to field
			* on update if no password entered for first field data should not be updated as recordInDatabase() return false
			* however, as we were iterating over all groups, the 2nd password field's data is used instead!
			* this if statement ensures we only look at the correct group
			*/
			if ($isJoin == false || $group->id == $joinGroupTable->id)
			{
				if (($isJoin && $groupModel->isJoin()) || (!$isJoin && !$groupModel->isJoin()))
				{
					$elementModels = $groupModel->getPublishedElements();

					/*
					 * If the group is un-editable - then the form won't contain the group data, thus we don't want to add blank data into $oRecord
					 * @see http://fabrikar.com/forums/index.php?threads/changing-access-level-for-a-group-corrupts-data.34067/
					 */
					if (!$groupModel->canView())
					{
						continue;
					}

					foreach ($elementModels as $elementModel)
					{
						$element = $elementModel->getElement();
						$key = $element->name;
						$fullkey = $elementModel->getFullName(true, false);

						// For radio buttons and dropdowns otherwise nothing is stored for them??
						$postkey = array_key_exists($key . '_raw', $data) ? $key . '_raw' : $key;

						if ($elementModel->recordInDatabase($data))
						{
							if (array_key_exists($key, $data) && !in_array($key, $noRepeatFields))
							{
								$noRepeatFields[] = $key;
								$lastKey = $key;
								$val = $elementModel->storeDatabaseFormat($data[$postkey], $data);
								$elementModel->updateRowId($rowId);

								if (array_key_exists('fabrik_copy_from_table', $data))
								{
									$val = $elementModel->onCopyRow($val);
								}

								if (array_key_exists('Copy', $data))
								{
									$val = $elementModel->onSaveAsCopy($val);
								}

								// Test for backslashed quotes
								if (get_magic_quotes_gpc())
								{
									if (!$elementModel->isUpload())
									{
										$val = stripslashes($val);
									}
								}

								if ($elementModel->dataIsNull($data, $val))
								{
									$val = null;
								}

								$oRecord->$key = $val;
								$aBindData[$key] = $val;

								if ($elementModel->isJoin() && $isJoin && array_key_exists('params', $data))
								{
									// Add in params object set by element plugin - eg fileupload element rotation/scale
									$oRecord->params = JArrayHelper::getValue($data, 'params');
									$aBindData[$key] = $oRecord->params;
								}

								$c++;
							}
						}
					}
				}
			}
		}

		$primaryKey = FabrikString::shortColName($this->getTable()->db_primary_key);

		if ($rowId != '' && $c == 1 && $lastKey == $primaryKey)
		{
			return;
		}
		/*
		 * $$$ rob - correct rowid is now inserted into the form's rowid hidden field
		* even when useing usekey and -1, we just need to check if we are adding a new record and if so set rowid to 0
		*/
		if ($input->get('usekey_newrecord', false))
		{
			$rowId = 0;
			$origRowId = 0;
		}

		$primaryKey = str_replace("`", "", $primaryKey);

		// $$$ hugh - if we do this, CSV importing can't maintain existing keys
		if (!$this->importingCSV)
		{
			// If its a repeat group which is also the primary group $primaryKey was not set.
			if ($primaryKey)
			{
				if (isset($oRecord->$primaryKey) && is_numeric($oRecord->$primaryKey))
				{
					$oRecord->$primaryKey = $rowId;
				}
			}
		}

		if ($origRowId == '')
		{
			// $$$ rob added test for auto_inc as sugarid key is set from storeDatabaseFormat() and needs to be maintained
			// $$$ rob don't do this when importing via CSV as we want to maintain existing keys (hence check on task var
			if (($primaryKey !== '' && $this->getTable()->auto_inc == true) && $input->get('task') !== 'doImport')
			{
				unset($oRecord->$primaryKey);
			}

			$ok = $this->insertObject($table->db_table_name, $oRecord, $primaryKey, false);
		}
		else
		{
			$ok = $this->updateObject($table->db_table_name, $oRecord, $primaryKey, true);
		}

		$this->_tmpSQL = $fabrikDb->getQuery();

		if (!$ok)
		{
			$q = JDEBUG ? $fabrikDb->getQuery() : '';
			throw new ErrorException('Store row failed: ' . $q . "<br>" . $fabrikDb->getErrorMsg(), 500);
		}
		else
		{
			// Clean the cache.
			JFactory::getCache('com_' . $package)->clean();

			// $$$ rob new as if you update a record the insertid() returns 0
			$this->lastInsertId = ($rowId == '') ? $fabrikDb->insertid() : $rowId;

			return $this->lastInsertId;
		}
	}

	/**
	 * hack! copied from mysqli db driver to enable AES_ENCRYPT calls
	 *
	 * @param   string  $table        table name
	 * @param   object  &$object      update object
	 * @param   string  $keyName      name of pk field
	 * @param   bool    $updateNulls  update null values
	 *
	 * @return  mixed  query result
	 */

	public function updateObject($table, &$object, $keyName, $updateNulls = true)
	{
		$db = $this->getDb();
		$secret = JFactory::getConfig()->get('secret');
		$fmtsql = 'UPDATE ' . $db->quoteName($table) . ' SET %s WHERE %s';
		$tmp = array();

		foreach (get_object_vars($object) as $k => $v)
		{
			if (is_array($v) or is_object($v) or $k[0] == '_')
			{
				// Internal or NA field
				continue;
			}

			if ($k == $keyName)
			{
				// PK not to be updated
				$where = $keyName . '=' . $db->quote($v);
				continue;
			}

			if ($v === null)
			{
				if ($updateNulls)
				{
					$val = 'NULL';
				}
				else
				{
					continue;
				}
			}
			else
			{
				$val = $db->quote($v);
			}

			if (in_array($k, $this->encrypt))
			{
				$val = "AES_ENCRYPT($val, '$secret')";
			}

			$tmp[] = $db->quoteName($k) . '=' . $val;
		}

		$db->setQuery(sprintf($fmtsql, implode(",", $tmp), $where));

		if (!$db->execute())
		{
			throw new Exception($db->getErrorMsg());

			return false;
		}

		return true;
	}

	/**
	 * Hack! copied from mysqli db driver to enable AES_ENCRYPT calls
	 * Inserts a row into a table based on an objects properties
	 *
	 * @param   string  $table    The name of the table
	 * @param   object  &$object  An object whose properties match table fields
	 * @param   string  $keyName  The name of the primary key. If provided the object property is updated.
	 *
	 * @return  bool
	 */

	public function insertObject($table, &$object, $keyName = null)
	{
		$db = $this->getDb();
		$secret = JFactory::getConfig()->get('secret');
		$fmtsql = 'INSERT INTO ' . $db->quoteName($table) . ' ( %s ) VALUES ( %s ) ';
		$fields = array();
		$values = array();

		foreach (get_object_vars($object) as $k => $v)
		{
			if (is_array($v) or is_object($v) or $v === null)
			{
				continue;
			}

			if ($k[0] == '_')
			{
				// Internal field
				continue;
			}

			$fields[] = $db->quoteName($k);
			$val = $db->quote($v);

			if (in_array($k, $this->encrypt))
			{
				$val = "AES_ENCRYPT($val, '$secret')";
			}

			$values[] = $val;
		}

		$db->setQuery(sprintf($fmtsql, implode(",", $fields), implode(",", $values)));

		if (!$db->execute())
		{
			throw new Exception($db->getErrorMsg());

			return false;
		}

		$id = $db->insertid();

		if ($keyName && $id)
		{
			$object->$keyName = $id;
		}

		return true;
	}

	/**
	 * If an element is set to readonly, and has a default value selected then insert this
	 * data into the array that is to be bound to the table record
	 *
	 * @param   array   &$data           List data
	 * @param   object  &$oRecord        To bind to table row
	 * @param   int     $isJoin          Is record join record
	 * @param   int     $rowid           Row id
	 * @param   JTable  $joinGroupTable  Join group table
	 *
	 * @since	1.0.6
	 *
	 * @deprecated  since 3.0.7 - we should be using formmodel addEncrytedVarsToArray() only
	 *
	 * @return  void
	 */

	protected function addDefaultDataFromRO(&$data, &$oRecord, $isJoin, $rowid, $joinGroupTable)
	{
		// $$$ rob since 1.0.6 : 10 June 08
		// Get the current record - not that which was posted
		$formModel = $this->getFormModel();
		$table = $this->getTable();
		$app = JFactory::getApplication();
		$input = $app->input;

		if (is_null($this->origData))
		{
			/* $$$ hugh FIXME - doesn't work for rowid=-1 / usekey submissions,
			 * ends up querying "WHERE foo.userid = '<rowid>'" instead of <userid>
			* OK for now, as we should catch RO data from the encrypted vars check
			* later in this method.
			*/
			if (empty($rowid))
			{
				$this->origData = $origdata = array();
			}
			else
			{
				$sql = $formModel->buildQuery();
				$db = $this->getDb();
				$db->setQuery($sql);
				$origdata = $db->loadObject();
				$origdata = JArrayHelper::fromObject($origdata);
				$origdata = is_array($origdata) ? $origdata : array();
				$this->origData = $origdata;
			}
		}
		else
		{
			$origdata = $this->origData;
		}

		$form = $formModel->getForm();
		$groups = $formModel->getGroupsHiarachy();

		/* $$$ hugh - seems like there's no point in doing this chunk if there is no
		 $origdata to work with?  Not sure if there's ever a valid reason for doing so,
		but it certainly breaks things like onCopyRow(), where (for instance) user
		elements will get reset to 0 by this code.
		*/
		$repeatGroupCounts = $input->get('fabrik_repeat_group', array(), 'array');

		if (!empty($origdata))
		{
			$gcounter = 0;

			foreach ($groups as $groupModel)
			{
				if (($isJoin && $groupModel->isJoin()) || (!$isJoin && !$groupModel->isJoin()))
				{
					$elementModels = $groupModel->getPublishedElements();

					foreach ($elementModels as $elementModel)
					{
						// $$$ rob 25/02/2011 unviewable elements are now also being encrypted
						// if (!$elementModel->canUse() && $elementModel->canView()) {
						if (!$elementModel->canUse())
						{
							$element = $elementModel->getElement();
							$fullkey = $elementModel->getFullName(true, false);

							// $$$ rob 24/01/2012 if a previous joined data set had a ro element then if we werent checkign that group is the
							// same as the join group then the insert failed as data from other joins added into the current join
							if ($isJoin && ($groupModel->getId() != $joinGroupTable->id))
							{
								continue;
							}

							$key = $element->name;

							// $$$ hugh - allow submission plugins to override RO data
							// TODO - test this for joined data
							if ($formModel->updatedByPlugin($fullkey))
							{
								continue;
							}
							// Force a reload of the default value with $origdata
							unset($elementModel->defaults);
							$default = array();
							$repeatGroupCount = JArrayHelper::getValue($repeatGroupCounts, $groupModel->getGroup()->id);

							for ($repeatCount = 0; $repeatCount < $repeatGroupCount; $repeatCount++)
							{
								$def = $elementModel->getValue($origdata, $repeatCount);

								if (is_array($def))
								{
									// Radio buttons getValue() returns an array already so don't array the array.
									$default = $def;
								}
								else
								{
									$default[] = $def;
								}
							}

							$default = count($default) == 1 ? $default[0] : json_encode($default);
							$data[$key] = $default;
							$oRecord->$key = $default;
						}
					}
				}

				$gcounter++;
			}
		}

		$copy = $input->getBool('Copy');

		// Check crypted querystring vars (encrypted in form/view.html.php ) _cryptQueryString
		if (array_key_exists('fabrik_vars', $_REQUEST) && array_key_exists('querystring', $_REQUEST['fabrik_vars']))
		{
			$crypt = FabrikWorker::getCrypt();

			foreach ($_REQUEST['fabrik_vars']['querystring'] as $key => $encrypted)
			{
				// $$$ hugh - allow submission plugins to override RO data
				// TODO - test this for joined data
				if ($formModel->updatedByPlugin($key))
				{
					continue;
				}

				$key = FabrikString::shortColName($key);

				/* $$$ hugh - trying to fix issue where encrypted elements from a main group end up being added to
				 * a joined group's field list for the update/insert on the joined row(s).
				*/
				/*
				 * $$$ rob - commenting it out as this was stopping data that was not viewable or editable from being included
				* in $data. New test added inside foreach loop below
				**/
				/* if (!array_key_exists($key, $data))
				 {
				continue;
				} */
				foreach ($groups as $groupModel)
				{
					// New test to replace if (!array_key_exists($key, $data))
					// $$$ hugh - this stops elements from joined groups being added to main row, but see 'else'
					if ($isJoin)
					{
						if ($groupModel->getGroup()->id != $joinGroupTable->id)
						{
							continue;
						}
					}
					else
					{
						// $$$ hugh - need test here if not $isJoin, to stop keys from joined groups being added to main row!
						if ($groupModel->isJoin())
						{
							continue;
						}
					}

					$elementModels = $groupModel->getPublishedElements();

					foreach ($elementModels as $elementModel)
					{
						$element = $elementModel->getElement();

						/*
						 * $$$ hugh - I have a feeling this test is a Bad Thing <tm> as it is using short keys,
						 * so if two joined groups share the same element name(s) ...
						 */
						if ($element->name == $key)
						{
							// Don't overwrite if something has been entered

							// $$$ rob 25/02/2011 unviewable elements are now also being encrypted
							// if (!$elementModel->canUse() && $elementModel->canView()) {
							if (!$elementModel->canUse())
							{
								// Repeat groups
								$default = array();
								$repeatGroupCount = JArrayHelper::getValue($repeatGroupCounts, $groupModel->getGroup()->id);

								for ($repeatCount = 0; $repeatCount < $repeatGroupCount; $repeatCount++)
								{
									$enc = JArrayHelper::getValue($encrypted, $repeatCount);

									if (is_array($enc))
									{
										$v = array();

										foreach ($enc as $e)
										{
											$e = urldecode($e);
											$v[] = empty($e) ? '' : $crypt->decrypt($e);
										}

										$v = json_encode($v);
									}
									else
									{
										$enc = urldecode($enc);
										$v = !empty($enc) ? $crypt->decrypt($enc) : '';
									}
								}

								/* $$$ hugh - also gets called in storeRow(), not sure if we really need to
								 * call it here?  And if we do, then we should probably be calling onStoreRow
								* as well, if $data['fabrik_copy_from_table'] is set?  Can't remember why,
								* but we differentiate between the two, with onCopyRow being when a row is copied
								* using the list plugin, and onSaveAsCopy when the form plugin is used.
								*/
								if ($copy)
								{
									$v = $elementModel->onSaveAsCopy($v);
								}

								$data[$key] = $v;
								$oRecord->$key = $v;
							}

							break 2;
						}
					}
				}
			}
		}
	}

	/**
	 * Called when the form is submitted to perform calculations
	 *
	 * @return  void
	 */

	public function doCalculations()
	{
		$cache = FabrikWorker::getCache($this);
		$cache->call(array(get_class($this), 'cacheDoCalculations'), $this->getId());
	}

	/**
	 * Cache do calculations
	 *
	 * @param   int  $listId  List id
	 *
	 * @return  void
	 */

	public static function cacheDoCalculations($listId)
	{
		$listModel = JModelLegacy::getInstance('List', 'FabrikFEModel');
		$listModel->setId($listId);
		$db = FabrikWorker::getDbo();
		$formModel = $listModel->getFormModel();
		$groups = $formModel->getGroupsHiarachy();

		foreach ($groups as $groupModel)
		{
			$elementModels = $groupModel->getPublishedElements();

			foreach ($elementModels as $elementModel)
			{
				$element = $elementModel->getElement();
				$params = $elementModel->getParams();
				$update = false;

				if ($params->get('sum_on', 0) == 1)
				{
					$aSumCals = $elementModel->sum($listModel);
					$params->set('sum_value_serialized', serialize($aSumCals[1]));
					$params->set('sum_value', $aSumCals[0]);
					$update = true;
				}

				if ($params->get('avg_on', 0) == 1)
				{
					$aAvgCals = $elementModel->avg($listModel);
					$params->set('avg_value_serialized', serialize($aAvgCals[1]));
					$params->set('avg_value', $aAvgCals[0]);
					$update = true;
				}

				if ($params->get('median_on', 0) == 1)
				{
					$medians = $elementModel->median($listModel);
					$params->set('median_value_serialized', serialize($medians[1]));
					$params->set('median_value', $medians[0]);
					$update = true;
				}

				if ($params->get('count_on', 0) == 1)
				{
					$aCountCals = $elementModel->count($listModel);
					$params->set('count_value_serialized', serialize($aCountCals[1]));
					$params->set('count_value', $aCountCals[0]);
					$update = true;
				}

				if ($params->get('custom_calc_on', 0) == 1)
				{
					$aCustomCalcCals = $elementModel->custom_calc($listModel);
					$params->set('custom_calc_value_serialized', serialize($aCustomCalcCals[1]));
					$params->set('custom_calc_value', $aCustomCalcCals[0]);
					$update = true;
				}

				if ($update)
				{
					$elementModel->storeAttribs();
				}
			}
		}
	}

	/**
	 * Check to see if prefilter should be applied
	 *
	 * @param   int  $gid  view access level to check against
	 *
	 * @return  bool  Must apply filter
	 */

	protected function mustApplyFilter($gid)
	{
		return in_array($gid, JFactory::getUser()->getAuthorisedViewLevels());
	}

	/**
	 * Set the connection id - used when creating a new table
	 *
	 * @param   int  $id  connection id
	 *
	 * @return  void
	 */

	public function setConnectionId($id)
	{
		$this->getTable()->connection_id = $id;
	}

	/**
	 * Get group by (can be set via qs group_by var)
	 *
	 * @return  string
	 */

	public function getGroupBy()
	{
		$elementModel = $this->getGroupByElement();

		if (!$elementModel)
		{
			return '';
		}

		return $elementModel->getFullName(true, false);
	}

	/**
	 * Get the element ids for list odering
	 *
	 * @since  3.0.7
	 *
	 * @return  array  element ids
	 */

	public function getOrderBys()
	{
		$item = $this->getTable();
		$orderBys = FabrikWorker::JSONtoData($item->order_by, true);
		$formModel = $this->getFormModel();

		foreach ($orderBys as &$orderBy)
		{
			$elementModel = $formModel->getElement($orderBy, true);
			$orderBy = $elementModel ? $elementModel->getId() : '';
		}

		return $orderBys;
	}

	/**
	 * Test if the main J user can create mySQL tables
	 *
	 * @return  bool
	 */

	public function canCreateDbTable()
	{
		return true;
	}

	/**
	 * Make id element
	 *
	 * @param   int  $groupId  element group id
	 *
	 * @since Fabrik 3.0
	 *
	 * @return  void
	 */

	public function makeIdElement($groupId)
	{
		$pluginMananger = FabrikWorker::getPluginManager();
		$element = $pluginMananger->getPlugIn('internalid', 'element');
		$item = $element->getDefaultProperties();
		$item->name = $item->label = 'id';
		$item->group_id = $groupId;

		if (!$item->store())
		{
			JError::raiseWarning(500, $item->getError());

			return false;
		}

		return true;
	}

	/**
	 * Make foreign key element
	 *
	 * @param   int  $groupId  element group id
	 *
	 * @since   Fabrik 3.0
	 *
	 * @return void
	 */

	public function makeFkElement($groupId)
	{
		$pluginMananger = FabrikWorker::getPluginManager();
		$element = $pluginMananger->getPlugIn('field', 'element');
		$item = $element->getDefaultProperties();
		$item->name = $item->label = 'parent_id';
		$item->hidden = 1;
		$item->group_id = $groupId;

		if (!$item->store())
		{
			JError::raiseWarning(500, $item->getError());

			return false;
		}

		return true;
	}

	/**
	 * Updates the table record to point to the newly created form
	 *
	 * @param   int  $formId  form id
	 *
	 * @deprecated - not used
	 *
	 * @return  mixed  null/error
	 */

	protected function _updateFormId($formId)
	{
		$item = $this->getTable();
		$item->form_id = $formId;

		if (!$item->store())
		{
			return JError::raiseWarning(500, $item->getError());
		}
	}

	/**
	 * Get the tables primary key and if the primary key is auto increment
	 *
	 * @param   string  $table  optional table name (used when getting pk to joined tables
	 *
	 * @return  mixed	if ok returns array(key, extra, type, name) otherwise
	 */

	public function getPrimaryKeyAndExtra($table = null)
	{
		$origColNames = $this->getDBFields($table);
		$keys = array();
		$origColNamesByName = array();

		if (is_array($origColNames))
		{
			foreach ($origColNames as $origColName)
			{
				$colName = $origColName->Field;
				$key = $origColName->Key;
				$extra = $origColName->Extra;
				$type = $origColName->Type;

				if ($key == "PRI")
				{
					$keys[] = array("key" => $key, "extra" => $extra, "type" => $type, "colname" => $colName);
				}
				else
				{
					// $$$ hugh - if we never find a PRI, it may be a view, and we'll need this info in the Hail Mary.
					$origColnamesByName[$colName] = $origColName;
				}
			}
		}

		if (empty($keys))
		{
			// $$$ hugh - might be a view, so Hail Mary attempt to find it in our lists
			// $$$ So ... see if we know about it, and if so, fake out the PK details
			$db = FabrikWorker::getDbo(true);
			$query = $db->getQuery(true);
			$query->select('db_primary_key')->from('#__{package}_lists')->where('db_table_name = ' . $db->quote($table));
			$db->setQuery($query);
			$join_pk = $db->loadResult();

			if (!empty($join_pk))
			{
				$shortColName = FabrikString::shortColName($join_pk);
				$key = $origColName->Key;
				$extra = $origColName->Extra;
				$type = $origColName->Type;
				$keys[] = array('colname' => $shortColName, 'type' => $type, 'extra' => $extra, 'key' => $key);
			}
		}

		return empty($keys) ? false : $keys;
	}

	/**
	 * Run the prefilter sql and replace any placeholders in the subsequent prefilter
	 *
	 * @param   mixed  $selValue  string/array prefilter value
	 *
	 * @return  mixed  string/array prefilter value
	 */

	protected function prefilterParse($selValue)
	{
		$isstring = false;

		if (is_string($selValue))
		{
			$isstring = true;
			$selValue = array($selValue);
		}

		$preSQL = htmlspecialchars_decode($this->getParams()->get('prefilter_query'), ENT_QUOTES);

		if (trim($preSQL) != '')
		{
			$db = FabrikWorker::getDbo();
			$w = new FabrikWorker;
			$w->replaceRequest($preSQL);
			$preSQL = $w->parseMessageForPlaceHolder($preSQL);
			$db->setQuery($preSQL);
			$q = $db->loadObjectList();

			if (!$q)
			{
				// Try the table's connection db for the query
				$thisDb = $this->getDb();
				$thisDb->setQuery($preSQL);
				$q = $thisDb->loadObjectList();
			}

			if (!empty($q))
			{
				$q = $q[0];
			}
		}

		if (isset($q))
		{
			foreach ($q as $key => $val)
			{
				if (substr($key, 0, 1) != '_')
				{
					$found = false;

					for ($i = 0; $i < count($selValue); $i++)
					{
						if (strstr($selValue[$i], '{$q-&gt;' . $key))
						{
							$found = true;
							$pattern = '{$q-&gt;' . $key . "}";
						}

						if (strstr($selValue[$i], '{$q->' . $key))
						{
							$found = true;
							$pattern = '{$q->' . $key . "}";
						}

						if ($found)
						{
							$selValue[$i] = str_replace($pattern, $val, $selValue[$i]);
						}
					}
				}
			}
		}
		else
		{
			/* Parse for default values only
			 * $$$ hugh - this pattern is being greedy, so for example ...
			 * foo {$my->id} bar {$my->id} gaprly
			 * ... matches everyting from first to last brace, like ...
			 * {$my->id} bar {$my->id}
			 *$pattern = "/({[^}]+}).*}?/s";
			 */
			$pattern = "/({[^}]+})/";

			for ($i = 0; $i < count($selValue); $i++)
			{
				$ok = preg_match($pattern, $selValue[$i], $matches);

				foreach ($matches as $match)
				{
					$matchx = JString::substr($match, 1, JString::strlen($match) - 2);

					// A default option was set so lets use that
					if (strstr($matchx, '|'))
					{
						$bits = explode('|', $matchx);
						$selValue[$i] = str_replace($match, $bits[1], $selValue[$i]);
					}
				}
			}
		}

		$selValue = $isstring ? $selValue[0] : $selValue;
		$user = JFactory::getUser();

		// Replace {authorisedViewLevels} with array of view levels the user can access
		if (is_array($selValue))
		{
			foreach ($selValue as &$v)
			{
				if (strstr($v, '{authorisedViewLevels}'))
				{
					$v = $user->getAuthorisedViewLevels();
				}
			}
		}
		else
		{
			if (strstr($selValue, '{authorisedViewLevels}'))
			{
				$selValue = $user->getAuthorisedViewLevels();
			}
		}

		return $selValue;
	}

	/**
	 * Get the lists db table's indexes
	 *
	 * @return array  list indexes
	 */

	protected function getIndexes()
	{
		if (!isset($this->indexes))
		{
			$db = $this->getDb();
			$db->setQuery('SHOW INDEXES FROM ' . $this->getTable()->db_table_name);
			$this->indexes = $db->loadObjectList();
		}

		return $this->indexes;
	}

	/**
	 * Add an index to the table
	 *
	 * @param   string  $field   field name
	 * @param   string  $prefix  index name prefix (allows you to differentiate between indexes created in
	 * different parts of fabrik)
	 * @param   string  $type    index type
	 * @param   int     $size    index length
	 *
	 * @return void
	 */

	public function addIndex($field, $prefix = '', $type = 'INDEX', $size = '')
	{
		$indexes = $this->getIndexes();

		if (is_numeric($field))
		{
			$el = $this->getFormModel()->getElement($field, true);
			$field = $el->getFullName(true, false);
		}

		/* $$$ hugh - @TODO $field is in 'table.element' format but $indexes
		 * has Column_name as just 'element' ... so we're always rebuilding indexes!
		* I'm in the middle of fixing something else, must come back and fix this!!
		* OK, moved these two lines from below to here
		*/
		$field = str_replace('_raw', '', $field);

		// $$$ rob 29/03/2011 ensure its in tablename___elementname format
		$field = str_replace('.', '___', $field);

		// $$$ rob 28/02/2011 if index in joined table we need to use that the make the key on
		$table = !strstr($field, '___') ? $this->getTable()->db_table_name : array_shift(explode('___', $field));
		$field = FabrikString::shortColName($field);
		FArrayHelper::filter($indexes, 'Column_name', $field);

		if (!empty($indexes))
		{
			// An index already exists on that column name no need to add
			return;
		}

		$db = $this->getDb();

		if ($field == '')
		{
			return;
		}

		if ($size != '')
		{
			$size = '( ' . $size . ' )';
		}

		$this->dropIndex($field, $prefix, $type, $table);
		$query = ' ALTER TABLE ' . $db->quoteName($table) . ' ADD INDEX ' . $db->quoteName("fb_{$prefix}_{$field}_{$type}") . ' ('
				. $db->quoteName($field) . ' ' . $size . ')';
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			// Try to suppress error
			$this->setError($e->getMessage());
		}
	}

	/**
	 * Drop an index
	 *
	 * @param   string  $field   field name
	 * @param   stirng  $prefix  index name prefix (allows you to differentiate between indexes created in
	 * different parts of fabrik)
	 * @param   string  $type    table name @since 29/03/2011
	 * @param   string  $table   db table name
	 *
	 * @return  string  index type
	 */

	public function dropIndex($field, $prefix = '', $type = 'INDEX', $table = '')
	{
		$db = $this->getDb();
		$table = $table == '' ? $this->getTable()->db_table_name : $table;
		$field = FabrikString::shortColName($field);

		if ($field == '')
		{
			return;
		}

		$db->setQuery("SHOW INDEX FROM " . $db->quoteName($table));
		$dbIndexes = $db->loadObjectList();

		if (is_array($dbIndexes))
		{
			foreach ($dbIndexes as $index)
			{
				if ($index->Key_name == "fb_{$prefix}_{$field}_{$type}")
				{
					$db->setQuery("ALTER TABLE " . $db->quoteName($table) . " DROP INDEX " . $db->quoteName("fb_{$prefix}_{$field}_{$type}"));

					try
					{
						$db->execute();
					}
					catch (Exception $e)
					{
						$this->setError($e->getMessage());
					}
					break;
				}
			}
		}
	}

	/**
	 * Drop all indexes for a give element name
	 * required when encrypting text fileds whcih have a key on them , as blobs cant have keys
	 *
	 * @param   string  $field  field name to drop
	 * @param   string  $table  table to drop from
	 *
	 * @return  void
	 */

	public function dropColumnNameIndex($field, $table = '')
	{
		$db = $this->getDb();
		$table = $table == '' ? $this->getTable()->db_table_name : $table;
		$field = FabrikString::shortColName($field);

		if ($field == '')
		{
			return;
		}

		$db->setQuery("SHOW INDEX FROM " . $db->quoteName($table) . ' WHERE Column_name = ' . $db->quote($field));
		$dbIndexes = $db->loadObjectList();

		foreach ($dbIndexes as $index)
		{
			$db->setQuery(" ALTER TABLE " . $db->quoteName($table) . " DROP INDEX " . $db->quoteName($index->Key_name));
			$db->execute();
		}
	}

	/**
	 * Delete joined records when deleting the main row
	 *
	 * @param   string  $val  quoted primary key values from the main table's rows that are to be deleted
	 *
	 * @return  void
	 */

	protected function deleteJoinedRows($val)
	{
		$db = $this->getDb();
		$params = $this->getParams();

		if ($params->get('delete-joined-rows', false))
		{
			$joins = $this->getJoins();

			for ($i = 0; $i < count($joins); $i++)
			{
				$join = $joins[$i];

				if ((int) $join->list_id !== 0)
				{
					$sql = "DELETE FROM " . $db->quoteName($join->table_join) . " WHERE " . $db->quoteName($join->table_join_key) . " IN (" . $val
					. ")";
					$db->setQuery($sql);
					$db->execute();
				}
			}
		}
	}

	/**
	 * Deletes records from a table
	 *
	 * @param   mixed   &$ids  Key values to delete (string or array)
	 * @param   string  $key   Key to use (leave empty to default to the list's key)
	 *
	 * @throws  Exception  If no key found or main delete row fails (perhaps due to INNODB foreign constraints)
	 *
	 * @return  bool
	 */

	public function deleteRows(&$ids, $key = '')
	{
		if (!is_array($ids))
		{
			$ids = array($ids);
		}

		$val = $ids;
		$app = JFactory::getApplication();
		$table = $this->getTable();
		$db = $this->getDb();
		$params = $this->getParams();

		if ($key == '')
		{
			$key = $table->db_primary_key;

			if ($key == '')
			{
				throw new Exception(JText::_("COM_FABRIK_NO_KEY_FOUND_FOR_THIS_TABLE"));
			}
		}

		$c = count($val);

		foreach ($val as &$v)
		{
			$v = $db->quote($v);
		}

		$val = implode(",", $val);

		// $$$ rob - if we are not deleting joined rows then onloy load in the first row
		// otherwise load in all rows so we can apply onDeleteRows() to all the data
		if ($this->getParams()->get('delete-joined-rows', false) == false)
		{
			$nav = $this->getPagination($c, 0, $c);
		}

		$this->setPluginQueryWhere('list.deleteRows', $key . ' IN (' . $val . ')');

		/* $$$ hugh - need to clear cached data, 'cos we called getTotalRecords from the controller, which now
		 * calls getData(), and will have cached all rows on this page, not just the ones being deleted, which means
		* things like form and element onDelete plugins will get handed a whole page of rows, not just the ones
		* selected for delete!  Ooops.
		*/
		unset($this->data);
		$rows = $this->getData();

		/* $$$ hugh - we need to check delete perms, see:
		 * http://fabrikar.com/forums/showthread.php?p=102670#post102670
		* Short version, if user has access for a table plugin, they get a checkbox on the row, but may not have
		* delete access on that row.
		*/
		$removed_id = false;

		foreach ($rows as &$group)
		{
			foreach ($group as $group_key => $row)
			{
				if (!$this->canDelete($row))
				{
					// Can't delete, so remove row data from $rows, and the id from $ids, and queue a message
					foreach ($ids as $id_key => $id)
					{
						if ($id == $row->__pk_val)
						{
							unset($ids[$id_key]);
							continue;
						}
					}

					unset($group[$group_key]);
					$app->enqueueMessage('NO PERMISSION TO DELETE ROW');
					$removed_id = true;
				}
			}
		}

		// See if we have any rows left to delete after checking perms
		if (empty($ids))
		{
			return;
		}
		// Redo $val list of ids in case we zapped any on canDelete check
		if ($removed_id)
		{
			$val = $ids;
			$c = count($val);

			foreach ($val as &$v)
			{
				$v = $db->quote($v);
			}

			$val = implode(",", $val);
		}

		$this->rowsToDelete = $rows;
		$groupModels = $this->getFormGroupElementData();

		foreach ($groupModels as $groupModel)
		{
			$elementModels = $groupModel->getPublishedElements();

			foreach ($elementModels as $elementModel)
			{
				$elementModel->onDeleteRows($rows);
			}
		}

		$pluginManager = FabrikWorker::getPluginManager();

		/* $$$ hugh - added onDeleteRowsForm plugin (needed it so fabrikjuser form plugin can delete users)
		 * NOTE - had to call it onDeleteRowsForm rather than onDeleteRows, otherwise runPlugins() automagically
		* runs the element onDeleteRows(), which we already do above.  And with the code as-is, that won't work
		* from runPlugins() 'cos it won't pass it the $rows it needs.  So i have to sidestep the issue by using
		* a different trigger name.  Added a default onDeleteRowsForm() to plugin-form.php, and implemented
		* (and tested) user deletion in fabrikjuser.php using this trigger.  All seems to work.  7/28/2009
		*/

		if (in_array(false, $pluginManager->runPlugins('onDeleteRowsForm', $this->getFormModel(), 'form', $rows)))
		{
			return false;
		}

		$pluginManager->getPlugInGroup('list');

		if (in_array(false, $pluginManager->runPlugins('onDeleteRows', $this, 'list')))
		{
			return false;
		}

		$query = $db->getQuery(true);
		$query->delete($db->quoteName($table->db_table_name))->where($key . ' IN (' . $val . ')');
		$db->setQuery($query);

		if (!$db->execute())
		{
			throw new Exception($db->getErrorMsg());
		}

		$this->deleteJoinedRows($val);

		// Clean the cache.
		$cache = JFactory::getCache($app->input->get('option'));
		$cache->clean();

		return true;
	}

	/**
	 * Remove all records from the table
	 *
	 * @return  mixed
	 */

	public function dropData()
	{
		$db = $this->getDb();
		$query = $db->getQuery(true);
		$table = $this->getTable();
		$query->delete($db->quoteName($table->db_table_name));
		$db->setQuery($query);

		if (!$db->execute())
		{
			return JError::raiseWarning(JText::_($db->getErrorMsg()));
		}
		// Clean the cache.
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		JFactory::getCache('com_' . $package)->clean();

		return true;
	}

	/**
	 * Drop the table containing the fabriktables data and drop any internal joins db tables.
	 *
	 * @return  mixed
	 */

	public function drop()
	{
		$db = $this->getDb();
		$item = $this->getTable();
		$db->dropTable($item->db_table_name);

		// Remove any groups that were set to be repeating and hence were storing in their own db table.
		$joinModels = $this->getInternalRepeatJoins();

		foreach ($joinModels as $joinModel)
		{
			if ($joinModel->getJoin()->table_join !== '')
			{
				$db->dropTable($joinModel->getJoin()->table_join);
			}
		}

		return true;
	}

	/**
	 * Get an array of join models relating to the groups which were set to be repeating and thus thier data
	 * stored in a separate db table
	 *
	 * @return  array  join models.
	 */

	public function getInternalRepeatJoins()
	{
		$return = array();
		$groupModels = $this->getFormGroupElementData();

		// Remove any groups that were set to be repeating and hence were storing in their own db table.
		foreach ($groupModels as $groupModel)
		{
			if ($groupModel->isJoin())
			{
				$joinModel = $groupModel->getJoinModel();
				$join = $joinModel->getJoin();
				$joinParams = is_string($join->params) ? json_decode($join->params) : $join->params;

				if (isset($joinParams->type) && $joinParams->type === 'group')
				{
					$return[] = $joinModel;
				}
			}
		}

		return $return;
	}

	/**
	 * Truncate the main db table and any internal joined groups
	 *
	 * @return  void
	 */

	public function truncate()
	{
		$db = $this->getDb();
		$item = $this->getTable();

		// Remove any groups that were set to be repeating and hence were storing in their own db table.
		$joinModels = $this->getInternalRepeatJoins();

		foreach ($joinModels as $joinModel)
		{
			$db->setQuery("TRUNCATE " . $db->quoteName($joinModel->getJoin()->table_join));
			$db->execute();
		}

		$db->setQuery("TRUNCATE " . $db->quoteName($item->db_table_name));
		$db->execute();

		// 3.0 clear filters (resets limitstart so that subsequently added records are shown)
		$this->getFilterModel()->clearFilters();

		// Clean the cache.
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		JFactory::getCache('com_' . $package)->clean();
	}

	/**
	 * Test if a field already exists in the database
	 *
	 * @param   string  $field   field to test
	 * @param   array   $ignore  id's to ignore
	 *
	 * @return  bool
	 */

	public function fieldExists($field, $ignore = array())
	{
		$field = JString::strtolower($field);
		$groupModels = $this->getFormGroupElementData();

		foreach ($groupModels as $groupModel)
		{
			if (!$groupModel->isJoin())
			{
				// Don't check groups that aren't in this table
				$elementModels = $groupModel->getMyElements();

				foreach ($elementModels as $elementModel)
				{
					$element = $elementModel->getElement();
					$n = JString::strtolower($element->name);

					if (JString::strtolower($element->name) == $field && !in_array($element->id, $ignore))
					{
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Build a dropdown list of fileds
	 *
	 * @param   int     $cnnId           Connection id to use
	 * @param   string  $tbl             Table to load fields for
	 * @param   string  $incSelect       Show "please select" top option
	 * @param   bool    $incTableName    Append field name values with table name
	 * @param   string  $selectListName  Name of drop down
	 * @param   string  $selected        Selected option
	 * @param   string  $className       Class name
	 *
	 * @return  string	html to be added to DOM
	 */

	public function getFieldsDropDown($cnnId, $tbl, $incSelect, $incTableName = false, $selectListName = 'order_by', $selected = null,
		$className = "inputbox")
	{
		$this->setConnectionId($cnnId);
		$aFields = $this->getDBFields($tbl);
		$fieldNames = array();

		if ($incSelect != '')
		{
			$fieldNames[] = JHTML::_('select.option', '', $incSelect);
		}

		if (is_array($aFields))
		{
			foreach ($aFields as $oField)
			{
				if ($incTableName)
				{
					$fieldNames[] = JHTML::_('select.option', $tbl . '___' . $oField->Field, $oField->Field);
				}
				else
				{
					$fieldNames[] = JHTML::_('select.option', $oField->Field);
				}
			}
		}

		$opts = 'class="' . $className . '" size="1" ';
		$fieldDropDown = JHTML::_('select.genericlist', $fieldNames, $selectListName, $opts, 'value', 'text', $selected);

		return str_replace("\n", "", $fieldDropDown);
	}

	/**
	 * Create the RSS href link to go in the table template
	 *
	 * @return  string	RSS link
	 */

	public function getRSSFeedLink()
	{
		$app = JFactory::getApplication();
		$link = '';
		$package = $app->getUserState('com_fabrik.package', 'fabrik');

		if ($this->getParams()->get('rss') == '1')
		{
			// $$$ rob test fabriks own feed renderer
			$link = 'index.php?option=com_' . $package . '&view=list&listid=' . $this->getId() . "&format=fabrikfeed";

			if (!$app->isAdmin())
			{
				$link = JRoute::_($link);
			}
		}

		return $link;
	}

	/**
	 * Iterates through string to replace every
	 * {placeholder} with row data
	 * (added by hugh, does the same thing as parseMessageForPlaceHolder in parent
	 * class, but for rows instead of forms)
	 *
	 * @param   string  $msg         text to parse
	 * @param   array   &$row        of row data
	 * @param   bool    $addslashes  add slashes to the replaced data (default = false) set to true in fabrikcalc element
	 *
	 * @return  string  parsed message
	 */

	public function parseMessageForRowHolder($msg, &$row, $addslashes = false)
	{
		$this->aRow = $row;

		if (!strstr($msg, '{'))
		{
			return $msg;
		}

		$this->parseAddSlases = $addslashes;
		$msg = FabrikWorker::replaceWithUserData($msg);
		$msg = FabrikWorker::replaceWithGlobals($msg);
		$msg = preg_replace("/{}/", "", $msg);
		$this->rowIdentifierAdded = false;
		/* replace {element name} with form data */
		/* $$$ hugh - testing changing the regex so we don't blow away PHP structures!  Added the \s so
		 * we only match non-space chars in {}'s.  So unless you have some code like "if (blah) {foo;}", PHP
		* block level {}'s should remain unmolested.
		*/
		$msg = preg_replace_callback("/{[^}\s]+}/i", array($this, 'replaceWithRowData'), $msg);

		return $msg;
	}

	/**
	 * Called from parseMessageForRowHolder to iterate through string to replace
	 * {placeholder} with row data
	 *
	 * @param   array  $matches  found in parseMessageForRowHolder
	 *
	 * @return  string	posted data that corresponds with placeholder
	 */

	private function replaceWithRowData($matches)
	{
		$match = $matches[0];

		// $$$ felixkat - J! plugin closings, i.e  {/foo} were getting caught here.
		if (preg_match('[{/]', $match))
		{
			return $match;
		}

		/* strip the {} */
		$match = JString::substr($match, 1, JString::strlen($match) - 2);

		// $$$ hugh - in case any {$my->foo} or {$_SERVER->FOO} paterns are left over, avoid 'undefined index' warnings
		if (preg_match('#^\$#', $match))
		{
			return '';
		}

		$match = str_replace('.', '___', $match);

		// $$$ hugh - allow use of {$rowpk} or {rowpk} to mean the rowid of the row within a table
		if ($match == 'rowpk' || $match == '$rowpk' || $match == 'rowid')
		{
			$this->rowIdentifierAdded = true;
			$match = '__pk_val';
		}

		$match = preg_replace("/ /", "_", $match);

		if ($match == 'formid')
		{
			return $this->getFormModel()->getId();
		}

		$return = JArrayHelper::getValue($this->aRow, $match);

		if (is_array($return))
		{
			$this->parseAddSlases = true;
			$return = json_encode($return);
		}

		if ($this->parseAddSlases)
		{
			$return = htmlspecialchars($return, ENT_QUOTES, 'UTF-8');
		}

		return $return;
	}

	/**
	 * This is just way too confuins - view details link now always returns a view details link and not an edit link ?!!!
	 * get the link to view the records details
	 *
	 * @param   object  &$row  active list row
	 * @param   string  $view  3.0 depreciated
	 *
	 * @return  string	url of view details link
	 *
	 * @since  3.0
	 *
	 * @retun  string  link
	 */

	protected function viewDetailsLink(&$row, $view = null)
	{
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$Itemid = FabrikWorker::itemId();
		$keyIdentifier = $this->getKeyIndetifier($row);
		$params = $this->getParams();
		$table = $this->getTable();
		$link = '';
		$view = 'details';
		$customLink = $this->getCustomLink('url', 'details');

		if (trim($customLink) === '')
		{
			$link = '';

			// $$$ hugh - if we don't do this on feeds, links with subfolders in root get screwed up because no BASE_HREF is set
			if ($app->input->get('format', '') == 'fabrikfeed')
			{
				$link .= COM_FABRIK_LIVESITE;
			}

			if ($app->isAdmin())
			{
				$link .= 'index.php?option=com_' . $package . '&task=' . $view . '.view&formid=' . $table->form_id . '&listid=' . $this->getId() . $keyIdentifier;
			}
			else
			{
				$link .= 'index.php?option=com_' . $package . '&view=' . $view . '&formid=' . $table->form_id . $keyIdentifier;
			}

			$link = JRoute::_($link);
		}
		else
		{
			// Custom link
			$link = $this->makeCustomLink($customLink, $row);
		}

		return $link;
	}

	/**
	 * Create a custom edit/view details link
	 *
	 * @param   string  $link  link
	 * @param   object  $row   row's data
	 *
	 * @return  string  custom link
	 */

	protected function makeCustomLink($link, $row)
	{
		$link = htmlspecialchars($link);
		$keyIdentifier = $this->getKeyIndetifier($row);
		$row = JArrayHelper::fromObject($row);
		$link = $this->parseMessageForRowHolder($link, $row);

		if (preg_match('/([\?&]rowid=)/', htmlspecialchars_decode($link)))
		{
			$this->rowIdentifierAdded = true;
		}

		if ($this->rowIdentifierAdded === false)
		{
			if (strstr($link, '?'))
			{
				$link .= $keyIdentifier;
			}
			else
			{
				$link .= '?' . str_replace('&', '', $keyIdentifier);
			}
		}

		$link = JRoute::_($link);

		return $link;
	}

	/**
	 * Get a custome link
	 *
	 * @param   string  $type  link type
	 * @param   string  $mode  edit/details link
	 *
	 * @return  string  link
	 */

	protected function getCustomLink($type = 'url', $mode = 'edit')
	{
		$params = $this->getParams();

		if ($type === 'url')
		{
			$str = ($mode == 'edit') ? $params->get('editurl') : $params->get('detailurl');
		}
		else
		{
			$str = ($mode == 'edit') ? $params->get('editurl_attribs') : $params->get('detailurl_attribs');
		}

		$w = new FabrikWorker;

		return $w->parseMessageForPlaceHolder($str);
	}

	/**
	 * Get the link to edit the records details
	 *
	 * @param   object  &$row  active table row
	 *
	 * @return  string  url of view details link
	 */

	protected function editLink(&$row)
	{
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$Itemid = FabrikWorker::itemId();
		$keyIdentifier = $this->getKeyIndetifier($row);
		$table = $this->getTable();
		$customLink = $this->getCustomLink('url', 'edit');

		if ($customLink == '')
		{
			if ($app->isAdmin())
			{
				$url = 'index.php?option=com_' . $package . '&task=form.view&formid=' . $table->form_id . $keyIdentifier;
			}
			else
			{
				$url = 'index.php?option=com_' . $package . '&view=form&Itemid=' . $Itemid . '&formid=' . $table->form_id . $keyIdentifier . '&listid='
						. $this->getId();
			}

			$link = JRoute::_($url);
		}
		else
		{
			$link = $this->makeCustomLink($customLink, $row);
		}

		return $link;
	}

	/**
	 * Make the drop sql statement for the table
	 *
	 * @return  string  drop table sql
	 */

	public function getDropTableSQL()
	{
		$db = FabrikWorker::getDbo();
		$genTable = $this->getGenericTableName();
		$sql = "DROP TABLE IF EXISTS " . $db->quoteName($genTable);

		return $sql;
	}

	/**
	 * Convert a prefix__tablename to #__tablename
	 *
	 * @return  string  table name
	 */

	public function getGenericTableName()
	{
		$app = JFactory::getApplication();
		$table = $this->getTable();

		return str_replace($app->getCfg('dbprefix'), '#__', $table->db_table_name);
	}

	/**
	 * Make the create sql statement for the table
	 *
	 * @param   bool    $addIfNotExists  add 'if not exists' to query
	 * @param   string  $table           table to get sql for(leave out to use models table)
	 *
	 * @return  string	sql to drop & or create table
	 */

	public function getCreateTableSQL($addIfNotExists = false, $table = null)
	{
		$addIfNotExists = $addIfNotExists ? 'IF NOT EXISTS ' : '';

		if (is_null($table))
		{
			$table = $this->getGenericTableName();
		}

		$fields = $this->getDBFields($table);
		$primaryKey = "";
		$sql = "";
		$table = FabrikString::safeColName($table);

		if (is_array($fields))
		{
			$sql .= "CREATE TABLE $addIfNotExists" . $table . " (\n";

			foreach ($fields as $field)
			{
				$field->Field = FabrikString::safeColName($field->Field);

				if ($field->Key == 'PRI' && $field->Extra == 'auto_increment')
				{
					$primaryKey = "PRIMARY KEY ($field->Field)";
				}

				$sql .= "$field->Field ";
				$sql .= ' ' . $field->Type . ' ';

				if ($field->Null == '')
				{
					$sql .= " NOT NULL ";
				}

				if ($field->Default != '' && $field->Key != 'PRI')
				{
					if ($field->Default == 'CURRENT_TIMESTAMP')
					{
						$sql .= "DEFAULT $field->Default";
					}
					else
					{
						$sql .= "DEFAULT '$field->Default'";
					}
				}

				$sql .= $field->Extra . ",\n";
			}

			if ($primaryKey == '')
			{
				$sql = rtrim($sql, ",\n");
			}

			$sql .= $primaryKey . ");";
		}

		return $sql;
	}

	/**
	 * Make the create sql statement for inserting the table data
	 * used in package export
	 *
	 * @param   object  $oExporter  exporter
	 *
	 * @deprecated - not used?
	 *
	 * @return  string	sql to drop & or create table
	 */

	public function getInsertRowsSQL($oExporter)
	{
		@set_time_limit(300);
		$table = $this->getTable();
		$memoryLimit = ini_get('memory_limit');
		$db = $this->getDb();
		/*
		 * dont load in all the table data as on large tables this gives a memory error
		* in fact this wasnt the problem, but rather the $sql var becomes too large to hold in memory
		* going to try saving to a file on the server and then compressing that and sending it as a header for download
		*/
		$query = $db->getQuery(true);
		$query->select($table->db_primary_key)->from($table->db_table_name);
		$db->setQuery($query);
		$keys = $db->loadColumn();
		$sql = "";
		$query = $db->getQuery(true);
		$dump_buffer_len = 0;

		if (is_array($keys))
		{
			foreach ($keys as $id)
			{
				$query->clear();
				$query->select('*')->from($table->db_table_name)->where($table->db_primary_key = $id);
				$db->setQuery($query);
				$row = $db->loadObject();
				$fmtsql = "\t<query>INSERT INTO " . $table->db_table_name . " ( %s ) VALUES ( %s )</query>";
				$values = array();
				$fields = array();

				foreach ($row as $k => $v)
				{
					$fields[] = $db->quoteName($k);
					$values[] = $db->quote($v);
				}

				$sql .= sprintf($fmtsql, implode(",", $fields), implode(",", $values));
				$sql .= "\n";
				$dump_buffer_len += JString::strlen($sql);

				if ($dump_buffer_len > $memoryLimit)
				{
					$oExporter->writeExportBuffer($sql);
					$sql = "";
					$dump_buffer_len = 0;
				}

				unset($values);
				unset($fmtsql);
			}
		}

		$oExporter->writeExportBuffer($sql);
	}

	/**
	 * Get a row of data from the table
	 *
	 * @param   int   $id        Id
	 * @param   bool  $format    The data
	 * @param   bool  $loadJoin  Load the rows joined data @since 2.0.5 (used in J Content plugin)
	 *
	 * @return  object	Row
	 */

	public function getRow($id, $format = false, $loadJoin = false)
	{
		if (is_null($this->rows))
		{
			$this->rows = array();
		}

		$sig = $id . '.' . $format . '.' . $loadJoin;

		if (array_key_exists($sig, $this->rows))
		{
			return $this->rows[$sig];
		}

		$fabrikDb = $this->getDb();
		$formModel = $this->getFormModel();
		$formModel->rowId = $id;
		unset($formModel->query);
		$sql = $formModel->buildQuery();
		$fabrikDb->setQuery($sql);

		if (!$loadJoin)
		{
			if ($format == true)
			{
				$row = $fabrikDb->loadObject();
				$row = array($row);
				$this->formatData($row);
				/* $$$ hugh - if table is grouped, formatData will have turned $row into an
				 * assoc array, so can't assume 0 is first key.
				* $this->rows[$sig] = $row[0][0];
				*/
				$row = JArrayHelper::getValue($row, FArrayHelper::firstKey($row), array());
				$this->rows[$sig] = JArrayHelper::getValue($row, 0, new stdClass);
			}
			else
			{
				$this->rows[$sig] = $fabrikDb->loadObject();
			}
		}
		else
		{
			$rows = $fabrikDb->loadObjectList();
			$formModel->setJoinData($rows);

			if ($format == true)
			{
				$rows = array(JArrayHelper::toObject($rows));
				$this->formatData($rows);
				$rows = $rows[0];
				/* $$$ hugh - if list is grouped, formatData will have re-index as assoc array,
				 /* so can't assume 0 is first key.
				*/
				$this->rows[$sig] = JArrayHelper::getValue($rows, FArrayHelper::firstKey($rows), array());
			}
			else
			{
				// Content plugin - rows is 1 dimensional array
				$this->rows[$sig] = $rows;
			}
		}

		if (is_array($this->rows[$sig]))
		{
			$this->rows[$sig] = JArrayHelper::toObject($this->rows[$sig]);
		}

		return $this->rows[$sig];
	}

	/**
	 * Find a row in the table that matches " key LIKE '%val' "
	 *
	 * @param   string  $key     key
	 * @param   string  $val     value
	 * @param   bool    $format  format the row
	 *
	 * @return  object	row
	 */

	public function findRow($key, $val, $format = false)
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$usekey = $input->get('usekey');
		$usekey_comparison = $input->get('usekey_comparison');
		$input->set('usekey', $key);
		$input->set('usekey_comparison', 'like');
		$row = $this->getRow($val, $format);
		$input->set('usekey', $usekey);
		$input->set('usekey_comparison', $usekey_comparison);

		return $row;
	}

	/**
	 * Ajax get record specified by row id
	 *
	 * @param   string  $mode  mode
	 *
	 * @return  string  json encoded row
	 */

	public function xRecord($mode = 'table')
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$fabrikDb = $this->getDb();
		$cursor = $input->getInt('cursor', 1);
		$this->getConnection();
		$this->outputFormat = 'json';
		$nav = $this->getPagination(1, $cursor, 1);

		if ($mode == 'table')
		{
			$query = $this->buildQuery();
			$this->setBigSelects();
			$fabrikDb->setQuery($query, $this->limitStart, $this->limitLength);
			$data = $fabrikDb->loadObjectList();
		}
		else
		{
			// Get the row id
			$table = $this->getTable();
			$query = $db->getQuery(true);
			$query->select($table->db_primary_key)->from($table->db_table_name);
			$query = $this->buildQueryJoin($query);
			$query = $this->buildQueryOrder($query);
			$fabrikDb->setQuery($query, $nav->limitstart, $nav->limit);
			$rowid = $fabrikDb->loadResult();
			$input->set('rowid', $rowid);
			$app = JFactory::getApplication();
			$formid = $input->getInt('formid');
			$app->redirect('index.php?option=' . $package . '&view=form&formid=' . $formid . '&rowid=' . $rowid . '&format=raw');
		}

		return json_encode($data);
	}

	/**
	 * Ajax get next record
	 *
	 * @return  string  json object representing record/row
	 */

	public function nextRecord()
	{
		$app = JFactory::getApplication();
		$cursor = $app->input->getInt('cursor', 1);
		$this->getConnection();
		$this->outputFormat = 'json';
		$nav = $this->getPagination(1, $cursor, 1);
		$data = $this->getData();
		echo json_encode($data);
	}

	/**
	 * Ajax get previous record
	 *
	 * @return  string json  object representing record/row
	 */

	public function previousRecord()
	{
		$app = JFactory::getApplication();
		$cursor = $app->input->getInt('cursor', 1);
		$this->getConnection();
		$this->outputFormat = 'json';
		$nav = $this->getPagination(1, $cursor - 2, 1);
		$data = $this->getData();

		return json_encode($data);
	}

	/**
	 * Ajax get first record
	 *
	 * @return  string  json object representing record/row
	 */

	public function firstRecord()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$cursor = $input->getInt('cursor', 1);
		$this->getConnection();
		$this->outputFormat = 'json';
		$nav = $this->getPagination(1, 0, 1);
		$data = $this->getData();

		return json_encode($data);
	}

	/**
	 * Ajax get last record
	 *
	 * @return  string  json object representing record/row
	 */

	public function lastRecord()
	{
		$app = JFactory::getApplication();
		$total = $app->input->getInt('total', 0);
		$this->getConnection();
		$this->outputFormat = 'json';
		$nav = $this->getPagination(1, $total - 1, 1);
		$data = $this->getData();

		return json_encode($data);
	}

	/**
	 * Get a single column of data from the table, test for element filters
	 *
	 * @param   mixed  $col       Column to grab. Element full name or id
	 * @param   bool   $distinct  Select distinct values only
	 * @param   array  $opts      Options: filterLimit bool - should limit to filter_list_max global param (default true)
	 *                                     where - additional where filter to apply to query (@since 3.0.8)
	 *
	 * @return  array  Values for the column - empty array if no results found
	 */

	public function getColumnData($col, $distinct = true, $opts = array())
	{
		$app = JFactory::getApplication();

		if (!array_key_exists($col, $this->columnData))
		{
			$fbConfig = JComponentHelper::getParams('com_fabrik');
			$cache = FabrikWorker::getCache($this);
			$res = $cache->call(array(get_class($this), 'columnData'), $this->getId(), $col, $distinct, $opts);

			if (is_null($res))
			{
				$app->enqueueMessage('list model getColumn Data for ' . $col . ' failed', 'notice');
				$res = array();
			}

			if ((int) $fbConfig->get('filter_list_max', 100) == count($res))
			{
				$app->enqueueMessage(JText::sprintf('COM_FABRIK_FILTER_LIST_MAX_REACHED', $col), 'notice');
			}

			$this->columnData[$col] = $res;
		}

		return $this->columnData[$col];
	}

	/**
	 * Cached method to grab a colums' data, called from getColumnData()
	 *
	 * @param   int    $listId    List id
	 * @param   mixed  $col       Column to grab. Element full name or id
	 * @param   bool   $distinct  Select distinct values only
	 * @param   array  $opts      Options: filterLimit bool - should limit to filter_list_max global param (default true)
	 *                                     where - additional where filter to apply to query (@since 3.0.8)
	 *
	 * @since   3.0.7
	 *
	 * @return  array  Column's values
	 */

	public static function columnData($listId, $col, $distinct = true, $opts = array())
	{
		$listModel = JModelLegacy::getInstance('List', 'FabrikFEModel');
		$listModel->setId($listId);
		$table = $listModel->getTable();
		$fbConfig = JComponentHelper::getParams('com_fabrik');
		$db = $listModel->getDb();
		$el = $listModel->getFormModel()->getElement($col);
		$col = $db->quoteName($col);
		$el->encryptFieldName($col);
		$tablename = $table->db_table_name;
		$tablename = FabrikString::safeColName($tablename);
		$query = $db->getQuery(true);
		$query->select('DISTINCT(' . $col . ')')->from($tablename);
		$query = $listModel->buildQueryJoin($query);
		$query = $listModel->buildQueryWhere(false, $query);
		$query = $listModel->pluginQuery($query);
		$filterLimit = JArrayHelper::getValue($opts, 'filterLimit', true);
		$where = JArrayHelper::getValue($opts, 'where', '');

		if ($where != '')
		{
			$query->where($where);
		}

		if ($filterLimit)
		{
			$db->setQuery($query, 0, $fbConfig->get('filter_list_max', 100));
		}
		else
		{
			$db->setQuery($query);
		}

		$res = $db->loadColumn(0);

		return $res;
	}

	/**
	 * Determine how the model does filtering and navigation
	 *
	 * @return  bool  ajax true /post false; default post
	 */

	public function isAjax()
	{
		$params = $this->getParams();

		if (is_null($this->ajax))
		{
			// $$$ rob 11/07/2011 if post method set to ajax in request use that over the list_nav option
			$app = JFactory::getApplication();
			$input = $app->input;

			if ($input->get('ajax', false) == '1')
			{
				$this->ajax = true;
			}
			else
			{
				$this->ajax = $params->get('list_ajax', $input->Bool('ajax', false));
			}
		}

		return (bool) $this->ajax;
	}

	/**
	 * Model edit/add links can be set separately to the ajax option
	 *
	 * @return  bool
	 */

	protected function isAjaxLinks()
	{
		$params = $this->getParams();
		$ajax = $this->isAjax();

		return (bool) $params->get('list_ajax_links', $ajax);
	}

	/**
	 * Get an array of the table's elements that match a certain plugin type
	 *
	 * @param   string  $plugin  name
	 *
	 * @return  array	matched element models
	 */

	public function getElementsOfType($plugin)
	{
		$found = array();
		$groups = $this->getFormGroupElementData();

		foreach ($groups as $groupModel)
		{
			$elementModels = $groupModel->getMyElements();

			foreach ($elementModels as $elementModel)
			{
				$element = $elementModel->getElement();

				if ($element->plugin == $plugin)
				{
					$found[] = $elementModel;
				}
			}
		}

		return $found;
	}

	/**
	 * Get all the elements in the list
	 *
	 * @param   string  $key            key to key returned array on, currently accepts null, '', 'id', or 'filtername'
	 * @param   bool    $showInTable    show in table default true
	 * @param   bool    $onlyPublished  return only published elements
	 *
	 * @return  array	table element models
	 */

	public function getElements($key = 0, $showInTable = true, $onlyPublished = true)
	{
		if (!isset($this->elements))
		{
			$this->elements = array();
		}

		$sig = $key . '.' . (int) $showInTable;

		if (!array_key_exists($sig, $this->elements))
		{
			$this->elements[$sig] = array();
			$found = array();
			$groups = $this->getFormGroupElementData();

			foreach (array_keys($groups) as $gid)
			{
				$groupModel = $groups[$gid];
				$elementModels = $groupModel->getMyElements();

				foreach ($elementModels as $elementModel)
				{
					$element = $elementModel->getElement();

					if ($element->published == 0 && $onlyPublished)
					{
						continue;
					}

					$dbkey = $key == 'filtername' ? trim($elementModel->getFilterFullName()) : trim($elementModel->getFullName(true, false));

					switch ($key)
					{
						case 'safecolname':
							// Deprecated (except for querystring filters and inline edit)
						case 'filtername':
							// $$$ rob hack to ensure that querystring filters dont use the concat string when getting the
							// Dbkey for the element, otherwise related data doesn't work
							$origconcat = $elementModel->getParams()->get('join_val_column_concat');
							$elementModel->getParams()->set('join_val_column_concat', '');

							// $$$ rob if prefilter was using _raw field then we need to assign the model twice to both possible keys
							if (is_a($elementModel, 'PlgFabrik_ElementDatabasejoin'))
							{
								$dbkey2 = FabrikString::safeColName($elementModel->getFullName(false, false));
								$this->elements[$sig][$dbkey2] = $elementModel;
							}

							$elementModel->getParams()->set('join_val_column_concat', $origconcat);
							$this->elements[$sig][$dbkey] = $elementModel;
							break;
						case 'id':
							$this->elements[$sig][$element->id] = $elementModel;
							break;
						default:
							$this->elements[$sig][] = $elementModel;
							break;
					}
				}
			}
		}

		return $this->elements[$sig];
	}

	/**
	 * Does the list need to include the slimbox js code
	 *
	 * @return  bool
	 */

	public function requiresSlimbox()
	{
		$fbConfig = JComponentHelper::getParams('com_fabrik');

		if ($fbConfig->get('include_lightbox_js', 1) == 2)
		{
			return true;
		}

		$form = $this->getFormModel();
		$groups = $form->getGroupsHiarachy();

		foreach ($groups as $group)
		{
			$elements = $group->getPublishedElements();

			foreach ($elements as $elementModel)
			{
				$element = $elementModel->getElement();

				if ($element->show_in_list_summary && $elementModel->requiresLightBox())
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Does the list need to include the slimbox js code
	 *
	 * @return  bool
	 */

	public function requiresSlideshow()
	{
		$form = $this->getFormModel();
		$groups = $form->getGroupsHiarachy();

		foreach ($groups as $group)
		{
			$elements = $group->getPublishedElements();

			foreach ($elements as $elementModel)
			{
				$element = $elementModel->getElement();

				if ($element->show_in_list_summary && $elementModel->requiresSlideshow())
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get pluginmanager (get reference to form's plugin manager
	 *
	 * @deprecated - use FabrikWorker::getPluginManager() instead since 3.0b
	 *
	 * @return  object  plugin manager model
	 */

	public function getPluginManager()
	{
		return FabrikWorker::getPluginManager();
	}

	/**
	 * Called via advanced search to load in a given element filter
	 *
	 * @return string html for filter
	 */

	public function getAdvancedElementFilter()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$element = $input->get('element');
		$elementid = $input->getId('elid');
		$pluginManager = FabrikWorker::getPluginManager();
		$className = $input->get('plugin');
		$plugin = $pluginManager->getPlugIn($className, 'element');
		$plugin->setId($elementid);
		$el = $plugin->getElement();

		if ($app->input->get('context') == 'visualization')
		{
			$container = $app->input->get('parentView');
		}
		else
		{
			$container = 'listform_' . $this->getRenderContext();
		}

		$script = $plugin->filterJS(false, $container);
		FabrikHelperHTML::addScriptDeclaration($script);

		// For update col plug-in we override to use the field filter type.
		$filterOverride = $input->get('filterOverride', '');

		if ($filterOverride !== '')
		{
			$plugin->getElement()->filter_type = 'field';
		}

		echo $plugin->getFilter($input->getInt('counter', 0), false);
	}

	/**
	 * Get add button label
	 *
	 * @since   3.1rc1
	 *
	 * @return  string
	 */

	public function addLabel()
	{
		$params = $this->getParams();

		return JText::_($params->get('addlabel', JText::_('COM_FABRIK_ADD')));
	}

	/**
	 * Get view details button label
	 *
	 * @since   3.1rc1
	 *
	 * @return  string
	 */

	public function viewLabel()
	{
		$params = $this->getParams();

		return JText::_($params->get('detaillabel', JText::_('COM_FABRIK_VIEW')));
	}

	/**
	 * Get edit row button label
	 *
	 * @since   3.1rc1
	 *
	 * @return  string
	 */

	public function editLabel()
	{
		$params = $this->getParams();

		return JText::_($params->get('editlabel', JText::_('COM_FABRIK_EDIT')));
	}

	/**
	 * Build the table's add record link
	 * if a querystring filter has been passed in to the table then apply this to the link
	 * this means that table->faceted table->add will auto select the data you browsed on
	 *
	 * @return string  url
	 */

	public function getAddRecordLink()
	{
		$qs = array();
		$w = new FabrikWorker;
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$input = $app->input;
		$Itemid = FabrikWorker::itemId();
		$params = $this->getParams();
		$addurl_url = $params->get('addurl', '');
		$filters = $this->getRequestData();
		$keys = JArrayHelper::getValue($filters, 'key', array());
		$vals = JArrayHelper::getValue($filters, 'value', array());
		$types = JArrayHelper::getValue($filters, 'search_type', array());

		for ($i = 0; $i < count($keys); $i++)
		{
			$ftype = JArrayHelper::getValue($types, $i, '');

			// Append content plugin filters or querystring filters
			if (in_array($ftype, array('jpluginfilters', 'querystring')))
			{
				$qs[FabrikString::safeColNameToArrayKey($keys[$i]) . '_raw'] = $vals[$i];
			}
		}

		$addurl_qs = array();

		if (!empty($addurl_url))
		{
			$addurl_parts = explode('?', $addurl_url);

			if (count($addurl_parts) > 1)
			{
				$addurl_url = $addurl_parts[0];

				foreach (explode('&', $addurl_parts[1]) as $urlvar)
				{
					$key_value = explode('=', $urlvar);
					$addurl_qs[$key_value[0]] = $key_value[1];
				}
			}
		}
		// $$$ rob needs the item id for when sef urls are turned on
		if ($input->get('option') !== 'com_' . $package)
		{
			if (!array_key_exists('Itemid', $addurl_qs))
			{
				$qs['Itemid'] = $Itemid;
			}
		}

		if (empty($addurl_url))
		{
			$formModel = $this->getFormModel();
			$formid = $formModel->getForm()->id;
			$qs['option'] = 'com_' . $package;

			if ($app->isAdmin())
			{
				$qs['task'] = 'form.view';
			}
			else
			{
				$qs['view'] = 'form';
			}

			$qs['formid'] = $this->getTable()->form_id;
			$qs['rowid'] = '';

			/* $$$ hugh - testing social profile session hash, which may get set by things like
			 * the CB or JomSocial plugin.  Needed so things like the 'user' element can derive the
			* user ID of the profile being viewed, to which a record is being added.
			*/
			if ($input->get('fabrik_social_profile_hash', '') != '')
			{
				$qs['fabrik_social_profile_hash'] = $input->get('fabrik_social_profile_hash', '');
			}
		}

		$qs = array_merge($qs, $addurl_qs);
		$qs_args = array();

		foreach ($qs as $key => $val)
		{
			if (!is_array($val))
			{
				$qs_args[] = $key . '=' . $val;
			}
			else
			{
				// Rob says do nothing if a multi-value array
			}
		}

		$qs = implode('&', $qs_args);
		$inputs = unserialize($input->serialize());
		$request = $inputs[1];
		$qs = $w->parseMessageForPlaceHolder($qs, $request);

		return !empty($addurl_url) ? JRoute::_($addurl_url . '?' . $qs) : JRoute::_('index.php?' . $qs);
	}

	/**
	 * Create the JS to load element list JS
	 *
	 * @param   array  &$srcs  JS scripts to load
	 *
	 * @return  string  script
	 */

	public function getElementJs(&$srcs)
	{
		$form = $this->getFormModel();
		$script = '';
		$groups = $form->getGroupsHiarachy();
		$run = array();

		foreach ($groups as $groupModel)
		{
			$elementModels = $groupModel->getPublishedElements();

			foreach ($elementModels as $elementModel)
			{
				$element = $elementModel->getElement();

				if (!in_array($element->plugin, $run))
				{
					$run[] = $element->plugin;
					$elementModel->tableJavascriptClass($srcs);
				}

				$script .= $elementModel->elementListJavascript();
			}
		}

		return $script;
	}

	/**
	 * Return the url for the list form - this url is used when submitting searches, and ordering
	 *
	 * @return  string  action url
	 */

	public function getTableAction()
	{
		if (isset($this->tableAction))
		{
			return $this->tableAction;
		}

		$app = JFactory::getApplication();
		$input = $app->input;
		$option = $input->get('option');

		// Get the router
		$router = $app->getRouter();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');

		$uri = clone (JURI::getInstance());
		/* $$$ rob force these to be 0 once the menu item has been loaded for the first time
		 * subsequent loads of the link should have this set to 0. When the menu item is re-clicked
		* rest filters is set to 1 again
		*/
		$router->setVar('resetfilters', 0);

		if ($option !== $package)
		{
			// $$$ rob these can't be set by the menu item, but can be set in {fabrik....}
			$router->setVar('clearordering', 0);
			$router->setVar('clearfilters', 0);
		}

		$queryvars = $router->getVars();
		$form = $this->getFormModel();
		$page = 'index.php?';

		foreach ($queryvars as $k => $v)
		{
			$rawK = FabrikString::rtrimword($k, '_raw');
			$el = $form->getElement($k);

			if ($el === false)
			{
				$el = $form->getElement($rawK);
			}

			if (is_array($v))
			{
				/* $$$ rob if you were using URL filters such as
				 *
				* &jos_fabble_activity___create_date[value][]=now
				* &jos_fabble_activity___create_date[value][]=%2B2%20week&jos_fabble_activity___create_date[condition]=BETWEEN
				*
				* then we don't want to re-add them to the table action.
				* Instead they are aded to the filter sessions and reapplied that way
				* otherwise we ended up with elementname=Array in the query string
				*/

				/*
				 * $$$ hugh ... yeah, but $v is still an array, so we need to deal with it
				 * if it isn't an element ... like (say) ids[]=1&ids[]=2 in a table plugin, like
				 * email list
				 */
				if ($el === false && $k !== 'fabrik___filter')
				{
					foreach ($v as $v1)
					{
						$qs[] = $k . '[]=' . $v1;
					}
				}
			}
			else
			{
				if ($el === false)
				{
					$qs[] = $k . '=' . $v;
				}
				else
				{
					/* $$$ e-kinst
					 * let's keep &id for com_content - in other case in Content Plugin
					* we have incorrect action in form and as a result bad pagination URLS.
					* In any case this will not be excessive (I suppose)
					*/
					if ($k == 'id' && $option == 'com_content')
					{
						// At least. May be even  $option != 'com_fabrik'
						$qs[] = $k . '=' . $v;
					}
					// Check if its a tag element if it is we want to clear that when we clear the form
					// (if the filter was set via the url we generally want to keep it though

					/* 28/12/2011 $$$ rob testing never keeping querystring filters in the qs but instead always
					 * adding them to the filters (if no filter set up in element settings then hidden fields added anyway
					 		* this is to try to get round issue of related data (countries->regions) filter region from country list,
					 		* then clear filters (ok) but then if you go to 2nd page of results country url filter re-applied
					 		*/
					/* if($el->getElement()->plugin !== 'textarea' && $el->getParams()->get('textarea-tagify') !== true) {
					 $qs[] = "$k=$v";
					} */
				}
			}
		}

		$action = $page . implode('&amp;', $qs);
		$action = preg_replace("/limitstart{$this->getId()}=(\d+)?(&amp;|)/", '', $action);
		$action = FabrikString::removeQSVar($action, 'fabrik_incsessionfilters');
		$action = FabrikString::rtrimword($action, '&');
		$this->tableAction = JRoute::_($action);

		return $this->tableAction;
	}

	/**
	 * Allow plugins to add arbitrary WHERE clauses.  Gets checked in buildQueryWhere().
	 *
	 * @param   string  $pluginName   Plugin name
	 * @param   string  $whereClause  Where clause (WITHOUT prepended where/and etc)
	 *
	 * @return  bool
	 */

	public function setPluginQueryWhere($pluginName, $whereClause)
	{
		// Strip any prepended conditions off
		$whereClause = preg_replace('#(^where |^and |^or )#', '', $whereClause);
		/* only do anything if it's a different clause ...
		 * if it's the same, no need to clear the table data, can use cached
		*/
		if (!array_key_exists($pluginName, $this->pluginQueryWhere) || $whereClause != $this->pluginQueryWhere[$pluginName])
		{
			// Set the internal data, which will get used in buildQueryWhere
			$this->pluginQueryWhere[$pluginName] = $whereClause;
			/* as we are modifying the main getData query, we need to make sure and
			 * clear table data, forcing next getData() to do the query again, no cache
			*/
			$this->resetQuery();
		}
		// Return true just for the heck of it
		return true;
	}

	/**
	 * Plugins sometimes need to clear their where clauses
	 *
	 * @param   string  $pluginName  Pugin name
	 *
	 * @return  bool
	 */

	public function unsetPluginQueryWhere($pluginName)
	{
		if (array_key_exists($pluginName, $this->pluginQueryWhere))
		{
			unset($this->pluginQueryWhere[$pluginName]);
		}

		return true;
	}

	/**
	 * If all filters are set to read only then don't return a clear button
	 * otherwised do
	 *
	 * @return  string	clear filter button link
	 */

	public function getClearButton()
	{
		$filters = $this->getFilters('listform_' . $this->getRenderContext(), 'list');
		$params = $this->getParams();

		if (count($filters) > 0 || $params->get('advanced-filter'))
		{
			$table = $this->getTable();
			$tmpl = $this->getTmpl();
			$title = '<span>' . JText::_('COM_FABRIK_CLEAR') . '</span>';
			$opts = array('alt' => JText::_('COM_FABRIK_CLEAR'), 'class' => 'fabrikTip', 'opts' => "{notice:true}", 'title' => $title);
			$img = FabrikHelperHTML::image('filter_delete.png', 'list', $tmpl, $opts);

			return '<a href="#" class="clearFilters">' . $img . '</a>';
		}
		else
		{
			return '';
		}
	}

	/**
	 * Get the join display mode - merge, normal or reduce
	 *
	 * @return  string	1 if merge, 2 if reduce, 0 if no merge or reduce
	 */

	public function mergeJoinedData()
	{
		$params = $this->getParams();
		$display = $params->get('join-display', '');

		switch ($display)
		{
			case 'merge':
				$merge = 1;
				break;
			case 'reduce':
				$merge = 2;
				break;
			default:
				$merge = 0;
				break;
		}

		return $merge;
	}

	/**
	 * Ask each element to preFormatFormJoins() for $data
	 *
	 * @param   array  &$data  to preformat
	 *
	 * @return  void
	 */

	protected function preFormatFormJoins(&$data)
	{
		$profiler = JProfiler::getInstance('Application');
		$form = $this->getFormModel();
		$tableParams = $this->getParams();
		$table = $this->getTable();
		$pluginManager = FabrikWorker::getPluginManager();
		$method = 'renderListData_' . $this->outputFormat;
		$this->_aLinkElements = array();

		// $$$ hugh - temp foreach fix
		$groups = $form->getGroupsHiarachy();
		$ec = count($data);

		foreach ($groups as $groupModel)
		{
			/* if (($tableParams->get('group_by_template', '') !== '' && $this->getGroupBy() != '') || $this->outputFormat == 'csv'
			 || $this->outputFormat == 'feed')
			{
			$elementModels = $groupModel->getPublishedElements();
			}
			else
			{
			$elementModels = $groupModel->getPublishedListElements();
			} */

			/*
			 * $$$ rob 29/10/2012 - see http://fabrikar.com/forums/showthread.php?t=28830
			* Calc may be set to show in list via menu item, but groupModel::getPublishedListElements() doesn't know
			* this. Seems best to run all calcs regardless of whether they are set to show in list.
			*/
			$elementModels = $groupModel->getPublishedElements();

			foreach ($elementModels as $elementModel)
			{
				$col = $elementModel->getFullName(true, false);

				if (!empty($data) && array_key_exists($col, $data[0]))
				{
					for ($i = 0; $i < $ec; $i++)
					{
						$thisRow = $data[$i];
						$coldata = $thisRow->$col;
						$data[$i]->$col = $elementModel->preFormatFormJoins($coldata, $thisRow);
					}
				}
			}
		}
	}

	/**
	 * $$$ rob 19/10/2011 now called before formatData() from getData() as otherwise element tips (created in element->renderListData())
	 * only contained first merged records data and not all merged records
	 *
	 * Collapses 'repeated joined' rows into a single row.
	 * If a group is not repeating we just use the first row's data (as subsequent rows will contain the same data
	 * Otherwise if the group is repeating we append each repeated record's data into the first row's data
	 * All rows execpt the first row for each group are then unset (as unique subsequent row's data will be contained within
	 * the first row)
	 *
	 * @param   array  &$data  list data
	 *
	 * @return  void
	 */

	protected function formatForJoins(&$data)
	{
		$merge = $this->mergeJoinedData();

		if (empty($merge))
		{
			return;
		}

		$listid = $this->getTable()->id;
		$dbprimaryKey = FabrikString::safeColNameToArrayKey($this->getTable()->db_primary_key);
		$formModel = $this->getFormModel();
		$db = $this->getDb();
		FabrikHelperHTML::debug($data, 'render:before formatForJoins');
		$count = count($data);

		$last_pk = '';
		$last_i = 0;
		$count = count($data);
		$can_repeats = array();
		$can_repeats_tables = array();
		$can_repeats_keys = array();
		$can_repeats_pk_vals = array();
		$remove = array();

		if (empty($data))
		{
			return;
		}
		/* First, go round first row of data, and prep some stuff.
		 * Basically, if doing a "reduce data" merge (merge == 2), we need to know what the
		* PK element is for each joined group (well, for each element, really)
		*/
		foreach ($data[0] as $key => $val)
		{
			$shortkey = FabrikString::rtrimword($key, '_raw');
			/* $$$ hugh - had to cache this stuff, because if you have a lot of rows and a lot of elements,
			 * doing this many hundreds of times causes huge slowdown, exceeding max script execution time!
			* And we really only need to do it once for the first row.
			*/
			if (!isset($can_repeats[$shortkey]))
			{
				$elementModel = $formModel->getElement($shortkey);

				// $$$ rob - testing for linking join which is repeat but linked join which is not - still need separate info from linked to join
				// $can_repeats[$shortkey] = $elementModel ? ($elementModel->getGroup()->canRepeat()) : 0;
				if ($merge == 2 && $elementModel)
				{
					if ($elementModel->getGroup()->canRepeat() || $elementModel->getGroup()->isJoin())
					{
						// We need to work out the PK of the joined table.
						// So first, get the table name.
						$group = $elementModel->getGroup();
						$join = $group->getJoinModel()->getJoin();
						$join_table_name = $join->table_join;

						// We have the table name, so see if we already have it cached ...
						if (!isset($can_repeats_tables[$join_table_name]))
						{
							// We don't have it yet, so grab the PK
							$keys = $this->getPrimaryKeyAndExtra($join_table_name);

							if (!empty($keys) && array_key_exists('key', $keys[0]))
							{
								// OK, now we have the PK for the table
								$can_repeats_tables[$join_table_name] = $keys[0];
							}
							else
							{
								// $$$ hugh - might be a view, so Hail Mary attempt to get PK
								$query = $db->getQuery(true);
								$query->select('db_primary_key')->from('#__{package}_lists')
								->where('db_table_name = ' . $db->quote($join_table_name));
								$db->setQuery($query);
								$join_pk = $db->loadResult();

								if (!empty($join_pk))
								{
									$can_repeats_tables[$join_table_name] = array('colname' => FabrikString::shortColName($join_pk));
								}
							}
						}
						// Hopefully we now have the PK
						if (isset($can_repeats_tables[$join_table_name]))
						{
							$can_repeats_keys[$shortkey] = $join_table_name . '___' . $can_repeats_tables[$join_table_name]['colname'];
						}

						$crk_sk = $can_repeats_keys[$shortkey];

						// Create the array if it doesn't exist
						if (!isset($can_repeats_pk_vals[$crk_sk]))
						{
							$can_repeats_pk_vals[$crk_sk] = array();
						}
						// Now store the
						if (!isset($can_repeats_pk_vals[$crk_sk][0]) and isset($data[0]->$crk_sk))
						{
							$can_repeats_pk_vals[$crk_sk][0] = $data[0]->$crk_sk;
						}
					}
				}

				$can_repeats[$shortkey] = $elementModel ? ($elementModel->getGroup()->canRepeat() || $elementModel->getGroup()->isJoin()) : 0;
			}
		}

		for ($i = 0; $i < $count; $i++)
		{
			// $$$rob if rendering J article in PDF format __pk_val not in pdf table view
			$next_pk = isset($data[$i]->__pk_val) ? $data[$i]->__pk_val : $data[$i]->$dbprimaryKey;

			if (!empty($last_pk) && ($last_pk == $next_pk))
			{
				foreach ($data[$i] as $key => $val)
				{
					$origKey = $key;
					$shortkey = FabrikString::rtrimword($key, '_raw');

					if ($can_repeats[$shortkey])
					{
						if ($merge == 2
							&& !isset($can_repeats_pk_vals[$can_repeats_keys[$shortkey]][$i])
							&& isset($data[$i]->$can_repeats_keys[$shortkey]))
						{
							$can_repeats_pk_vals[$can_repeats_keys[$shortkey]][$i] = $data[$i]->$can_repeats_keys[$shortkey];
						}

						if ($origKey == $shortkey)
						{
							/* $$$ rob - this was just appending data with a <br> but as we do thie before the data is formatted
							 * it was causing all sorts of issues for list rendering of links, dates etc. So now turn the data into
							* an array and at the end of this method loop over the data to encode the array into a json object.
							*/
							$do_merge = true;

							if ($merge == 2)
							{
								$pk_vals = array_count_values(array_filter($can_repeats_pk_vals[$can_repeats_keys[$shortkey]]));

								if (isset($data[$i]->$can_repeats_keys[$shortkey]))
								{
									if ($pk_vals[$data[$i]->$can_repeats_keys[$shortkey]] > 1)
									{
										$do_merge = false;
									}
								}
							}

							if ($do_merge)
							{
								/* The raw data is not altererd at the moment - not sure that that seems correct but can't see any issues
								 * with it currently
								* $$$ hugh - added processing of raw data, needed for _raw placeholders
								* in things like custom links
								*/
								$data[$last_i]->$key = (array) $data[$last_i]->$key;
								array_push($data[$last_i]->$key, $val);
								$rawkey = $key . '_raw';
								/* if (!isset($data[$i]->$rawkey))
								{
									$rawkey = $key;
								}
								$rawval = $data[$i]->$rawkey;
								$data[$last_i]->$rawkey = (array) $data[$last_i]->$rawkey;
								array_push($data[$last_i]->$rawkey, $rawval); */

								if (isset($data[$i]->$rawkey))
								{
									$rawval = $data[$i]->$rawkey;
									$data[$last_i]->$rawkey = (array) $data[$last_i]->$rawkey;
									array_push($data[$last_i]->$rawkey, $rawval);
								}
							}
						}
						else
						{
							/* $$$ hugh - don't think we need this, now we're processing _raw data?
							 if (!is_array($data[$last_i]->$origKey)) {
							$json= $val;
							$data[$last_i]->$origKey = json_encode($json);
							}
							*/
						}
					}
				}

				$remove[] = $i;
				continue;
			}
			else
			{
				if ($merge == 2)
				{
					foreach ($data[$i] as $key => $val)
					{
						$origKey = $key;
						$shortkey = FabrikString::rtrimword($key, '_raw');

						if ($can_repeats[$shortkey]
							&& !isset($can_repeats_pk_vals[$can_repeats_keys[$shortkey]][$i])
							&& isset($data[$i]->$can_repeats_keys[$shortkey]))
						{
							$can_repeats_pk_vals[$can_repeats_keys[$shortkey]][$i] = $data[$i]->$can_repeats_keys[$shortkey];
						}
					}
				}

				$last_i = $i;

				// $$$rob if rendering J article in PDF format __pk_val not in pdf table view
				$last_pk = $next_pk;
			}
			// $$$ rob ensure that we have a sequental set of keys otherwise ajax json will turn array into object
			$data = array_values($data);
		}

		for ($c = count($remove) - 1; $c >= 0; $c--)
		{
			unset($data[$remove[$c]]);
		}
		// $$$ rob loop over any data that was merged into an array and turn that into a json object
		foreach ($data as $gkey => $d)
		{
			foreach ($d as $k => $v)
			{
				if (is_array($v))
				{
					foreach ($v as &$v2)
					{
						$v2 = FabrikWorker::JSONtoData($v2);
					}

					$v = json_encode($v);
					$data[$gkey]->$k = $v;
				}
			}
		}

		$data = array_values($data);
	}

	/**
	 * Does the list model have an associated table (can occur when form model
	 * which does not store in db, gets its list model)
	 *
	 * @return boolean
	 */

	public function noTable()
	{
		$id = $this->getId();

		return (bool) empty($id);
	}

	/**
	 * Save an individual element value to the fabrik db
	 *
	 * @param   string  $rowId  row id
	 * @param   string  $key    key
	 * @param   string  $value  value
	 *
	 * @return  void
	 */

	public function storeCell($rowId, $key, $value)
	{
		$data[$key] = $value;

		// Ensure the primary key is set in $data
		$primaryKey = FabrikString::shortColName($this->getTable()->db_primary_key);
		$primaryKey = str_replace("`", "", $primaryKey);

		if (!isset($data[$primaryKey]))
		{
			$data[$primaryKey] = $rowId;
		}

		$this->storeRow($data, $rowId);
	}

	/**
	 * Increment a value in a cell
	 *
	 * @param   string  $rowId  Row's id
	 * @param   string  $key    Field to increment
	 * @param   string  $dir    -1/1 etc
	 *
	 * @return  bool
	 */

	public function incrementCell($rowId, $key, $dir)
	{
		$db = $this->getDb();
		$table = $this->getTable();
		$query = "UPDATE $table->db_table_name SET $key = COALESCE($key, 0)  + $dir WHERE $table->db_primary_key = " . $db->quote($rowId);
		$db->setQuery($query);

		return $db->execute();
	}

	/**
	 * Set model sate
	 *
	 * @return  void
	 */

	protected function populateState()
	{
		$app = JFactory::getApplication('site');
		$input = $app->input;

		if (!$app->isAdmin())
		{
			// Load the menu item / component parameters.
			$params = $app->getParams();
			$this->setState('params', $params);

			// Load state from the request.
			$pk = $input->getInt('listid', $params->get('listid'));
		}
		else
		{
			$pk = $input->getInt('listid');
		}

		$this->setState('list.id', $pk);
		$offset = $input->getInt('limitstart');
		$this->setState('list.offset', $offset);
	}

	/**
	 * Get the output format
	 *
	 * @return  string	Outputformat
	 */

	public function getOutPutFormat()
	{
		return $this->outputFormat;
	}

	/**
	 * Set the list output format
	 *
	 * @param   string  $f  Format html/pdf/raw/csv
	 *
	 * @return  void
	 */

	public function setOutputFormat($f)
	{
		$this->outputFormat = $f;
	}

	/**
	 * Update a series of rows with a key = val , works across joined tables
	 *
	 * @param   array   $ids     Pk values to update
	 * @param   string  $col     Key to update should be in format 'table.element'
	 * @param   string  $val     Val to set to
	 * @param   string  $update  Optional update statement, overides $col = $val
	 *
	 * @return  void
	 */

	public function updateRows($ids, $col, $val, $update = '')
	{
		if ($col == '')
		{
			return;
		}

		if (empty($ids))
		{
			return;
		}

		$db = $this->getDb();
		$nav = $this->getPagination(1, 0, 1);
		$data = $this->getData();

		// $$$ rob dont unshift as this messes up for grouped data
		// $data = array_shift($data);
		$table = $this->getTable();

		$update = $update == '' ? $col . ' = ' . $db->quote($val) : $update;
		$colbits = explode('.', $col);
		$tbl = array_shift($colbits);

		$joinFound = false;
		JArrayHelper::toInteger($ids);
		$ids = implode(',', $ids);
		$dbk = $k = $table->db_primary_key;
		$joins = $this->getJoins();

		// If the update element is in a join replace the key and table name with the join table's name and key
		foreach ($joins as $join)
		{
			if ($join->table_join == $tbl)
			{
				$joinFound = true;
				$db->setQuery('DESCRIBE ' . $tbl);
				$fields = $db->loadObjectList('Key');
				$k = $tbl . '___' . $fields['PRI']->Field;
				$dbk = $tbl . '.' . $fields['PRI']->Field;
				$db_table_name = $tbl;
				$ids = array();

				foreach ($data as $groupdata)
				{
					foreach ($groupdata as $d)
					{
						$v = $d->{$k . '_raw'};

						if ($v != '')
						{
							$ids[] = $v;
						}
					}
				}

				if (!empty($ids))
				{
					$query = $db->getQuery(true);
					$ids = implode(',', $ids);
					$query->update($db_table_name)->set($update)->where($dbk . ' IN (' . $ids . ')');
					$db->setQuery($query);
					$db->execute();
				}
			}
		}

		if (!$joinFound)
		{
			$db_table_name = $table->db_table_name;
			$query = $db->getQuery(true);
			$query->update($db_table_name)->set($update)->where($dbk . ' IN (' . $ids . ')');
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * unset a series of model properties
	 *
	 * @return  void
	 */

	public function reset()
	{
		unset($this->_whereSQL);
		unset($this->table);
		unset($this->filters);
		unset($this->prefilters);
		unset($this->params);
		unset($this->viewfilters);

		// $$$ hugh - added some more stuff to clear, as per:
		// http://fabrikar.com/forums/showthread.php?p=115122#post115122
		unset($this->asfields);
		unset($this->formModel);
		unset($this->filterModel);
		unset($this->searchAllAsFields);
		unset($this->joinsSQL);
		unset($this->joins);
		unset($this->orderBy);
		unset($this->_joinsNoCdd);
		unset($this->elements);
		unset($this->data);
		unset($this->tmpl);
		unset($this->selectedOrderFields);
	}

	/**
	 * make sure a new getData query wil recreate data and query from scratch
	 *
	 * @return  void
	 */

	public function resetQuery()
	{
		unset($this->_whereSQL);
		unset($this->data);
	}

	/**
	 * Get the lists <table> class
	 *
	 * @return string
	 */

	public function htmlClass()
	{
		$params = $this->getParams();
		$class = array('table');

		if ($params->get('bootstrap_stripped_class', true))
		{
			$class[] = 'table-striped';
		}

		if ($params->get('bootstrap_bordered_class'))
		{
			$class[] = 'table-bordered';
		}

		if ($params->get('bootstrap_condensed_class'))
		{
			$class[] = 'table-condensed';
		}

		if ($params->get('bootstrap_hover_class', true))
		{
			$class[] = 'table-hover';
		}

		return implode(' ', $class);
	}

	/**
	 * Get the table template
	 *
	 * @since 3.0
	 *
	 * @return string template name
	 */

	public function getTmpl()
	{
		if (!isset($this->tmpl))
		{
			$app = JFactory::getApplication();
			$input = $app->input;
			$item = $this->getTable();
			$params = $this->getParams();
			$document = JFactory::getDocument();

			if ($app->isAdmin())
			{
				$this->tmpl = $input->get('layout', $params->get('admin_template'));
			}
			else
			{
				$this->tmpl = $input->get('layout', $item->template);

				if ($app->scope !== 'mod_fabrik_list')
				{
					$this->tmpl = FabrikWorker::getMenuOrRequestVar('fabriklayout', $this->tmpl, $this->isMambot);
					/* $$$ rob 10/03/2012 changed menu param to listlayout to avoid the list menu item
					 * options also being used for the form/details view template
					*/
					$this->tmpl = FabrikWorker::getMenuOrRequestVar('listlayout', $this->tmpl, $this->isMambot);
				}
			}

			if ($this->tmpl == '')
			{
				$this->tmpl = FabrikWorker::j3() ? 'bootstrap' : 'default';
			}

			if ($app->scope !== 'mod_fabrik_list')
			{
				/* $$$ rob 10/03/2012 changed menu param to listlayout to avoid the list menu item
				 * options also being used for the form/details view template
				*/
				// $this->tmpl = FabrikWorker::getMenuOrRequestVar('fabriklayout', $this->tmpl, $this->isMambot);
				$this->tmpl = FabrikWorker::getMenuOrRequestVar('listlayout', $this->tmpl, $this->isMambot);
			}

			if ($document->getType() === 'pdf')
			{
				$this->tmpl = $params->get('pdf_template', $this->tmpl);
			}

			// Migration test
			$modFolder = JPATH_SITE . '/templates/' . $app->getTemplate() . '/html/com_fabrik/list/' . $this->tmpl;
			$componentFolder = JPATH_SITE . '/components/com_fabrik/views/list/tmpl/' . $this->tmpl;

			if (!JFolder::exists($componentFolder) && !JFolder::exists($modFolder))
			{
				$this->tmpl = FabrikWorker::j3() ? 'bootstrap' : 'default';
			}
		}

		return $this->tmpl;
	}

	/**
	 * Set the lists elements' tempate to that of the list's
	 *
	 * @return  void
	 */

	protected function setElementTmpl()
	{
		$tmpl = $this->getTmpl();
		$groups = $this->getFormModel()->getGroupsHiarachy();
		$params = $this->getParams();

		foreach ($groups as $groupModel)
		{
			if (($params->get('group_by_template', '') !== '' && $this->getGroupBy() != '') || $this->outputFormat == 'csv'
				|| $this->outputFormat == 'feed')
			{
				$elementModels = $groupModel->getPublishedElements();
			}
			else
			{
				$elementModels = $groupModel->getPublishedListElements();
			}

			foreach ($elementModels as $elementModel)
			{
				$elementModel->tmpl = $tmpl;
			}
		}
	}

	/**
	 * Short cut to test if the lists connection is the same as the Joomla database
	 *
	 * @return bool
	 */

	public function inJDb()
	{
		return $this->getConnection()->isJdb();
	}

	/**
	 * Checks : J template html override css file then fabrik list tmpl template css file. Including them if found
	 *
	 * @since 3.0 loads lists's css files
	 *
	 * @return  void
	 */

	public function getListCss()
	{
		$tmpl = $this->getTmpl();
		$app = JFactory::getApplication();
		$jTmplFolder = FabrikWorker::j3() ? 'tmpl' : 'tmpl25';

		// Check for a form template file (code moved from view)
		if ($tmpl != '')
		{
			$qs = '?c=' . $this->getRenderContext();

			// $$$rob need &amp; for pdf output which is parsed through xml parser otherwise fails
			$qs .= '&amp;buttoncount=' . $this->rowActionCount;
			$overRide = 'templates/' . $app->getTemplate() . '/html/com_fabrik/list/' . $tmpl . '/template_css.php' . $qs;

			if (!FabrikHelperHTML::stylesheetFromPath($overRide))
			{
				FabrikHelperHTML::stylesheetFromPath('components/com_fabrik/views/list/' . $jTmplFolder . '/' . $tmpl . '/template_css.php' . $qs);
			}
			/* $$$ hugh - as per Skype convos with Rob, decided to re-instate the custom.css convention.  So I'm adding two files:
			 * custom.css - for backward compat with existing 2.x custom.css
			* custom_css.php - what we'll recommend people use for custom css moving foward.
			*/
			if (!FabrikHelperHTML::stylesheetFromPath('templates/' . $app->getTemplate() . '/html/com_fabrik/list/' . $tmpl . '/custom.css' . $qs))
			{
				FabrikHelperHTML::stylesheetFromPath('components/com_fabrik/views/list/' . $jTmplFolder . '/' . $tmpl . '/custom.css');
			}

			if (!FabrikHelperHTML::stylesheetFromPath('templates/' . $app->getTemplate() . '/html/com_fabrik/list/' . $tmpl . '/custom_css.php' . $qs))
			{
				FabrikHelperHTML::stylesheetFromPath('components/com_fabrik/views/list/' . $jTmplFolder . '/' . $tmpl . '/custom_css.php' . $qs);
			}
		}
	}

	/**
	 * Get a unique list identifier (enables the same list to be rendered in component and module at same time)
	 *
	 * @return  string
	 */

	public function getRenderContext()
	{
		if ($this->renderContext === '')
		{
			$this->setRenderContext($this->getId());
		}

		return $this->getId() . $this->renderContext;
	}

	/**
	 * Lists can be rendered in articles, as components and in modules
	 * we need to set a unique reference for them to avoid conflicts
	 *
	 * @param   int  $id  module/component list id
	 *
	 * @return  void
	 */

	public function setRenderContext($id = null)
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$task = $input->getCmd('task');

		if (strstr($task, '.'))
		{
			$task = explode('.', $task);
			$task = array_pop($task);
		}

		// $$$ rob if admin filter task = filter and not list.filter
		if ($task == 'filter' || ($app->isAdmin() && $input->get('task') == 'filter'))
		{
			$this->setRenderContextFromRequest();
		}
		else
		{
			$task = $input->get('task');

			if ((($task == 'list.view' || $task == 'list.delete') && $input->get('format') == 'raw')
				|| $input->get('layout') == '_advancedsearch' || $task === 'list.elementFilter'
				|| $input->get('setListRefFromRequest') == 1)
			{
				// Testing for ajax nav in content plugin or in advanced search
				$this->setRenderContextFromRequest();
			}
			else
			{
				$this->renderContext = '_' . JFactory::getApplication()->scope . '_' . $id;
			}
		}

		if ($this->renderContext == '')
		{
			$this->renderContext = '_' . JFactory::getApplication()->scope . '_' . $id;
		}
	}

	/**
	 * When dealing with ajax requests filtering etc we want to take the listref from the
	 * request array
	 *
	 * @return  string	listref
	 */

	protected function setRenderContextFromRequest()
	{
		$app = JFactory::getApplication();
		$listref = $app->input->get('listref', '');

		if ($listref === '')
		{
			$this->renderContext = '';
		}
		else
		{
			$listref = explode('_', $listref);
			array_shift($listref);
			$this->renderContext = '_' . implode('_', $listref);
		}

		return $this->renderContext;
	}

	/**
	 * Get lists group by headings
	 *
	 * @return   array  heading names
	 */

	public function getGroupByHeadings()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$base = JURI::getInstance();
		$base = $base->toString(array('scheme', 'user', 'pass', 'host', 'port', 'path'));
		$qs = $input->server->get('QUERY_STRING', '', 'string');

		if (JString::stristr($qs, 'group_by'))
		{
			$qs = FabrikString::removeQSVar($qs, 'group_by');
			$qs = FabrikString::ltrimword($qs, '?');
			$qs = str_replace('&', '&amp;', $qs);
		}

		$url = $base;

		if (!empty($qs))
		{
			$url .= JString::strpos($url, '?') !== false ? '&amp;' : '?';
			$url .= $qs;
		}

		$url .= JString::strpos($url, '?') !== false ? '&amp;' : '?';
		$a = array();
		list($h, $x, $b, $c) = $this->getHeadings();
		$a[$url . 'group_by=0'] = JText::_('COM_FABRIK_NONE');

		foreach ($h as $key => $v)
		{
			if (!in_array($key, array('fabrik_select', 'fabrik_edit', 'fabrik_view', 'fabrik_delete', 'fabrik_actions')))
			{
				$thisurl = $url . 'group_by=' . $key;
				$a[$thisurl] = strip_tags($v);
			}
		}

		return $a;
	}

	/**
	 * Get a list of elements to export in the csv file.
	 *
	 * @since 3.0b
	 *
	 * @return array full element names.
	 */

	public function getCsvFields()
	{
		$params = $this->getParams();
		$formModel = $this->getFormModel();
		$csvFields = array();

		if ($params->get('csv_which_elements', 'selected') == 'visible')
		{
			$csvIds = $this->getAllPublishedListElementIDs();
		}
		elseif ($params->get('csv_which_elements', 'selected') == 'all')
		{
			// Export code will export all, if list is empty
			$csvIds = array();
		}
		elseif ($params->get('csv_elements') == '' || $params->get('csv_elements') == 'null')
		{
			$csvIds = array();
		}
		else
		{
			$csvIds = json_decode($params->get('csv_elements'))->show_in_csv;
		}

		foreach ($csvIds as $id)
		{
			if ($id !== '')
			{
				$elementModel = $formModel->getElement($id, true);

				if ($elementModel !== false)
				{
					$csvFields[$elementModel->getFullName(true, false)] = 1;
				}
			}
		}

		return $csvFields;
	}

	/**
	 * Helper function for view to determine if filters should be shown
	 *
	 * @return  bool
	 */

	public function getShowFilters()
	{
		$app = JFactory::getApplication();
		$filters = $this->getFilters('listform_' . $this->getRenderContext());
		$params = $this->getParams();
		$filterMode = (int) $params->get('show-table-filters');

		return (count($filters) > 0 && $filterMode !== 0) && $app->input->get('showfilters', 1) == 1 ? true : false;
	}

	/**
	 * Get the number of buttons that are rendered for the list
	 *
	 * @return  number
	 */

	protected function getButtonCount()
	{
		$buttonCount = 0;

		return $buttonCount;
	}

	/**
	 * Helper view function to determine if any buttons are shown
	 *
	 * @return  bool
	 */

	public function getHasButtons()
	{
		$params = $this->getParams();

		if (($this->canAdd() && $params->get('show-table-add')) || $this->getShowFilters()
			|| $this->getAdvancedSearchLink() || $this->canGroupBy() || $this->canCSVExport()
			|| $this->canCSVImport() || $params->get('rss') || $params->get('pdf') || $this->canEmpty())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Compacts the ordering sequence of the selected records
	 *
	 * @param   string  $colid  column name to order on
	 * @param   string  $where  additional where query to limit ordering to a particular subset of records
	 *
	 * @since   3.0.5
	 *
	 * @return  bool
	 */

	public function reorder($colid, $where = '')
	{
		$elementModel = $this->getFormModel()->getElement($colid, true);
		$asfields = array();
		$fields = array();
		$elementModel->getAsField_html($asfields, $fields);
		$col = $asfields[0];
		$field = explode("AS", $col);
		$field = array_shift($field);
		$db = $this->getDb();
		$k = $this->getTable()->db_primary_key;
		$shortKey = FabrikString::shortColName($k);
		$tbl = $this->getTable()->db_table_name;

		// Get the primary keys and ordering values for the selection.
		$query = $db->getQuery(true);
		$query->select($k . ' AS id, ' . $field . ' AS ordering');
		$query->from($tbl);
		$query->where($field . ' >= 0');
		$query->order($field);

		// Setup the extra where and ordering clause data.
		if ($where)
		{
			$query->where($where);
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		// Compact the ordering values.
		foreach ($rows as $i => $row)
		{
			// Only update rows that are necessary.
			if ($row->ordering != $i + 1)
			{
				// Update the row ordering field.
				$query = $db->getQuery(true);
				$query->update($tbl);
				$query->set($field . ' = ' . ($i + 1));
				$query->where($k . ' = ' . $db->quote($row->id));
				$db->setQuery($query);
				echo $db->getQuery() . "\n\n";
				$db->execute();
			}
		}

		return true;
	}

	/**
	 * Load the JS files into the document
	 *
	 * @param   array  &$srcs  reference: js script srcs to load in the head
	 *
	 * @return  null
	 */

	public function getCustomJsAction(&$srcs)
	{
		if (JFile::exists(COM_FABRIK_FRONTEND . '/js/table_' . $this->getId() . '.js'))
		{
			$srcs[] = 'components/com_fabrik/js/table_' . $this->getId() . '.js';
		}

		if (JFile::exists(COM_FABRIK_FRONTEND . '/js/list_' . $this->getId() . '.js'))
		{
			$srcs[] = 'components/com_fabrik/js/list_' . $this->getId() . '.js';
		}
	}

	/**
	 * When saving an element it can effect the list parameters, update them here.
	 *
	 * @param   object  $elementModel  element model
	 *
	 * @deprecated since 3.0b
	 *
	 * @since 3.0.6
	 *
	 * @return  void
	 */

	public function updateFromElement($elementModel)
	{
	}

	/**
	 * Get / set formatAll, which forces formatData() to ignore 'show in table'
	 * and just format everything, needed by things like the table email plugin.
	 * If called without an arg, just returns current setting.
	 *
	 * $$$ hugh - doesn't work, now that finesseData() is called via call_user_func().
	 *
	 * @param   bool  $format_all  optional arg to set format
	 *
	 * @return  bool
	 */

	public function formatAll($format_all = null)
	{
		if (isset($format_all))
		{
			$this->format_all = $format_all;
		}

		return $this->format_all;
	}

	/**
	 * Copy rows
	 *
	 * @param   mixed  $ids  array or string of row ids to copy
	 *
	 * @since	3.0.6
	 *
	 * @return  bool	all rows copied (true) or false if a row copy fails.
	 */

	public function copyRows($ids)
	{
		$ids = (array) $ids;
		$formModel = $this->getFormModel();
		$formModel->copyingRow(true);
		$state = true;

		foreach ($ids as $id)
		{
			$formModel->rowId = $id;
			$formModel->unsetData();
			$row = $formModel->getData();
			$formModel->copyFromRaw($row, 'fromraw', true);
			$row['Copy'] = '1';
			$row['fabrik_copy_from_table'] = '1';
			$formModel->formData = $row;

			if (!$formModel->process())
			{
				$state = false;
			}
		}

		return $state;
	}

	/**
	 * Return an array of element ID's of all published and visible list elements
	 * Created to call from GetCsvFields()
	 *
	 * @return   array  array of element IDs
	 */
	public function getAllPublishedListElementIDs()
	{
		$ids = array();
		$form = $this->getFormModel();
		$groups = $form->getGroupsHiarachy();

		foreach ($groups as $groupModel)
		{
			$elementModels = $groupModel->getPublishedListElements();

			foreach ($elementModels as $key => $elementModel)
			{
				$ids[] = $elementModel->getId();
			}
		}

		return $ids;
	}

	/**
	 * Return an array of elements which are set to always render
	 *
	 * @param   bool  $not_shown_only  Only return elements which have 'always render' enabled, AND are not displayed in the list
	 *
	 * @return  bool  array of element models
	 */

	public function getAlwaysRenderElements($not_shown_only = true)
	{
		$form = $this->getFormModel();
		$alwaysRender = array();
		$groups = $form->getGroupsHiarachy();

		foreach ($groups as $groupModel)
		{
			$elementModels = $groupModel->getPublishedElements();

			foreach ($elementModels as $elementModel)
			{
				if ($elementModel->isAlwaysRender($not_shown_only))
				{
					$alwaysRender[] = $elementModel;
				}
			}
		}

		return $alwaysRender;
	}

	/**
	 * Does the list have any 'always render' elements?
	 *
	 * @return   bool
	 */

	public function hasAlwaysRenderElements()
	{
		$alwaysRender = $this->getAlwaysRenderElements();

		return !empty($alwaysRender);
	}

	/**
	 * Get the name of the tab field
	 *
	 * @return string  tablename___elementname
	 */

	private function getTabField()
	{
		if (!isset($this->tabsField))
		{
			$params = $this->getParams();
			$tabsField = $params->get('tabs_field', '');
			$this->tabsField = FabrikString::safeColNameToArrayKey($tabsField);
		}

		return $this->tabsField;
	}

	/**
	 * Get tab categories and merge as necessary to get down to tab limit
	 *
	 * @return   array  Tabs
	 */

	private function getTabCategories()
	{
		/**
		 * 2013-07-19 Paul - This function determines the tabs to be shown at the top of the list table.
		 * The tabs are defined by the contents of a user specified field e.g for alphabetic tabs you might
		 * use a calc field with the first character of a surname.
		 * To prevent a list of
		 **/
		$app = JFactory::getApplication();
		$params = $this->getParams();
		$tabsField = $this->getTabField();

		if (empty($tabsField))
		{
			return;
		}

		list($tableName, $tabsField) = explode('___', $tabsField);
		$table = $this->getTable();

		if ($tableName != $table->db_table_name)
		{
			$app->enqueueMessage(sprintf(JText::_('COM_FABRIK_LIST_TABS_TABLE_ERROR'), $tableName, $table->db_table_name), 'error');

			return;
		}

		$tabsMax = (int) $params->get('tabs_max', 10);
		$tabsAll = (bool) $params->get('tabs_all', '1');

		// Get values and count in the tab field
		$db = $this->getDb();
		$query = $db->getQuery(true);
		$query->select(array($tabsField, 'Count(' . $tabsField . ') as count'))
			->from($db->quoteName($table->db_table_name))
			->group($tabsField)
			->order($tabsField);

		/**
		 * Filters include any existing tab filters - so we cannot calculate tabs based on any user set filters
		 * or pre-filters, until we can exclude them from being used here.
		 $this->buildQueryWhere($app->input->getInt('incfilters', 1), $query, false);
		 **/
		$db->setQuery($query);
		FabrikHelperHTML::debug($query->dump(), 'list getTabCategories query:' . $table->label);
		$profiler = JProfiler::getInstance('Application');
		JDEBUG ? $profiler->mark('before fabrik list tabs query run') : null;
		$db->execute();
		$counts = $db->loadRowList();
		JDEBUG ? $profiler->mark('after fabrik list tabs query run') : null;
		FabrikHelperHTML::debug($counts, 'list getTabCategories counts: ' . $table->label);

		/**
		 * We consolidate by finding the two consecutive rows with the smallest total and merging them.
		 * To avoid excessive looping if user tabField is too fragmented, we should skip tabs if
		 * we will iterate more than 100 times.
		 **/
		if (count($counts) - $tabsMax > 100)
		{
			$app->enqueueMessage(sprintf(JText::_('COM_FABRIK_LIST_TABS_MERGE_ERROR'), count($counts), $tabsMax), 'notice');

			return;
		}

		$tabs = array();

		if ($tabsAll)
		{
			// Set value to null to differentiate between all and empty string values
			$tabs[] = array(JText::_('COM_FABRIK_LIST_TABS_ALL'), null);
		}

		while (count($counts) > $tabsMax)
		{
			/**
			 * Primary algorithm is to find the two consecutive rows whose total count is smallest and merge them.
			 *
			 * If this turns out to be too time consuming in real-life scenarios, we can add further optimisation
			 * by adding logic to the loop so that first time through it sums the total of count, and
			 * second time through it merges any consecutive rows whose values total less than a small proportion (say 1/4)
			 * of the total count / tabs. This will not be completely optimum but will be a whole lot quicker for many small
			 * categories than the base iteration. Using 1/4 should result in no more than 3 further iterations per tab to get to
			 * the number of tabs required. In this case we can probably remove the test / error message above.
			 **/
			$minCount = 999999999;
			$minIndex = 0;

			for ($i = 1; $i < count($counts); $i++)
			{
				$totCount = $counts[$i - 1][1] + $counts[$i][1];

				if ($totCount < $minCount)
				{
					$minCount = $totCount;
					$minIndex = $i;
				}
			}

			// Merge mins
			$counts[$minIndex - 1][0] = (array) $counts[$minIndex - 1][0];
			$counts[$minIndex][0] = (array) $counts[$minIndex][0];
			$counts[$minIndex - 1][0] = array($counts[$minIndex - 1][0][0], end($counts[$minIndex][0]));
			$counts[$minIndex - 1][1] += $counts[$minIndex][1];

			// Array_splice not working as advertised - working like array_slice for some reason!!
			// $counts = array_splice($counts, $minIndex, 1);
			unset($counts[$minIndex]);
			$counts = array_values($counts);
		}

		JDEBUG ? $profiler->mark('after fabrik list tabs counts merge') : null;
		FabrikHelperHTML::debug($counts, 'list getTabCategories merged counts: ' . $table->label);

		for ($i = 0; $i < count($counts); $i++)
		{
			if (is_array($counts[$i][0]))
			{
				$tabs[] = array($counts[$i][0][0] . '-' . $counts[$i][0][1], $counts[$i][0]);
			}
			else
			{
				$tabLabel = empty($counts[$i][0]) ? '-' : $counts[$i][0];
				$tabs[] = array($tabLabel, $counts[$i][0]);
			}
		}

		return $tabs;
	}

	/**
	 * Set the List's tab HTML
	 *
	 * @return  array  Tabs
	 */

	public function loadTabs()
	{
		$this->tabs = array();
		$tabs = $this->getTabCategories();

		if (!is_array($tabs) || empty($tabs))
		{
			return;
		}

		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$listid = $this->getId();
		$tabsField = $this->getTabField();
		$uri = JURI::getInstance();
		$urlBase = $uri->toString(array('path'));
		$urlBase .= '?option=com_' . $package . '&';

		if ($app->isAdmin())
		{
			$urlBase .= 'task=list.view&';
		}
		else
		{
			$urlBase .= 'view=list&';
		}

		$urlBase .= 'listid=' . $listid . '&resetfilters=1';
		$urlEquals = $urlBase . '&' . $tabsField . '=%s';
		$urlRange = $urlBase . '&' . $tabsField . '[value][]=%s&' . $tabsField . '[value][]=%s&' . $tabsField . '[condition]=BETWEEN';
		$uri = JURI::getInstance();
		$thisUri = rawurldecode($uri->toString(array('path', 'query')));

		foreach ($tabs as $i => $tabArray)
		{
			$row = new stdClass;
			list($label, $range) = $tabArray;
			$row->label = $label;

			if (is_null($range))
			{
				$row->url = $urlBase;
			}
			elseif (!is_array($range))
			{
				$row->url = sprintf($urlEquals, $range);
			}
			else
			{
				list($low, $high) = $range;
				$row->url = sprintf($urlEquals, sprintf($urlRange, $low, $high));
			}

			$row->class = ($thisUri == $row->url) ? 'class="active"' : '';
			$this->tabs[] = $row;
		}

		return $this->tabs;
	}
}
