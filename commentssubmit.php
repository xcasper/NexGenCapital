<?php

  $name = JRequest::getVar('name');
  $number = JRequest::getVar('number');
  $relationship = JRequest::getVar('relationship');
  $user = JRequest::getVar('user_id');
  $my_id = JRequest::getVar('my_id');

  $db = JFactory:: getDbo();

  $query = $db->getQuery(true);

  $query->update($db->quoteName('team_references'));
  $query->set($db->quoteName('name'));
  $query->where($db->quoteName('user_id') . " = " . $user_id);
  $query->order('user_id ASC');

  $db->setQuery($query);

  $results = $db->loadRowList($query);

    $query->update($db->quoteName('team_references'));
  $query->set($db->quoteName('number'));
  $query->where($db->quoteName('user_id') . " = " . $user_id);
  $query->order('user_id ASC');

  $db->setQuery($query);

    $query->update($db->quoteName('team_references'));
  $query->set($db->quoteName('relationship'));
  $query->where($db->quoteName('user_id') . " = " . $user_id);
  $query->order('user_id ASC');

  $db->setQuery($query);

  $results = $db->loadRowList($query);

    $query->update($db->quoteName('team_references'));
  $query->set($db->quoteName('user'));
  $query->where($db->quoteName('user_id') . " = " . $user_id);
  $query->order('user_id ASC');

  $db->setQuery($query);

  $results = $db->loadRowList($query);

  $query->update($db->quoteName('team_references'));
  $query->set($db->quoteName('my_id'));
  $query->where($db->quoteName('user_id') . " = " . $user_id);
  $query->order('user_id ASC');

  $db->setQuery($query);

  $results = $db->loadRowList($query);

?>