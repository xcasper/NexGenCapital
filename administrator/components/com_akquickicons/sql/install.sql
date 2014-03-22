CREATE TABLE IF NOT EXISTS `#__akquickicons_icons` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `catid` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `introtext` mediumtext NOT NULL,
  `fulltext` mediumtext NOT NULL,
  `images` text NOT NULL,
  `icon_class` varchar(255) NOT NULL,
  `version` int(10) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `modified` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `publish_up` datetime NOT NULL,
  `publish_down` datetime NOT NULL,
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `access` int(10) unsigned NOT NULL,
  `language` char(7) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_alias` (`alias`),
  KEY `idx_createdby` (`catid`),
  KEY `idx_language` (`language`),
  KEY `idx_checkout` (`checked_out`),
  KEY `cat_index` (`published`,`access`,`catid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

-- Category
INSERT INTO `#__categories` (`parent_id`, `lft`, `rgt`, `level`, `path`, `extension`, `title`, `alias`, `note`, `description`, `published`, `checked_out`, `checked_out_time`, `access`, `params`, `metadesc`, `metakey`, `metadata`, `created_user_id`, `created_time`, `modified_user_id`, `modified_time`, `hits`, `language`) VALUES
(1, 135, 136, 1, 'core-icons', 'com_akquickicons', 'Core Icons', 'core-icons', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', 908, '2012-08-12 04:03:49', 908, '2012-08-12 04:05:29', 0, '*');

-- Icons
INSERT INTO `#__akquickicons_icons` (`catid`, `title`, `alias`, `link`, `introtext`, `fulltext`, `images`, `icon_class`, `version`, `created`, `created_by`, `modified`, `modified_by`, `ordering`, `published`, `publish_up`, `publish_down`, `checked_out`, `checked_out_time`, `access`, `language`, `params`) VALUES
(88, 'Add New Article', 'add-new-article', 'index.php?option=com_content&task=article.add', '', '', 'images/quickicons/Primo-Icons/blog_add_48.png', 'akicon-plus', 0, '2012-08-12 04:03:16', 908, '0000-00-00 00:00:00', 0, 1, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 1, '*', '{"langkey":"MOD_QUICKICON_ADD_NEW_ARTICLE"}'),
(88, 'Article Manager', 'article-manager', 'index.php?option=com_content', '', '', 'images/quickicons/Primo-Icons/blog_compose_48.png', 'akicon-pencil', 0, '2012-08-12 04:09:56', 908, '0000-00-00 00:00:00', 0, 2, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 1, '*', '{"langkey":"MOD_QUICKICON_ARTICLE_MANAGER"}'),
(88, 'Category Manager', 'category-manager', 'index.php?option=com_categories&extension=com_content', '', '', 'images/quickicons/Primo-Icons/bookmark_48.png', 'akicon-folder-close', 0, '2012-08-12 04:14:42', 908, '0000-00-00 00:00:00', 0, 3, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 1, '*', '{"langkey":"MOD_QUICKICON_CATEGORY_MANAGER"}'),
(88, 'Media Manager', 'media-manager', 'index.php?option=com_media', '', '', 'images/quickicons/Primo-Icons/photo_48.png', 'akicon-picture', 0, '2012-11-15 18:48:13', 153, '0000-00-00 00:00:00', 0, 4, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 1, '*', '{"langkey":"MOD_QUICKICON_MEDIA_MANAGER"}'),
(88, 'Menu Manager', 'menu-manager', 'index.php?option=com_menus', '', '', 'images/quickicons/Primo-Icons/archive_48.png', 'akicon-th-list', 0, '2012-11-15 18:52:16', 153, '0000-00-00 00:00:00', 0, 5, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 1, '*', '{"langkey":"MOD_QUICKICON_MENU_MANAGER"}'),
(88, 'User Manager', 'user-manager', 'index.php?option=com_users', '', '', 'images/quickicons/Primo-Icons/user_group_48.png', 'akicon-user', 0, '2012-11-15 18:53:30', 153, '0000-00-00 00:00:00', 0, 6, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 1, '*', '{"langkey":"MOD_QUICKICON_USER_MANAGER"}'),
(88, 'Module Manager', 'module-manager', 'index.php?option=com_modules', '', '', 'images/quickicons/Primo-Icons/puzzle_48.png', 'akicon-th-large', 0, '2012-11-15 18:55:21', 153, '0000-00-00 00:00:00', 0, 7, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 1, '*', '{"langkey":"MOD_QUICKICON_MODULE_MANAGER"}'),
(88, 'Extension Manager', 'extension-manager', 'index.php?option=com_installer', '', '', 'images/quickicons/Primo-Icons/windows_48.png', 'akicon-hdd', 0, '2012-11-15 18:58:17', 153, '0000-00-00 00:00:00', 0, 8, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 1, '*', '{"langkey":"MOD_QUICKICON_EXTENSION_MANAGER"}'),
(88, 'Language Manager', 'language-manager', 'index.php?option=com_languages', '', '', 'images/quickicons/Primo-Icons/globe_48.png', 'akicon-globe', 0, '2012-11-15 18:59:27', 153, '0000-00-00 00:00:00', 0, 9, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 1, '*', '{"langkey":"MOD_QUICKICON_LANGUAGE_MANAGER"}'),
(88, 'Global Configuration', 'global-configuration', 'index.php?option=com_config', '', '', 'images/quickicons/Primo-Icons/gear_48.png', 'akicon-wrench', 0, '2012-11-15 19:00:54', 153, '0000-00-00 00:00:00', 0, 10, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 1, '*', '{"langkey":"MOD_QUICKICON_GLOBAL_CONFIGURATION"}'),
(88, 'Template Manager', 'template-manager', 'index.php?option=com_templates', '', '', 'images/quickicons/Primo-Icons/blackboard_48.png', 'akicon-eye-open', 0, '2012-11-15 19:04:02', 153, '0000-00-00 00:00:00', 0, 11, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 1, '*', '{"langkey":"MOD_QUICKICON_TEMPLATE_MANAGER"}');