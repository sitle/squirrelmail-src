$Id$
$Author$
$Date$
--

Proposal for base class or include file for core and controller
Author: Lewis Bergman <lbergman@squirrelmail.org>
=======================================================

Purpose:  To hopefully display in some minor way why sm2 should have this.
	  Or maybe not.


Pros
===========
The example seems stupid I know.
But:
   Add a new extension of sq and there is no need to change
   any files other than all.inc or whatever you want
   to name it.
   
   All vars declared in the sq class can be used by any extension
   without a global declaration.
   
   Make a new function for all the core to use that doesn't need
   a file of its own and it is available. I would argue that this
   last point is a non-issue since a misc.inc should probably be
   made to hold those kind of things.
   
   Seems easier than including ten files and I doubt there
   is a speed penalty of any consequence.

Cons
===========
   Hey, this is my example! Put some here and slam away!

