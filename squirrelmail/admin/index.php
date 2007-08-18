<?php

/**
 * index.php
 *
 * This is the source of the configuration page.
 *
 * TODO :
 *  - Add support for locales
 *  - Template this page
 *
 * @copyright &copy; 1999-2007 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package squirrelmail
 */

define('SM_PATH', realpath(dirname(__FILE__).'/..').'/');
require_once SM_PATH.'class/config/configurator.class.php';

$conf = new SMConfigurator(true, true);

if(empty($conf->admin_password))
{
  header('Location: ../index.php');
  exit;
}


ini_set('session.use_cookies', '0');
ini_set('session.name', 'SQCONFID');

session_start();
define('SID', 'SQCONFID='.session_id());
if(isset($_POST['admin_password']))
{
 $_SESSION['admpass'] = md5($_POST['admin_password'].'squirrelmail');
 
 $config = new SMConfigurator(false);
 $_SESSION['confobj'] = serialize($config);
 $_SESSION['made_changes'] = false;
}

if(isset($_GET['logout']))
{
  $_SESSION = array('admpass');
  session_destroy();
}

if($_SESSION['admpass'] && $_SESSION['admpass'] == $conf->admin_password)
{
  $config = unserialize($_SESSION['confobj']);
  
  if(isset($_GET['saveconfig']))
  {
    $result = $config->sources[0]->Save($conf);  
  }  
  
?><html>

<head>
  <title>SquirrelMail Configuration</title>
  <meta http-equiv="Content-Type" content="text/html; charset=<?=$conf->GetVar('default_charset')?>" />
  <style type="text/css">
   a{ color: #333333; }
   .sec{ background-color: #EFEFEF; border: 1px solid gray; padding: 5px;}
   .sdesc{ margin-left:20px; padding:5px;}
   .tcaption{border-top:1px solid black;border-right:1px solid black;background:#DEDEDE; padding-right:10px;}
   .biglink{text-align:center;font-size:12pt;padding:3px;background:#EFEFEF;border:1px solid black;width:300px;}
   .warning{border:1px solid red; background:#FFEFEF; padding:10px;}
  </style>
</head>

<body>
<?php

if(isset($_POST['save']))
{
  unset($_POST['save']);
//var_dump($_POST);
  foreach($_POST as $name => $value)
  {
    if(!is_null($config->GetVar($name)))
    {
      $config->SetVar($name, $value);
    }
    elseif(substr($name, 0, 5) == '_add_' && is_array($value))
    {
      if(!isset($value['_add'])) continue;
      $name = substr($name, 5);
      
			unset($value['_add']);
      $values = $config->GetVar($name);
      
      if(is_array($values))
      {
        $values[] = $value;
        $config->SetVar($name, $values);
      }      
    }
    elseif(substr($name, 0, 5) == '_add_' && !empty($value))
    {
      $name = substr($name, 5);
      $values = $config->GetVar($name);
      
      if(is_array($values))
      {
        $values[] = $value;
        $config->SetVar($name, $values);
      }
    }
    elseif(substr($name, 0, 5) == '_del_' && is_array($value))
    {
      $name = substr($name, 5);
      $values = $config->GetVar($name);
      
      foreach($values as $key=>$val)
      {
        if(in_array($key, $value))
          unset($values[$key]);
      }
      $config->SetVar($name, $values);
		}    
  }
  $_SESSION['confobj'] = serialize($config);
  $_SESSION['made_changes'] = true;
}

if(isset($_GET['section']) && is_array($section = $conf->get_section($_GET['section'])))
{
?>
<h1><?=$section['title']?></h1>
<br/>
<div class="biglink"><a href="?<?=SID?>">Back to section list</a></div>
<br />

<form action="?<?=SID?>" method="post">

<table align="center" width="75%" border="0" bgcolor="#EFEFEF" cellspacing="0" cellpadding="4" style="border:1px solid black; border-top:0px none;">
<?
  foreach($section['vars'] as $name)
  {
   $type = $conf->get_type($name);
   $desc = $conf->get_meta($name, "description");
   $value = $config->GetVar($name);
?>

<tr>
  <td class="tcaption" width="40%" align="right"><?=_($desc)?> :</td>
  <td style="border-top:1px solid black;"><?
   switch($type[0])
   {
     case SM_CONF_BOOL:
       echo '<input type="radio" value="1" name="'.$name.'"'.($value? ' checked' : '').'> Yes &nbsp; &nbsp; <input type="radio" value="0" name="'.$name.'"'.(!$value? ' checked' : '').'> No';
       break;
     case SM_CONF_PATH:
       $value = str_replace(SM_PATH, 'SM_PATH', $value);
     case SM_CONF_STRING:
     case SM_CONF_INTEGER:
       echo '<input type="text" name="'.$name.'" value="'.$value.'" size="'.$type[1].'">';
       break;
     case SM_CONF_KEYED_ENUM: $keyed = true;
     case SM_CONF_ENUM:
       echo '<select name="'.$name.'">';
     
       $values = explode(',', $type[1]);
       foreach($values as $val)
       {
         if($keyed)
         {
           list($val,$caption) = explode('=', $val, 2);
           $caption = _($caption);
         }
         else
         {
           $caption = $val;
         }
         echo '<option'.($val==$value ? ' selected':'').' value="'.$val.'">'.$caption.'</option>';
       }
     
       echo '</select>';     
       $keyed = false;
       
       break;
     case SM_CONF_ARRAY_ENUM:
       $enum = $type[1];
       echo '<select name="'.$name.'">';
       
       foreach($config->GetVar($enum) as $id => $option)
       {
         echo '<option value="'.$id.'"'.($id==$value?' selected':'').'>'.current($option).'</option>';
       }
              
       echo '</select>';
       break;
     case SM_CONF_ARRAY:
       $protect_index = $conf->get_meta($name, 'readonly');
       if(is_null($protect_index)) $protect_index = 0;
       
       list($array_type, $params) = explode(',', $type[1], 2);
       switch($array_type)
       {
         case SM_CONF_ARRAY_SIMPLE:
           echo '(select one or more to delete) :<br /><select name="_del_'.$name.'[]" size="3" style="width:250px;" multiple>';
           
           foreach($value as $id => $option)
           {
             echo '<option value="'.$id.'">'.$option.'</option>';
           }
           
           echo '</select><br />Add a value : <input type="text" name="_add_'.$name.'" value="">';
         break;
         case SM_CONF_ARRAY_KEYS:
           echo '<table width="100%"><tr bgcolor="#BBBBBB">';
           $params = explode(',', $params);
           foreach($params as $key) echo "<td>$key</td>";
         echo '<td width="100">Add / Delete</td></tr>';
         
         foreach($value as $id => $option)
         {
          echo '<tr bgcolor="#DFDFDF">';
          foreach($params as $key) echo "<td>".$option[$key]."</td>";
          echo '<td align="center"><input type="checkbox" name="_del_'.$name.'[]" value="'.$id.'"'.($id < $protect_index ? ' disabled' : '').'>';
          echo "</td>\n</tr>\n";
         }

         echo '<tr bgcolor="#CCCCCC">';
         foreach($params as $key) echo '<td><input type="text" name="_add_'.$name."[$key]".'" value="New '.strtolower($key).'" style="width:100%;"></td>';
         echo '<td align="center"><input type="checkbox" name="_add_'.$name.'[_add]" value="1"></td>';
         echo "\n</tr>\n";
         
         echo '</table>';
         break;
       }
     
       break;
    }
?></td>
</tr>
<?php
  }
?>
<tr>
  <td colspan="2" style="background:#EFEFEF;border-top:1px solid black;" align="center"><input type="submit" name="save" value="Commit my changes !" /></td>
</tr>
</table></form>
<?php
}
else
{
  $sections = $conf->get_section();
?>
<h1>Select a section :</h1>
<?php if($_SESSION['made_changes']){ ?><div class="warning">You changes have no effects until you save your new configuration ! Use the link below to save your new settings.</div><?php } ?>

<table border="0" width="100%" cellspacing="5">
<?php
 $i = 0;
 foreach($sections as $name => $section)
 {
   if($i++ == 0) echo '<tr>';
   echo '<td width="50%"><div class="sec"><b><a href="?section='.$name.'&'.SID.'">'.$section['title'].'</a></b> :<br /><div class="sdesc">'._($conf->get_meta($name, 'description')).'</div></div></td>'."\n";
   if($i==2){ echo '</tr>'; $i=0; }
 }
 if($i == 1) echo "<td>&nbsp;</td>\n</tr>\n\n";
?>
</table>

<?php
}
?>

<br /><br />
<span class="biglink"><a href="?saveconfig&<?=SID?>">Save my new configuration</a></span> &nbsp; <span class="biglink"><a href="?logout&<?=SID?>">Logout</a></span>

</body>

</html>
<?php

session_write_close();
}
else
{
?>
<html>

<head>
  <title>SquirrelMail Configuration</title>
  <meta http-equiv="Content-Type" content="text/html; charset=<?=$conf->GetVar('default_charset')?>" />
</head>

<body>

<form method="post" action="index.php">

<table cellspacing="0" align="center">
 <tr>
  <td class="sqm_loginTop" colspan="2">
   <img src="../images/sm_logo.png" class="sqm_loginImage" alt="SquirrelMail Logo" width="308" height="111" /><br />SquirrelMail Configuration<br />&nbsp;</td>
 </tr>
 <tr>
  <td>Administrator password : &nbsp;</td>
  <td><input type="password" name="admin_password" value="" /></td>
 </tr>
 <tr>
  <td colspan="2" align="center"><br /><input type="submit" value="Login" /></td>
 </tr>
</table>

</form>

</body>

</html>
<?php
}

