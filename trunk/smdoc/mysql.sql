-- MySQL dump 8.22
--
-- Host: localhost    Database: smdocs
---------------------------------------------------------
-- Server version	3.23.56-log

--
-- Table structure for table 'tblobject'
--

CREATE TABLE `tblobject` (
  `objectid` int(11) NOT NULL default '0',
  `version` int(10) unsigned NOT NULL default '1',
  `classid` int(11) NOT NULL default '0',
  `workspaceid` int(11) NOT NULL default '0',
  `object` longblob,
  `title` varchar(255) NOT NULL default '',
  `updated` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`objectid`,`version`,`classid`,`workspaceid`),
  KEY `idxtblObjectTitle` (`title`),
  KEY `idxtblObjectupdated` (`updated`),
  KEY `idxtblObjectObjectid` (`objectid`),
  KEY `idxtblObjectClassid` (`classid`),
  KEY `idxtblObjectVersion` (`version`),
  KEY `idxtblObjectWorkspaceid` (`workspaceid`)
) TYPE=MyISAM;

--
-- Dumping data for table 'tblobject'
--


INSERT INTO `tblobject` VALUES (936075699,1,1158898744,0,'O:15:\"foowd_text_html\":15:{s:5:\"title\";s:7:\"Welcome\";s:8:\"objectid\";i:936075699;s:7:\"version\";s:1:\"1\";s:7:\"classid\";s:10:\"1158898744\";s:11:\"workspaceid\";s:1:\"0\";s:7:\"created\";s:10:\"1050232200\";s:9:\"creatorid\";s:11:\"-1316331025\";s:11:\"creatorName\";s:4:\"Peej\";s:7:\"updated\";i:1055048794;s:9:\"updatorid\";i:1538498348;s:11:\"updatorName\";s:9:\"ebullient\";s:11:\"permissions\";a:0:{}s:4:\"body\";s:181:\"<h1>Congratulations!</h1>\r\n\r\n<h2>If you can see this page then FOOWD is working, well done.</h2>\r\n\r\n<p>Please follow the instructions in the README file to help get you started.</p>\";s:8:\"evalCode\";s:1:\"0\";s:14:\"processInclude\";s:1:\"0\";}','Welcome','2003-06-08 01:06:34');
INSERT INTO `tblobject` VALUES (1671172598,1,1158898744,0,'O:15:\"foowd_text_html\":15:{s:5:\"title\";s:26:\"Default Method Permissions\";s:8:\"objectid\";s:10:\"1671172598\";s:7:\"version\";s:1:\"1\";s:7:\"classid\";s:10:\"1158898744\";s:11:\"workspaceid\";s:1:\"0\";s:7:\"created\";s:10:\"1054469742\";s:9:\"creatorid\";s:11:\"-1316331025\";s:11:\"creatorName\";s:4:\"Peej\";s:7:\"updated\";i:1055036221;s:9:\"updatorid\";i:1538498348;s:11:\"updatorName\";s:9:\"ebullient\";s:11:\"permissions\";a:11:{s:4:\"view\";s:4:\"Gods\";s:7:\"history\";s:4:\"Gods\";s:5:\"admin\";s:4:\"Gods\";s:6:\"revert\";s:4:\"Gods\";s:6:\"delete\";s:4:\"Gods\";s:5:\"clone\";s:4:\"Gods\";s:3:\"xml\";s:4:\"Gods\";s:11:\"permissions\";s:4:\"Gods\";s:4:\"edit\";s:4:\"Gods\";s:4:\"diff\";s:4:\"Gods\";s:3:\"raw\";s:4:\"Gods\";}s:4:\"body\";s:790:\"<p>This page shows the default method permissions for all the methods of all the classes loaded into this FOOWD system:</p>\r\n<?php\r\nforeach (get_declared_classes() as $class) {\r\n    if (substr($class, 0, 6) == \'foowd_\') {\r\n        echo \'<div class=\"indexhead\">\', $class, \'</div>\';\r\n        echo \'<table cellspacing=\"2\">\';\r\n        foreach (get_class_methods($class) as $method) {\r\n            if ( substr($method, 0, 6) == \'class_\' || \r\n                 substr($method, 0, 7) == \'method_\') {\r\n                $explode = explode(\'_\', $method);\r\n		echo \'<tr><td>\', \r\n                     $method, \'</td><td>&nbsp;</td><td>\', \r\n                     getPermission($class, $explode[1], $explode[0]) ,\r\n                     \'</td></tr>\';\r\n            }\r\n        }\r\n	echo \'</table>\';\r\n    }\r\n}\r\n?>\";s:8:\"evalCode\";s:1:\"1\";s:14:\"processInclude\";s:1:\"0\";}','Default Method Permissions','2003-06-07 21:37:01');
INSERT INTO `tblobject` VALUES (1445327007,1,1158898744,0,'O:15:\"foowd_text_html\":15:{s:5:\"title\";s:17:\"Defined Constants\";s:8:\"objectid\";s:10:\"1445327007\";s:7:\"version\";s:1:\"1\";s:7:\"classid\";s:10:\"1158898744\";s:11:\"workspaceid\";s:1:\"0\";s:7:\"created\";s:10:\"1054468860\";s:9:\"creatorid\";s:11:\"-1316331025\";s:11:\"creatorName\";s:4:\"Peej\";s:7:\"updated\";i:1055036253;s:9:\"updatorid\";i:1538498348;s:11:\"updatorName\";s:9:\"ebullient\";s:11:\"permissions\";a:10:{s:4:\"view\";s:4:\"Gods\";s:7:\"history\";s:4:\"Gods\";s:6:\"revert\";s:6:\"Nobody\";s:6:\"delete\";s:6:\"Nobody\";s:5:\"clone\";s:6:\"Nobody\";s:3:\"xml\";s:6:\"Nobody\";s:4:\"edit\";s:4:\"Gods\";s:4:\"diff\";s:6:\"Nobody\";s:3:\"raw\";s:6:\"Nobody\";s:5:\"admin\";s:4:\"Gods\";}s:4:\"body\";s:320:\"<p>This page shows all PHP constants defined by FOOWD during this execution cycle:</p>\r\n<pre><?php\r\n$ok = FALSE;\r\nforeach (get_defined_constants() as $name => $value) {\r\n	if ($name == \'PATH\') $ok = TRUE;\r\n	if ($ok) {\r\n		if (is_string($value)) $value = \'\"\'.$value.\'\"\';\r\n		echo $name, \' = \', $value, \"\\n\";\r\n	}\r\n}\r\n?></pre>\";s:8:\"evalCode\";s:1:\"1\";s:14:\"processInclude\";s:1:\"0\";}','Defined Constants','2003-06-07 21:37:33');

