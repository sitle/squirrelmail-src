<?php
/**
 * config_revisions.php - description of what file is for
 *
 * Copyright (c) 1999-2002 The SquirrelMail development team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */
include_once('common_header.inc');
set_title('Config Revisions');
set_original_author('ebullient, grootkoerkamp');
set_attributes('$Author$','$Revision$','$Date$');
print_header();

function print_links() {
 echo '<p class="link-bar">';
 echo '<a href="#version">Versioning</a> | ';
 echo '<a href="#config">Configuration Process</a> | ';
 echo '<a href="#data_format">Data Format</a> | ';
 echo '<a href="#paths">Setting Paths/SM Initialization</a>';
 echo '</p>';
}

?>

<P>Modifications need to be made to our current configuration 
mechanism (conf.pl and php admin plugin both modify config.php). 

<P>Also seeking better mechanism for setting paths, especially
paths to elements potentially located outside of the SM tree.

<P><a href="plugin_revisions.php">Plugin Revisions</a> are also 
relevant to this discussion.

<?php print_links(); ?>
<a name="versioning"></a>
<H2>Versioning</H2>

<p><b>Target Release: 1.3.x and up (added 12/29 to 1.3.3 CVS)</b></p>

Due to changes in the 1.3.x stream (SM_PATH, etc.) and upcoming changes
slated for 1.4/1.5, it's now necessary to allow plugins to check for the 
SquirrelMail version, in order to procede properly.</p>

<p>To that end, FUTURE versions of SM (including the current dev stream), 
will define an internal global constant, $SQM_INTERNAL_VERSION, as an array
containing 3 parts (release, major, minor) 
(defined in functions/strings.php - with other version constant).</p>

<p>Also in global.php will be a version checking function that will take 
arguments similar to those provided for php version checking, and will 
return true if the current version is >= the one specified 
(just as check_php_version does).</p>

<p class="ebullient">&lt;ebullient - 12/29&gt;<br />
Updated this section to reflect what I added to 1.3.3 CVS (release/major/minor).
The constants in strings.php (for version) should be moved to sm_init.php, 
in my opinion (as described below).<br />
&lt;/ebullient - 12/29&gt;</p>

<?php print_links(); ?>
<a name="config"></a>
<H2>Configuration Process</H2>
<p><b>Target Release: 1.5</b></p>

<P><b>Configuration system plans</b></p>

<P>Currently we have 3 systems for configuration settings.

<UL><LI>Perl utility conf.pl for system settings
    <LI>    admin plugin for systems settings
    <LI>config.php for user preferences
</UL></p>

<p>In order to simplify maintainance of SquirrelMail we should rewrite the config system and make a system that can handle system settings and user preferences because they are all the same.</p>

<p>The system for system settings we have right now is based on hard coded config-entries inside conf.pl or admin.php. Such system is static and quickly out of sync.</p>

<p>To achieve a more flexible, easier to maintain config system we should bundle the 3 systems by separating the data (the config entries) and the logic and use the same logic for system settings and user preferences. </p>

<?php print_links(); ?>
<a name="data_format"></a>
<H2>The Data Format</H2>

<p>We have to define a format for storing information about config settings. 
That format should be extendable for future enhancements.</p>

<p>If we look at the current hard coded information regarding config 
entries we make use of the following information:

<UL><LI>Short description
    <LI>Long description
    <LI>Variable name
    <LI>Default value
</UL></p>

<p>We can also say that we have different data formats:

<UL><LI>Boolean
    <LI>String
    <LI>Integer
    <LI>Lookup list
</UL></p>

<P>Lookup list is the most complex one and will get extra attention later in this document.</p>

<P>With the just described parts of a config entry it should be possible to define a format for storing the complete entry in an external file.</p>

<P>A good example of a format that will be suitable for the job is the ini file format. For example purposes I will use that format in the rest of this document. Later we can decide what the best format for SquirrelMail is.</p>

<P>The ini format consist out of a section header and a list of key value pairs belonging to the section.</p>

<P>Example:<br />
<pre>
	[sectionheader1]
	key1 = value1
	key2 = value2

	[sectionheader2]
	key1 = value1
	....
</pre></p>

<p>Back to a config-entry, the section header would be an unique
identifier for the related variable.</p>

<p>Let's take a simple config-entry, namely Organisation name in conf.pl.</p>

<p>The entry in an external datafile would be like this:<br />
<pre>
	[OrgName]
	description_short = 'Organisation name'
	description_long  = '<name_of_choice>'
	default_value 	  = 'SquirrelMail'
	variable_type     = string
        variable_name     = OrgName
</pre></p>

<p>As you can see the variable_name is the same as the section header. In the example ini file model we could use the section header for storing the variable_name.</p>

<p>The description_short name will be the entry name for accessing the
related config-var (for lists of config_vars).</p>

<p>To render a page with config-settings we only need an engine that can read the external config file and know how to handle the keys.</p>

<p>By defining new keys, program the logic in the config-systems (perl / php) you can extend the possibilities of the different config entries.</p>

<p>A good addition to SquirrelMail would be disabling specific user preferences or even override user preferences.</p>

