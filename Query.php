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
if( $debug ) echo '<br />$debug => '.$debug.'<br />';

if( $debug ) {
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
}

$_REQUEST['agemax']				= 17;
$_REQUEST['institutionname']	= NULL;
$_REQUEST['gender']				= "Female";
$_REQUEST['studentlevel']		= NULL;
$_REQUEST['programname']		= "Computer Science";

//	Inputs	***********************************************************************************

$agemax				= $_REQUEST['agemax'];
if( $debug ) echo '<br />$agemax => '.$agemax.'<br />';

$institutionname	= $_REQUEST['institutionname'];
if( $debug ) echo '<br />$institutionname => '.$institutionname.'<br />';

$gender				= $_REQUEST['gender'];
if( $debug ) echo '<br />$gender => '.$gender.'<br />';

$studentlevel		= $_REQUEST['studentlevel'];
if( $debug ) echo '<br />$studentlevel => '.$studentlevel.'<br />';

$programname		= $_REQUEST['programname'];
if( $debug ) echo '<br />$programname => '.$programname.'<br />';

unset( $agemax, $institutionname, $gender, $studentlevel, $programname);

$inputs				= [
	"agemax" 			=> $agemax,
	"institutionname"	=> $institutionname,
	"studentlevel"		=> $studentlevel,
	"programname"		=> $programname
];

foreach( $inputs as $key => $value ) {
	if( $debug ) echo '<br />$key => '.$key.' $value => '.$value.'<br />';
	if( empty( $value ) ) unset( $key );
	else {
		//	Unzip $key => $value
		$keys[]	 	= $key;
		$values[]	= $key;
} unset( $key, $value );


//	Build dat Query	***************************************************************************

$select	= "SELECT ";

for( $i = 1; $i < count( $keys ); $i++ ) {
if( $debug ) echo '<br />$i => '.$i.'<br />';
	
	switch( count( $inputs ) {
		case 0:
			$select	.= '* ';
			break;
		case 1:
			$select	.= $keys	
			$where	.= "where ".$keys."='".$values."'";
			break;
		default: 
			foreach( $keys as $key ) {
				$select	.= $keys	
				$where	.= "where ".$keys."='".$values."' AND ";
			}
			break;
	}
	
	$query	.= $select.'FROM #q8jr-gbau ';
	if( $debug ) echo '<br />$query => '.$query.'<br />';
}

//	Sanitize Inputs ***************************************************************************