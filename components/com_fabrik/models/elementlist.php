<?php
/**
 * Fabrik Element List Model
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
 * Fabrik Element List Model
 *
 * @package  Fabrik
 * @since    3.0
 */

class PlgFabrik_ElementList extends PlgFabrik_Element
{
	/**
	 * Does the element have sub elements
	 *
	 * @var bool
	 */
	public $hasSubElements = true;

	/**
	 * Default values
	 *
	 * @var array
	 */
	public $defaults = null;

	/**
	 * Db table field type
	 *
	 * @var  string
	 */
	protected $fieldDesc = 'TEXT';

	/**
	 * Db table field size
	 *
	 * @var  string
	 */
	protected $inputType = 'radio';

	/**
	 * Should the table render functions use html to display the data
	 *
	 * @var bool
	 */
	public $renderWithHTML = true;

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
		$val = $this->getValue($data, $repeatCounter, $opts);
		$labels = $this->getSubOptionLabels();
		$values = $this->getSubOptionValues();
		$str = '';

		if (is_array($val))
		{
			foreach ($val as $tmpVal)
			{
				$key = array_search($tmpVal, $values);
				$str .= ($key === false) ? $tmpVal : $labels[$key];
				$str .= ' ';
			}
		}
		else
		{
			$str = $val;
		}

