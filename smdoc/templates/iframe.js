<!--

// Function to resize the iframe (passed as obj)
// based on the viewable size of the window.
function resizeMe(obj) {
  var winHeight;

  // Which is used when from here: http://www.howtocreate.co.uk/tutorials/index.php?tut=0&part=16
  
  // First, non-IE
  if ( window.innerHeight ) {
    winHeight=window.innerHeight; 
  // This covers IE 6, where the element shows window height
  } else if( document.documentElement && document.documentElement.clientHeight ) {
    winHeight=document.documentElement.clientHeight;
  // This covers earlier versions of IE 
  } else if (document.body && document.body.clientHeight) {
    winHeight=document.body.clientHeight;
  // If we can't get a height, just forget it - defaults to CSS definition
  } else {
    return true;
  }

  if ( winHeight >= 400 )
    newHeight = winHeight - 200;
  else
    newHeight = winHeight;

  window.status= obj.name + ' ' + winHeight + '->' + newHeight;
  with (obj) {
    style.height=newHeight + 'px';
  }
  return true;
}
-->
