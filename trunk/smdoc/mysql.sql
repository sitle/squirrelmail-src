#
# Data creation script for FOOWD v0.8.4
#
# This script will create the database structure for the Foowd library,
# this consists of a single database table which will be populated with
# a one row defining the HTML object used by the demo/ application.
#

#
# Table structure for table 'tblobject'
#

CREATE TABLE `tblobject` (
  `objectid` int(11) NOT NULL default '0',
  `version` int(10) unsigned NOT NULL default '1',
  `classid` int(11) NOT NULL default '0',
  `workspaceid` int(11) NOT NULL default '0',
  `object` longblob,
  `title` varchar(255) NOT NULL default '',
  `updated` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`objectid`,`version`,`classid`,`workspaceid`),
  KEY `idxtblObjectTitle`(`title`),
  KEY `idxtblObjectupdated`(`updated`),
  KEY `idxtblObjectObjectid`(`objectid`),
  KEY `idxtblObjectClassid`(`classid`),
  KEY `idxtblObjectVersion`(`version`),
  KEY `idxtblObjectWorkspaceid`(`workspaceid`)
) TYPE=MyISAM COMMENT='';

#
# Dumping data for table 'tblobject'	
#

INSERT INTO `tblobject` VALUES("936075699","1","1158898744","0","O:15:\"foowd_text_html\":15:{s:5:\"title\";s:7:\"Welcome\";s:8:\"objectid\";s:9:\"936075699\";s:7:\"version\";s:1:\"1\";s:7:\"classid\";s:10:\"1158898744\";s:11:\"workspaceid\";s:1:\"0\";s:7:\"created\";s:10:\"1050232200\";s:9:\"creatorid\";s:11:\"-1316331025\";s:11:\"creatorName\";s:4:\"Peej\";s:7:\"updated\";i:1053681659;s:9:\"updatorid\";s:11:\"-1316331025\";s:11:\"updatorName\";s:4:\"Peej\";s:11:\"permissions\";a:5:{s:5:\"admin\";s:4:\"Gods\";s:6:\"delete\";s:4:\"Gods\";s:5:\"clone\";s:4:\"Gods\";s:4:\"edit\";s:4:\"Gods\";s:11:\"permissions\";s:4:\"Gods\";}s:4:\"body\";s:181:\"<h1>Congratulations!</h1>\r\n\r\n<h2>If you can see this page then FOOWD is working, well done.</h2>\r\n\r\n<p>Please follow the instructions in the README file to help get you started.</p>\";s:8:\"evalCode\";s:1:\"0\";s:14:\"processInclude\";s:1:\"0\";}","Welcome","2003-05-23 10:20:59");
