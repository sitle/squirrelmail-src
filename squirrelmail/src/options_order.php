<?php

/**
 * options_order.php
 *
 * Copyright (c) 1999-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Displays messagelist column order options
 *
 * @version $Id$
 * @package squirrelmail
 */

/**
 * Path for SquirrelMail required files.
 * @ignore
 */
define('SM_PATH','../');

/* SquirrelMail required files. */
require_once(SM_PATH . 'include/validate.php');
require_once(SM_PATH . 'functions/global.php');
require_once(SM_PATH . 'functions/display_messages.php');
require_once(SM_PATH . 'functions/imap.php');
require_once(SM_PATH . 'functions/plugin.php');
require_once(SM_PATH . 'functions/html.php');
require_once(SM_PATH . 'functions/forms.php');
require_once(SM_PATH . 'functions/arrays.php');
require_once(SM_PATH . 'functions/options.php');

/* get globals */
if (sqgetGlobalVar('num',       $num,       SQ_GET)) {
   $num = (int) $num;
} else {
   $num = false;
}
if (!sqgetGlobalVar('method', $method)) {
    $method = '';
} else {
    $method = htmlspecialchars($method);
}
if (!sqgetGlobalVar('positions', $pos, SQ_GET)) {
    $pos = 0;
} else {
    $pos = (int) $pos;
}

if (!sqgetGlobalVar('account', $account, SQ_GET)) {
    $iAccount = 0;
} else {
    $iAccount = (int) $account;
}

if (sqgetGlobalVar('mailbox', $mailbox, SQ_GET)) {
   $aMailboxPrefs = unserialize(getPref($data_dir, $username, "pref_".$iAccount.'_'.urldecode($mailbox)));
   if (isset($aMailboxPrefs[MBX_PREF_COLUMNS])) {
       $index_order = $aMailboxPrefs[MBX_PREF_COLUMNS];
   }
} else {
    $index_order_ser = getPref($data_dir, $username, 'index_order');
    if ($index_order_ser) {
        $index_order=unserialize($index_order_ser);
    }
}
if (!isset($index_order)) {
    $index_order = array(SQM_FLD_CHECK,SQM_FLD_FROM,SQM_FLD_DATE,SQM_FLD_SUBJ,SQM_FLD_FLAGS);
}
if (!sqgetGlobalVar('account', $account,  SQ_GET)) {
   $account = 0; // future work, multiple imap accounts
} else {
   $account = (int) $account;
}







/* end of get globals */
    define('SMOPT_GRP_ORDER',0);
define('SMOPT_MODE_SUBMIT', 'submit');

    $optpage_data = array();
    $optpage_data = '';//$optpage_loader();
    //do_hook($optpage_loadhook);

    $optpage_name = _("Index Order");
    $optpage_file = SM_PATH . 'include/options/order.php';
    $optpage_loader = 'load_optpage_data_order';
    $optpage_loadhook = 'optpage_loadhook_order';
    $optpage = 'order';

    $optgrps[SMOPT_GRP_ORDER] = _("Mailbox Column Order Options");
    $optvals[SMOPT_GRP_ORDER] = array();

    $available[SQM_COL_CHECK]      = _("Checkbox");
    $available[SQM_COL_FROM]       = _("From");
    $available[SQM_COL_DATE]       = _("Date");
    $available[SQM_COL_SUBJ]       = _("Subject");
    $available[SQM_COL_FLAGS]      = _("Flags");
    $available[SQM_COL_SIZE]       = _("Size");
    $available[SQM_COL_PRIO]       = _("Priority");
    $available[SQM_COL_ATTACHMENT] = _("Attachments");
    $available[SQM_COL_INT_DATE]   = _("Received");
    $available[SQM_COL_TO]         = _("To");
    $available[SQM_COL_CC]         = _("Cc");
    $available[SQM_COL_BCC]        = _("bcc");

    $aDummy = array(SQM_COL_CHECK,SQM_COL_SUBJ,SQM_COL_TO,SQM_COL_FLAGS);
    foreach ($aDummy as $value) {
        $aValue[$value] = $available[$value];
    }
    $aDummy = $aValue;

    $optvals[SMOPT_GRP_ORDER][] = array(
        'name'    => 'aDummy',
        'caption' => _("Column Order"),
        'type'    => SMOPT_TYPE_HDRLIST,
        'refresh' => SMOPT_REFRESH_ALL,
        'posvals' => $available,
        'save'    => 'save_option_header'
    );

    $optpage_data['options'] =
        create_option_groups($optgrps, $optvals);



