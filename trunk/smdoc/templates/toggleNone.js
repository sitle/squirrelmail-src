<!--
function toggleNone() 
{
  the_form = window.document.forms[0];
  the_element = the_form.elements[0];
  for (var i = 0; i < the_element.options.length; i++) {
    if ( the_element.options[i].text == 'None' && the_element.options[i].selected == true )
    {
      the_element.selectedIndex = i;
      return false;
    }
  }
}
-->

