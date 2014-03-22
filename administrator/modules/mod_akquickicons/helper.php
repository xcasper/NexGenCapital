<?php
/**
 * @package		Asikart Joomla! Extansion Example
 * @subpackage	mod_example
 * @copyright	Copyright (C) 2012 Asikart.com, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

abstract class modAkquickiconsHelper
{
	public static $buttons = array() ;
	
	public static function getList(&$params)
	{
		$list = null ;
		self::$buttons = array();
		$date = JFactory::getDate( 'now' , JFactory::getConfig()->get('offset') ) ;
		$user = JFactory::getUser() ;
		$lang = JFactory::getLanguage();
		
		// Load Mod Quickicon language
		$lang->load('mod_quickicon', JPATH_BASE, null, false, false) || $lang->load('mod_quickicon', JPATH_BASE . '/modules/mod_quickicon', null, false, false)
            || $lang->load('mod_quickicon', JPATH_BASE, $lang->getDefault(), false, false)
            || $lang->load('mod_quickicon', JPATH_BASE . '/modules/mod_quickicon', $lang->getDefault(), false, false);
		
		
		$db   = JFactory::getDbo();
		$q    = $db->getQuery(true) ;
		
		$catid = $params->get('catid', array(78)) ;
		
		// v1.1.1
		$catid = is_array($catid) ? $catid : array($catid) ;
		
		
		// v1.1.3
		if( !in_array(1, $catid) ){
			$catid = implode(',', $catid);
			$q->where("a.catid IN ({$catid})") ;
		}
		
		
		$now = $date->toSQL(true);
		
		$publish = "( (a.publish_up = '0000-00-00 00:00:00' OR a.publish_up < '{$now}') AND
					(a.publish_down = '0000-00-00 00:00:00' OR a.publish_down > '{$now}') )" ;
		
		$viewlevel = $user->getAuthorisedViewLevels();
		$viewlevel = implode(',', $viewlevel);
		
		$language 	= JFactory::getLanguage()->getTag() ;
		$select 	= "a.*, b.*,
		a.link 		AS link,
		a.images 	AS images,
		a.title 	AS title,
		a.access 	AS access,
		a.alias		AS alias,
		a.params	AS params,
		b.title 	AS cat_title,
		b.alias	    AS cat_alias
		";
		
		$q->select($select)
			->from("#__akquickicons_icons AS a")
			->join('LEFT', "#__categories AS b ON a.catid = b.id")
			->where("b.extension = 'com_akquickicons'")
			->where("a.published >=1")
			->where($publish)
			->where("a.access IN ({$viewlevel})")
			->where("(a.language = '{$language}' OR a.language = '*')")
			->where("b.published > 0")
			->order("b.lft, a.ordering")
			;
		
		$db->setQuery($q);
		$buttons = $db->loadObjectList();
		
		foreach( $buttons as $button ):
			
			$button->params = new JRegistry($button->params);
			
			$uri = JFactory::getURI($button->link) ;
			
			// Smart URL
			if( $button->params->get('smart_url', 1) ) {
				$uri = self::smartUrlConvertor($uri, $button);
			}
			
			$catid = 1 ;
			$plugin = JPATH_ADMINISTRATOR.'/components/com_akquickicons/includes/plugins/pro/pro.php' ;
			if( JFile::exists($plugin) ) {
				$catid = $button->catid ;
			}
			
			self::$buttons[$catid][] = array(
				'link' 		=> JRoute::_($uri->toString()),
				'image' 	=> JURI::root().$button->images,
				'text' 		=> $button->params->get('langkey') ? JText::_($button->params->get('langkey')) : $button->title,
				'icon_class'=> $button->icon_class,
				'access' 	=> true,
				'cat_title' => $button->cat_title,
				'id' 		=> $button->params->get('id', 'akicon_' . str_replace('-', '_', $button->alias)),
				'class'     => $button->params->get('class', 'akicon'),
				'params' 	=> $button->params
			);
			
		endforeach;
		
		if($params->get('get_icon_from_plugins', 1)) {
			self::getIconFromPlugins($params);
		}
		
		return self::$buttons ;
	}
	
	/*
	 * function getIconFromPlugins
	 * @param $params
	 */
	
	public static function getIconFromPlugins($params)
	{
		// Include buttons defined by published quickicon plugins
		$keys = array_keys(self::$buttons);
		JPluginHelper::importPlugin('quickicon');
		$app = JFactory::getApplication();
		$arrays = (array) $app->triggerEvent('onGetIcons', array('mod_quickicon'));
		
		
		// Extensions plugin image map
		$root = JURI::root();
		$img_map = array(
			'asterisk' => $root.'images/quickicons/bluestock/icon-48-extension.png', // For Joomla!Update
			'download' => $root.'images/quickicons/bluestock/icon-48-download.png', // For Joomla! Extenaion Update
			'pictures' => $root.'images/quickicons/Primo-Icons/photo_48.png', // For JCE
            'header/icon-48-media.png' => $root.'images/quickicons/Primo-Icons/photo_48.png' // For JCE
		);
		
		
		foreach ($arrays as $response) {
			foreach ($response as $icon) {
				$default = array(
					'link' => null,
					'image' => 'cog',
					'text' => null,
					'access' => true
				);
				$icon = array_merge($default, $icon);
				if (!is_null($icon['link']) && !is_null($icon['text'])) {
					
					// Fit Joomla!3.0 icons
					if( JVERSION >= 3 ) {
						$icon['icon_class'] = 'icon-'.$icon['image'];
						
						if(array_key_exists($icon['image'], $img_map)) {
							$icon['image'] = $img_map[$icon['image']] ;
						}
					}else{
						$cur_template = JFactory::getApplication()->getTemplate();
						//$icon['image'] = JURI::base(true) .'/templates/'. $cur_template .'/images/'.$icon['image'];
					}
					
					// Set params
					if( isset($icon['params']) ){
						$icon['params'] = ( $icon['params'] instanceof JRegistry ) ? $icon['params'] : new JRegistry() ;
					}else{
						$icon['params'] = new JRegistry ;
					}
					
					self::$buttons[$keys[0]][] = $icon;
				}
			}
		}
		
		return self::$buttons ;
	}
	
	/*
	 * function smartUrlConvertor
	 * @param $url
	 */
	
	public static function smartUrlConvertor($uri, $button)
	{
		// fix task redirect
		if($uri->getVar('layout') == 'edit'){
			$uri->setVar('task', "{$uri->getVar('view')}.{$uri->getVar('layout')}") ;
			$uri->delVar('view');
			$uri->delVar('layout');
		}
		
		return $uri ;
	}
}
