<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Content Component HTML Helper
 *
 * @static
 * @package     Joomla.Site
 * @subpackage  com_content
 * @since       1.5
 */
class JHtmlIcon
{
	public static function create($category, $params, $attribs = array(), $legacy = false)
	{
		if(version_compare(JVERSION, '3.0', 'ge')){
			JHtml::_('bootstrap.tooltip');
			$tip = 'hasTooltip';
		} else {
			JHtml::_('behavior.tooltip');
			$tip = 'hasTip';
		}
		
		$uri = JUri::getInstance();

		$url = 'index.php?option=com_content&task=article.add&return='.base64_encode($uri).'&a_id=0&catid=' . $category->id;

		if ($params->get('show_icons')) {
			$text = '<i class="icon-plus"></i> ' . JText::_('JNEW') . '&#160;';
		} else {
			$text = JText::_('JNEW').'&#160;';
		}

		// Add the button classes to the attribs array
		if (isset($attribs['class']))
		{
			$attribs['class'] = $attribs['class'] . ' btn btn-primary';
		}
		else
		{
			$attribs['class'] = 'btn btn-primary';
		}

		$button = JHtml::_('link', JRoute::_($url), $text, $attribs);

		$output = '<span class="' . $tip . '" title="'.JText::_('COM_CONTENT_CREATE_ARTICLE').'">'.$button.'</span>';
		return $output;
	}

	public static function email($article, $params, $attribs = array())
	{
		require_once JPATH_SITE . '/components/com_mailto/helpers/mailto.php';

		$uri      = JUri::getInstance();
		$base     = $uri->toString(array('scheme', 'host', 'port'));
		$template = JFactory::getApplication()->getTemplate();
		$link     = $base . JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catid), false);
		$url      = 'index.php?option=com_mailto&tmpl=component&template=' . $template . '&link=' . MailToHelper::addLink($link);
		$status   = 'width=400,height=350,menubar=yes,resizable=yes';

		if ($params->get('show_icons')) {
			$text = '<i class="icon-envelope"></i> ' . JText::_('JGLOBAL_EMAIL');
		} else {
			$text = JText::_('JGLOBAL_EMAIL');
		}

		$attribs['title']   = JText::_('JGLOBAL_EMAIL');
		$attribs['onclick'] = "window.open(this.href,'win2','" . $status . "'); return false;";

		$output = JHtml::_('link', JRoute::_($url), $text, $attribs);

		return $output;
	}

	/**
	 * Display an edit icon for the article.
	 *
	 * This icon will not display in a popup window, nor if the article is trashed.
	 * Edit access checks must be performed in the calling code.
	 *
	 * @param   object     $article  The article information
	 * @param   JRegistry  $params   The item parameters
	 * @param   array      $attribs  Optional attributes for the link
	 * @param   boolean    $legacy   True to use legacy images, false to use icomoon based graphic
	 *
	 * @return	string	The HTML for the article edit icon.
	 * @since	1.6
	 */
	public static function edit($article, $params, $attribs = array(), $legacy = false)
	{
		$user = JFactory::getUser();
		$uri  = JURI::getInstance();

		// Ignore if in a popup window.
		if ($params && $params->get('popup')) {
			return;
		}

		// Ignore if the state is negative (trashed).
		if ($article->state < 0) {
			return;
		}

		if(version_compare(JVERSION, '3.0', 'ge')){
			JHtml::_('bootstrap.tooltip');
			$tip = 'hasTooltip';
		} else {
			JHtml::_('behavior.tooltip');
			$tip = 'hasTip';
		}

		// Show checked_out icon if the article is checked out by a different user
		if (property_exists($article, 'checked_out') && property_exists($article, 'checked_out_time') && $article->checked_out > 0 && $article->checked_out != $user->get('id')) {
			$checkoutUser = JFactory::getUser($article->checked_out);
			$button = JHtml::_('image', 'system/checked_out.png', null, null, true);
			$date = JHtml::_('date', $article->checked_out_time);
			$tooltip = JText::_('JLIB_HTML_CHECKED_OUT').' :: '.JText::sprintf('COM_CONTENT_CHECKED_OUT_BY', $checkoutUser->name).' <br /> '.$date;
			return '<span class="' . $tip . '" title="' . htmlspecialchars($tooltip, ENT_COMPAT, 'UTF-8') . '">' . $button . '</span>';
		}

		$url	= 'index.php?option=com_content&task=article.edit&a_id='.$article->id.'&return='.base64_encode($uri);

		if ($article->state == 0) {
					$overlib = JText::_('JUNPUBLISHED');
				}
				else {
					$overlib = JText::_('JPUBLISHED');
				}

				$date = JHtml::_('date', $article->created);
				$author = $article->created_by_alias ? $article->created_by_alias : $article->author;

				$overlib .= '&lt;br /&gt;';
				$overlib .= $date;
				$overlib .= '&lt;br /&gt;';
				$overlib .= JText::sprintf('COM_CONTENT_WRITTEN_BY', htmlspecialchars($author, ENT_COMPAT, 'UTF-8'));

		$icon	= $article->state ? 'edit' : 'eye-close';
		$text = '<i class="' . $tip . ' icon-'.$icon.' tip" title="' . (method_exists('JHtml', 'tooltipText') ? JHtml::tooltipText(JText::_('COM_CONTENT_EDIT_ITEM'), $overlib, 0) : JText::_('COM_CONTENT_EDIT_ITEM') . ' :: ' . $overlib) . '"></i> '.JText::_('JGLOBAL_EDIT');

		$output = JHtml::_('link', JRoute::_($url), $text);

		return $output;
	}

	/**
	 * Method to generate a popup link to print an article
	 *
	 * @param   object     $article  The article information
	 * @param   JRegistry  $params   The item parameters
	 * @param   array      $attribs  Optional attributes for the link
	 * @param   boolean    $legacy   True to use legacy images, false to use icomoon based graphic
	 *
	 * @return  string  The HTML markup for the popup link
	 */
	public static function print_popup($article, $params, $attribs = array(), $legacy = false)
	{
		$url = ContentHelperRoute::getArticleRoute($article->slug, $article->catid);
		$url .= '&tmpl=component&print=1&layout=default&page=' . @ $request->limitstart;

		$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

		// checks template image directory for image, if non found default are loaded
		if ($params->get('show_icons')) {
			$text = '<i class="icon-print"></i> '.JText::_('JGLOBAL_PRINT');
		} else {
			$text = JText::_('JGLOBAL_PRINT');
		}

		$attribs['title'] = JText::_('JGLOBAL_PRINT');
		$attribs['onclick'] = "window.open(this.href,'win2','" . $status . "'); return false;";
		$attribs['rel'] = 'nofollow';

		return JHtml::_('link', JRoute::_($url), $text, $attribs);
	}

	/**
	 * Method to generate a link to print an article
	 *
	 * @param   object     $article  Not used, @deprecated for 4.0
	 * @param   JRegistry  $params   The item parameters
	 * @param   array      $attribs  Not used, @deprecated for 4.0
	 * @param   boolean    $legacy   True to use legacy images, false to use icomoon based graphic
	 *
	 * @return  string  The HTML markup for the popup link
	 */
	public static function print_screen($article, $params, $attribs = array(), $legacy = false)
	{
		// checks template image directory for image, if non found default are loaded
		if ($params->get('show_icons')) {
			$text = $text = '<i class="icon-print"></i> '.JText::_('JGLOBAL_PRINT');
		} else {
			$text = JText::_('JGLOBAL_PRINT');
		}
		return '<a href="#" onclick="window.print();return false;">'.$text.'</a>';
	}

}
