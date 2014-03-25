<!DOCTYPE html>
<style type="text/css">
  #bus {
  display: none;
  }
  #other {
  display: none;
  }
</style>
<script type="text/javascript">
 function custom_getval(sel) {

      if ((sel.value) == 2)
  {
	 document.getElementById('other').style.display = 'none';
    document.getElementById('bus').style.display = 'block';
  }
  if ((sel.value) == 3)
  {
	 document.getElementById('bus').style.display = 'none';
    document.getElementById('other').style.display = 'block';
  }
   if ((sel.value) == 1)
  {
    document.getElementById('bus').style.display = 'none';
    document.getElementById('other').style.display = 'none';
  }
  
    }
</script>

<div id="custom_submission_form">
<!-- just a quick note, we can add or remove any of these fields as needed, or write jquery to optionally hide or display fields that might not be relevant as the user goes through the form (like showing the skills option if the user selects they are type individual) -->
  <form id="custom_sbt_frm" method="post">
    <fieldset>
      Position: <select id="selector" onchange="custom_getval(this);">
          <option value="1">Individual</option>
          <option value="2">Business</option>
          <option value="3">Organization</option>
        </select><br /><br />
      Name: <input type="text" name="name" /><br /><br />
      <div id="individual">
      Career: <input type="text" name="career" /><br /><br />
      Age: <input type="text" name="age" /><br /><br />
      Location:  <input type="text" name="location" /><br /><br />
      Email:  <input type="text" name="email" /><br /><br />
      Phone Number: <input type="text" name="phone" /><br /><br />
      Skills: <select>
          <option value="1"></option>
          <!-- This will loop through all listed skills available in the skills table -->
        </select>: <input type="text" name="references" /><br /><br />
      Personal Statement: <input type="text" name="personal" /><br /><br />
      </div>
     <div id="bus">
     		 Industry: <input type="text" name="industry" /><br /><br />
      </div>
      <div id="other">
      Title: <input type="text" name="age" /><br /><br />
      Fax:  <input type="text" name="fax" /><br /><br />
      Point of Contact: <input type="text" name="contactpoint" /><br /><br />
      Description Statement: <input type="text" name="personal" /><br/><br />
      </div>

  
      <input type="submit" name="custom_sbt_frm" value="Submit" />
    </fieldset>
  </form>
</div>

<?php
 include('dbfunc.php');

$submit = isset($_POST['custom_sbt_frm']) ? true : false ;
	if ($submit) {
	
	require_once ( JPATH_BASE .'/includes/defines.php' );
	require_once ( JPATH_BASE .'/includes/framework.php' );
	require_once ( JPATH_BASE .'/libraries/joomla/factory.php' );

	$position = isset($_POST['position']) ? $_POST['position'] : '' ;
	$name = isset($_POST['name']) ? $_POST['name'] : '' ;
	$career = isset($_POST['career']) ? $_POST['career'] : '' ;
	$age = isset($_POST['age']) ? $_POST['age'] : '' ;
	$location = isset($_POST['location']) ? $_POST['location'] : '' ;
	$email = isset($_POST['email']) ? $_POST['email'] : '' ;
	$fax = isset($_POST['fax']) ? $_POST['fax'] : '' ;
	$phone_number = isset($_POST['phone_number']) ? $_POST['phone_number'] : '' ;
	$contact_point = isset($_POST['contactpoint']) ? $_POST['contactpoint'] : '' ;
	$statement = isset($_POST['personal']) ? $_POST['personal'] : '' ;
	$skills = isset($_POST['skills']) ? $_POST['skills'] : '' ;
	$user = JFactory::getUser();

		$db = JFactory::getDbo();
 
		$query = $db->getQuery(true);
 
		// Fields to update.
		$fields = array(
   		  $db->quoteName('type') . '=' . $position,
		 $db->quoteName('realname') . '=' . $name,
		 $db->quoteName('career') . '=' . $career,
		 $db->quoteName('age') . '=' . $age,
		 $db->quoteName('email') . '=' . $email,
		 $db->quoteName('fax') . '=' . $fax,
		 $db->quoteName('phone_number') . '=' . $phone_number,
		 $db->quoteName('contact_point') . '=' . $contact_point,
		 $db->quoteName('statement') . '=' . $statement,
   		 $db->quoteName('location') . '=' . $location
		);
 
		// Conditions for which records should be updated.
		$conditions = array(
 			   $db->quoteName('user_id') . '=' . $user
			);

		//$query->update($db->quoteName('ojviy_users'))->set($fields)->where($conditions);
		//$query= " UPDATE ojviy_users SET ".$db->nameQuote('type')."=". $db->Quote($position) ."  where ".$db->nameQuote('user_id')." = ". $db->Quote($user);

 		//Can't find documentation for joomla 3.2 update queries. Not sure what's causing the error messages. 
$db->setQuery($query);
 
 
		//$result = $db->query();
		echo "form submitted (queries are not active yet.)";
	}
?>