<p>A way to implement it could be defining an extra key for use in the config sections that will show us an extra checkbox for hiding certain config-entries for users or in case of a perl implementation show us an extra entry on the screen where we can set hide to true or false.</p>

<p>Same counts for disabling and overriding. It's just a matter of defining the keys, adapt the external config file for enabling the new functionality on specific sections and program the logic.</p>

<h3>Lookup fields</h3>

<p>Like mentioned before, the lookup fields deserve special attention.</p>

<p>A lookup field is a field with a limited set of values. In the most simple case a lookup field looks up it's allowed values from a predefined list.</p>

<p>More advanced lookup lists are for example the way we choose themes, enable/disable plugins. First case is a lookup list based on files in a certain directory and place a filter on the files for displaying purposes (strip .php from the filenames). The second case is a lookup list based on existing directories in the plugin directory.<p>

<p>To implement those lookup capabilities we need additional information regarding the lookup type and we probably need to split up the lookup type in more specific lookup types like:
<UL><LI>lookup_list - static list
    <LI>lookup_filesystem - different config file
</UL>

<p>In case of lookup_list (static list) we also need to provide the list entries and that's why we need another key for providing that information.</p>

<p>A config section for a lookup_list called list might look like:
<pre>
	variable_type = lookup_list
	list = entry1, entry2, entry3
</pre></p>

<p>In case of lookup_filesystem we need keys specifying the basedir, 
the mask and a filter. With mask we specify what files to display or 
what directories to display.

<h3>Grouping</h3>

<p>The only thing discussed yet is the description of defining single 
config-entries. To develop a usable system we also need to define a 
way for accessing pages with organised related sets of config
entries (group).</p>

<p>A group could be all config-entries related to a specific
component in SquirrelMail. A group could also be a single
config entry that needs multiple values like for example 
the specification of an imap server address (hostname, port).</p>

<p>Requirements:
<UL><LI>Grouped settings can contain other grouped settings. (hierarchical)
    <LI>Groups should be accessible by a menu system.
    <LI>Group-hierarchy can be provided by an external datafile(s)
</UL>    

<p>We already use the section header for specifying a related set 
belonging to one config-entry. Now we need to define something
similar for specifying a group.</p>

<p>One approach (there are multiple approaches possible!) is using the 
same section header for specifying a group and use a predefined 
keyname that tell is that the data that follows belongs to a group. 
There is However one difference, we need a closing method for 
defining the end of the group.</p>

<p>Example: <br />
<pre>
	[groupname]
	group=inline
	description_short = 'A grouped set of vars'

	[groupedvar1]
	key1 = value1
	key2 = value2
	
	[groupedvar2]
	key1 = value1
	key2 = value2

	[/groupname]
</pre></p>

<p>In the example, '/' is used for closing the group.

<p>With key-name 'group' I specify that the sections that 
follow belong to the same group. In this case the value of group = inline. 
Inline means that the needed data is in the current open datafile. </p>

<p>If we use as value 'external' we have to include the external file 
and get the specified settings from that file. However, 'external' is 
not sufficient. We need a location of the external file and that's 
why we introduce another key-name 'group_location'.

<p>Summary:
<UL><LI>hierarchical grouping
    <LI>key-names: group, group_location
    <LI>internal / external data
</UL>

<h3>Storing / Accessing config data</h3>

<p>First we need to define how we access the config-vars.

<p>The best approach is defining arrays which can be accessed by key. The key should be the name of the variable we want to access just like we defined in the config datafile. In case we have to deal with hierarchical groups we access the group by key by using the group-name as key. The value is an array just like just described.

<p>Now we need to define how to write the array structure into some persistant area. Because we said we will use the same architecture for user-preferences and system-settings we have to deal with different ways of storing the array-structure.

<p>System settings are written to a php file that is included as config.php.

<p>User-preferences can be stored in a db backend or in an user.pref file.

<p>The db backend can be problematic because it's developed for single key-value pairs instead of structural data. If we decide that all configuration related data should be accessible all over the place we might consider to serialize the whole config-structure and save it in a blob field. On login we store it in a session and we never need to call the db backend again during the session unless we are changing config-settings (then we have to update the whole structure to the blob instead of a single value).

<p>Even better would be writing our own sessionhandler and program a method that will write back the config-data to the persistant backend on a session-expire or on a session-destroy.

<p>Right now I do not know how it will influence speed.

<p>When it comes to filebased storing of information we should write the whole config structure to a php file that can be included directly without any parsing. At this moment we do that for system settings but we can use this also for user preferences.

<P>To be continued....<br />
undocumented parts:
<UL><LI> include plugin config pages
    <LI> Acces Control Lists and the config system
    <LI> Validating information
    <LI> Related entries (excluding / adapting lookup values / required modules)
    <LI> external lookup data from sockets (imap capabilities for example)
    <LI> hiding config-entries for users
    <LI> admin override config-entries
    <LI> .....
</UL>

<?php print_links(); ?>
<a name="config"></a>
<H2>Setting Paths/SM Initialization - sm_init.php</H2>

<p><b>Target Release: 1.5</b></p>

</body>
</html>
