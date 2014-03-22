<?php
/**
 * Plugin Helper File
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

// Load common functions
require_once JPATH_PLUGINS . '/system/nnframework/helpers/text.php';
require_once JPATH_PLUGINS . '/system/nnframework/helpers/tags.php';
require_once JPATH_PLUGINS . '/system/nnframework/helpers/protect.php';

/**
 * Plugin that replaces stuff
 */
class plgSystemTabsHelper
{
	function __construct(&$params)
	{
		$this->params = $params;

		$this->params->comment_start = '<!-- START: Tabs -->';
		$this->params->comment_end = '<!-- END: Tabs -->';

		$bts = '((?:<[a-zA-Z][^>]*>\s*){0,3})'; // break tags start
		$bte = '((?:\s*<(?:/[a-zA-Z]|br|BR)[^>]*>){0,3})'; // break tags end

		$this->params->tag_open = preg_replace('#[^a-z0-9-_]#si', '', $this->params->tag_open);
		$this->params->tag_close = preg_replace('#[^a-z0-9-_]#si', '', $this->params->tag_close);
		$this->params->tag_link = preg_replace('#[^a-z0-9-_]#si', '', $this->params->tag_link);
		$this->params->tag_delimiter = ($this->params->tag_delimiter == 'space') ? '(?: |&nbsp;)' : '=';

		$this->params->regex = '#'
			. $bts
			. '\{(' . $this->params->tag_open . 's?'
			. '((?:-[a-zA-Z0-9-_]+)?)'
			. $this->params->tag_delimiter
			. '((?:[^\}]*?\{[^\}]*?\})*[^\}]*?)|/' . $this->params->tag_close
			. '(?:-[a-z0-9-_]*)?)\}'
			. $bte
			. '#s';
		$this->params->regex_end = '#'
			. $bts
			. '\{/' . $this->params->tag_close
			. '(?:-[a-z0-9-_]+)?\}'
			. $bte
			. '#s';
		$this->params->regex_link = '#'
			. '\{' . $this->params->tag_link
			. '(?:-[a-z0-9-_]+)?' . $this->params->tag_delimiter
			. '([^\}]*)\}'
			. '(.*?)'
			. '\{/' . $this->params->tag_link . '\}'
			. '#s';

		$this->ids = array();
		$this->matches = array();
		$this->allitems = array();
		$this->setcount = 0;

		$this->mainclass = array();
		if ($this->params->load_stylesheet == 2)
		{
			$this->mainclass[] = 'oldschool';
			$this->params->use_responsive_view = 0;
		}
		else
		{
			if ($this->params->outline_handles)
			{
				$this->mainclass[] = 'outline_handles';
			}
			if ($this->params->outline_content)
			{
				$this->mainclass[] = 'outline_content';
			}
			if (!$this->params->alignment)
			{
				$this->params->alignment = JFactory::getLanguage()->isRTL() ? 'right' : 'left';
			}
			$this->mainclass[] = 'align_' . $this->params->alignment;
		}

	}

