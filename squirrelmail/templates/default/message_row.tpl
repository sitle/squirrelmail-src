<?php

/**
* Displays message header row in messages list
*
* @param  array $aMsg contains all message related parameters
* @return void
*/


function message_row($aTemplateVars) {

    extract($aTemplateVars);

    $aColumns = $aMsg['columns'];

    /**
     * Check usage of images for attachments, flags and priority
     */
    $bIcons = ($use_icons && $icon_theme) ? true : false;

    /**
     * Location of icon images
     */
    if ($bIcons) {
        $sImageLocation = SM_PATH . 'images/themes/' . $icon_theme . '/';
    }

    /**
     * Check the flags and set a class var.
     */
    if (isset($aColumns[SQM_COL_FLAGS])) {
        $aFlags = $aColumns[SQM_COL_FLAGS]['value'];
        if ($bIcons) {

            $sFlags = getFlagIcon($aFlags, $sImageLocation);
        } else {
            $sFlags = getFlagText($aFlags);
        }
        /* add the flag string to the value index */
        $aColumns[SQM_COL_FLAGS]['value'] = $sFlags;
    }
    /**
     * Check the priority column
     */
    if (isset($aColumns[SQM_COL_PRIO])) {
        /* FIX ME, we should use separate templates for icons */
        if ($bIcons) {
            $sValue = '<img src="' . $sImageLocation;
            switch ($aColumns[SQM_COL_PRIO]) {
                case 1:
                case 2:  $sValue .= 'prio_high.png" border="0" height="10" width="5" /> ' ; break;
                case 5:  $sValue .= 'prio_low.png" border="0" height="10" width="5" /> '  ; break;
                default: $sValue .= 'transparent.png" border="0" width="5" /> '           ; break;
            }
        } else {
            $sValue = '';
            switch ($aColumns[SQM_COL_PRIO]) {
                case 1:
                case 2: $sValue .= "<font color=\"$color[1]\">!</font>"; break;
                case 5: $sValue .= "<font color=\"$color[8]\">?</font>"; break;
                default: break;
            }
        }
        $aColumns[SQM_COL_PRIO]['value'] = $sValue;
    }

    /**
     * Check the attachment column
     */
    if (isset($aColumns[SQM_COL_ATTACHMENT])) {
        /* FIX ME, we should use separate templates for icons */
        if ($bIcons) {
            $sValue = '<img src="' . $sImageLocation;
            $sValue .= ($aColumns[SQM_COL_ATTACHMENT]['value'])
                    ? 'attach.png" border="0" height="10" width="6" />'
                    : 'transparent.png" border="0" width="6" />';
        } else {
            $sValue = ($aColumns[SQM_COL_ATTACHMENT]['value']) ? '+' : '';
        }
        $aColumns[SQM_COL_ATTACHMENT]['value'] = $sValue;
    }


    $bgcolor = $color[4];

    /**
     * If alternating row colors is set, adapt the bgcolor
     */
    if (isset($alt_index_colors) && $alt_index_colors) {
        if (!($t % 2)) {
            if (!isset($color[12])) {
                $color[12] = '#EAEAEA';
            }
            $bgcolor = $color[12];
        }

    }
    $bgcolor = (isset($aMsg['row']['color'])) ? $aMsg['row']['color']: $bgcolor;
    $class = 'msg_row';

?>
<tr class="<?php echo $class;?>" valign="top" bgcolor="<?php echo $bgcolor; ?>">
<?php
    // flag style mumbo jumbo
    $sPre = $sEnd = '';
    if (!in_array('seen',$aFlags)) {
        $sPre = '<b>'; $sEnd = '</b>';
    }
    if (in_array('deleted',$aFlags) && $aFlags['deleted']) {
        $sPre = "<font color=\"$color[9]\">" . $sPre;
        $sEnd .= '</font>';
    } else {
        if (in_array('flagged',$aFlags) && $aFlags['flagged']) {
            $sPre = "<font color=\"$color[2]\">" . $sPre;
            $sEnd .= '</font>';
        }
    }
    /**
     * Because the order of the columns and which columns to show is a user preference
     * we have to do some php coding to display the columns in the right order
     */
    foreach ($aOrder as $iCol) {
        $aCol = (isset($aColumns[$iCol])) ? $aColumns[$iCol] : '';
        $title  = (isset($aCol['title']))  ? $aCol['title']  : '';
        $link   = (isset($aCol['link']))   ? $aCol['link']   : '';
        $value  = (isset($aCol['value']))  ? $aCol['value']  : '';
        $target = (isset($aCol['target'])) ? $aCol['target'] : '';
        if ($iCol !== SQM_COL_CHECK) {
            $value = $sPre.$value.$sEnd;
        }
        switch ($iCol) {
          case SQM_COL_CHECK:
            echo '<td align="' .$align['left'] .'">'.addCheckBox("msg[$t]", $value, $iUid) . "</td>\n";
            break;
          case SQM_COL_SUBJ:
            $indent = $aCol['indent'];
            $sText = "    <td class=\"col_subject\" align=\"$align[left]\">";
            if ($align['left'] == 'left') {
                $sText .= str_repeat('&nbsp;&nbsp;',$indent);
            }
            $sText .= "<a href=\"$link\"";
            if ($target) { $sText .= " target=\"$target\"";}
            if ($title)  { $sText .= " title=\"$title\""  ;}
            $sText .= ">";
            $sText .= $value . '</a>';
            if ($align['left'] == 'right') {
                $sText .= str_repeat('&nbsp;&nbsp;',$indent);
            }
            echo $sText."</td>\n";
            break;
          case SQM_COL_SIZE:
          case SQM_COL_FLAGS:
            $sText = "    <td class=\"col_\" align=\"$align[right]\">";
            $sText .= '<small>'.$value. "</small></td>\n";
            echo $sText;
            break;
          case SQM_COL_INT_DATE:
          case SQM_COL_DATE:
            $sText = "    <td class=\"col_\" align=\"center\">";
            $sText .= $value. "</td>\n";
            echo $sText;
            break;
          default:
            $sText = "    <td class=\"col_\" align=\"$align[left]\"";
            if ($link) {
                $sText .= "><a href=\"$link\"";
                if ($target) { $sText .= " target=\"$target\"";}
                if ($title)  { $sText .= " title=\"$title\""  ;}
                $sText .= ">";
            } else {
                if ($title) {$sText .= " title=\"$title\"";}
                $sText .= ">";
            }
            $sText .= $value;
            if ($link) { $sText .= '</a>';}
            echo $sText."</td>\n";
            break;
        }
    }
?>
</tr>
<?php

}

