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
     * Check the flags and set a class var.
     */
    //sm_print_r($aColumns);
    $class = 'n';
    if (isset($aColumns[SQM_COL_FLAGS])) {
        $aFlags = $aColumns[SQM_COL_FLAGS]['value'];
        $bIcons = ($use_icons && $icon_theme) ? false : false;
        If ($aFlags['deleted']) {
            $class = '_d';
        } else if (!$aFlags['seen'] && $aFlags['flagged']) {
            $class = '_fs';
        } else if ($aFlags['flagged']) {
            $class = '_f';
        } else if (!$aFlags['seen']) {
            $class = '_s';
        } else {
            $class = '_n';
        }
        $sFlags = '';
        /**
         * The flags entry contain all items displayed in the flag column.
         */
        foreach ($aFlags as $flag => $flagvalue) {
            /* FIX ME, we should use separate templates for icons */
            if ($bIcons) {
              $sPreImg = '<img src="' . SM_PATH . 'images/themes/' . $icon_theme . '/';
              switch ($flag) {
                case 'deleted':
                    $sFlags .= ($flagvalue)
                        ? $sPreImg . 'msg_deleted.png" border="0" alt="('. _("Deleted") . ')" title="('. _("Deleted") . ')" height="12" width="18" />' : '';
                    break;
                case 'answered':
                    $sFlags .= ($flagvalue)
                        ? $sPreImg . 'msg_reply.png" border="0" alt="('. _("Answered") . ')" title="('. _("Answered") . ')" height="12" width="18" />' : '';
                    break;
                case 'seen':
                    $sFlags .= ($flagvalue)
                        ? $sPreImg . 'msg_read.png" border="0" alt="('. _("Read") . ')" title="('. _("Read") . ')" height="12" width="18" />'
                        : $sPreImg . 'msg_new.png" border="0" alt="('. _("New") . ')" title="('. _("New") . ')" height="12" width="18" />';
                    break;
                case 'flagged':
                    $sFlags .= ($flagvalue) ? $sPreImg .'flagged.png" border="0" height="10" width="10" />':'';
                    break;
                default:
                    break;
                }
            } else {
                switch ($flag) {
                  case 'answered':
                  case 'deleted':  $sFlags .= ($flagvalue) ? $flagvalue : ''; break;
                  default: break;
                }
            }
        }
        if (!$sFlags) { $sFlags = '&nbsp;'; }
        /* add the flag string to the value index */
        $aColumns[SQM_COL_FLAGS]['value'] = $sFlags;
    }
    /**
     * Check the priority column
     */
    if (isset($aColumns[SQM_COL_PRIO])) {
        /* FIX ME, we should use separate templates for icons */
        if ($bIcons) {
            $sValue = '<img src="' . SM_PATH . 'images/themes/' . $icon_theme . '/';
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
            $sValue = '<img src="' . SM_PATH . 'images/themes/' . $icon_theme . '/';
            $sValue .= ($aColumns[SQM_COL_ATTACHMENT]['value'])
                    ? 'attach.png" border="0" height="10" width="6" />'
                    : 'transparent.png" border="0" width="6" />';
        } else {
            $sValue = ($aColumns[SQM_COL_ATTACHMENT]['value']) ? '+' : '';
        }
        $aColumns[SQM_COL_ATTACHMENT]['value'] = $sValue;

    }


    $bgcolor = $color[4];
    if (isset($alt_index_colors) && $alt_index_colors) {
        if (!($t % 2)) {
            if (!isset($color[12])) {
                $color[12] = '#EAEAEA';
            }
            $bgcolor = $color[12];
        }

    }
    $bgcolor = (isset($aMsg['row']['color'])) ? $aMsg['row']['color']: $bgcolor;

?>
<tr class="<?php echo $class;?>" valign="top" bgcolor="<?php echo $bgcolor; ?>">
<?php
    foreach ($aOrder as $iCol) {
        $aCol = (isset($aColumns[$iCol])) ? $aColumns[$iCol] : '';
        $title  = (isset($aCol['title']))  ? $aCol['title']  : '';
        $link   = (isset($aCol['link']))   ? $aCol['link']   : '';
        $value  = (isset($aCol['value']))  ? $aCol['value']  : '';
        $target = (isset($aCol['target'])) ? $aCol['target'] : '';
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