$optpage_title = "TEST";

/***************************************************************/
/* Finally, display whatever page we are supposed to show now. */
/***************************************************************/

displayPageHeader($color, 'None', (isset($optpage_data['xtra']) ? $optpage_data['xtra'] : ''));

echo html_tag( 'table', '', 'center', $color[0], 'width="95%" cellpadding="1" cellspacing="0" border="0"' ) . "\n" .
        html_tag( 'tr' ) . "\n" .
            html_tag( 'td', '', 'center' ) .
                "<b>$optpage_title</b><br>\n".
                html_tag( 'table', '', '', '', 'width="100%" cellpadding="5" cellspacing="0" border="0"' ) . "\n" .
                    html_tag( 'tr' ) . "\n" .
                        html_tag( 'td', '', 'center', $color[4] ) . "\n";


    echo addForm('options_order.php', 'POST', 'f')
       . create_optpage_element($optpage)
       . create_optmode_element(SMOPT_MODE_SUBMIT)
       . html_tag( 'table', '', '', '', 'width="100%" cellpadding="2" cellspacing="0" border="0"' ) . "\n"
       . html_tag( 'tr' ) . "\n"
       . html_tag( 'td', '', 'left' ) . "\n";

    /* Output the option groups for this page. */
    print_option_groups($optpage_data['options']);

    $inside_hook_name = '';
    $bottom_hook_name = '';
    $submit_name = 'submit';

    /* If it is not empty, trigger the inside hook. */
    if ($inside_hook_name != '') {
        do_hook($inside_hook_name);
    }

    /* Spit out a submit button. */
    OptionSubmit($submit_name);
    echo '</td></tr></table></form>';

    /* If it is not empty, trigger the bottom hook. */
    if ($bottom_hook_name != '') {
        do_hook($bottom_hook_name);
    }


echo        '</td></tr>' .
        '</table>'.
        '</td></tr>'.
     '</table>' .
     '</body></html>';












/**
 * Change the column order of a mailbox
 *
 * @param array  $index_order (reference) contains an ordered list with columns
 * @param string $method action to take, move, add and remove are supported
 * @param int    $num target column
 * @param int    $pos positions to move a column in the index_order array
 * @return bool  $r A change in the ordered list took place.
 */
function change_columns_list(&$index_order,$method,$num,$pos=0) {
    $r = false;
    switch ($method) {
      case 'move': $r = sqm_array_move_value($index_order,$num,$pos); break;
      case 'add':  $index_order[] = (int) $num; $r = true; break;
      case 'remove':
        if(in_array($num, $index_order)) {
            unset($index_order[array_search($num, $index_order)]);
            $index_order = array_values($index_order);
            $r = true;
        }
        break;
      default: break;
    }
    return $r;
}

/**
 * Column to string translation array
 */
$available[SQM_COL_CHECK]      = _("Checkbox");
$available[SQM_COL_FROM]       = _("From");
$available[SQM_COL_DATE]       = _("Date");
$available[SQM_COL_SUBJ]       = _("Subject");
$available[SQM_COL_FLAGS]      = _("Flags");
$available[SQM_COL_SIZE]       = _("Size");
$available[SQM_COL_PRIO]       = _("Priority");
$available[SQM_COL_ATTACHMENT] = _("Attachments");
$available[SQM_COL_INT_DATE]   = _("Received");
$available[SQM_COL_TO]         = _("To");
$available[SQM_COL_CC]         = _("Cc");
$available[SQM_COL_BCC]        = _("bcc");

