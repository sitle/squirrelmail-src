<?php

/**
 * message_list.tpl
 *
 * Copyright (c) 1999-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Template for viewing a messages list
 *
 * @version $Id$
 * @package squirrelmail
 */

include_once('message_row.tpl');
include_once('paginator.tpl');

function message_list($aTemplateVars) {

    extract($aTemplateVars);

    do_hook('mailbox_index_before');

    /**
     * Calculate string "Viewing message x to y (z total)"
     */
    $msg_cnt_str = '';
    if ($pageOffset < $end_msg) {
        $msg_cnt_str = sprintf(_("Viewing Messages: %s to %s (%s total)"),
                        '<b>'.$pageOffset.'</b>', '<b>'.$end_msg.'</b>', $iNumberOfMessages);
    } else if ($pageOffset == $end_msg) {
        $msg_cnt_str = sprintf(_("Viewing Message: %s (1 total)"), '<b>'.$pageOffset.'</b>');
    }



    if (!($sort & SQSORT_THREAD) && $enablesort) {
        $aSortSupported = array(SQM_COL_SUBJ =>     array(SQSORT_SUBJ_ASC    , SQSORT_SUBJ_DESC),
                                SQM_COL_DATE =>     array(SQSORT_DATE_ASC    , SQSORT_DATE_DESC),
                                SQM_COL_INT_DATE => array(SQSORT_INT_DATE_ASC, SQSORT_INT_DATE_DESC),
                                SQM_COL_FROM =>     array(SQSORT_FROM_ASC    , SQSORT_FROM_DESC),
                                SQM_COL_TO =>       array(SQSORT_TO_ASC      , SQSORT_TO_DESC),
                                SQM_COL_CC =>       array(SQSORT_CC_ASC      , SQSORT_CC_DESC),
                                SQM_COL_SIZE =>     array(SQSORT_SIZE_ASC    , SQSORT_SIZE_DESC));
    } else {
        $aSortSupported = array();
    }

    /**
     * Create array with required vars for the paginator template
     */
    $aPaginatorTemplateVars = array(
                                    'mailbox'           => $mailbox,
                                    'iOffset'           => $pageOffset,
                                    'iTotal'            => $iNumberOfMessages,
                                    'iLimit'            => $messagesPerPage,
                                    'bShowAll'          => $showall,
                                    'javascript_on'     => $javascript_on,
                                    'compact_paginator' => $compact_paginator,
                                    'page_selector'     => $page_selector,
                                    'page_selector_max' => $page_selector_max);

    ob_start();
?>

<table border="0" width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td>
    <form id="<?php echo $form_id;?>" name="<?php echo $form_name;?>" method="post" action="<?php echo $php_self;?>">
    <table width="100%" cellpadding="1"  cellspacing="0" style="border: 1px solid <?php echo $color[0]; ?>">
      <tr>
        <td>
          <table bgcolor="<?php echo $color[4]; ?>" border="0" width="100%" cellpadding="1"  cellspacing="0">
            <tr>
              <td align="<?php $align['left']; ?>">
                <small>
<!-- paginator and thread link string -->
                  <?php paginator($aPaginatorTemplateVars) . $thread_link_str ."\n"; ?>
<!-- end paginator and thread link string -->
                </small>
              </td>
<!-- message count string -->
              <td align="right"><small><?php echo $msg_cnt_str; ?></small></td>
<!-- end message count string -->
            </tr>
          </table>
        </td>
      </tr>
<?php
    if (count($aFormElements)) {
?>
<!-- start message list form control -->
      <tr width="100%" cellpadding="1"  cellspacing="0" border="0" bgcolor="<?php echo $color[0]; ?>">
        <td>
          <table border="0" width="100%" cellpadding="1"  cellspacing="0">
            <tr>
              <td align="<?php echo $align['left']; ?>">
                <small>

<?php
        foreach ($aFormElements as $key => $value) {
            switch ($value[1]) {
            case 'submit':
                if ($key != 'moveButton') { // add move in a different table cell
?>
                  <input type="submit" name="<?php echo $key; ?>" value="<?php echo $value[0]; ?>" style="padding: 0px; margin: 0px" />&nbsp;
<?php
            }
                break;
            case 'checkbox':
?>
                  <input type="checkbox" name="<?php echo $key; ?>" /><?php echo $value[0]; ?>&nbsp;
<?php
                break;
            case 'hidden':
                 echo '<input type="hidden" name="'.$key.'" value="'. $value[0]."\">\n";
                 break;
            default: break;
            }
        }
?>
                </small>
              </td>
              <td align="<?php echo $align['right']; ?>">


<?php
        if (isset($aFormElements['moveButton'])) {
?>              <small>&nbsp;
                  <tt>
                    <select name="targetMailbox">
                       <?php echo $aFormElements['targetMailbox'][0];?>
                    </select>
                  </tt>
                  <input type="submit" name="moveButton" value="<?php echo $aFormElements['moveButton'][0]; ?>" style="padding: 0px; margin: 0px" />
                </small>
<?php
        } // if (isset($aFormElements['move']))
?>
                <a href="options.php?optpage=mailbox&amp;mailbox=<?php echo urlencode($mailbox); ?>"><?php echo _("Options"); ?></a>
              </td>
            </tr>
          </table>
        </td>
      </tr>
<!-- end message list form control -->
<?php
    } // if (count($aFormElements))
?>
    </table>
<?php
    do_hook('mailbox_form_before');
?>
    </td>
  </tr>
  <tr><td height="5" bgcolor="<?php echo $color[4]; ?>"></td></tr>
  <tr>
    <td>
      <table width="100%" cellpadding="1" cellspacing="0" align="center" border="0" bgcolor="<?php echo $color[9]; ?>">
        <tr>
          <td>
            <table width="100%" cellpadding="1" cellspacing="0" align="center" border="0" bgcolor="<?php echo $color[5]; ?>">
              <tr>
                <td>
<!-- table header start -->
                  <tr>
<?php
    $aWidth = calcMessageListColumnWidth($aOrder);
    foreach($aOrder as $iCol) {

?>
                    <td align="<?php echo $align['left']; ?>" width="<?php echo $aWidth[$iCol]; ?>%" style="white-space:nowrap">
                        <b>
<?php
        switch ($iCol) {
          case SQM_COL_CHECK:
              if ($javascript_on) {
                  echo '<input type="checkbox" name="toggleAll" title="'._("Toggle All").'" onclick="toggle_all(\''.$form_id.'\');" />';
              } else {
                  $link = $baseurl . "&amp;startMessage=$pageOffset&amp;&amp;checkall=";
                  if (sqgetGlobalVar('checkall',$checkall,SQ_GET)) {
                      $link .= ($checkall) ? '0' : '1';
                  } else {
                      $link .= '1';
                  }
                  echo "<a href=\"$link\">"._("All").'</a>';
              }
              break;
          case SQM_COL_FROM:       echo _("From");     break;
          case SQM_COL_DATE:       echo _("Date");     break;
          case SQM_COL_SUBJ:       echo _("Subject");  break;
          case SQM_COL_FLAGS:      echo '&nbsp';       break;
          case SQM_COL_SIZE:       echo  _("Size");    break;
          case SQM_COL_PRIO:       echo  '!';          break;
          case SQM_COL_ATTACHMENT: echo '+';           break;
          case SQM_COL_INT_DATE:   echo _("Received"); break;
          case SQM_COL_TO:         echo _("To");       break;
          case SQM_COL_CC:         echo _("Cc");       break;
          case SQM_COL_BCC:        echo _("bcc");      break;
          default: break;
        }
        // add the sort buttons
        if (isset($aSortSupported[$iCol])) {
            if ($sort == $aSortSupported[$iCol][0]) {
               $newsort = $aSortSupported[$iCol][1];
               $img = 'up_pointer.png';
            } else if ($sort == $aSortSupported[$iCol][1]) {
               $newsort = 0;
               $img = 'down_pointer.png';
            } else {
               $newsort = $aSortSupported[$iCol][0];
               $img = 'sort_none.png';
            }
            /* Now that we have everything figured out, show the actual button. */
            echo " <a href=\"$baseurl&amp;startMessage=1&amp;srt=$newsort\">";
            echo '<img src="../images/' . $img
                . '" border="0" width="12" height="10" alt="sort" title="'
                . _("Click here to change the sorting of the message list") .'" /></a>';
        }
    }
?>
                      </b>
                    </td>
                  </tr>

<!-- Message headers start -->
<?php
                     $aTemplateVarsRow = array(
                                            'color' => $color,
                                            'use_icons' => $use_icons,
                                            'icon_theme' => $icon_theme,
                                            'align' => $align,
                                            'alt_index_colors' => $alt_index_colors,
                                            'aOrder' => $aOrder);
                     $t = 0;
                     $iColCnt = count($aOrder);
                     $sLine = '';
                     foreach ($aMessages as $iUid => $aMsg) {
                         echo $sLine;
                         $aTemplateVarsRow['aMsg'] = $aMsg;
                         $aTemplateVarsRow['iUid'] = $iUid;
                         $aTemplateVarsRow['t'] = $t;
                         /**
                          * Call the template responseable for printing the message row
                          */
                         message_row($aTemplateVarsRow);
                         $sLine = "<tr><td colspan=\"$iColCnt\" height=\"1\" bgcolor=\"$color[0]\"></td></tr>";
                         ++$t;
                     }
?>
<!-- Message headers end -->
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
      <tr><td height="5" bgcolor="<?php echo $color[4]; ?>" colspan="1"></td></tr>
        <tr>
          <td>
            <table width="100%" cellpadding="1"  cellspacing="0" style="border: 1px solid <?php echo $color[0]; ?>">
              <tr>
                <td>
                  <table bgcolor="<?php echo $color[4]; ?>" border="0" width="100%" cellpadding="1"  cellspacing="0">
                    <tr>
                      <td align="left"><small><?php echo paginator($aPaginatorTemplateVars); ?></small></td>
                      <td align="right"><small><?php echo $msg_cnt_str; ?></small></td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <?php do_hook('mailbox_index_after');?>
        </form>
    </td>
  </tr>
</table>

<?php
    ob_end_flush();
}

