$Id$
$Author$
$Date$
--

Preference system
Author: Juan van den Anker <juan@vdanker.net>
=======================================================

Purpose: This system makes it possible to use different locations
to store preference variables without modifying code.


General
===========
The current implementation doesn't allow to use different sources
(or destinations) to store user-preferences without modifying code.
I've created a few basic classes to store preferences and allowing 
them to write to various destinations (file / mysql / etc), depending
on the inherited classes created, without modifying any code. As
described in the next sections.


Basic classes
=================
The basic idea behind this is that is uses a class (class_pref) which
describes all functions needed to manipulate the list of preferences
it carries. Some functions inside this class are : 

  * setPref($name, $value). To set the value of a variabele.
  * getPref($name, $default). To retrieve to value of any given variabele.

This class doesn't know how to save these values to disk or whatever.
The two other classes (fig. 1) makes this possible.

[figure 1]
               +-------------+
               | class_pref  |
               +-+---------+-+
                 |         |
     +-----------+-----+ +-+----------------+
     | class_pref_file | | class_pref_mysql |
     +-----------------+ +------------------+

The last two classes (file and mysql) don't know how to maintain a list
of preferences or how to manipulate them. They don't have to, class_pref
will do that for them... They DO know HOW to store the preferences to
disk or database.

The functions used in these classes will be names the same for all of
the children. For example : class_pref_file::loadPreferences(), which
can be used to load al of the file-preferences into memory.


Example
=============
If you want to retrieve several values from a file, you could use the
following code:

  include_once "class.pref.file.php";
  $pref  = new class_pref_file('global.pref');
  $pref->loadPreferences();
  $username = $pref->getPref('username','default value');

This will read all preferences from the file 'global.pref'. If you want
to retrieve values from mysql, just use another class. The rest of the
code will remain the same!

  include_once "class.pref.mysql.php";
  $pref = new class_pref_mysql();
  $pref->host = ...
  ...
  $pref->loadPreferences();
  $username = $pref->getPref('username','default value');

It's that simple!


Squirrelmail Specific Classes
==================================
As you have seen in the previous examples, it's quite easy to change
the source of the data by using a different class. But, I don't want
to change code if I want to use a different location...
That's what the other class is for (sm_pref.php).

The classes described in the previous section, can be used for any
application. Not just SquirrelMail.

The other class desides what class it should use to store it's preferences.
It uses a preference-file which holds the name of the class it should use.
Therefor there will always be one file using this system... In this case
the file is named 'global.pref'. 

Because of this, you don't have to change ANY code if you want to use a 
different system. Just make it possible to change the value in the file
'global.pref' (using this system), and you can change location on the fly!


Example using global.pref
=============================
If we rewrite the previous example to use the configuration file as mentioned
in the previous section, then the following code would be nescesary:

  include_once "sm_pref.php";
  $username = $pref->getPref('username','default value');

This code can be used for BOTH methods (mysql and file). If you want to switch:
just change the file 'global.pref'. You don't have to change a single line of code.


Work to be done
====================
This is merely an example of how you could be able to use different systems without
modifying code. That's the basic idea behind it. The examples provided in the code
are working but essential code (like error-checking) is missing.

Besides the already written classes, other classes could be created for:

  * PGSQL
  * ODBC
  * etc...


