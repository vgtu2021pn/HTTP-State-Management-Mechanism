<?php

mb_internal_encoding('UTF-8');

ini_set("session.cookie_secure", 0);

ini_set("session.cookie_httponly", 0);

session_start();

header('Content-Type: text/html; charset=UTF-8');



/**

 * Prepare the input variables

 * @var {array} pr - define defaults and later on fill with data from GET and POST methods.

 */



$pr = array();

$pr['method'] = null;

$pr['temporary'] = null;

$pr['noHTTPS'] = null;

$pr['yesScripts'] = null;

foreach(@array_merge($_GET, $_POST) as $key => $value)

{

	$pr[$key] = $value;

}



/**

 * Prepare redirect of the GET Method

 * @var {string} rf - define part of address, which gonna be attached to the URL.

 */



$rf = (isset($pr['method']) || isset($pr['temporary']) || isset($pr['yesScripts']) || isset($pr['noHTTPS'])? "?":"");

$rf .= (isset($pr['method'])? "method={$pr['method']}&":"");

$rf .= (isset($pr['temporary'])? "temporary={$pr['temporary']}&":"");

$rf .= (isset($pr['yesScripts'])? "yesScripts={$pr['yesScripts']}&":"");

$rf .= (isset($pr['noHTTPS'])? "noHTTPS={$pr['noHTTPS']}&":"");

if(!empty($rf)){

$len = mb_strlen($rf, 'UTF-8');

$rf = mb_substr($rf, 0, $len-1, 'UTF-8');

}



/**

 * Create or delete the cookies

 * @var {array} co - settings of the cookie(s)

 */



if(isset($pr['setcookie']))

{

	$co = array();

	$co['value'] = json_encode(array(1 => 'pi', 2 => 'an', 3 => 'tr', 4 => 'ke', 5 => 'pe', 6 => 'še', 7 => 'se'));

	$unixtime = time(); //current timestamp

	$co['expires'] = (isset($pr['temporary'])? $unixtime + 10 : $unixtime + (42 * 24 * 60 * 60)); //(days * hours * minutes * seconds)

	$co['path'] = "/";

	$co['domain'] = ""; //$_SERVER['HTTP_HOST'];

	$co['secure'] = (isset($pr['noHTTPS'])? FALSE : TRUE);

	$co['httponly'] = (isset($pr['yesScripts'])? FALSE : TRUE);



	/**

	 * Will be created two kind of cookies: UID and "mycookie"

	 * UID - md5 valued cookie with expiration data control

	 * "mycookie" - json valued cookie with expiration, httponly, secure transfer controls and etc.

	 */



# Attributes:

# expires

# path

# domain

# secure - cookie is only sent from the client to the server when HTTPS protocol is available.

#	HTTP sites should not be able to set cookies using [secure] 'TRUE' value.

# httponly - forbids various Scripts to access the session cookie.

# samesite - possible values of None, Lax, Strict.

#	None let to send cookie with cross-site and same-site requests.

#	Strict let to send cookie only for same-site requests.

#	Lax let send cookie by navigating to site from external site, using cross-site requests.

#	By using [samesite] 'Lax' value, the cookies are allowed to be sent along with GET method. This OPTION should be set by default.

#	By using [samesite] 'None' value, the [secure] attribute with value of 'TRUE' in last browser versions are mandatory to have.



	if(PHP_MAJOR_VERSION > 7 || (PHP_MAJOR_VERSION == 7 && PHP_MINOR_VERSION >2))

	{

		setcookie("mycookie", $co['value'], array('expires' => $co['expires'], 'path' => $co['path'], 'domain' => $co['domain'], 'secure' => $co['secure'], 'httponly' => $co['httponly'], 'samesite' => 'Strict'));

	}

	else

	{

		setcookie("mycookie", $co['value'], $co['expires'], $co['path'], $co['domain'], $co['secure'], $co['httponly']);

	}



	setcookie("UID", md5(session_id()), $co['expires'], $co['path']); // How secure md5 valued cookie?

	//header("Refresh:0; url=cookies.php{$rf}");

}

elseif(isset($pr['delcookie']))

{

	session_destroy();

	$unixtime_past = time - (42 * 24 * 60 *60); // How to do it more logically?

	setcookie("mycookie", "", $unixtime_past, "/");

	setcookie("UID", "", $unixtime_past, "/");

	//header("Refresh:0; url=cookies.php{$rf}");

}



?>



<!DOCTYPE html>

<html lang="en">

