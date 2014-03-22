<?php
/**
 * Main Plugin File
 * Does all the magic!
 *
 * @package         Tabs
 * @version         3.4.2
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2014 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

/**
 * System Plugin that places a Tabs code block into the text
 */
class plgSystemTabs extends JPlugin
{
	function __construct(&$subject, $config)
	{
		$this->_pass = 0;
		parent::__construct($subject, $config);
	}

	function onAfterRoute()
	{
		$this->_pass = 0;

		jimport('joomla.filesystem.file');
		if (JFile::exists(JPATH_PLUGINS . '/system/nnframework/helpers/protect.php'))
		{
			require_once JPATH_PLUGINS . '/system/nnframework/helpers/protect.php';
			// return if page should be protected
			if (NNProtect::isProtectedPage('tabs', 1))
			{
				return;
			}
		}

		// load the admin language file
		JFactory::getLanguage()->load('plg_' . $this->_type . '_' . $this->_name, JPATH_ADMINISTRATOR);

		// return if NoNumber Framework plugin is not installed
		if (!JFile::exists(JPATH_PLUGINS . '/system/nnframework/nnframework.php'))
		{
			if (JFactory::getApplication()->isAdmin() && JFactory::getApplication()->input->get('option') != 'com_login')
			{
				$msg = JText::_('TAB_NONUMBER_FRAMEWORK_NOT_INSTALLED')
					. ' ' . JText::sprintf('TAB_EXTENSION_CAN_NOT_FUNCTION', JText::_('TABS'));
				$mq = JFactory::getApplication()->getMessageQueue();
				foreach ($mq as $m)
				{
					if ($m['message'] == $msg)
					{
						$msg = '';
						break;
					}
				}
				if ($msg)
				{
					JFactory::getApplication()->enqueueMessage($msg, 'error');
				}
			}
			return;
		}

		// Load plugin parameters
		require_once JPATH_PLUGINS . '/system/nnframework/helpers/parameters.php';
		$parameters = NNParameters::getInstance();
		$params = $parameters->getPluginParams($this->_name);

		// allow in admin?
		if (!$params->enable_admin || JFactory::getApplication()->input->get('option') == 'com_plugins')
		{
			if (JFile::exists(JPATH_PLUGINS . '/system/nnframework/helpers/protect.php'))
			{
				require_once JPATH_PLUGINS . '/system/nnframework/helpers/protect.php';
				// return if current page is an admin page
				if (NNProtect::isAdmin())
				{
					return;
				}
			}
			else if (JFactory::getApplication()->isAdmin())
			{
				return;
			}
		}


		// Include the Helper
		require_once JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/helper.php';
		$class = get_class($this) . 'Helper';
		$this->helper = new $class ($params);

		$this->_pass = 1;
	}

	function onAfterDispatch()
	{
		if ($this->_pass)
		{
			$this->helper->onAfterDispatch();
		}
	}

	function onAfterRender()
	{
		if ($this->_pass)
		{
			$this->helper->onAfterRender();
		}
	}
}
