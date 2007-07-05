<?php
require_once "config_class.php";

define('SM_PATH', 'SM_PATH');
$conf = new SQMConfigFile("meta_config.php");
$conf->SQMConfigFile("default_config.php", true);
?><html>

<head>
  <title>SquirrelMail Configuration</title>
  <meta http-equiv="Content-Type" content="text/html; charset=<?=$conf->V('default_charset')?>" />
</head>

<body>
<?php

if(isset($_GET['section']) && is_array($section = $conf->get_section($_GET['section'])))
{
?>
<h1><?=$section['title']?></h1>

<a href="?">Back to section list</a><br />

<form action="?section=<?=$_GET['section']?>" method="post">

<table align="center" width="80%" border="0">
<?
foreach($section['vars'] as $name)
{
 $type = $conf->get_type($name);
 $desc = $conf->get_desc($name);
?>

<tr>
  <td width="40%" align="right"><?=$desc?> :</td>
  <td><?
 switch($type[0])
 {
   case SM_CONF_BOOL:
     echo '<input type="checkbox" name="'.$name.'"'.($conf->V($name)? ' checked' : '').'>';
     break;
   case SM_CONF_STRING:
   case SM_CONF_INTEGER:
     echo '<input type="text" name="'.$name.'" value="'.($conf->V($name)).'" size="'.$type[1].'">';
     break;
   case SM_CONF_KEYED_ENUM: $keyed = true;
   case SM_CONF_ENUM:
     echo '<select name="'.$name.'">';
     
     $values = explode(',', $type[1]);
     foreach($values as $value)
     {
       if($keyed)
       {
         list($value,$caption) = explode('=', $value, 2);
         $caption = _($caption);
       }
       else
       {
         $caption = $value;
       }
       echo '<option'.($conf->V($name)==$value ? ' selected':'').' value="'.$value.'">'.$caption.'</option>';
     }
     
     echo '</select>';
     
     $keyed = false;
     break;
 }
}
?></td>
</tr>

</form>
<?
}
else
{
  $sections = $conf->get_section();
?>
<h1>Select a section :</h1>

<ul>
<?php
 foreach($sections as $name => $section)
 {
   echo '<li><a href="?section='.$name.'">'.$section['title'].'</a> : '.$section['desc'].'</li>';
 }
?>
</ul>
<?php
}
?>

</body>

</html>
