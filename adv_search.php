<!DOCTYPE html>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	$( "#nexgen_search_res3" ).load( "search/search.html" );

	$('#hiding_ipt').click(function() {
		$('#hidable_fields').hide();
		$(this).hide();
		$('#showing_ipt').show();
	});
	
	$('#showing_ipt').click(function() {
		$('#hidable_fields').show();
		$(this).hide();
		$('#hiding_ipt').show();
	});
	
	$( "#btn_submit" ).click( function( e ) {
		e.preventDefault();
		
		$( "#nexgen_search_res2" ).load( "Query.php", {
			"institutionname"	: $( "#inp_institutionname" ).val(),
			"year"				: $( "select#0 option:selected" ).val(),
			"gender"			: $( "select#1 option:selected" ).val(),
			"execute"			: 1
		});
	});
});

function display( data ) {
	var displayVal = data;
	//var sizeList = "10";
	
	var firstCharacter = String.fromCharCode(displayVal);
	var secondCharacter = String.fromCharCode(displayVal+1);
	
	var site = "https://data.colorado.gov/resource/cdhe-degrees-awarded.json?%24query=select%20programname%20from%20%23q8jr-gbau%20%20where%20programname%3E%27" + firstCharacter + "%25%27%20and%20programname%3C%27" + secondCharacter + "%25%27%20group%20by%20programname%20order%20by%20programname%20asc";
	
	$.getJSON( site, function( data ) {
		var items = [];
		$.each( data, function( key, val ) {
			items.push( "<input type=\"radio\">" + val.programname + '<br>' );
		});
	$("#nexgen_search_res1").html("");
	$( "<form>", {
		"class": "my-new-list",
		html: items.join( "" )
	}, "</form>").appendTo( "#nexgen_search_res1" );
	
	});
}


</script>
<style type="text/css">

	#nexgen_search_frm_container {
		background: url('./templates/beez4/css/sky.png');
		
	}

  #nexgen_search_frm input {
    padding: 0;
    margin: 0px;
  }
  #hidable_fields {
    border: none;
    margin: 0 auto;
	padding: 3px;
      background: url('./templates/beez4/css/sky.png');
  }
  #hidable_fields input {
    margin: -3px;
  }
  #nexgen_search_frm {
   
  }
  .class_res {
    height: 300px; 
    border: 1px solid gray;
    display: inline-block; 
    width: 49.5%;
    padding: 5px;
    overflow-x:scroll;
 
  }

  .class_res2 {
    height: 500px; 
    border: 1px solid gray; 
    width: 49%;
    float: right;
    
    overflow-x:hidden;
  }
   .class_res3 {
    height: 120px; 
	padding: 3px;
    border: 1px solid gray;
    display: inline-block; 
    width: 49.5%;
  }
  #overall_rst {
    height: 300px;
    width: 100%;
    border: 1px solid gray;
  }
  #alphabet {
 	margin: 0 auto;
	width: 1000px;
	}
</style>

<div id="hidable_fields">
  <div id="nexgen_search_frm_container">
  <form id="nexgen_search_frm" method="post">
    <fieldset>
	<!-- I am commenting these out because currently they do nothing -->
      <!--<input type="submit" name="undergrad_ipt" value="Undergraduate" />
      <input type="submit" name="graduate_ipt" value="Graduate" />
      <br /><br />-->
	<div id="alphabet">
  	<?php
	$base = 65;
	for($iteration=0; $iteration < 26; $iteration++){
      	echo '<input type="button" onclick="display('.($base+$iteration).')" id="'.$iteration.'" value="&#'.($base+$iteration).'" style="padding: 1px; margin: 1px;" />';
	}
	?>
	</div>
    </fieldset>
  </form> 
  </div>
  <div id="nexgen_search_res1" class="class_res">
  </div>
  <div id="nexgen_search_res2" class="class_res2">
  </div>
  <div id="nexgen_search_res3" class="class_res3">
  </div>
</div>
  <form id="nexgen_search_sbt">
    <fieldset>
	<!-- commenting this out because it seems pointless? -->
      <!-- <input type="button" value="Hide" id="hiding_ipt" /> -->
      <input type="button" id="showing_ipt" value="Show" style="display: none;" />
      <button value="Submit" id="btn_submit" name="sbt_btn">Submit</button>
    </fieldset>
  </form>

