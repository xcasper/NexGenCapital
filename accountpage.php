<!DOCTYPE html>
<b>Achievements</b><br />

<?php
 $userID = 889;
 include('dbfunc.php');
 $users = get_user($userID);
 foreach ($users as $key => $value)
    {
      echo('<div style="border: 1px solid gray;">');
      echo('<img src="./images/accounts/"' . $value['user_id'] . '.png" /> 
	  Account Page 						Points Awarded: ' . $value['points'] ' <br />
	  <br />
	  Name: ' . $value['name'] . '<br /> 
	  Career: ' . $value['career'] . '<br />
	  Age: ' . $value['age'] . '<br .>
	  'if(isset($value['location'])){'
	  Location: ' . $value['location'] . '<br />'
	  }
	  $educations = get_education($userID);
	  foreach($educations as $education => $edValue){
	  echo(' Education: ' . 
	  if(isset($edValue['schoolname'])){ $edValue['schoolname']} . 
	  if(isset($edValue['schoolcode'])){$edValue['schoolcode']} . 
	  if(isset($edValue['schoollevel'])){$edValue['schoollevel']} 
	  if(!issset($edValue['schoolname']) && !isset($edValue['schoolcode']) && !isset($edValue['schoollevel'])){ 'No Education Information Provided'});		
	  }
	' Phone: ' . $value['phone_number'] ' <br />
	  Email: ' . $value['email'] ' <br />
	  'if(isset($value['title'])){ 'Title: ' . $value['title']} ' <br />
	  'if(isset($value['contactpoint'])){ 'Point of Contact Name: ' . $value['contactpoint']} ' <br />
	  'if(issset($value['personal_statement'])){ 'Biography: ' . $value['personal_statement']} '<br />'
	  );
     echo('</div>');
      
    }

 ?>