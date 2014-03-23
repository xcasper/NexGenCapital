  <!DOCTYPE html>
  <b>Achievements</b><br />
<?php
 include('dbfunc.php');
 $var = get_achievements();
 foreach ($var as $key => $value)
    {
      echo('<div style="border: 1px solid gray;">');
      echo('<img src="./images/achievements/"' . $value['achievement_id'] . '.png" /> Achievement Name: ' . $value['name'] . '<br />Points Awarded: ' . $value['points']);
     echo('</div>');
      
    }

 ?>
