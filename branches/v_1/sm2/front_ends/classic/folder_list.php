<?php

	if (! ($folders = $sm_mail->get_subscribed_folders(''))) {
		header ("error.php");
	}

?>
<!DOCTYPE HTML PUBLIC "-//W3C/DTD HTML 4.0 Transitional//EN">
<html>
  <head>
    <title>SquirrelMail - <?php echo $org_title ?></title>
  </head>
  <body bgcolor="#aabbcc" text="#000000" link="#0000cc" vlink="0000cc" alink="0000cc">

	<center>
	<big><b>Folders</b></big><br>
	<small>(<a href="folder_list.php">refresh folder list</a>)</small>
	</center>
    <br>

<?php

	foreach ($folders as $folder) {
		echo "<a target=\"main_frame\" href=\"message_list.php?cfold=".urlencode($folder->full_name)."\">".$folder->full_name."</a><br>";
	}

?>

  </body>
</html>
