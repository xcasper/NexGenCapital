function submit_nexgen_form() {

  var r=confirm("Are you sure you wish to submit this form?");

    if (r==true)
     {
     
            $.post('./formsubmission.php',{ type: type, last_login: 0, settings: 0},function(returndata)
      {
      returndata;
      alert("Your form was submitted successfully");
      }
    );

}

function submit_nexgen_education() {
  var r=confirm("Are you sure you wish to submit this form?");

    if (r==true)
     {
     
            $.post('./educationsubmission.php',{ education: education, institute: institute, from: from, end: end},function(returndata)
      {
      returndata;
      alert("Your form was submitted successfully");
      }
    );
}

function submit_nexgen_work_history() {
  var r=confirm("Are you sure you wish to submit this form?");

    if (r==true)
     {
     
            $.post('./history_submission.php',{ company: company, start: start, end: end, reason: reason},function(returndata)
      {
      returndata;
      alert("Your form was submitted successfully");
      }
    );
}

function submit_references() {
  var r=confirm("Are you sure you wish to submit this form?");

    if (r==true)
     {
     
            $.post('./referencesubmit.php',{ reference: reference, number: number, relationship: relationship},function(returndata)
      {
      returndata;
      alert("Your form was submitted successfully");
      }
    );
}

function submit_nexgen_comments('user_id', 'my_id') {
  
//you need both the user_id of the user who is getting a comennt and the my_id of the user who has submitted the form. 

            $.post('./commentssubmit.php',{ reference: reference, number: number, relationship: relationship, user_id: user_id, my_id: my_id},function(returndata)
      {
      returndata;
      alert("Your form was submitted successfully");
      }
    
}