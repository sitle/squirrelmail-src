<?

function squirrelmail_plugin_init_quicksave()
{

	global $squirrelmail_plugin_hooks;

	$squirrelmail_plugin_hooks["left_main_after"]["quicksave"] = "quicksave_left_main_after";
	$squirrelmail_plugin_hooks["compose_bottom"]["quicksave"]  = "quicksave_compose_bottom";
	$squirrelmail_plugin_hooks["compose_form"]["quicksave"]    = "quicksave_compose_form";
         
}   
   

function quicksave_left_main_after()
{

	// QuickSave plugin
	echo "<form name=quicksave>\n";
	echo "\t<input type=hidden value=\"\" name=send_to>\n";
	echo "\t<input type=hidden value=\"\" name=send_to_cc>\n";
	echo "\t<input type=hidden value=\"\" name=send_to_bcc>\n";
	echo "\t<input type=hidden value=\"\" name=subject>\n";
	echo "\t<input type=hidden value=\"\" name=body>\n";
	echo "\t<input type=hidden value=0 name=is_active>\n";
	echo "</form>\n";
	// end -- QuickSave plugin

}


function quicksave_compose_bottom()
{

	// QuickSave plugin
	echo "<script language=Javascript>\n";
	echo "<!--\n";
	echo "function QuickSave_swap(from_form, to_form)\n";
	echo "{\n";
	echo "	if ( from_form.send_to.value.length >= 1 )\n";
	echo "		to_form.send_to.value = from_form.send_to.value;\n";
	echo "	if ( from_form.send_to_cc.value.length >= 1 )\n";
	echo "		to_form.send_to_cc.value = from_form.send_to_cc.value;\n";
	echo "	if ( from_form.send_to_bcc.value.length >= 1 )\n";
	echo "		to_form.send_to_bcc.value = from_form.send_to_bcc.value;\n";
	echo "	if ( from_form.subject.value.length >= 1 )\n";
	echo "		to_form.subject.value = from_form.subject.value;\n";
	echo "	if ( from_form.body.value.length >= 1 )\n";
	echo "		to_form.body.value = from_form.body.value;\n";
	echo "	self.setTimeout('QuickSave_swap(document.compose, parent.left.document.quicksave)', 10000);\n";
	echo "	QuickSave_activate(true);\n";
	echo "	return true;\n";
	echo "}\n";
	echo "\n";
	echo "function QuickSave_activate(do_we_save)\n";
	echo "{\n";
	echo "	if ( do_we_save )\n";
	echo "		parent.left.document.quicksave.is_active.value = 1;\n";
	echo "	else\n";
	echo "		parent.left.document.quicksave.is_active.value = 0;\n";
	echo "	return true;\n";
	echo "}\n";
	echo "\n";
	echo " // we check to see if we restore, but we only do this once\n";
	echo "if ( parent.left.document.quicksave.is_active.value == 1 )\n";
	echo "{\n";
	echo "	if ( confirm('There is a QuickSaved email!\\nDo you wish to restore it?') )\n";
	echo "	{\n";
	echo "		QuickSave_swap(parent.left.document.quicksave, document.compose);\n";
	echo "		alert('Email restored!\\nPlease try to be more careful in the future! \;\)');\n";
	echo "	}\n";
	echo "	else\n";
	echo "  {\n";
	echo "		QuickSave_swap(document.compose, parent.left.document.quicksave);\n";
	echo "  }\n";
	echo "}\n";
	echo "else\n";
	echo "{\n";
	echo "	QuickSave_swap(document.compose, parent.left.document.quicksave);\n";
	echo "}\n";
	echo "//-->\n";
	echo "</script>\n";
	// end -- QuickSave plugin


}


function quicksave_compose_form()
{

	echo " onSubmit=\"QuickSave_activate(false);\"";

}

