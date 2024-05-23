<?php

	// 	Donut 				🍩 
	//	Dictionary Toolkit
	// 		Version a.1
	//		Written by Thomas de Roo
	//		Licensed under GNUv3

	//	++	File: index.php

	// This is the only file that can (and should) be accessed directly. Let's make sure of that.
	if(!defined('donut'))
		define('donut', 1);

	// We may be dealing with the most crazy symbols evâh, so UTF-8 is needed
	header("content-type: text/html; charset=UTF-8");  
	
	//	Sessions are being started...
	session_start(); 

	// 	Include config.php, an important file
	require 'config.php';

	//	Let's load ALL the global code (functions and classes)
	pLoadGlobalCode();

	//	Rewelcome our previous-session logged in guest
	pUser::restore();

	//	If we're logged in, we need to have the user to our disposal
	if(pLogged())
		$donut['user'] = pGetUser();
	else
		$donut['user'] = null;

	//	We need to get the language loaded
	pLoadLanguage('English');

	// We are in beta!
	$donut['is_beta'] = true;

	// Gettin' ALL the apps
	$apps = pAppsArray();
	$app_set = false;

	// Defining the menu, intial state
	$donut['page']['menu'] = pMenu();

	// Going through the apps, looking for answers...
	while($app = $apps->fetchObject()){


		if(isset($_REQUEST[$app->getter]) and !$app_set)
		{

			if(($app_file = $donut['root_path'] . '/code/' . $app->app . '.index.php') && file_exists($app_file))
				require_once $app_file;
			else
				die("<div class='header'>Fatal error: Donut failed loading the ".$app->app."-section</div><br />");

			$app_set = true;

		}

		//	We can escape this check early
		if($app_set)
			break;
	}

	//	No app means back to home
	if($app_set == false)
		pUrl("?".CONFIG_HOMEPAGE, true);
		
	//	Starting with content output
	$donut['page']['header_final'] = "";
	$donut['page']['content_final'] = "";

	//	Putting the header sections into the donut.
	foreach($donut['page']['header'] as $output_section)
		$donut['page']['header_final'] .= "$output_section \n";

	//	Putting the page sections into the donut.
	foreach($donut['page']['content'] as $outputsection)
		$donut['page']['content_final'] .= "$outputsection \n";
	
	//	We need some extra structure if we are dealing with AJAX loaded pages
	if(isset($_REQUEST['ajax']) OR isset($_REQUEST['ajax_pOut'])){
		pAjaxStructure();
	}

	//	The template is loaded, that's the begining of the end.
	require pFromRoot("library/templates/main_template.php");

	//	† Rest in peace and blahblahblah.
		die();