	function onAfterDispatch()
	{
		// PDF
		if (JFactory::getDocument()->getType() == 'pdf')
		{
			$buffer = JFactory::getDocument()->getBuffer('component');
			if (is_array($buffer))
			{
				if (isset($buffer['component'], $buffer['component']['']))
				{
					if (isset($buffer['component']['']['component'], $buffer['component']['']['component']['']))
					{
						$this->replaceTags($buffer['component']['']['component'][''], 0);
					}
					else
					{
						$this->replaceTags($buffer['component'][''], 0);
					}
				}
				else if (isset($buffer['0'], $buffer['0']['component'], $buffer['0']['component']['']))
				{
					if (isset($buffer['0']['component']['']['component'], $buffer['0']['component']['']['component']['']))
					{
						$this->replaceTags($buffer['component']['']['component'][''], 0);
					}
					else
					{
						$this->replaceTags($buffer['0']['component'][''], 0);
					}
				}
			}
			else
			{
				$this->replaceTags($buffer, 0);
			}
			JFactory::getDocument()->setBuffer($buffer, 'component');
			return;
		}

		// only in html
		if (JFactory::getDocument()->getType() !== 'html' && JFactory::getDocument()->getType() !== 'feed')
		{
			return;
		}

		$buffer = JFactory::getDocument()->getBuffer('component');

		if (empty($buffer) || is_array($buffer))
		{
			return;
		}

		// do not load scripts/styles on print page
		if (JFactory::getDocument()->getType() !== 'feed' && !JFactory::getApplication()->input->getInt('print', 0))
		{
			if ($this->params->load_bootstrap_framework)
			{
				JHtml::_('bootstrap.framework');
			}
			$script = '
				var nn_tabs_urlscroll = 0;
				var nn_tabs_use_hash = ' . (int) $this->params->use_hash . ';
			';
			JFactory::getDocument()->addScriptDeclaration('/* START: Tabs scripts */ ' . preg_replace('#\n\s*#s', ' ', trim($script)) . ' /* END: Tabs scripts */');
			JHtml::script('tabs/script.min.js', false, true);
			if ($this->params->load_stylesheet == 2)
			{
				JHtml::stylesheet('tabs/old.min.css', false, true);
			}
			else if ($this->params->load_stylesheet)
			{
				JHtml::stylesheet('tabs/style.min.css', false, true);
			}
		}

		if (strpos($buffer, '{' . $this->params->tag_open) === false && strpos($buffer, '{' . $this->params->tag_link) === false)
		{
			return;
		}

		$this->replaceTags($buffer);

		JFactory::getDocument()->setBuffer($buffer, 'component');
	}

	function onAfterRender()
	{
		// only in html and feeds
		if (JFactory::getDocument()->getType() !== 'html' && JFactory::getDocument()->getType() !== 'feed')
		{
			return;
		}

		$html = JResponse::getBody();
		if ($html == '')
		{
			return;
		}

		if (strpos($html, '{' . $this->params->tag_open) === false)
		{
			if (strpos($html, 'class="nn_tabs-tab') === false)
			{
				// remove style and script if no items are found
				$html = preg_replace('#\s*<' . 'link [^>]*href="[^"]*/(tabs/css|css/tabs)/[^"]*\.css[^"]*"[^>]* />#s', '', $html);
				$html = preg_replace('#\s*<' . 'script [^>]*src="[^"]*/(tabs/js|js/tabs)/[^"]*\.js[^"]*"[^>]*></script>#s', '', $html);
				$html = preg_replace('#/\* START: Tabs .*?/\* END: Tabs [a-z]* \*/\s*#s', '', $html);
			}
		}
		else
		{
			// only do stuff in body
			list($pre, $body, $post) = nnText::getBody($html);
			$this->replaceTags($body);
			$html = $pre . $body . $post;
		}
		$this->cleanLeftoverJunk($html);

		JResponse::setBody($html, 1, 1);
	}

