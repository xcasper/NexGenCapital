<?php
/**
 * Plugin element to render an image already located on the server
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.image
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

/**
 * Plugin element to render an image already located on the server
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.image
 * @since       3.0
 */

class PlgFabrik_ElementImage extends PlgFabrik_Element
{
	/**
	 * Ignored folders
	 *
	 * @var array
	 */
	protected $ignoreFolders = array('cache', 'lib', 'install', 'modules', 'themes', 'upgrade', 'locks', 'smarty', 'tmp');

	/**
	 * Db table field type
	 *
	 * @var string
	 */
	protected $fieldDesc = 'TEXT';

	/**
	 * This really does get just the default value (as defined in the element's settings)
	 *
	 * @param   array  $data  form data
	 *
	 * @return mixed
	 */

	public function getDefaultValue($data = array())
	{
		if (!isset($this->default))
		{
			$params = $this->getParams();
			$element = $this->getElement();
			$w = new FabrikWorker;
			$this->default = $params->get('imagepath');

			// $$$ hugh - this gets us the default image, with the root folder prepended.
			// But ... if the root folder option is set, we need to strip it.
			$rootFolder = $params->get('selectImage_root_folder', '/');
			$rootFolder = JString::ltrim($rootFolder, '/');
			$rootFolder = JString::rtrim($rootFolder, '/') . '/';
			$this->default = preg_replace("#^$rootFolder#", '', $this->default);
			$this->default = $w->parseMessageForPlaceHolder($this->default, $data);

			if ($element->eval == "1")
			{
				$this->default = @eval((string) stripslashes($this->default));
				FabrikWorker::logEval($this->default, 'Caught exception on eval in ' . $element->name . '::getDefaultValue() : %s');
			}
		}

		return $this->default;
	}

	/**
	 * Helper method to get the default value used in getValue()
	 * For readonly elements:
	 *    If the form is new we need to get the default value
	 *    If the form is being edited we don't want to get the default value
	 * Otherwise use the 'use_default' value in $opts, defaulting to true
	 *
	 * Overrides element model as in edit/view details the image should be loaded regardless of $this->isEditable() #GH-527
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
		/**
		 * $$$rob - if no search form data submitted for the search element then the default
		 * selection was being applied instead
		 * otherwise get the default value so if we don't find the element's value in $data we fall back on this value
		 */
		return JArrayHelper::getValue($opts, 'use_default', true) == false ? '' : $this->getDefaultValue($data);
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
		$w = new FabrikWorker;
		$data = FabrikWorker::JSONtoData($data, true);
		$params = $this->getParams();
		$pathset = false;

		foreach ($data as $d)
		{
			if (strstr($d, '/'))
			{
				$pathset = true;
				break;
			}
		}

		if ($data === '' || empty($data) || !$pathset)
		{
			// No data so default to image (or simple image name stored).
			$iPath = $params->get('imagepath');

			if (!strstr($iPath, '/'))
			{
				// Single file specified so find it in tmpl folder
				$data = (array) FabrikHelperHTML::image($iPath, 'list', @$this->tmpl, array(), true);
			}
			else
			{
				$data = (array) $iPath;
			}
		}

		$selectImage_root_folder = $this->rootFolder();

		// $$$ hugh - tidy up a bit so we don't have so many ///'s in the URL's
		$selectImage_root_folder = JString::ltrim($selectImage_root_folder, '/');
		$selectImage_root_folder = JString::rtrim($selectImage_root_folder, '/');
		$selectImage_root_folder = $selectImage_root_folder === '' ? '' : $selectImage_root_folder . '/';

		$showImage = $params->get('show_image_in_table', 0);
		$linkURL = $params->get('link_url', '');

		if (empty($data) || $data[0] == '')
		{
			$data[] = $params->get('imagepath');
		}

		for ($i = 0; $i < count($data); $i++)
		{
			if ($showImage)
			{
				// $$$ rob 30/06/2011 - say if we import via csv a url to the image check that and use that rather than the relative path
				$src = JString::substr($data[$i], 0, 4) == 'http' ? $data[$i] : COM_FABRIK_LIVESITE . $selectImage_root_folder . $data[$i];
				$data[$i] = '<img src="' . $src . '" alt="' . $data[$i] . '" />';
			}

			if ($linkURL)
			{
				$data[$i] = '<a href="' . $linkURL . '" target="_blank">' . $data[$i] . '</a>';
			}

			$data[$i] = $w->parseMessageForPlaceHolder($data[$i], $thisRow);
		}