<head>

	<meta charset="utf-8">

	<meta http-equiv="Content-Security-Policy" content="default-src 'self'; style-src 'self' 'unsafe-inline' data:; img-src 'self'; script-src 'self' <?php $nonsence = md5(openssl_random_pseudo_bytes(rand(99,99999))); echo "'"."nonce-{$nonsence}"."'"; ?>; connect-src 'none';" />

	<title>The Cookie testing Page</title>

	<style>

		body {font-family: times;}

		h1 {text-indent: 10pt;}

		h2 {text-indent: 20pt;}

		ul li {list-style-type: none;}

		input[type="checkbox"] {margin: 1%;}

		input[type="submit"] {margin: 1%;}

		caption {text-align: left; margin-bottom: 3pt;}

		table {margin-bottom: 15pt;}

		table, tr {border: 1px solid black; border-collapse: collapse; padding: 2px; width: 75%; margin-left: 10 pt;}

		th, td {border: 1px solid black; border-collapse: collapse; padding: 2px; width: 25%;}

		span.red {color: red;}

	</style>

</head>

<body>

<h1>The Cookie testing Page</h1>

<h2>Cookie Settings</h2>

<form name="cookies" action="/session.cookie/index.php" <?php echo 'method='.(isset($pr['method'])? "post" : "get"); ?> target="_self">

<ul>

	<li><input type="checkbox" name="method" value="1" <?php echo (isset($pr['method'])? 'checked="checked"':''); ?>/> If checked, the request gonna be sent over POST method, otherwise over GET.</li>

	<li><input type="checkbox" name="temporary" value="1" <?php echo (isset($pr['temporary'])? 'checked="checked"':''); ?>/> If checked, [my]cookie gonna last 10 seconds, otherwise — 42 days.</li>

	<li><input type="checkbox" name="yesScripts" value="1" <?php echo (isset($pr['yesScripts'])? 'checked="checked"':''); ?>/> If checked, then [my]cookie gonna be ACCESSIBLE by Scripting languages, otherwise - not ACCESSIBLE by Scripts.</li>

	<li><input type="checkbox" name="noHTTPS" value="1" <?php echo (isset($pr['noHTTPS'])? 'checked="checked"':''); ?>/> If checked, then [my]cookie gonna be TRANSFERED from the client to server with HTTP or HTTPS, otherwise it should be transfered only when HTTPS protocol is available.</li>

	<li><input id="setcookie" type="submit" name="setcookie" value="create cookie" accesskey="s"/>

	    <input type="submit" name="delcookie" value="clear cookie" accesskey="d"/></li>

	<li><a href="javascript: setcookie();">Create cookies with a link and JavaScript</a><span class="red">(ongoing...)</span></li>

</ul>

</form>



<h2>Cookie Data</h2>



<table>

<caption><b>Table No. 1</b> Here shown <u>current info</u> about the Session Cookies.</caption>

<thead>

<tr>

	<th>No.</th>

	<th>Name</th>

	<th>Data</th>

</tr>

</thead>

<tbody>

<?php

if(!empty($_COOKIE) && is_array($_COOKIE))

{

	(int)$l = 1;



	$_SESSION['history'] = (!empty($_SESSION['history']) && count($_SESSION['history']) > 0)? $_SESSION['history'] : array();

	foreach($_COOKIE as $key => $value)

	{

		$_SESSION['history'][] = array('Name' => $key, 'Data' => $value);

		echo "<tr>";

			echo "<td>{$l}</td>";

			echo "<td>{$key}</td>";

			echo "<td>{$value}</td>";

		echo "</tr>";

		$l++;

	}

}

else

{

print <<<END

<tr>

	<td>&nbsp;</td>

	<td>mycookie</td>

	<td>The cookie is not set.</td>

</tr>

END;

}

print <<<END

</tbody>

</table>

END;



if (!empty($_SESSION['history']) && count($_SESSION['history']) > 0)

{

print <<<END

<table>

<caption><b>Table No. 2</b> Here shown <u>session history</u> about Session Cookies.</caption>

<thead>

<tr>

	<th>No.</th>

	<th>Name</th>

	<th>Data</th>

</tr>

</thead>

<tbody>

END;

	foreach($_SESSION['history'] as $key => $value)

	{

		echo "<tr>";

			echo "<td>".($key+1)."</td>";

			if(is_array($value))

			{

				echo "<td>{$value['Name']}</td>";

				echo "<td>{$value['Data']}</td>";

			}

			else

			{

				echo "<td>&nbsp;</td>";

				echo "<td>&nbsp;</td>";

			}

		echo "</tr>";

	}

print <<<END

</tbody>

</table>

END;

}

?>



<script type="text/javascript" nonce="<?php echo $nonsence; ?>">

function setcookie()

{

	//TODO. Need to click submit button named as setcookie.

	$("#setcookie".click(function () {

		document.cookie.submit();

	}));

}

</script>



</body>

</html>