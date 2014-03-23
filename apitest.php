Testing New API....

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

<script>
$( document ).ready( function() {
	$( "$btn_submit" ).click( function() {
		var agemax			= $( "#inp_agemax" ).val();
		var institutionname	= $( "#inp_institutionname" ).val();
		var gender			= $( "#inp_gender" ).val();
		var studentlevel	= $( "#inp_studentlevel" ).val();
		var programname		= $( "#inp_programname" ).val();
		
		$( "#results" ).post( "query.php", {
			'agemax'			: agemax,
			'institutionname'	: institutionname,
			'gender'			: gender,
			'studentlevel'		: studentlevel,
			'programname'		: programname
		});
	});
});
</script>



<div id="input">

<form>



</form>

</div>

<div id="results">
</div>

End of Page.

Sample Data:

    {
        "agemax": "20",
        "institutionlevelid": "2",
        "residency": "In-State",
        "institutionlevel": "College",
        "institutiontype": "Public",
        "cip2": "20",
        "division": "DHE",
        "agedesc": "17-20",
        "institutionname": "Aims Community College",
        "agemin": "17",
        "gender": "Female",
        "studentlevel": "Undergraduate",
        "year": "2001",
        "degreelevel": "Certificate",
        "taxtype": "Not-for-Profit",
        "residencyid": "1",
        "ethnicity": "Hispanic",
        "cip": "20.0201",
        "cobased": "Yes"
    