if (change_columns_list($index_order,$method,$num,$pos)) {
    if (isset($mailbox) && $mailbox) {
        $aMailboxPrefs[MBX_PREF_COLUMNS] = $index_order;
        setPref($data_dir, $username, "pref_".$iAccount.'_'.urldecode($mailbox), serialize($aMailboxPrefs));
    } else {
        setPref($data_dir, $username, 'index_order', serialize($index_order));
    }
}

$opts = array();
if (count($index_order) != count($available)) {
    for ($i=0; $i < count($available); $i++) {
        if (!in_array($i,$index_order)) {
             $opts[$i] = $available[$i];
         }
    }
}

if ($mailbox) {
//    displayPageHeader($color, urldecode($mailbox));
} else {
    // FIX ME, 'None' should be false, '' or everything but a string
//    displayPageHeader($color, 'None');
}

//viewOrderForm($available, $index_order,$opts,urldecode($mailbox));



// FOOD for html designers
function viewOrderForm($aColumns, $aOrder, $aOpts, $mailbox) {
   global $color;
?>

  <table align="center" width="95%" border="0" cellpadding="1" cellspacing="0">
    <tr>
      <td align="center" bgcolor="<?php echo $color[0];?>">
        <b> <?php echo _("Options");?> - <?php echo _("Index Order");?> </b>
        <table width="100%" border="0" cellpadding="8" cellspacing="0">
          <tr>
            <td align="center" bgcolor="<?php echo $color[4];?>">
              <table width="65%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                  <td>
                    <?php echo _("The index order is the order that the columns are arranged in the message index. You can add, remove, and move columns around to customize them to fit your needs.");?>
                  </td>
                </tr>
              </table>
              <br>

<?php if (count($aOrder)) { ?>
              <table cellspacing="0" cellpadding="0" border="0">
<?php     foreach($aOrder as $i => $iCol) {
             $sQuery = "&amp;num=$iCol";
             if (isset($mailbox) && $mailbox) {
                 $sQuery .= '&amp;mailbox='.urlencode($mailbox);
             }

?>
                <tr>
<?php         if ($i) { ?>
                  <td><small><a href="options_order.php?method=move&amp;positions=-1&amp;num=<?php echo $sQuery; ?>"> <?php echo _("up");?> </a></small></td>
<?php         } else { ?>
                  <td>&nbsp;</td>
<?php         } // else ?>
                  <td><small>&nbsp;|&nbsp;</small></td>
<?php         if ($i < count($aOrder) -1) { ?>
                  <td><small><a href="options_order.php?method=move&amp;positions=1&amp;num=<?php echo $sQuery; ?>"> <?php echo _("down");?> </a></small></td>
<?php         } else { ?>
                  <td>&nbsp;</td>
<?php         } // else ?>
                  <td><small>&nbsp;|&nbsp;</small></td>
<?php
              /* Always show the subject */
              if ($iCol != SQM_COL_SUBJ) {
?>
                  <td><small><a href="options_order.php?method=remove&amp;num=<?php echo $sQuery; ?>"> <?php echo _("remove");?> </a></small></td>
<?php         } else { ?>
                  <td>&nbsp;</td>
<?php         } // else ?>
                  <td><small>&nbsp;|&nbsp;</small></td>
                  <td><?php echo $aColumns[$iCol]; ?></td>
                </tr>
<?php
          } // foreach
      } // if
?>
              </table>

<?php
    if (count($aOpts)) {
        echo addForm('options_order.php', 'get', 'f');
        echo addSelect('num', $aOpts, '', TRUE);
        echo addHidden('method', 'add');
        if (isset($mailbox) && $mailbox) {
            echo addHidden('mailbox', urlencode($mailbox));
        }
        echo addSubmit(_("Add"), 'submit');
        echo '</form>';
    }
?>
          <p><a href="../src/options.php"><?php echo _("Return to options page");?></a></p><br>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body></html>

<?php
}
?>