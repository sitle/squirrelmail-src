<!--

// Function to resize the iframe (passed as obj)
// based on the viewable size of the window.
function resizeMe(obj,url) {
  var winHeight;
  var winWidth;

  // Which is used when from here: http://www.howtocreate.co.uk/tutorials/index.php?tut=0&part=16

  // First, non-IE
  if ( window.innerHeight ) {
    winHeight=window.innerHeight; 
    winWidth=window.innerWidth; 
  // This covers IE 6, where the element shows window height
  } else if( document.documentElement && document.documentElement.clientHeight ) {
    winHeight=document.documentElement.clientHeight;
    winWidth=document.documentElement.clientWidth;
  // This covers earlier versions of IE 
  } else if (document.body && document.body.clientHeight) {
    winHeight=document.body.clientHeight;
    winWidth=document.body.clientWidth;
  // If we can't get a height, just forget it - defaults to CSS definition
  } else {
    location.replace(url);
  }

  // If too narrow, give up, go to "narrow-friendly" URL
  if ( winWidth < 600 )
    location.replace(url);

  if ( winHeight >= 400 )
    newHeight = winHeight - 200;
  else
    newHeight = winHeight;

  with (obj) {
    style.height=newHeight + 'px';
    if ( winWidth > 800 ) {
        if ( id == 'right' )
            style.width = (winWidth - 500) + 'px';
    }
  }
  return true;
}
-->
