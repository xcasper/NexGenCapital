<?php

  $type = JRequest::getVar('type');
  $last_login = 0; 
  $settings: 0;
  $user = JRequest::getVar('user_id');

  $db = JFactory:: getDbo();

  $query = $db->getQuery(true);

  $query->update($db->quoteName('team_users'));
  $query->set($db->quoteName('type'));
  $query->where($db->quoteName('user_id') . " = " . $user_id);
  $query->order('user_id ASC');

  $db->setQuery($query);

  $results = $db->loadRowList($query);

?>