/**
 * Template logic
 *
 * The following functions are utility functions for this template. Do not
 * echo output in those functions. Output is generated above this comment block
 */

function calcMessageListColumnWidth($aOrder) {
    /**
     * Width of the displayed columns
     */
    $aWidthTpl = array(
        SQM_COL_CHECK => 1,
        SQM_COL_FROM =>  25,
        SQM_COL_DATE => 11,
        SQM_COL_SUBJ  => 100,
        SQM_COL_FLAGS => 2,
        SQM_COL_SIZE  => 5,
        SQM_COL_PRIO => 1,
        SQM_COL_ATTACHMENT => 1,
        SQM_COL_INT_DATE => 10,
        SQM_COL_TO => 25,
        SQM_COL_CC => 25,
        SQM_COL_BCC => 25
    );

    /**
     * Calculate the width of the subject column based on the
     * widths of the other columns
     */
    if (isset($aOrder[SQM_COL_SUBJ])) {
        foreach($aOrder as $iCol) {
            if ($iCol != SQM_COL_SUBJ) {
                $aWidthTpl[SQM_COL_SUBJ] -= $aWidthTpl[$iCol];
            }
        }
    }
    foreach($aOrder as $iCol) {
        $aWidth[$iCol] = $aWidthTpl[$iCol];
    }

    $iCheckTotalWidth = $iTotalWidth = 0;
    foreach($aOrder as $iCol) { $iTotalWidth += $aWidth[$iCol];}

    $iTotalWidth = ($iTotalWidth) ? $iTotalWidth : 100; // divide by zero check. shouldn't be needed
    // correct the width to 100%
    foreach($aOrder as $iCol) {
        $aWidth[$iCol] = round( (100 / $iTotalWidth) * $aWidth[$iCol] , 0);
        $iCheckTotalWidth += $aWidth[$iCol];
    }
    if ($iCheckTotalWidth > 100) { // correction needed
       $iCol = array_search(max($aWidth),$aWidth);
       $aWidth[$iCol] -= $iCheckTotalWidth-100;
    }
    return $aWidth;
}