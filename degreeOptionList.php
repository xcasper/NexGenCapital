Testing New API....

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

<script>
$( document ).ready( function() {
	//$( "#results" ).load( "https://data.colorado.gov/resource/cdhe-degrees-awarded.json?%24query=select%20programname%20from%20%23q8jr-gbau%20group%20by%20programname" );
//load( "https://data.colorado.gov/resource/cdhe-degrees-awarded.json?%24query=select%20programname%20from%20%23q8jr-gbau%20group%20by%20programname" );
});

var site = "https://data.colorado.gov/resource/cdhe-degrees-awarded.json?%24query=select%20programname%20from%20%23q8jr-gbau%20group%20by%20programname%20order%20by%20programname%20asc";
$.getJSON( site, function( data ) {
	var items = [];
	$.each( data, function( key, val ) {
		items.push( "<option id='" + key + "'>" + val.programname + "</option>" );
	});
$( "<select>", {
	"class": "my-new-list",
	html: items.join( "" )
}).appendTo( "#results" );
});
</script>

<div id="results">
</div>

End of Page.
