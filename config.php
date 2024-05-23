<?php
/* 
	Donut
	Dictionary Toolkit
	Version a.1
	Written by Thomas de Roo
	Licensed under GNUv3
	File: pol_config.php
*/

//	Database Driver (Which type of database we're using?)
	$db_driver = 'mysql'; // mysql, pgsql or sqlite


//  Setting up Database connection
	$db_host = 'localhost'; // database server
	$db_user = 'root'; // database username
	$db_password = ''; // database password
	$db_database = 'donut'; 	// name or path of/to database
	if($db_driver == 'mysql')
		try
		{
			$db = new PDO('mysql:host='.$db_host.';dbname='.$db_database, $db_user, $db_password);
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();
			exit;
		}
	if($db_driver == 'pgsql')
		try
		{
			$db = new PDO('pgsql:host='.$db_host.';dbname='.$db_database, $db_user, $db_password);
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();
			exit;
		}
	if($db_driver == 'sqlite')
		try
		{
			$db = new PDO('sqlite:'.$db_database);
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();
			exit;
		}
		$db->exec("set names utf8");
		$db->query("set character_set_results='utf8'");

##	Making array for functions and global objects
	$donut = array();
	$donut['file'] = 'index.php';
	$donut['settings'] =array();
	$donut['page'] = array();
	$donut['title'] = "Donut";
	$donut['page']['title'] = "Donut";
	$donut['page']['outofinner'] = null;
	$donut['page']['menu'] = null;
	$donut['page']['head'] = array();
	$donut['page']['head']['final'] = null;
	$donut['page']['header'] = array();
	$donut['page']['menu'] = '';
	$donut['page']['content'] = array();
	$donut['db'] = $db;
	$donut['session_auth'] = md5("kjj8f99e9iwj32ikm8391pok389iokn");

## 	Vowels for the vowel check
	$donut['vowels'] = array("a","e","i","o","u","ø");
##	These variables are the paths to the site root.
	$donut['root_path'] = dirname(__FILE__);
	$donut['absolute_path'] = "http://localhost/donut/";

##  This function is used to encrypt passwords

	function polHash($password, $userdata = '')
	{
		global $donut;

		# Set Salt
		$salt = md5("kjj8f99e9iwj32ikm8391pok389iokn").sha1("pol0.1");
		$hash = md5(sha1(sha1(md5(sha1(md5($salt).$salt.$password).$password).md5(strlen($password)).$salt.md5($password))));
		$hash = hash('ripemd160', $hash);
		$hash = hash('sha256', $hash);
		if($userdata != '')
			$hash = hash('ripemd160', sha1($hash.$salt.$userdata).$userdata.sha1($salt));
		return $hash;
	}
##	Function to check if a string starts with a certain piece of string

function polStartsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

function polEndsWith($haystack, $needle)
{
    return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}
	
##	This function is used to load all the other functions
	function polLoadFunctions()
	{
		global $donut;
		foreach (glob($donut['root_path']."/code/functions/*.functions.php") as $filename)
		{
			require $filename;
		}
	}


## This function is used for urls

	function pUrl($url = '', $header = false)
	{
		// Needed. :)
		global $donut;

		// Checking if we are in AJAX mode, headers are handled by javascript then!
		$ajax_mode = (isset($_GET['ajax']) OR isset($_GET['ajax_pOut']));

		// Just an adition on the index.php?
		if(polStartsWith($url, '?'))
			$url = $donut['absolute_path'].$donut['file'].$url;


		elseif(polStartsWith($url, 'pol://') && $exploded = explode('pol://', $url))
			$url = pUrl($exploded[1]);

		elseif(polStartsWith($url, 'http://') or polStartsWith($url, 'ftp://') or polStartsWith($url, 'https://'))
			$url = $url;

		else
			$url = $donut['absolute_path'].$url;

		if(!$header)
			return $url;

		else
			if($ajax_mode)
				return "<script>window.location = '".$url."';</script>";
				
			return header("Location:".$url);
	}

require $donut['root_path'].'/library/str.php';
	
	function pStr($str, $charset = null) {
	    return new \Delight\Str\Str($str, $charset);
	}
	
	function pFromRoot($url)
	{
		global $donut;
		return $donut['root_path'].'/'.$url;
	}

// Unsetting used variables
	unset($db);
	unset($db_prefix);
	unset($db_host);
	unset($db_user);
	unset($db_password);

?>