<?php
/*
 *  $Id$
 *  $Author$
 *  $Date$
 *
 *  An example of how a controller or 
 *  whatever might call the sq core libs
 */

   // get the stuff we need
   include_once("all.inc");

?>
<html>
<body bgcolor="FFFFFF">
<?php
echo "Display \$HTTP_POST_VARS<br>\n";
foreach($HTTP_POST_VARS as $k => $v){
   echo"$k ==>  $v<br>\n";
}

   if (count($HTTP_POST_VARS) > 0) {
   echo "<br>below is the sq constructor<br>\n";
   $hi = new sq;
   $hi->sayHi($username);
   echo "I know the above isn't useful but I am sure 
         the core will need something done when it 
         starts. Maybe?";
   }
?>
<form method="post" action="<?php print $PHP_SELF; ?>">

<!-- --- make a form --- -->
   <center><table border="1" >
   <th colspan="2" bgcolor="blue"><font color="white">Enter Something</font></th>
      <tr>
      <td>Name</td>
      <td><input type="text" name="username" size="20" value="<?php print $username; ?>"></td>
      </tr>
      <tr>
      <td>A word</td>
      <td><input type="text" name="string" size="20" value="<?php print $string; ?>"></td>
      </tr>
      <th colspan="2" bgcolor="blue"><font color="white"><input type="submit" name="submit" value="Submit"></font></td>   
<!-- ---  end form  --- -->
<?php
   if ($string == "") {
      echo "   </table></center></form>\n";
   } else {
      echo "      <tr>\n" .
           "      <th colspan=\"2\">\n" .
           "      <font color=\"blue\">example extends sq, it's constructor says => ";
           $stuff = new example($string);      
      echo "</font></th>\n" .
           "   </table></center></form>\n";
   }
   echo "function done() of class example spews => ";
   $stuff->done();

?>
   
   
