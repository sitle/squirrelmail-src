<?php
//
// START init
//
  require_once('class.pref.file.php');

  $init = new class_pref_file('global.pref', true);
  $PREF_CLASS = $init->getPref('PREF_CLASS','file');
  unset($init);
// 
// END init
//

switch ($PREF_CLASS) {
	case 'file':
		require_once('class.pref.file.php');
  		$pref = new class_pref_file('pref.pref',true);
		break;
	case 'mysql':
		include_once('class.pref.mysql.php');

		$pref = new class_pref_mysql(false);
		$pref->host= "somehost";
                $pref->user = "someuser";
                $pref->password = "somepassword";
                $pref->database = "somedatabase";
		$pref->loadPreferences();

		break;
	default:
		die ('Class: ' . $PREF_CLASS . 'not implemented');
		break;
}

?>
