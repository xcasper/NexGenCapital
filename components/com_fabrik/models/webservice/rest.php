<?php
/**
 * REST web service
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * REST web service
 *
 * @package  Fabrik
 * @since    3.0
 */

class FabrikWebServiceRest extends FabrikWebService
{
	/**
	 * Constructor
	 *
	 * @param   array  $options  ini state
	 */

	public function __construct($options)
	{
		$this->options = $options;
	}

	/**
	 * Query the web service to get the data
	 *
	 * @param   string  $method      method to call at web service (soap only)
	 * @param   array   $options     key value filters to send to web service to filter the data
	 * @param   string  $startPoint  startPoint of actual data, if soap this is an xpath expression,
	 * otherwise its a key.key2.key3 string to traverse the returned data to arrive at the data to map to the fabrik list
	 * @param   string  $result      result method name - soap only, if not set then "$method . 'Result' will be used.
	 *
	 * @return	array	series of objects which can then be bound to the list using storeLocally()
	 */

	public function get($method, $options = array(), $startPoint = null, $result = null)
	{
		$url = $this->options['endpoint'];

		if (!strstr($url, '?'))
		{
			$url .= '?';
		}

		foreach ($options as $k => $v)
		{
			$url .= '&' . $k . '=' . $v;
		}

		$session = curl_init($url);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		$json = curl_exec($session);
		$phpObj = json_decode($json);

		if (!is_null($phpObj))
		{
			$startPoints = explode('.', $startPoint);

			foreach ($startPoints as $p)
			{
				$phpObj = &$phpObj->$p;
			}

			return $phpObj;
		}
		else
		{
			$error = (string) $json == '' ? 'Returned data not parsable as JSON' : (string) $json;
			throw new Exception($error, 500);

			return array();
		}
	}
}
