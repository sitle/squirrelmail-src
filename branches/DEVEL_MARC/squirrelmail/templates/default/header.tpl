  <?php

/**
 * header.tpl
 *
 * Copyright (c) 1999-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Template for the page header
 *
 * @version $Id$
 * @package squirrelmail
 */

function page_header() {
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<head lang="<?php echo $squirrelmail_language;?>">

<?php if ( (!isset( $custom_css ) || $custom_css == 'none') && $theme_css) {?>
    <link rel="stylesheet" type="text/css" href="<?php echo $theme_css;?>" />
<?php } else {?>
    <link rel="stylesheet" type="text/css" href="<?php echo $base_uri . 'themes/css/'.$custom_css;?>" />
<?php }?>

<title><?php echo $title;?></title>

<?php echo $xtra;?>

<style type="text/css">
<!--
  /* avoid stupid IE6 bug with frames and scrollbars */
  body {
      voice-family: "\"}\"";
      voice-family: inherit;
      width: expression(document.documentElement.clientWidth - 30);
  }
//-->
</style>

<script language="JavaScript" type="text/javascript">
<!--
    function toggle_all(formname) {
        var form = document.getElementById(formname);
        for (var i = 0; i < form.elements.length; i++) {
            if(form.elements[i].type == 'checkbox' &&  form.elements[i].name.substring(0,3) == 'msg'){
                form.elements[i].checked = !(form.elements[i].checked);
            }
        }
    }
//-->
</script>

</head>

<?php

}
?>
