<?php
//	Query.php	*******************************************************************************
//
//	Build a SoQL query statement based on passed values
//		Values are static for now
//		View displays query in human readable and satisfying and reinforcing way
//
//	03.22.2014	|	js	|	Prototyping
//
//*********************************************************************************************

//	Debug	***********************************************************************************

$debug = 1;

if( $debug ) {
	echo '<br />$debug => '.$debug.'<br />';

	error_reporting(E_ALL);
	ini_set("display_errors", 1);
}

//	Includes	*******************************************************************************
?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script> 
<?
//	Inputs	***********************************************************************************

if( isset( $_REQUEST['agemax'] ) ) $agemax = $_REQUEST['agemax'];
if( $debug ) echo '<br />$agemax => '.$agemax.'<br />';

if( isset( $_REQUEST['institutionname'] ) ) $institutionname	= $_REQUEST['institutionname'];
if( $debug ) echo '<br />$institutionname => '.$institutionname.'<br />';

if( isset( $_REQUEST['gender'] ) ) $gender	= $_REQUEST['gender'];
if( $debug ) echo '<br />$gender => '.$gender.'<br />';

if( isset( $_REQUEST['studentlevel'] ) ) $studentlevel = $_REQUEST['studentlevel'];
if( $debug ) echo '<br />$studentlevel => '.$studentlevel.'<br />';

if( isset( $_REQUEST['prgramname'] ) ) $programname	= $_REQUEST['programname'];
if( $debug ) echo '<br />$programname => '.$programname.'<br />';

$agemax				= 17;
$institutionname	= NULL;
$gender				= "Female";
$studentlevel		= NULL;
$programname		= "Computer Science";


$inputs				= [
	"agemax" 			=> $agemax,
	"institutionname"	=> $institutionname,
	"studentlevel"		=> $studentlevel,
	"programname"		=> $programname
];
if( $debug ) echo '<br />$inputs => '.print_r( $inputs ).'<br />';

unset( $agemax, $institutionname, $gender, $studentlevel, $programname );

$keys		= array();
$values		= array();

foreach( $inputs as $key => $value ) {
	if( $debug ) echo '<br />$key => '.$key.' $value => '.$value.'<br />';
	if( empty( $value ) ) {
		if( $debug ) echo '<br />Unset $key => '.$key.'<br />';
		unset( $inputs[$key] );	
	} else {
		if( $debug ) echo '<br />Else loop:<br />';		
		$keys[]	 	= $key;
		if( $debug ) echo '<br />$keys => '.print_r( $keys ).'<br />';
		$values[]	= $value;
		if( $debug ) echo '<br />$values => '.print_r( $values ).'<br />';
		
	}
}
unset( $key, $value );

//	Build dat Query	***************************************************************************

$select	= "SELECT ";

for( $i = 1; $i < count( $inputs ); $i++ ) {
if( $debug ) echo '<br />$i => '.$i.'<br />';
if( $debug ) echo '<br />count( $keys ) => '.count( $keys ).'<br />';
	
	switch( count( $inputs ) ) {
		case 0:
			if( $debug ) echo '<br />Case 0: <br />';
			$select	.= '* ';
			break;
		case 1:
			if( $debug ) echo '<br />Case 1: <br />';
			$select	.= $keys;
			$where	= "where ".$keys[$i]."='".$values[$i]."'";
			break;
		default: 
			if( $debug ) echo '<br />Case Default: <br />';
			
			$where = "WHERE ";
			
			foreach( $inputs as $key => $value ) {
				if( $debug ) echo '<br />$key => '.$key.'<br />';
				$value	= urlencode( $value );
				$select	.= $key.', ';	
				$where	.= $key."='".$value."' AND ";
			}
			
			$where	= trim( $where, 'AND ' );
			
			break;
	}
	
}

//	Strip unnecessary content	***************************************************************

if( $debug ) echo '<br />Strip extraneous strings...<br />';
$select = trim( $select, ', ' );
if( $debug ) echo '<br />$select => '.$select.'<br />';

$query	= $select.' FROM #q8jr-gbau ';

if( isset( $where ) ) $query.= $where;
if( $debug ) echo '<br />$query => '.$query.'<br />';

//	View	***********************************************************************************

if( $debug ) echo '<br />Start view: <br />';
if( $debug ) echo '<br />$inputs => '.print_r( $inputs ).'<br />';

foreach( $inputs as $key => $value ) {
	echo '<p>Search for '.$key.' that matches '.$value.'</p>';
}

//	Execute Search	***************************************************************************

nexgen_execute_search( $query );

function nexgen_execute_search( $query ) {
	//	Debug	*******************************************************************************
	
	$debug = 1;
	if( $debug ) echo '<br />Start execute_search function: <br />';
?>
	<script>
	$( document ).ready( function() {
		var query	= "<?=$query?>";
		console.log( query );
	
		$( "#results" ).load( "https://data.colorado.gov/resource/cdhe-degrees-awarded.json?query=" + query, function( data ) {
			console.log( data );
			if( data == '' ) {
				printf( "Returned no results." );
			}	
		});
	});
	</script>	
<?}?>

<div id="results">
</div>