/**
 * Template logic
 *
 * The following functions are utility functions for this template. Do not
 * echo output in those functions. Output is generated above this comment block
 */

/**
 * Function to retrieve the correct flag icon belonging to the set of
 * provided flags
 *
 * @param array $aFlags associative array with seen,deleted,anwered and flag keys.
 * @param string $sImageLocation directory location of flagicons
 * @return string $sFlags string with the correct img html tag
 * @author Marc Groot Koerkamp
 */
function getFlagIcon($aFlags, $sImageLocation) {
    $sFlags = '';

    /**
     * 0  = unseen
     * 1  = seen
     * 2  = deleted
     * 3  = deleted seen
     * 4  = answered
     * 5  = answered seen
     * 6  = answered deleted
     * 7  = answered deleted seen
     * 8  = flagged
     * 9  = flagged seen
     * 10 = flagged deleted
     * 11 = flagged deleted seen
     * 12 = flagged answered
     * 13 = flagged aswered seen
     * 14 = flagged answered deleted
     * 15 = flagged anserwed deleted seen
     */

    /**
     * Use static vars to avoid initialisation of the array on each displayed row
     */
    static $aFlagImages, $aFlagValues;
    if (!isset($aFlagImages)) {
        $aFlagImages = array(
                            array('msg_new.png','('._("New").')'),
                            array('msg_read.png','('._("Read").')'),
                            array('msg_new_deleted.png','('._("Deleted").')'),
                            array('msg_read_deleted.png','('._("Deleted").')'),
                            array('msg_new_reply.png','('._("Answered").')'),
                            array('msg_read_reply.png','('._("Answered").')'),
                            array('msg_read_deleted_reply.png','('._("Answered").')'),
                            array('flagged.png', '('._("Flagged").')'),
                            array('flagged.png', '('._("Flagged").')'),
                            array('flagged.png', '('._("Flagged").')'),
                            array('flagged.png', '('._("Flagged").')'),
                            array('flagged.png', '('._("Flagged").')'),
                            array('flagged.png', '('._("Flagged").')'),
                            array('flagged.png', '('._("Flagged").')'),
                            array('flagged.png', '('._("Flagged").')'),
                            array('flagged.png', '('._("Flagged").')')
                            ); // as you see the list is not completed yet.
        $aFlagValues = array('seen'     => 1,
                             'deleted'  => 2,
                             'answered' => 4,
                             'flagged'  => 8,
                             'draft'    => 16);
    }

    /**
     * The flags entry contain all items displayed in the flag column.
     */
    $iFlagIndx = 0;
    foreach ($aFlags as $flag => $flagvalue) {
        /* FIX ME, we should use separate templates for icons */
         switch ($flag) {
            case 'deleted':
            case 'answered':
            case 'seen':
            case 'flagged': if ($flagvalue) $iFlagIndx+=$aFlagValues[$flag]; break;
            default: break;
        }
    }
    if (isset($aFlagImages[$iFlagIndx])) {
        $aFlagEntry = $aFlagImages[$iFlagIndx];
    } else {
        $aFlagEntry = end($aFlagImages);
    }

    $sFlags = '<img src="' . $sImageLocation . $aFlagEntry[0].'"'.
              ' border="0" alt="'.$aFlagEntry[1].'" title="'. $aFlagEntry[1] .'" height="12" width="18" />' ;
    if (!$sFlags) { $sFlags = '&nbsp;'; }
    return $sFlags;
}

