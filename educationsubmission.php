<?php

  $education = JRequest::getVar('education');
  $institute = JRequest::getVar('institute');
  $from = JRequest::getVar('from');
  $from = JRequest::getVar('from');
  $user = JRequest::getVar('end');

  $db = JFactory:: getDbo();

  $query = $db->getQuery(true);

  $query->update($db->quoteName('team_education'));
  $query->set($db->quoteName('institute'));
  $query->where($db->quoteName('user_id') . " = " . $user_id);
  $query->order('user_id ASC');

  $db->setQuery($query);

  $results = $db->loadRowList($query);

  $query->update($db->quoteName('team_education'));
  $query->set($db->quoteName('time'));
  $query->where($db->quoteName('user_id') . " = " . $user_id);
  $query->order('user_id ASC');

  $db->setQuery($query);

  $results = $db->loadRowList($query);


?>