		$data = json_encode($data);

		return parent::renderListData($data, $thisRow);
	}

	/**
	 * Manupulates posted form data for insertion into database
	 *
	 * @param   mixed  $val   this elements posted form data
	 * @param   array  $data  posted form data
	 *
	 * @return  mixed
	 */

	public function storeDatabaseFormat($val, $data)
	{
		$groupModel = $this->getGroup();
		$params = $this->getParams();
		$selectImage_root_folder = $params->get('selectImage_root_folder', '');
		$key = $this->getFullName(true, false);

		if (!array_key_exists($key, $data))
		{
			$element = $this->getElement();
			$key = $element->name;
		}

		if ($groupModel->canRepeat() && !$groupModel->isJoin())
		{
			if ($groupModel->isJoin())
			{
				// @TODO - not tested with join group data
			}

			if (!array_key_exists($key . '_folder', $data))
			{
				$retval = json_encode($data[$key]);
			}
			else
			{
				$retvals = array();

				foreach ($data[$key] as $k => $v)
				{
					$retvals[] = preg_replace("#^$selectImage_root_folder#", '', $data[$key . '_folder'][$k]) . $data[$key . '_image'][$k];
				}

				$retval = json_encode($retvals);
			}
		}
		else
		{
			/* $$$ hugh - if we're using default image, no user selection,
			 * the _folder and _image won't exist,
			 * we'll just have the relative path in the element $key
			 */
			if (!array_key_exists($key . '_image', $data))
			{
				$retval = $data[$key];
			}
			else
			{
				$retval = preg_replace("#^$selectImage_root_folder#", '', $data[$key]);
			}
		}

		return $retval;
	}

	/**
	 * Shows the data formatted for RSS export
	 *
	 * @param   string  $data     Data
	 * @param   object  $thisRow  All the data in the tables current row
	 *
	 * @return string formatted value
	 */

	public function renderListData_rss($data, $thisRow)
	{
		$params = $this->getParams();
		$selectImage_root_folder = $params->get('selectImage_root_folder', '');
		$selectImage_root_folder = JString::ltrim($selectImage_root_folder, '/');
		$selectImage_root_folder = JString::rtrim($selectImage_root_folder, '/');
		$selectImage_root_folder = $selectImage_root_folder === '' ? '' : $selectImage_root_folder . '/';

		return '<img src="' . COM_FABRIK_LIVESITE . $selectImage_root_folder . $data . '" />';
	}

	/**
	 * Draws the html form element
	 *
	 * @param   array  $data           to preopulate element with
	 * @param   int    $repeatCounter  repeat group counter
	 *
	 * @return  string	elements html
	 */

	public function render($data, $repeatCounter = 0)
	{
		$params = $this->getParams();
		$name = $this->getHTMLName($repeatCounter);
		$value = $this->getValue($data, $repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		$rootFolder = $this->rootFolder($value);
		$value = str_replace($rootFolder, '', $value);

		// $$$ rob - 30/06/2011 can only select an image if its not a remote image
		$canSelect = ($params->get('image_front_end_select', '0') && JString::substr($value, 0, 4) !== 'http');

		// $$$ hugh - tidy up a bit so we don't have so many ///'s in the URL's
		$rootFolder = JString::ltrim($rootFolder, '/');
		$rootFolder = JString::rtrim($rootFolder, '/');
		$rootFolder = $rootFolder === '' ? '' : $rootFolder . '/';

		// $$$ rob - 30/062011 allow for full urls in the image. (e.g from csv import)
		$defaultImage = JString::substr($value, 0, 4) == 'http' ? $value : COM_FABRIK_LIVESITE . $rootFolder . $value;

		$float = $params->get('image_float');
		$float = $float != '' ? "style='float:$float;'" : '';
		$str = array();
		$str[] = '<div class="fabrikSubElementContainer" id="' . $id . '">';

		$rootFolder = str_replace('/', DS, $rootFolder);

		if ($canSelect && $this->isEditable())
		{
			$str[] = '<img src="' . $defaultImage . '" alt="' . $value . '" ' . $float . ' class="imagedisplayor"/>';

			if (array_key_exists($name, $data))
			{
				if (trim($value) == '' && $rootFolder === '')
				{
					$path = "/";
				}
				else
				{
					$bits = explode("/", $value);

					if (count($bits) > 1)
					{
						$path = '/' . array_shift($bits) . '/';
						$path = $rootFolder . $path;
						$val = array_shift($bits);
					}
					else
					{
						$path = $rootFolder;
					}
				}
			}
			else
			{
				$path = $rootFolder;
			}

			$images = array();
			$imagenames = (array) JFolder::files(JPATH_SITE . '/' . $path);

			foreach ($imagenames as $n)
			{
				$images[] = JHTML::_('select.option', $n, $n);
			}

			// $$$rob not sure about his name since we are adding $repeatCounter to getHTMLName();
			$imageName = $this->getGroupModel()->canRepeat() ? FabrikString::rtrimWord($name, "][$repeatCounter]") . "_image][$repeatCounter]"
				: $id . '_image';
			$bits = explode('/', $value);
			$image = array_pop($bits);

			// $$$ hugh - append $rootFolder to JPATH_SITE, otherwise we're showing folders
			// they aren't supposed to be able to see.
			$folders = JFolder::folders(JPATH_SITE . DS . $rootFolder);

			// @TODO - if $folders is empty, hide the button/widget?  All they can do is select
			// from the initial image dropdown list, so no point having the widget for changing folder?
			$str[] = '<br/>' . JHTML::_('select.genericlist', $images, $imageName, 'class="inputbox imageselector" ', 'value', 'text', $image);
			$str[] = FabrikHelperHTML::folderAjaxSelect($folders);
			$str[] = '<input type="hidden" name="' . $name . '" value="' . $value . '" class="fabrikinput hiddenimagepath folderpath" />';
		}
		else
		{
			$w = new FabrikWorker;
			$value = $w->parseMessageForPlaceHolder($value, $data);
			$linkURL = $params->get('link_url', '');
			$imgstr = '<img src="' . $defaultImage . '" alt="' . $value . '" ' . $float . ' class="imagedisplayor"/>' . "\n";

			if ($linkURL)
			{
				$imgstr = '<a href="' . $linkURL . '" target="_blank">' . $imgstr . '</a>';
			}

			$str[] = $imgstr;
			$str[] = '<input type="hidden" name="' . $name . '" value="' . $value . '" class="fabrikinput hiddenimagepath folderpath" />';
		}

		$str[] = '</div>';

		return implode("\n", $str);
	}

	/**
	 * On Ajax files
	 *
	 * @return  void
	 */

	public function onAjax_files()
	{
		$this->loadMeForAjax();
		$app = JFactory::getApplication();
		$folder = $app->input->get('folder', '', 'string');

		if (!strstr($folder, JPATH_SITE))
		{
			$folder = JPATH_SITE . '/' . $folder;
		}

		$pathA = JPath::clean($folder);
		$folder = array();
		$files = array();
		$images = array();
		FabrikWorker::readImages($pathA, "/", $folders, $images, $this->ignoreFolders);

		if (!array_key_exists('/', $images))
		{
			$images['/'] = array();
		}

		echo json_encode($images['/']);
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
		$app = JFactory::getApplication();
		$params = $this->getParams();
		$element = $this->getElement();
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts->rootPath = $this->rootFolder();
		$opts->canSelect = (bool) $params->get('image_front_end_select', false);
		$opts->id = $element->id;
		$opts->ds = DS;
		$opts->dir = JPATH_SITE . '/' . str_replace('/', DS, $opts->rootPath);

		return array('FbImage', $id, $opts);
	}

	/**
	 * Get the root folder for images
	 *
	 * @param   string  $value  Value
	 *
	 * @return  string  root folder
	 */

	protected function rootFolder($value = '')
	{
		$rootFolder = '';
		$params = $this->getParams();
		$canSelect = ($params->get('image_front_end_select', '0') && JString::substr($value, 0, 4) !== 'http');
		$defaultImg = $params->get('imagepath');

		if ($canSelect && (JFolder::exists($defaultImg) || JFolder::exists(COM_FABRIK_BASE . $defaultImg)))
		{
			$rootFolder = $defaultImg;
		}

		return $rootFolder;
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
		return $this->render($data);
	}

	/**
	 * Does the element conside the data to be empty
	 * Used in isempty validation rule
	 *
	 * $$$ hugh - right now this is the default code, here as a reminder we
	 * need to fix this so it makes sensible decisions about 'empty' image
	 *
	 * @param   array  $data           data to test against
	 * @param   int    $repeatCounter  repeat group #
	 *
	 * @return  bool
	 */

	public function dataConsideredEmpty($data, $repeatCounter)
	{
		return ($data == '') ? true : false;
	}
}