	function replaceTags(&$str, $checkprint = 1, $last = 0)
	{
		if (!is_string($str) || $str == '')
		{
			return;
		}

		$this->protect($str);

		$print = $checkprint ? (!JFactory::getApplication()->input->getInt('print', 0)) : 0;

		if (!$print || ($last && strpos($str, '{/' . $this->params->tag_close) === false && strpos($str, 'class="nn_tabs_container') === false))
		{
			// Replace syntax with general html on print pages
			if (preg_match_all($this->params->regex, $str, $matches, PREG_SET_ORDER) > 0)
			{
				foreach ($matches as $match)
				{
					$title = NNText::cleanTitle($match['4']);
					if (!(strpos($title, '|') === false))
					{
						list($title, $extra) = explode('|', $title, 2);
					}
					$title = trim($title);
					$name = NNText::cleanTitle($title, 1);
					$title = preg_replace('#<\?h[0-9](\s[^>]* )?>#', '', $title);
					$replace = '<' . $this->params->title_tag . ' class="nn_tabs-title"><a name="' . $name . '" class="anchor"></a>' . $title . '</' . $this->params->title_tag . '>';
					$str = str_replace($match['0'], $replace, $str);
				}
			}
			if (preg_match_all($this->params->regex_end, $str, $matches, PREG_SET_ORDER) > 0)
			{
				foreach ($matches as $match)
				{
					$str = str_replace($match['0'], '', $str);
				}
			}
			if (preg_match_all($this->params->regex_link, $str, $matches, PREG_SET_ORDER) > 0)
			{
				foreach ($matches as $match)
				{
					$href = NNText::getURI($match['1']);
					$link = '<a href="' . $href . '">' . $match['2'] . '</a>';
					$str = str_replace($match['0'], $link, $str);
				}
			}
			NNProtect::unprotect($str);
			return;
		}

		$sets = array();
		$setids = array();

		if (preg_match_all($this->params->regex, $str, $matches, PREG_SET_ORDER) > 0)
		{
			foreach ($matches as $match)
			{
				if ($match['2']['0'] == '/')
				{
					array_pop($setids);
					continue;
				}
				end($setids);
				$item = new stdClass;
				$item->orig = $match['0'];
				$item->setid = trim(str_replace('-', '_', $match['3']));
				if (empty($setids) || current($setids) != $item->setid)
				{
					$this->setcount++;
					$setids[$this->setcount . '.'] = $item->setid;
				}
				$item->set = str_replace('__', '_', array_search($item->setid, array_reverse($setids)) . $item->setid);
				$item->title = NNText::cleanTitle($match['4']);
				list($item->pre, $item->post) = NNTags::setSurroundingTags($match['1'], $match['5']);
				if (!isset($sets[$item->set]))
				{
					$sets[$item->set] = array();
				}
				$sets[$item->set][] = $item;
			}
		}

		$urlitem = JFactory::getApplication()->input->getString('tab', '', 'default', 1);
		$urlitem = trim($urlitem);
		if (is_numeric($urlitem))
		{
			$urlitem = '1-' . $urlitem;
		}
		$active_url = '';


		foreach ($sets as $set_id => $items)
		{
			$active_by_url = '';
			$active_by_cookie = '';
			$active = 0;
			foreach ($items as $i => $item)
			{
				$tag = NNTags::getTagValues($item->title);
				$item->title = $tag->title;
				$item->alias = isset($tag->alias) ? $tag->alias : '';
				$item->active = 0;

				foreach ($tag->params as $j => $val)
				{
					if ($val && strpos($val, ' ') !== false)
					{
						$vals = explode(' ', $val);
						foreach ($vals as $v)
						{
							$tag->params[] = $v;
						}
						unset($tag->params[$j]);
					}
				}
				foreach ($tag->params as $j => $val)
				{
					if ($val && in_array($val, array('active', 'opened', 'open')))
					{
						$active = $i;
						$item->active = 1;
						unset($tag->params[$j]);
					}
				}
				$item->classes = $tag->params;
				$item->class = trim(implode(' ', $item->classes));

				$item->set = (int) $set_id;
				$item->count = $i + 1;
				$item->haslink = preg_match('#<a [^>]*>.*?</a>#usi', $item->title);


				$item->title_full = $item->title;
				$item->title = NNText::cleanTitle($item->title, 1);
				if ($item->title == '')
				{
					$item->title = NNText::getAttribute('title', $item->title_full);
					if ($item->title == '')
					{
						$item->title = NNText::getAttribute('alt', $item->title_full);
					}
				}
				$item->title = str_replace(array('&nbsp;', '&#160;'), ' ', $item->title);
				$item->title = preg_replace('#\s+#', ' ', $item->title);

				$item->alias = NNText::createAlias($item->alias ? $item->alias : $item->title);

				$item->id = $this->createId($item->alias);

				$item->matches = NNText::createUrlMatches(array($item->id, $item->title));
				$item->matches[] = ($i + 1) . '';
				$item->matches[] = $item->set . '-' . ($i + 1);

				$item->matches = array_unique($item->matches);
				$item->matches = array_diff($item->matches, $this->matches);
				$this->matches = array_merge($this->matches, $item->matches);

				if ($urlitem != '' && (in_array($urlitem, $item->matches, 1) || in_array(strtolower($urlitem), $item->matches, 1)))
				{
					if (!$item->haslink)
					{
						$active_by_url = $i;
					}
				}
				if (!$item->active && $active == $i && $item->haslink)
				{
					$active++;
				}

				$sets[$set_id][$i] = $item;
				$this->allitems[] = $item;
			}

			$active = (int) $active;

			if ($active_by_url !== '' && isset($sets[$set_id][$active_by_url]))
			{
				$sets[$set_id][$active_by_url]->active = 1;
				$active_url = $sets[$set_id][$active_by_url]->id;
			}
			else if (isset($sets[$set_id][$active]))
			{
				$sets[$set_id][$active]->active = 1;
			}
			else
			{
				$sets[$set_id]['0']->active = 1;
			}
		}

		if (preg_match($this->params->regex_end, $str))
		{
			$aclass = 'anchor';

			foreach ($sets as $items)
			{
				$first = key($items);
				end($items);
				foreach ($items as $i => &$item)
				{
					$s = '#' . preg_quote($item->orig, '#') . '#';
					if (@preg_match($s . 'u', $str))
					{
						$s .= 'u';
					}
					if (preg_match($s, $str, $match))
					{
						$html = array();
						$html[] = $item->post;
						$html[] = $item->pre;
						if ($i == $first)
						{
							$classes = $this->mainclass;

							// Set overruling main classes
							if (in_array('nooutline', $item->classes))
							{
								$classes = array_diff($classes, array('outline_handles', 'outline_content'));
								$item->classes = array_diff($classes, array('nooutline', 'outline_handles=0', 'outline_content=0'));
							}
							else
							{
								if (in_array('outline_handles=0', $item->classes))
								{
									$classes = array_diff($classes, array('outline_handles'));
									$item->classes = array_diff($classes, array('outline_handles=0'));
								}
								if (in_array('outline_content=0', $item->classes))
								{
									$classes = array_diff($classes, array('outline_content'));
									$item->classes = array_diff($classes, array('outline_content=0'));
								}
							}

							if (in_array('align_left', $item->classes))
							{
								$classes = array_diff($classes, array('align_left', 'align_right', 'align_center', 'align_justify'));
								$item->classes = array_diff($item->classes, array('align_left', 'align_right', 'align_center', 'align_justify'));
								$classes[] = 'align_left';
							}
							else if (in_array('align_right', $item->classes))
							{
								$classes = array_diff($classes, array('align_left', 'align_right', 'align_center', 'align_justify'));
								$item->classes = array_diff($item->classes, array('align_left', 'align_right', 'align_center', 'align_justify'));
								$classes[] = 'align_right';
							}
							else if (in_array('align_center', $item->classes))
							{
								$classes = array_diff($classes, array('align_left', 'align_right', 'align_center', 'align_justify'));
								$item->classes = array_diff($item->classes, array('align_left', 'align_right', 'align_center', 'align_justify'));
								$classes[] = 'align_center';
							}
							else if (in_array('align_justify', $item->classes))
							{
								$classes = array_diff($classes, array('align_left', 'align_right', 'align_center', 'align_justify'));
								$item->classes = array_diff($item->classes, array('align_left', 'align_right', 'align_center', 'align_justify'));
								$classes[] = 'align_justify';
							}

							$item->class = trim(implode(' ', $item->classes));

							$html[] = '<div class="' . trim('nn_tabs ' . implode(' ', $classes)) . '">';
							$html[] = $this->getNav($items);
							$html[] = '<div class="tab-content">';
						}
						else
						{
							$html[] = '</div>';
						}

						$class = array();
						$class[] = 'tab-pane';
						if ($item->active)
						{
							$class[] = 'active';
						}
						$class[] = trim($item->class);

						$html[] = '<div class="' . trim(implode(' ', $class)) . '" id="' . $item->id . '">';

						if (!$item->haslink)
						{
							$html[] = '<' . $this->params->title_tag . ' class="nn_tabs-title">'
								. '<a name="' . $item->id . '" class="' . $aclass . '"></a>'
								. $item->title . '</' . $this->params->title_tag . '>';
						}

						$html = implode("\n", $html);
						$str = NNText::strReplaceOnce($match['0'], $html, $str);
					}
				}
			}
		}

		// closing tag
		if (preg_match_all($this->params->regex_end, $str, $matches, PREG_SET_ORDER) > 0)
		{
			$script = '';
			if ($active_url)
			{
				$script .= 'nnTabs.show(\'' . $active_url . '\');';
			}
			if ($script)
			{
				$script = '<script type="text/javascript">'
					. 'jQuery(document).ready(function(){ '
					. $script
					. ' });'
					. '</script>';
				if ($script)
				{
					$str = preg_replace($this->params->regex_end, $script . '\0', $str, 1);
				}
			}
			foreach ($matches as $match)
			{
				$html = '</div></div></div>';
				list($pre, $post) = NNTags::setSurroundingTags($match['1'], $match['2']);
				$html = $pre . $html . $post;
				$str = NNText::strReplaceOnce($match['0'], $html, $str);
			}
		}

		// link tag
		if (preg_match_all($this->params->regex_link, $str, $matches, PREG_SET_ORDER) > 0)
		{
			foreach ($matches as $match)
			{
				$linkitem = 0;
				$names = NNText::createUrlMatches(array($match['1']));
				foreach ($names as $name)
				{
					if (is_numeric($name))
					{
						foreach ($this->allitems as $item)
						{
							if (in_array($name, $item->matches, 1) || in_array((int) $name, $item->matches, 1))
							{
								$linkitem = $item;
								break;
							}
						}
					}
					else
					{
						foreach ($this->allitems as $item)
						{
							if (in_array($name, $item->matches, 1) || in_array(strtolower($name), $item->matches, 1))
							{
								$linkitem = $item;
								break;
							}
						}
					}
					if ($linkitem)
					{
						break;
					}
				}
				if ($linkitem)
				{
					$href = NNText::getURI($linkitem->id);
					$onclick = 'nnTabs.show(this.rel);return false;';
					$html = '<a href="' . $href . '"'
						. ' class="nn_tabs-link nn_tabs-link-' . $linkitem->alias . '"'
						. ' rel="' . $linkitem->id . '"'
						. ' onclick="' . $onclick . '"><span class="nn_tabs-link-inner">' . $match['2'] . '</span></a>';
				}
				else
				{
					$href = NNText::getURI($name);
					$html = '<a href="' . $href . '">' . $match['2'] . '</a>';
				}
				$str = NNText::strReplaceOnce($match['0'], $html, $str);
			}
		}
		NNProtect::unprotect($str);
	}

