<?php 

function get_user($user_id) {

	$db = JFactory:: getDbo();

	$query = $db->getQuery(true);

	$query->select($db->quoteName('user_id'));
	$query->from($db->quoteName('team_users'));
	$query->where($db->quoteName('user_id') . " = " . $user_id);
	$query->order('user_id ASC');

	$db->setQuery($query);

	$results = $db->loadRowList($query);

}

function get_user_values($user_id) {

	$db = JFactory:: getDbo();

	$query = $db->getQuery(true);

	$query->select($db->quoteName(array('user_id', 'username', 'password', 'permission', 'points', 'type', 'last_login', 'settings')));
	$query->from($db->quoteName('team_users'));
	$query->where($db->quoteName('user_id') . " = " . $user_id);
	$query->order('user_id ASC');

	$db->setQuery($query);

	$results = $db->loadRowList($query);
	
}

function get_skills() {

	$db = JFactory:: getDbo();

	$query = $db->getQuery(true);

	$query->select($db->quoteName(array('skill_id', 'description', 'name', 'permission')));
	$query->from($db->quoteName('team_skills'));
	$query->order('skill_id ASC');

	$db->setQuery($query);

	$results = $db->loadRowList($query);
	
}

function get_achievements() {

	$db = JFactory:: getDbo();

	$query = $db->getQuery(true);

	$query->select($db->quoteName(array('achievement_id', 'description', 'name', 'points')));
	$query->from($db->quoteName('team_achievements'));
	$query->order('achievement_id ASC');

	$db->setQuery($query);

	$results = $db->loadRowList($query);

}

function get_user_achievements($user_id) {
	$db = JFactory:: getDbo();

	$query = $db->getQuery(true);

	$query->select($db->quoteName(array('achievement_id', 'time')));
	$query->from($db->quoteName('team_user_achievements'));
	$query->where($db->quoteName('user_id') . " = " . $user_id);
	$query->order('achievement_id ASC');

	$db->setQuery($query);

	$results = $db->loadRowList($query);
}

function get_comments($user_id) {

	$db = JFactory:: getDbo();

	$query = $db->getQuery(true);

	$query->select($db->quoteName(array('comment_id', 'commenter_id', 'rating')));
	$query->from($db->quoteName('team_comments'));
	$query->where($db->quoteName('user_id') . " = " . $user_id);
	$query->order('comment_id ASC');

	$db->setQuery($query);

	$results = $db->loadRowList($query);
}

function get_votes($user_id) {
	$db = JFactory:: getDbo();

	$query = $db->getQuery(true);

	$query->select($db->quoteName('voted_id'));
	$query->from($db->quoteName('team_voting'));
	$query->where($db->quoteName('user_id') . " = " . $user_id);
	$query->order('voted_id ASC');

	$db->setQuery($query);

	$results = $db->loadRowList($query);

}

function get_work_history($user_id) {
	
	$db = JFactory:: getDbo();

	$query = $db->getQuery(true);

	$query->select($db->quoteName(array('work_name', 'work_end', 'work_start', 'reason')));
	$query->from($db->quoteName('team_work_history'));
	$query->where($db->quoteName('user_id') . " = " . $user_id);

	$db->setQuery($query);

	$results = $db->loadRowList($query);
}

function get_state_info($id) {

	$db = JFactory:: getDbo();

	$query = $db->getQuery(true);

	$query->select($db->quoteName(array('password', 'description', 'name')));
	$query->from($db->quoteName('team_state'));
	$query->where($db->quoteName('user_id') . " = " . $id);

	$db->setQuery($query);

	$results = $db->loadRowList($query);

}
function get_resume($user_id) {

	$db = JFactory:: getDbo();

	$query = $db->getQuery(true);

	$query->select($db->quoteName(array('a.experience', 'a.custom_field', 'a.disability', 'b.work_name', 'b.work_end', 'b.work_start', 'b.reason', 'c.institute', 'c.time')));
	$query->from($db->quoteName('team_state'));
	$query->join('INNER', $db->quoteName('team_work_history', 'b') . ' ON (' . $db->quoteName('a.user_id') . ' = ' . $db->quoteName('b.user_id') . ')')
	$query->join('INNER', $db->quoteName('team_education', 'c') . ' ON (' . $db->quoteName('a.user_id') . ' = ' . $db->quoteName('c.user_id') . ')')
	$query->where($db->quoteName('user_id') . " = " . $user_id);

	$db->setQuery($query);

	$results = $db->loadRowList($query);

}


function get_references($user_id) {

	$db = JFactory::getDbo();

	$query = $db->getQuery(true);
	
	$query->select($db->quoteName(array('user_id', 'name', 'number', 'relationship')));
	$query->from($db->quoteName('team_references'));
	$query->where($db->quoteName('user_id'). "=" . $user_id);
	$query->order($db->quoteName('user_id ASC');
	
	$db->setQuery($query);
	
	$results = $db->loadRowList($query);	

}

function get_logs() {
	$db = JFactory::getDbo();

	$query = $db->getQuery(true);
	
	$query->select($db->quoteName(array('user_id', 'search_query', 'date')));
	$query->from($db->quoteName('team_log'));

	$db->setQuery($query);
	
	$results = $db->loadRowList($query);
}

?>