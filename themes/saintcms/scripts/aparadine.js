var curlayout = 1;
function checkwindowsize() {
	var myWidth = 0, myHeight = 0;
	if( typeof( window.innerWidth ) == 'number' ) {
		//Non-IE
		myWidth = window.innerWidth;
		myHeight = window.innerHeight;
	} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
		//IE 6+ in 'standards compliant mode'
		myWidth = document.documentElement.clientWidth;
		myHeight = document.documentElement.clientHeight;
	} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
		//IE 4 compatible
		myWidth = document.body.clientWidth;
		myHeight = document.body.clientHeight;
	}
	
	if (myWidth <= 800 && curlayout != 0) {
		curlayout = 0;
		$('body').removeClass('wide');
		$('body').addClass('narrow');
	} else if (myWidth > 800 && myWidth <= 1024 && curlayout != 1) {
		curlayout = 1;
		$('body').removeClass('wide');
		$('body').removeClass('narrow');
	}
	else if (myWidth > 1024 && curlayout != 2) {
		curlayout = 2;
		$('body').removeClass('narrow');
		$('body').addClass('wide');
	}
}

var alreadyrunflag=0

if (document.addEventListener)
	document.addEventListener("DOMContentLoaded", function(){alreadyrunflag=1; checkwindowsize()}, false)
else if (document.all && !window.opera) {
	document.write('<script type="text/javascript" id="contentloadtag" defer="defer" src="javascript:void(0)"><\/script>')
	var contentloadtag=document.getElementById("contentloadtag")
	contentloadtag.onreadystatechange=function() {
		if (this.readyState=="complete") {
			alreadyrunflag=1
			checkwindowsize()
		}
	}
}

window.onload=function(){
	setTimeout("if (!alreadyrunflag) checkwindowsize()", 0)
}

window.onresize=function(){
	checkwindowsize();
}
