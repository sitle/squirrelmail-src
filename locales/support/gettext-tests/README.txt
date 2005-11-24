This directory stores scripts that can be used to test gettext
support in used OS. Test results are stored in results.txt

PHP gettext extension should be tested with system call trace utility.
You should check which .mo files are opened.

Available system call tracing utilities
---------------------------------------
Linux   - strace ('strace -o test.log php gettext.php' command)
OpenBSD - ktrace
Solaris - truss ('truss -o test.log php gettext.php' command)

Situations that should be checked
---------------------------------
1. system locale is 
  a) available
  b) unavailable
  c) uses different character set

2. test setlocale() calls and setlocale()+putenv() calls.

3. test short locale names. (setlocale(LC_ALL,'ru_RU'))

OS specifics
------------
* Solaris
  - check Sun gettext and GNU gettext libraries
  - check use of NLSPATH environment variable

