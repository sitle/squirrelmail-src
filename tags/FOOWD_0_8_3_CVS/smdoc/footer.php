<?php
if (isset($object) && $object->objectid != 904872506) { // is object but isn't error object
	echo '<hr /><em>"', $object->title, '" ';
	if ($object->workspaceid != 0) {
		echo ' (<a href="', getURI(array('objectid' => $object->workspaceid, 'classid' => -679419151)), '">workspace</a>) ';
	}
	echo 'Methods:-</em> ';
	$className = get_class($object);
	foreach (get_class_methods(get_class($object)) as $methodName) {
		if (substr($methodName, 0, 7) == 'method_') {
			$methodName = substr($methodName, 7);
			if (!isset($object->permissions[$methodName]) || $object->permissions[$methodName] == NULL || $foowd->user->inGroup($object->permissions[$methodName]) || ($object->permissions[$methodName] == 'Author' && $object->creatorid == $foowd->user->objectid)) {
				echo '<a href="', getURI(array('objectid' => $object->objectid, 'classid' => $object->classid, 'version' => $object->version, 'method' => $methodName)), '">', ucfirst($methodName), '</a> | ';
			} else {
				echo ucfirst($methodName), ' | ';
			}
		}
	}
}
echo '<hr /><em>Class Methods:-</em> ';
foreach (get_declared_classes() as $className) {
	if (substr($className, 0, 6) == 'foowd_' && $className != 'foowd_anonuser') {
		$shortClassName = substr($className, 6);
		foreach (get_class_methods($className) as $methodName) {
			if (substr($methodName, 0, 6) == 'class_') {
				$methodName = substr($methodName, 6);
				$permission = getPermission($className, $methodName, 'class');
				if ($permission == NULL || $foowd->user->inGroup($permission)) {
					echo '<a href="', getURI(array('class' => $className, 'method' => $methodName)), '">', ucfirst($shortClassName), '::', ucfirst($methodName), '</a> | ';
				} else {
					echo ucfirst($shortClassName), '::', ucfirst($methodName), ' | ';
				}
			}
		}
	}
}
echo '<hr />';
if ($foowd->user->classid == crc32(strtolower(getConstOrDefault('ANONYMOUS_USER_CLASS', 'foowd_anonuser')))) {
	echo 'Welcome anonymous user "';
} else {
	echo 'You are logged in as "';
}
if (isset($foowd->user->objectid)) {
	echo '<a href="', getURI(array('objectid' => $foowd->user->objectid, 'classid' => getConstOrDefault('USER_CLASS_ID', 425464453))), '">', $foowd->user->title, '</a>';
} else {
	echo $foowd->user->title;
}
echo '"';
if ($foowd->user->workspaceid != 0) {
	echo ' (<a href="', getURI(array('objectid' => $foowd->user->workspaceid, 'classid' => -679419151)), '">workspace</a>)';
}
echo ' | <a href="', getURI(array()), '">Homepage</a> | <a href="', getURI(array('object' => 'Object List')), '">Object List</a> | <a href="', getURI(array('object' => 'Recent Changes')), '">Recent Changes</a>';
echo '<hr />';
?>

</body>
</html>