<?php
/**
 * index.php - Index for planning documents
 *
 * Copyright (c) 1999-2002 The SquirrelMail development team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 *
 * $Id$
 */
include_once('common_header.inc');
set_title('Document Index');
/* set_original_author('ebullient'); */
/* set_attributes('$Author$','$Revision$','$Date$'); */
print_header();

function print_links() {
 echo '<p class="link-bar">';
 echo '<a href="#doclist">Planning Documents</a> | ';
 echo '<a href="#doccreate">Making Comments</a> | ';
 echo '<a href="#doccreate">Creating New Documents</a>';
 echo '</p>';
}

?>

<?php print_links(); ?>
<a name="doclist"></a>
<H2>Planning Documents</H2>

<a href="dev_todo.php">Active Dev Items</a><br />
<br />
<a href="config_revisions.php">Config revisions (devel)</a><br />
<br />
<a href="plugin_revisions.php">Plugin revisions (stable/devel)</a><br />

<?php print_links(); ?>
<a name="doccreate"></a>
<H2>Making Comments</H2>

<H3>Have you picked a color/style?</H3>
<OL>
<LI>Check out a copy of sm2-planning.</LI>
<LI>Edit developer.css<br />
Append a line with your sourceforge id, and a hex color of your choice that
is readable against a white background.<br />
The line should resemble this: <br />
<pre>.ebullient      { color:#666699; }</pre>
</LI>
<LI>Save and commit developer.css<br />
</OL>

<H3>Marking your comments</H3>

<P>Now that you have a color, open the file you wish to make comments in.
Find the place where you'd like to place your comment, and do 
something like the following (using span or p tags):
<pre>
&lt;p class="your_id"&gt;&amp;lt;your_id&amp;gt; 2002/11/19 &lt;br /&gt;
I personally think this entire system is way overdone. 
We should be less anal. &lt;br /&gt;
&amp;lt;/your_id&amp;gt;&lt;/p&gt;
</pre>
</p>

<P>Yuck, you say? Well sure. But let's use 'sample' as an example, this is 
what that mucky comment would look like without all the formatting gorp:</p>

<p class="sample">&lt;sample&gt; 2002/11/19 <br />
I personally think this entire system is way overdone. 
We should be less anal. &lt;/sample&gt;</p>

<P>There are variations to this of course, and you can play to your 
heart's content. Comment guidelines (to keep things readable):
<UL>
<LI>Beginning and end of comment should be clearly marked
<LI>Comment should be dated (should discussions/notes be ongoing,
dates will be useful).
<LI>Try to insert comments only between paragraphs/blocks of text.
<LI>Try to pick colors that don't make your eyes bleed ('sample' above is
an example of a color that can put your eyes out on some machines.. be kind).
</UL>


<?php print_links(); ?>
<a name="doccreate"></a>
<H2>Creating New Documents</H2>

<p>To create a new Document, use the following template, updating 
document_title and sourceforge_id appropriately:

<pre>
&lt;?php
/**
 * filename.php - description of what file is for
 *
 * Copyright (c) 1999-2002 The SquirrelMail development team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * &#36;Id&#36;
 */
include_once('common_header.inc');
set_title('document_title');
set_original_author('sourceforge_id');
set_attributes('&#36;Author&#36;','&#36;Revision&#36;','&#36;Date&#36;');
print_header();
?&gt;

&lt;!-- Content here --&gt;

&lt;/body&gt;
&lt;/html&gt;
</pre>

<p>The following is pure retentiveness. You don't have to read it.

<p>common_header.inc will draw a standard header, similar to the
one at the top of this index, containing the following information:

<UL>
<LI><b>Document Title</b><br />
The title passed as an argument to $set_title will be appended to 
'SquirrelMail Planning: '. e.g. the title of this document is 'Document Index',
and hence, the title of the page, and the title shown in the header is
'SquirrelMail Planning: Document Index'.</LI>
<LI><b>Original Author Id</b><br />
For posterity, the original author's id will be preserved in the
header.</LI>
<LI><b>Modification Information</b><br />
Other information is filled in with CVS keys. 
The id of the last editor, along with the current
revision number and date, will also be displayed in the
header.</LI>
</UL>

<?php print_links(); ?>

</body>
</html>