		return $str;
	}

	/**
	 * Get sub elements initial selection
	 *
	 * @return  array  Initially selected values
	 */

	public function getSubInitialSelection()
	{
		$params = $this->getParams();
		$opts = $params->get('sub_options');
		$r = isset($opts->sub_initial_selection) ? (array) $opts->sub_initial_selection : array();

		return $r;
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
		$data = (array) $data;

		foreach ($data as $d)
		{
			if ($d != '')
			{
				return false;
			}
		}

		return true;
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
		$params = $this->getParams();
		$opts = $params->get('sub_options');

		if (!isset($this->default))
		{
			if (isset($opts->sub_initial_selection))
			{
				$this->default = $this->getSubInitialSelection();
			}
			else
			{
				$this->default = parent::getDefaultValue($data);
			}
		}

		return $this->default;
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
		if (is_array($value))
		{
			foreach ($value as &$v)
			{
				$v = $this->prepareFilterVal($v);
			}
		}
		else
		{
			$value = $this->prepareFilterVal($value);
		}

		return parent::getFilterValue($value, $condition, $eval);
	}

	/**
	 * Build the filter query for the given element.
	 * Can be overwritten in plugin - e.g. see checkbox element which checks for partial matches
	 *
	 * @param   string  $key            Element name in format `tablename`.`elementname`
	 * @param   string  $condition      =/like etc
	 * @param   string  $value          Search string - already quoted if specified in filter array options
	 * @param   string  $originalValue  Original filter value without quotes or %'s applied
	 * @param   string  $type           Filter type advanced/normal/prefilter/search/querystring/searchall
	 *
	 * @return  string	sql query part e,g, "key = value"
	 */

	public function getFilterQuery($key, $condition, $value, $originalValue, $type = 'normal')
	{
		$element = $this->getElement();
		$db = JFactory::getDbo();
		$this->encryptFieldName($key);
		$glue = 'OR';

		if ($element->filter_type == 'checkbox' || $element->filter_type == 'multiselect')
		{
			$str = array();

			if ($condition === 'NOT IN')
			{
				$partialComparison = ' NOT LIKE ';
				$comparison = ' <> ';
				$glue = ' AND ';
			}
			else
			{
				$partialComparison = ' LIKE ';
				$comparison = ' = ';
				$glue = ' OR ';
			}

			switch ($condition)
			{
				case 'IN':
				case 'NOT IN':
					/**
					 * Split out 1,2,3 into an array to iterate over.
					 * It's a string if pre-filter, array if element filter
					 */
					if (!is_array($originalValue))
					{
						$originalValue = explode(',', $originalValue);
					}

					foreach ($originalValue as &$v)
					{
						$v = trim($v);
						$v = FabrikString::ltrimword($v, '"');
						$v = FabrikString::ltrimword($v, "'");
						$v = FabrikString::rtrimword($v, '"');
						$v = FabrikString::rtrimword($v, "'");
					}
					break;
				default:
					$originalValue = (array) $originalValue;
					break;
			}

			foreach ($originalValue as $v2)
			{
				$v2 = str_replace("/", "\\\\/", $v2);
				$str[] = '(' . $key . $partialComparison . $db->quote('%"' . $v2 . '"%') . $glue . $key . $comparison . $db->quote($v2) . ') ';
			}

			$str = '(' . implode($glue, $str) . ')';
		}
		else
		{
			$originalValue = trim($value, "'");

			/*
			 * JSON stored values will back slash "/". So wwe need to add "\\\\"
			* before it to escape it for the query.
			*/
			$originalValue = str_replace("/", "\\\\/", $originalValue);

			switch ($condition)
			{
				case '=':
				case '<>':

					$condition2 = $condition == '=' ? 'LIKE' : 'NOT LIKE';
					$glue = $condition == '=' ? 'OR' : 'AND';
					$db = FabrikWorker::getDbo();
					$str = "($key $condition $value " . " $glue $key $condition2 " . $db->quote('["' . $originalValue . '"%') . " $glue $key $condition2 "
					. $db->quote('%"' . $originalValue . '"%') . " $glue $key $condition2 " . $db->quote('%"' . $originalValue . '"]') . ")";
					break;
				default:
					$str = " $key $condition $value ";
					break;
			}
		}

		return $str;
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
		$element = $this->getElement();

		if ($element->filter_type === 'checkbox')
		{
			$listModel = $this->getListModel();
			$v = 'fabrik___filter[list_' . $listModel->getRenderContext() . '][value]';
			$v .= '[' . $counter . ']';
		}
		else
		{
			$v = parent::filterName($counter, $normal);
		}

		return $v;
	}

	/**
	 * Get the table filter for the element
	 *
	 * @param   int   $counter  Filter order
	 * @param   bool  $normal   Do we render as a normal filter or as an advanced search filter
	 * if normal include the hidden fields as well (default true, use false for advanced filter rendering)
	 *
	 * @return  string	Filter HTML
	 */

	public function getFilter($counter = 0, $normal = true)
	{
		$element = $this->getElement();
		$values = $this->getSubOptionValues();
		$default = $this->getDefaultFilterVal($normal, $counter);
		$elName = $this->getFullName(true, false);
		$htmlid = $this->getHTMLId() . 'value';
		$listModel = $this->getListModel();
		$params = $this->getParams();
		$class = $this->filterClass();
		$v = $this->filterName($counter, $normal);

		if (in_array($element->filter_type, array('range', 'dropdown', '', 'checkbox', 'multiselect')))
		{
			$rows = $this->filterValueList($normal);

			if ($params->get('filter_groupby') != -1)
			{
				JArrayHelper::sortObjects($rows, $params->get('filter_groupby', 'text'));
			}

			if (!in_array('', $values) && !in_array($element->filter_type, array('checkbox', 'multiselect')))
			{
				array_unshift($rows, JHTML::_('select.option', '', $this->filterSelectLabel()));
			}
		}

		$attribs = 'class="' . $class . '" size="1" ';
		$size = $params->get('filter_length', 20);
		$return = array();

		switch ($element->filter_type)
		{
			case 'range':
				if (!is_array($default))
				{
					$default = array('', '');
				}

				$return[] = JHTML::_('select.genericlist', $rows, $v . '[]', $attribs, 'value', 'text', $default[0],
					$element->name . "_filter_range_0");
				$return[] = JHTML::_('select.genericlist', $rows, $v . '[]', $attribs, 'value', 'text', $default[1],
					$element->name . "_filter_range_1");
				break;
			case 'checkbox':
				$return[] = $this->checkboxFilter($rows, $default, $v);
				break;
			case 'dropdown':
			case 'multiselect':
			default:
				$size = $element->filter_type === 'multiselect' ? 'multiple="multiple" size="7"' : 'size="1"';
				$attribs = 'class="' . $class . '" ' . $size;
				$v = $element->filter_type === 'multiselect' ? $v . '[]' : $v;
				$return[] = JHTML::_('select.genericlist', $rows, $v, $attribs, 'value', 'text', $default, $htmlid);
				break;

			case 'field':
				if (get_magic_quotes_gpc())
				{
					$default = stripslashes($default);
				}

				$default = htmlspecialchars($default);
				$return[] = '<input type="text" name="' . $v . '" class="' . $class . '" size="' . $size . '" value="' . $default . '" id="'
					. $htmlid . '" />';
				break;

			case 'hidden':
				if (get_magic_quotes_gpc())
				{
					$default = stripslashes($default);
				}

				$default = htmlspecialchars($default);
				$return[] = '<input type="hidden" name="' . $v . '" class="' . $class . '" value="' . $default . '" id="' . $htmlid . '" />';
				break;

			case 'auto-complete':
				$defaultLabel = $this->getLabelForValue($default);
				$autoComplete = $this->autoCompleteFilter($default, $v, $defaultLabel, $normal);
				$return = array_merge($return, $autoComplete);
				break;
		}

		$return[] = $normal ? $this->getFilterHiddenFields($counter, $elName) : $this->getAdvancedFilterHiddenFields();

		return implode("\n", $return);
	}

	/**
	 * Get an array of element html ids and their corresponding
	 * js events which trigger a validation.
	 * Examples of where this would be overwritten include timedate element with time field enabled
	 *
	 * @param   int  $repeatCounter  Repeat group counter
	 *
	 * @return  array  HTML ids to watch for validation
	 */

	public function getValidationWatchElements($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$ar = array('id' => $id, 'triggerEvent' => 'click');

		return array($ar);
	}

	/**
	 * Turn form value into email formatted value
	 *
	 * @param   mixed  $value          Element value
	 * @param   array  $data           Form data
	 * @param   int    $repeatCounter  Group repeat counter
	 *
	 * @return  string  Email formatted value
	 */

	protected function getIndEmailValue($value, $data = array(), $repeatCounter = 0)
	{
		$params = $this->getParams();
		$split_str = $params->get('options_split_str', '');
		$element = $this->getElement();
		$values = $this->getSubOptionValues();
		$labels = $this->getSubOptionLabels();
		$aLabels = array();

		if (is_string($value))
		{
			$value = array($value);
		}

		if (is_array($value))
		{
			foreach ($value as $tmpVal)
			{
				$key = array_search($tmpVal, $values);

				if ($key !== false)
				{
					$aLabels[] = $labels[$key];
				}
			}
		}

		if ($split_str == '')
		{
			if (count($aLabels) === 1)
			{
				$val = $aLabels[0];
			}
			else
			{
				$val = '<ul><li>' . implode('</li><li>', $aLabels) . '</li></ul>';
			}
		}
		else
		{
			$val = implode($split_str, $aLabels);
		}

		if ($val === '')
		{
			$val = $params->get('sub_default_label');
		}

		return $val;
	}

	/**
	 * Used by radio and dropdown elements to get a dropdown list of their unique
	 * unique values OR all options - basedon filter_build_method
	 *
	 * @param   bool    $normal     Do we render as a normal filter or as an advanced search filter
	 * @param   string  $tableName  Table name to use - defaults to element's current table
	 * @param   string  $label      Field to use, defaults to element name
	 * @param   string  $id         Field to use, defaults to element name
	 * @param   bool    $incjoin    Include join
	 *
	 * @return  array  Text/value objects
	 */

	public function filterValueList($normal, $tableName = '', $label = '', $id = '', $incjoin = true)
	{
		$rows = parent::filterValueList($normal, $tableName, $label, $id, $incjoin);
		$this->unmergeFilterSplits($rows);
		$this->reapplyFilterLabels($rows);

		return $rows;
	}

	/**
	 * Cache method to populate autocomplete options
	 *
	 * @param   plgFabrik_Element  $elementModel  Element model
	 * @param   string             $search        Search string
	 * @param   array              $opts          Options, 'label' => field to use for label (db join)
	 *
	 * @since   3.0.7
	 *
	 * @return string  Json encoded search results
	 */

	public static function cacheAutoCompleteOptions($elementModel, $search, $opts = array())
	{
		$app = JFactory::getApplication();
		$listModel = $elementModel->getListModel();
		$label = JArrayHelper::getValue($opts, 'label', '');
		$rows = $elementModel->filterValueList(true, '', $label);
		$v = $app->input->get('value', '', 'string');

		// Search for every word separately in the result rather than the single string (of multiple words)
		$regex  = "/(?=.*" .
			implode(")(?=.*",
				array_filter(explode(" ", addslashes($v)))
			) . ").*/i";
		$start = count($rows) - 1;

		for ($i = $start; $i >= 0; $i--)
		{
			$rows[$i]->text = strip_tags($rows[$i]->text);

			// Check that search strings are not in the HTML we just stripped
			if (!preg_match($regex, $rows[$i]->text))
			{
				unset($rows[$i]);
			}
		}

		$rows = array_values($rows);
		echo json_encode($rows);
	}

	/**
	 * Will the element allow for multiple selections
	 *
	 * @since	3.0.6
	 *
	 * @return  bool
	 */

	protected function isMultiple()
	{
		$params = $this->getParams();

		return $params->get('multiple', 0) || $this->isJoin();
	}

	/**
	 * Optionally pre-format list data before rendering to <ul>
	 *
	 * @param   array  &$data    Element Data
	 * @param   array  $thisRow  Row data
	 *
	 * @return  void
	 */
	protected function listPreformat(&$data, $thisRow)
	{
	}

	/**
	 * Shows the data formatted for the list view
	 *
	 * @param   string    $data      Elements data
	 * @param   stdClass  &$thisRow  All the data in the lists current row
	 *
	 * @return  string	formatted value
	 */

	public function renderListData($data, stdClass &$thisRow)
	{
		$element = $this->getElement();
		$params = $this->getParams();
		$listModel = $this->getListModel();
		$multiple = $this->isMultiple();
		$mergeGroupRepeat = ($this->getGroup()->canRepeat() && $this->getListModel()->mergeJoinedData());
		$sLabels = array();
		$useIcon = $params->get('icon_folder', 0);

		// Give priority to raw value icons (podion)
		$raw = $this->getFullName(true, false) . '_raw';

		if (isset($thisRow->$raw))
		{
			$rawData = FabrikWorker::JSONtoData($thisRow->$raw, true);

			foreach ($rawData as &$val)
			{
				$val = $useIcon ? $this->replaceWithIcons($val, 'list', $listModel->getTmpl()) : $val;
			}

			if ($this->iconsSet)
			{
				// Use raw icons
				$data = $rawData;
				$useIcon = false;
			}
		}

		// Repeat group data
		$gdata = FabrikWorker::JSONtoData($data, true);
		$this->listPreformat($gdata, $thisRow);
		$addHtml = (count($gdata) !== 1 || $multiple || $mergeGroupRepeat) && $this->renderWithHTML;
		$uls = array();

		foreach ($gdata as $i => $d)
		{
			$lis = array();
			$vals = is_array($d) ? $d : FabrikWorker::JSONtoData($d, true);

			foreach ($vals as $tmpVal)
			{
				$l = $useIcon ? $this->replaceWithIcons($tmpVal, 'list', $listModel->getTmpl()) : $tmpVal;

				if (!$this->iconsSet == true)
				{
					if (!is_a($this, 'PlgFabrik_ElementDatabasejoin'))
					{
						$l = $this->getLabelForValue($tmpVal);
					}
					else
					{
						$l = $tmpVal;
					}

					$l = $this->replaceWithIcons($l, 'list', $listModel->getTmpl());
				}

				if ($this->renderWithHTML)
				{
					$l = $this->rollover($l, $thisRow, 'list');
					$l = $listModel->_addLink($l, $this, $thisRow, $i);
				}

				if (trim($l) !== '')
				{
					$lis[] = $l;
				}
			}

			if (!empty($lis))
			{
				$uls[] = $lis;
			}
		}

		// Do all uls only contain one record, if so condense to 1 ul (avoids nested <ul>'s each with one <li>
		$condense = true;

		foreach ($uls as $ul)
		{
			if (count($ul) > 1)
			{
				$condense = false;
			}
		}

		$consdenced = array();

		if ($condense)
		{
			foreach ($uls as $ul)
			{
				$consdenced[] = $ul[0];
			}

			return $addHtml ? '<ul class="fabrikRepeatData"><li>' . implode('</li><li>', $consdenced) . '</li></ul>' : implode(' ', $consdenced);
		}
		else
		{
			$html = array();
			$html[] = $addHtml ? '<ul class="fabrikRepeatData"><li>' : '';

			foreach ($uls as $ul)
			{
				$html[] = $addHtml ? '<ul class="fabrikRepeatData"><li>' : '';
				$html[] = $addHtml ? implode('</li><li>', $ul) : implode(' ', $ul);
				$html[] = $addHtml ? '</li></ul>' : '';
			}

			$html[] = $addHtml ? '</li></ul>' : '';

			return $addHtml ? implode('', $html) : implode(' ', $html);
		}
	}

	/**
	 * Prepares the element data for CSV export
	 *
	 * @param   string  $data      Element data
	 * @param   object  &$thisRow  All the data in the lists current row
	 *
	 * @return  string	Formatted value
	 */

	public function renderListData_csv($data, &$thisRow)
	{
		$this->renderWithHTML = false;
		$d = $this->renderListData($data, $thisRow);
		$this->renderWithHTML = true;

		return $d;
	}

	/**
	 * Draws the html form element
	 *
	 * @param   array  $data           To preopulate element with
	 * @param   int    $repeatCounter  Repeat group counter
	 *
	 * @return  string	Elements html
	 */

	public function render($data, $repeatCounter = 0)
	{
		$name = $this->getHTMLName($repeatCounter);
		$app = JFactory::getApplication();
		$input = $app->input;
		$id = $this->getHTMLId($repeatCounter);
		$params = $this->getParams();
		$values = $this->getSubOptionValues();
		$labels = $this->getSubOptionLabels();

		/**
		 * $$$ hugh -- working on issue with radio and checkbox, where extra blank subitem gets added
		 * if nothing selected.  this fix assumes that 'value' cannot be empty string for sub-options,
		 * and I'm not sure if we enforce that.  Problem being that if we just cast directly to
		 * an array, the array isn't "empty()", as it has a single, empty string entry.  So then
		 * the array_diff() we're about to do sees that as a diff.
		 *
		 * $$$ rob - Need more logic that the previous test, as we weren't applying default value/label if set and data empty
		*/
		$selected = (array) $this->getValue($data, $repeatCounter);

		if (FArrayHelper::emptyIsh($selected))
		{
			$selected = array();

			// Nothing previously selected, and not editable, set selected to default value, which later on is replaced with default label
			if (!$this->isEditable() && $params->get('sub_default_value', '') !== '')
			{
				$selected[] = $params->get('sub_default_value');
			}
		}

		// $$$ rob 06/10/2011 if front end add option on, but added option not saved we should add in the selected value to the
		// values and labels.
		$diff = array_diff($selected, $values);

		if (!empty($diff))
		{
			$values = array_merge($values, $diff);

			// Swap over the default value to the default label
			if (!$this->isEditable())
			{
				foreach ($diff as &$di)
				{
					if ($di === $params->get('sub_default_value'))
					{
						$di = $params->get('sub_default_label');
					}
				}
			}

			$labels = array_merge($labels, $diff);
		}

		if (!$this->isEditable())
		{
			$aRoValues = array();

			for ($i = 0; $i < count($values); $i++)
			{
				if (in_array($values[$i], $selected))
				{
					$aRoValues[] = $this->getReadOnlyOutput($values[$i], $labels[$i]);
				}
			}

			$splitter = ($params->get('icon_folder') != -1 && $params->get('icon_folder') != '') ? ' ' : ', ';

			if (empty($aRoValues))
			{
				return'';
			}

			return ($this->isMultiple() && $this->renderWithHTML)
			? '<ul class="fabrikRepeatData"><li>' . implode('</li><li>', $aRoValues) . '</li></ul>' : implode($splitter, $aRoValues);
		}

		// Remove the default value
		$key = array_search($params->get('sub_default_value'), $values);

		if ($key)
		{
			unset($values[$key]);
		}

		$optionsPerRow = (int) $this->getParams()->get('options_per_row', 4);
		$elBeforeLabel = (bool) $this->getParams()->get('element_before_label', true);

		// Element_before_label
		if ($input->get('format') == 'raw')
		{
			$optionsPerRow = 1;
		}

		$classes = $this->labelClasses();
		$buttonGroup = $this->buttonGroup();
		$grid = FabrikHelperHTML::grid($values, $labels, $selected, $name, $this->inputType, $elBeforeLabel, $optionsPerRow, $classes, $buttonGroup);
		array_unshift($grid, '<div class="fabrikSubElementContainer" id="' . $id . '">');
		$grid[] = '</div><!-- close subElementContainer -->';

		if ($params->get('allow_frontend_addto', false))
		{
			$onlylabel = $params->get('allowadd-onlylabel');
			$grid[] = $this->getAddOptionFields($repeatCounter, $onlylabel);
		}

		return implode("\n", $grid);
	}

	/**
	 * Should the grid be rendered as a Bootstrap button-group
	 *
	 * @since 3.1
	 *
	 * @return  bool
	 */

	protected function buttonGroup()
	{
		$params = $this->getParams();

		return FabrikWorker::j3() && $params->get('btnGroup', false);
	}

	/**
	 * Get classes to assign to the label
	 *
	 * @return  array
	 */

	protected function labelClasses()
	{
		return array();
	}

	/**
	 * Should the sub label appear before or after the sub element?
	 *
	 * @return  bool
	 */

	protected function getElementBeforeLabel()
	{
		return (bool) $this->getParams()->get('radio_element_before_label', true);
	}

	/**
	 * called from within function getValue
	 * needed so we can append _raw to the name for elements such as db joins
	 *
	 * @param   array  $opts  Options
	 *
	 * @return  string  Element name inside data array
	 */

	protected function getValueFullName($opts)
	{
		return $this->getFullName(true, false);
	}

	/**
	 * Determines the value for the element in the form view
	 *
	 * @param   array  $data           Form data
	 * @param   int    $repeatCounter  When repeating joinded groups we need to know what part of the array to access
	 * @param   array  $opts           Options
	 *
	 * @return  string	value
	 */

	public function getValue($data, $repeatCounter = 0, $opts = array())
	{
		$v = parent::getValue($data, $repeatCounter, $opts);

		if (is_string($v))
		{
			$v = FabrikWorker::JSONtoData($v, true);
		}

		return $v;
	}

	/**
	 * Is the dropdowns cnn the same as the main Joomla db
	 *
	 * @return  bool
	 */

	protected function inJDb()
	{
		return $this->getlistModel()->inJDb();
	}

	/**
	 * format the read only output for the page
	 *
	 * @param   string  $value  Initial value
	 * @param   string  $label  Label
	 *
	 * @return  string  read only value
	 */

	protected function getReadOnlyOutput($value, $label)
	{
		$params = $this->getParams();

		if ($params->get('icon_folder') != -1 && $params->get('icon_folder') != '')
		{
			$icon = $this->replaceWithIcons($value);

			if ($this->iconsSet)
			{
				$label = $icon;
			}
		}

		return $label;
	}

	/**
	 * Trigger called when a row is stored.
	 * Check if new options have been added and if so store them in the element for future use.
	 *
	 * @param   array  &$data          Data to store
	 * @param   int    $repeatCounter  Repeat group index
	 *
	 * @return  void
	 */

	public function onStoreRow(&$data, $repeatCounter = 0)
	{
		if (!parent::onStoreRow($data, $repeatCounter))
		{
			return false;
		}

		$element = $this->getElement();
		$params = $this->getParams();
		$formModel = $this->getFormModel();
		$formData = $formModel->formData;

		if ($params->get('savenewadditions') && array_key_exists($element->name . '_additions', $formData))
		{
			$added = stripslashes($formData[$element->name . '_additions']);

			if (trim($added) == '')
			{
				return true;
			}

			$added = json_decode($added);
			$values = $this->getSubOptionValues();
			$labels = $this->getSubOptionLabels();
			$found = false;

			foreach ($added as $obj)
			{
				if (!in_array($obj->val, $values))
				{
					$values[] = $obj->val;
					$found = true;
					$labels[] = $obj->label;
				}
			}

			if ($found)
			{
				$opts = $params->get('sub_options');
				$opts->sub_values = $values;
				$opts->sub_labels = $labels;

				// $$$ rob dont json_encode this - the params object has its own toString() magic method
				$element->params = (string) $params;
				$element->store();
			}
		}

		return true;
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
		$ext = FabrikHelperHTML::isDebug() ? '.js' : '-min.js';
		$files = array('media/com_fabrik/js/element' . $ext, 'media/com_fabrik/js/elementlist' . $ext);

		foreach ($files as $file)
		{
			if (!in_array($file, $srcs))
			{
				$srcs[] = $file;
			}
		}

		parent::formJavascriptClass($srcs, $script, $shim);
	}

	/**
	 * used by elements with suboptions
	 *
	 * $$$ hugh - started working on adding this to elementlist, as we need to handle
	 * JSON-ified options for multiselect elements, which the main element model getLabelForValue()
	 * doesn't do.  But I need to sort out how this gets handled in rendering as well.
	 *
	 * @param   string  $v             Value
	 * @param   string  $defaultLabel  Default label
	 *
	 * @return  string	label
	 */

	public function notreadyyet_getLabelForValue($v, $defaultLabel = '')
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
		$multiple = $this->isMultiple();
		$vals = is_array($v) ? $v : FabrikWorker::JSONtoData($v, true);

		foreach ($vals as $val)
		{
			$l = JArrayHelper::getValue($labels, $val, $defaultLabel);

			if (trim($l) !== '')
			{
				if ($multiple && $this->renderWithHTML)
				{
					$lis[] = '<li>' . $l . '</li>';
				}
				else
				{
					$lis[] = $l;
				}
			}
		}

		$return = '';

		if (!empty($lis))
		{
			$return = ($multiple && $this->renderWithHTML) ? '<ul class="fabrikRepeatData">' . implode(' ', $lis) . '</ul>' : implode(' ', $lis);
		}

		/**
		 * $$$ rob if we allow adding to the dropdown but not recording
		 * then there will be no $key set to revert to the $val instead
		 */
		/*
		if ($v === $params->get('sub_default_value'))
		{
		$v = $params->get('sub_default_label');
		}
		return ($key === false) ? $v : JArrayHelper::getValue($labels, $key, $defaultLabel);
		*/
		return $return;
	}
}