/**
 * Function to retrieve the correct flag text belonging to the set of
 * provided flags
 *
 * @param array $aFlags associative array with seen,deleted,anwered and flag keys.
 * @return string $sFlags string with the correct flag text
 * @author Marc Groot Koerkamp
 */
function getFlagText($aFlags) {
    $sFlags = '';

    /**
     * 0  = unseen
     * 1  = seen
     * 2  = deleted
     * 3  = deleted seen
     * 4  = answered
     * 5  = answered seen
     * 6  = answered deleted
     * 7  = answered deleted seen
     * 8  = flagged
     * 9  = flagged seen
     * 10 = flagged deleted
     * 11 = flagged deleted seen
     * 12 = flagged answered
     * 13 = flagged aswered seen
     * 14 = flagged answered deleted
     * 15 = flagged anserwed deleted seen
     */
    /**
     * Use static vars to avoid initialisation of the array on each displayed row
     */
    static $aFlagText, $aFlagValues;
    if (!isset($aFlagText)) {
        $aFlagText = array(
                            array('&nbsp;', '('._("New").')'),
                            array('&nbsp;', '('._("Read").')'),
                            array(_("D")  , '('._("Deleted").')'),
                            array(_("D")  , '('._("Deleted").')'),
                            array(_("A")  , '('._("Answered").')'),
                            array(_("A")  , '('._("Answered").')'),
                            array(_("D")  , '('._("Answered").')'),
                            array(_("F")  , '('._("Flagged").')'),
                            array(_("F")  , '('._("Flagged").')'),
                            array(_("F")  , '('._("Flagged").')'),
                            array(_("F")  , '('._("Flagged").')'),
                            array(_("F")  , '('._("Flagged").')'),
                            array(_("F")  , '('._("Flagged").')'),
                            array(_("F")  , '('._("Flagged").')'),
                            array(_("F")  , '('._("Flagged").')'),
                            array(_("F")  , '('._("Flagged").')')
                            ); // as you see the list is not completed yet.
        $aFlagValues = array('seen'     => 1,
                             'deleted'  => 2,
                             'answered' => 4,
                             'flagged'  => 8,
                             'draft'    => 16);
    }

    /**
     * The flags entry contain all items displayed in the flag column.
     */
    $iFlagIndx = 0;
    foreach ($aFlags as $flag => $flagvalue) {
        /* FIX ME, we should use separate templates for icons */
        switch ($flag) {
            case 'deleted':
            case 'answered':
            case 'seen':
            case 'flagged': if ($flagvalue) $iFlagIndx+=$aFlagValues[$flag]; break;
            default: break;
        }
    }
    if (isset($aFlagText[$iFlagIndx])) {
        $sFlags = $aFlagText[$iFlagIndx][0];
    } else {
        $aLast = end($aFlagText);
        $sFlags = $aLast[0];
    }
    if (!$sFlags) { $sFlags = '&nbsp;'; }
    return $sFlags;
}
