<?

require_once('sm_pref.php');

$pref->dump();

$dummy = $pref->getPref('A');
echo '$dummy = ' . $dummy . '<BR>';

$pref->setPref('TEST','value');

$pref->dump();

$pref->unsetPref('TEST');

$pref->dump();

$pref->savePreferences();

?>
