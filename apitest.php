Testing New API....

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

<script>
$( document ).ready( function() {
	$( "#results" ).load( "https://data.colorado.gov/resource/cdhe-degrees-awarded.json?%24query=select%20programname%20from%20%23q8jr-gbau%20group%20by%20programname" );
	
});
</script>

<div id="input">

<form>

<select name="programname">
<option></option>
</select>

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
    }