	function getNav(&$items)
	{
		$html = array();


		// Nav for non-mobile view
		$html[] = '<ul class="nav nav-tabs" id="set-nn_tabs-' . $items['0']->set . '">';
		foreach ($items as $item)
		{
			$html[] = '<li class="' . trim('nn_tabs-tab ' . ($item->active ? 'active' : '') . ' ' . trim($item->class)) . '">';
			if ($item->haslink)
			{
				$html[] = $item->title_full;
			}
			else
			{
				$html[] = '<a href="#' . $item->id . '" data-toggle="tab"'
					. '>'
					. '<span class="nn_tabs-toggle-inner">'
					. $item->title_full
					. '</span></a>';
			}
			$html[] = '</li>';
		}
		$html[] = '</ul>';

		return implode("\n", $html);
	}

	function createId($alias)
	{
		$id = $alias;

		$i = 1;
		while (in_array($id, $this->ids))
		{
			$id = $alias . '-' . ++$i;
		}

		$this->ids[] = $id;

		return $id;
	}

	function protect(&$str)
	{
		NNProtect::protectFields($str);
		NNProtect::protectSourcerer($str);
	}

	/**
	 * Just in case you can't figure the method name out: this cleans the left-over junk
	 */
	function cleanLeftoverJunk(&$str)
	{
		NNProtect::removeFromHtmlTagContent($str, array($this->params->tag_open, $this->params->tag_close, $this->params->tag_link));
		NNProtect::removeInlineComments($str, 'Tabs');
	}
}
