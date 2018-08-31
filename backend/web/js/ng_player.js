// Authors: S. Marechal smarechal@anevia.com
//          JL Tresset jltresset@anevia.com
//
// js demo for ott
// TODO: Transform those functions into a beautiful javascript object
//
// Rev. 2011-08-12
/////////////////////////////////////////////////////////////////////

// Below, add some capabilities to string
// => new methods trim(), startsWith(), endsWith
String.prototype.trim = function()
{return (this.replace(/^[\s\xA0]+/, "").replace(/[\s\xA0]+$/, ""))}

String.prototype.startsWith = function(str)
{return (this.match("^"+str)==str)}

String.prototype.endsWith = function(str)
{return (this.match(str+"$")==str)}


// html5 player, ie, create a video tag inside the container tag
function playHtml5Video( containerId, src, width, height ) {
    var videoId = "video_inside_" + containerId;
    var cnt = document.getElementById(containerId);
    var elt = document.getElementById(videoId);
    if( elt == null ) {
        cnt.innerHTML = "<video id='"+videoId+"' src='"+src+"' controls width='"+width+"' height='"+height+"' webkit-playsinline></video>";
        elt = document.getElementById(videoId);
    } else {
    }
    elt.src = src;
    elt.width = width;
    elt.height = height;
    elt.load();
    elt.play();
}

// flash player. needs the swfobject.js
// (adapted from hello-world-javascript.html, an example from osmf)
// Uses the osmf player, ie, capable of playing m4f playlists.
function playFlashVideo( containerId, src, width, height ) {
    // create a container inside the container to be able to kill flash by
    // resetting the child container to an empty string
    var objectId = "object_inside_" + containerId;
    var cnt = document.getElementById(containerId);
    var elt = document.getElementById(objectId);
    if( elt == null ) {
        cnt.innerHTML = "<object id='" + objectId + "' />";
        elt = document.getElementById(objectId);
    }
    // Create a StrobeMediaPlayback configuration
    var parameters =
            { src: src
            	,autoPlay: true
		,controlBarPosition: "none",
		wmode: "transparent"
            };
     
    // Embed the player SWF:
    swfobject.embedSWF
        ("js/swplayer/StrobeMediaPlaybackDis.swf"
        , objectId
        , width
        , height
        , "10.1.0"
        , {}
        , parameters
        , { allowFullScreen: "true"}
        , { name: objectId }
        );
}

function playM3U8Video ( containerId, src, width, height ) {
	var cnt = document.getElementById(containerId);
	 //cnt.innerHTML = "<h1>Test</h1>";
	 var url = "tvPlayUrl.jsp?path="+src;
	 $.ajax({
	  		url: url,
	  		cache:false,
	  		success: function(data) {
	  			$('#'+containerId+'').html(data);
	  			
			}
	});
}

function playFlashVideo2( containerId, src, width, height ) {
    // create a container inside the container to be able to kill flash by
    // resetting the child container to an empty string
    var objectId = "object_inside_" + containerId;
    src = src + "/manifest";
    var flashvars = {
    	    'provider':  'js/lives/SmoothStreamingProvider-1.4.44.swf',
    	    //'debug':     'console',
    	    'autostart': 'true'
    	  };
    flashvars['file'] = src;
    //flashvars['file'] = "http://203.162.16.22/lives/channel1.isml/manifest";
    //flashvars['file'] = '../SmoothStream.isml/Manifest';    
    var params = {
    		 'stretching':    'exactfit',
    		 'controlbar':    'over',
    		 'seamlesstabbing': 'true',
    		 'wmode': 'opaque',
    	     'allowfullscreen':    'true',
    	     'allowscriptaccess':  'always',
    	     'bgcolor':            '#000000',
    	     'WMode': "transparent"
    	  };
    var attributes = {
    	     'id':                 'player1',
    	     'name':               'player1'
    };
    	     
    swfobject.embedSWF('js/lives/5.3.swf'
    		, 'vid_container_id'
    		, width
    		, height
    		, '10.1.0'
    		, null
    		, flashvars
    		, params
    		, attributes);    

}
//end playFlashVideo2

// Silverlight error mgr (adapted from demo.anevia.com)
function onSilverlightError(sender, args) {
    var appSource = "";
    if (sender != null && sender != 0) {
        appSource = sender.getHost().Source;
    }

    var errorType = args.ErrorType;
    var iErrorCode = args.ErrorCode;

    if (errorType == "ImageError" || errorType == "MediaError") {
        return;
    }

    var errMsg = "Unhandled Error in Silverlight Application " +  appSource + "\n" ;

    errMsg += "Code: "+ iErrorCode + "    \n";
    errMsg += "Category: " + errorType + "       \n";
    errMsg += "Message: " + args.ErrorMessage + "     \n";

    if (errorType == "ParserError") {
        errMsg += "File: " + args.xamlFile + "     \n";
        errMsg += "Line: " + args.lineNumber + "     \n";
        errMsg += "Position: " + args.charPosition + "     \n";
    }
    else if (errorType == "RuntimeError") {
        if (args.lineNumber != 0) {
            errMsg += "Line: " + args.lineNumber + "     \n";
            errMsg += "Position: " +  args.charPosition + "     \n";
        }
        errMsg += "MethodName: " + args.methodName + "     \n";
    }

    throw new Error(errMsg);
}

// Silverlight player (adapted from demo.anevia.com)
function playSilverVideo( containerId, src, width, height ) {
    var objectId = "object_inside_" + containerId;
    var cnt = document.getElementById(containerId);
    var elt = document.getElementById(objectId);
    if( elt == null ) {
        cnt.innerHTML =
            "<object data='data:application/x-silverlight-2,' type='application/x-silverlight-2' width='"+width + "' height='" + height + "'>\n" +
            "  <param name='source' value='SmoothStreamingPlayer_lite.xap'/>\n"+
            "  <param name='onError' value='onSilverlightError' />\n" +
            "  <param name='background' value='white' />\n" +
            "  <param name='minRuntimeVersion' value='4.0.50401.0' />\n" +
            "  <param name='autoUpgrade' value='true' />\n"+
            "  <param name='InitParams' value='mediaurl=" + src + "' />\n" +
            "  <a href='http://go.microsoft.com/fwlink/?LinkID=149156&v=4.0.50401.0' style='text-decoration:none'>\n" +
            "    <img src='http://go.microsoft.com/fwlink/?LinkId=161376' alt='Get Microsoft Silverlight' style='border-style:none'/>\n" +
            "  </a>\n"+
            "</object>\n";
        elt = document.getElementById(objectId);
    } else {
    }
    // XXX. This could be good to use a javascript api to control the silver player (play/pause ..)
}

// jwplayer. Useful to play hls on windows/linux with flash
// Need the adaptive jwplayer capable of hls plays
function playHlsFlashIosVideo( containerId, src, width, height ) {
    // Same as flash: Container inside the container to kill it
    var objectId = "object_inside_" + containerId;
    var cnt = document.getElementById(containerId);
    var elt = document.getElementById(objectId);
    if( elt == null ) {
        cnt.innerHTML = "<object id='" + objectId + "' />";
        elt = document.getElementById(objectId);
    }
    jwplayer(objectId).setup({
        height: height,
        width: width,
        // plugins: { 'assets/qualitymonitor.swf':{} },
        modes: [
            { type: "flash",
            src: "jwplayer.swf",
            config: {
                provider:"jwadaptiveProvider.swf",
                file: src
                }
            },
            { type: "html5",
            config: {
                file: src
                }
            },
            { type: "download" }
        ]
        });
    // XXX: sometimes the jwplayer is hidden
    var elt = document.getElementById(objectId);
    if( elt != null ) {
        elt.style.visibility = "visible";
    }
    jwplayer(objectId).play();
}

// build a complete uri using path / file / ext
// use the string method defined at the beginning of this file
function formatSrc( srcPath, srcFile, srcExt, options) {
    var ret = srcPath;
    if( ! srcPath.endsWith("/") ) {
        ret = ret + "/";
    }
    ret = ret + srcFile;
    if( srcExt != "" ) {
        if( ! srcExt.startsWith( ".") ) {
            ret = ret + ".";
        }
        ret = ret + srcExt;
    }
    
    if ( options ) {
      ret = ret + options;
    }

    return ret;
}

// The simplest way I found to cleanup the player
function resetVideo( containerId ) {
    var cnt = document.getElementById(containerId);
    cnt.innerHTML = "";
    // var objectId = "object_inside_" + containerId;
    // var elt = document.getElementById(objectId);
    // elt.innerHTML = "";
    // swfobject.removeSWF(objectId);
    // jwplayer(objectId).reset();
}

// start a player
function playVideo( type, containerId, srcPath, srcFile, width, height, options) {
    if( type == "type0" ) {
        var b = browserDectect();
        if( b.isApple() ) {
            playHtml5Video( containerId, formatSrc(srcPath, srcFile, "m3u8", options), width, height );
        } else {
        	playFlashVideo2( containerId, srcPath, width, height );
        }
    } else if( type == "type1" ) {
        var b = browserDectect();
        if( b.isApple() ) {
            playHtml5Video( containerId,srcPath, width, height );
        } else {
        	
        	playM3U8Video ( containerId, srcPath, width, height )
        	//window.location.href = "tvPlayUrl.jsp?path="+srcPath;
        }
    } else if( type == "silver" ) {
        playSilverVideo( containerId, formatSrc(srcPath, "Manifest", "", options), width, height );
    }
    
}

function doWork(location, url, type) {
	
    var reqId = new Date().getTime();
            //Kiem tra neu trang thai tra ve thanh cong thi moi play
                //var type = "live";
                var device = browserDectect();
               // hidediv();
                
                if(device.isApple()) {alert('apple');
                    document.getElementById("playFilm").innerHTML = '<video width="100%" height="100%" controls><source src="'+url+'" type="video/mp4">Your browser does not support the video tag.</video>';
                }else{
                	alert(type);
                    if("video" == type){
                        jwplayer("playFilm").setup({
                            file:url,
                            flashplayer:location + "/js/jwplayer/player.swf",
                            autostart:true,
                            value:"netstreambasepath",
                            quality:false,
                            stretching:"uniform",
                            screencolor:"000000",
                            skin:location + "/js/jwplayer/skins/modieus/modieus.zip",
                            display:{
                                icons:true
                            },
                            dock:false,
                            height:"100%",
                            width:"100%",
                            events : {
                                onComplete : function(event) {
//                                        countReload = 0;
                                   // setText(url);
                                    return;
                                },
                                onPlay : function(event) {
                                    countReload = 0;
                                  //  setText(url);
                                    if(myVar){
                                        clearTimeout(myVar);
                                    }
                                    return;
                                },
                                onBuffer : function(event) {
                                    //setText(jwplayer("container").getBuffer() + "%");
                                    return;
                                },
                                onError : function(message) {
                                    if(countReload >3){
                                     //   setText("Error: Có lỗi khi play.  Vui lòng thử lại sau !");
                                        return;
                                    }
                                    jwplayer("playFilm").stop();
                                    jwplayer("playFilm").remove();
                                    countReload ++;
                                  //  setText("Reloading:  "+ countReload + " lần ...");
                                    doWork(location, url);
                                    myVar = setTimeout(function(){  doWork(location, url);  },5000);
                                    return;
                                }
                            }//end events
                        }); // end setup
                    }else {
                        // Collect query parameters in an object that we can
                        // forward to SWFObject:
                        var pqs = new ParsedQueryString();
                        var parameterNames = pqs.params(false);
                        url = url.replace("&","%26");
                        var parameters = {
                            //src: "http://streambox.fr/playlists/test_001/stream.m3u8",
                            //src: "http://origin02.tvod.vn/lives/origin02/test/test.isml/test.m3u8",
                            //src: "http://203.162.235.26:8081/movies/Video/disk5/video-raw-cp-10309/0d6682273d5b22d06ac5658a20bc0dab.ssm/0d6682273d5b22d06ac5658a20bc0dab.m3u8",
                            src: url,
                            autoPlay: false,
                            verbose: true,
                            controlBarAutoHide: "true",
                            controlBarPosition: "bottom",
                            poster: location + "/js/osmf/images/poster.png",
                            javascriptCallbackFunction: "jsbridge",
                            plugin_hls: location + "/js/osmf/HLSProviderOSMF.swf"
                        };

                        for (var i = 0; i < parameterNames.length; i++) {
                            var parameterName = parameterNames[i];
                            parameters[parameterName] = pqs.param(parameterName) ||
                                    parameters[parameterName];
                        }

                        var wmodeValue = "direct";
                        var wmodeOptions = ["direct", "opaque", "transparent", "window"];
                        if (parameters.hasOwnProperty("wmode"))
                        {
                            if (wmodeOptions.indexOf(parameters.wmode) >= 0)
                            {
                                wmodeValue = parameters.wmode;
                            }
                            delete parameters.wmode;
                        }

                        // Embed the player SWF:
                        swfobject.embedSWF(
                                location + "/js/osmf/GrindPlayer.swf"
                                , "playFilm"
                                , "100%"
                                , "100%"
                                , "10.1.0"
                                , "expressInstall.swf"
                                , parameters
                                , {
                                    allowFullScreen: "true",
                                    wmode: wmodeValue
                                }
                                , {
                                    name: "playFilm"
                                }
                        );  // end embedSWF
                    }   //end else
                }   // end if
                //setText(url);   //cai nay cho vao de test local

}

// From http://www.quirksmode.org/js/detect.html
// add iPad support
function browserDectect1() {
    var BrowserDetect = {
    init: function () {
        this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
        this.version = this.searchVersion(navigator.userAgent)
            || this.searchVersion(navigator.appVersion)
            || "an unknown version";
        this.OS = this.searchString(this.dataOS) || "an unknown OS";
    },
    searchString: function (data) {
        for (var i=0;i<data.length;i++) {
            var dataString = data[i].string;
            var dataProp = data[i].prop;
            this.versionSearchString = data[i].versionSearch || data[i].identity;
            if (dataString) {
                if (dataString.indexOf(data[i].subString) != -1)
                    return data[i].identity;
            }
            else if (dataProp)
                return data[i].identity;
        }
    },
    searchVersion: function (dataString) {
        var index = dataString.indexOf(this.versionSearchString);
        if (index == -1) return;
        return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
    },
    dataBrowser: [
        {
            string: navigator.userAgent,
            subString: "Chrome",
            identity: "Chrome"
        },
        {   string: navigator.userAgent,
            subString: "OmniWeb",
            versionSearch: "OmniWeb/",
            identity: "OmniWeb"
        },
        {
            string: navigator.vendor,
            subString: "Apple",
            identity: "Safari",
            versionSearch: "Version"
        },
        {
            prop: window.opera,
            identity: "Opera",
            versionSearch: "Version"
        },
        {
            string: navigator.vendor,
            subString: "iCab",
            identity: "iCab"
        },
        {
            string: navigator.vendor,
            subString: "KDE",
            identity: "Konqueror"
        },
        {
            string: navigator.userAgent,
            subString: "Firefox",
            identity: "Firefox"
        },
        {
            string: navigator.vendor,
            subString: "Camino",
            identity: "Camino"
        },
        {       // for newer Netscapes (6+)
            string: navigator.userAgent,
            subString: "Netscape",
            identity: "Netscape"
        },
        {
            string: navigator.userAgent,
            subString: "MSIE",
            identity: "Explorer",
            versionSearch: "MSIE"
        },
        {
            string: navigator.userAgent,
            subString: "Gecko",
            identity: "Mozilla",
            versionSearch: "rv"
        },
        {       // for older Netscapes (4-)
            string: navigator.userAgent,
            subString: "Mozilla",
            identity: "Netscape",
            versionSearch: "Mozilla"
        }
    ],
    dataOS : [
        {
            string: navigator.platform,
            subString: "Win",
            identity: "Windows"
        },
        {
            string: navigator.platform,
            subString: "Mac",
            identity: "Mac"
        },
        {
               string: navigator.userAgent,
               subString: "iPhone",
               identity: "iPhone/iPod"
        },
        {
               string: navigator.userAgent,
               subString: "iPad",
               identity: "iPad"
        },
        {
            string: navigator.userAgent,
            subString: "Android",
            identity: "Android"
        },
        {
            string: navigator.platform,
            subString: "Linux",
            identity: "Linux"
        }
    ]

    };
    // "Mozilla/5.0 (iPad; U; CPU OS 4_3_3 like Mac OS X; fr-fr) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J3 Safari/6533.18.5"
    // "Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_2_1 like Mac OS X; fr-fr) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8C148 Safari/6533.18.5"
    // "Mozilla/5.0 (Windows; U; Windows NT 5.1; fr-FR) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1"
    // "Mozilla/5.0 (X11; Linux x86_64; rv:5.0) Gecko/20100101 Firefox/5.0"
    BrowserDetect.init();
    // alert( " ----" + BrowserDetect.browser + ' ' + BrowserDetect.version + ' on ' + BrowserDetect.OS );
    var os = {
        isWindows : function() {
            return BrowserDetect.OS == "Windows" ? true : false;
        },

        isLinux : function() {
            return BrowserDetect.OS == "Linux" ? true : false;
        },
        isMac : function() {
            return BrowserDetect.OS == "Mac" ? true : false;
        },
        isIos : function() {
            return (BrowserDetect.OS == "iPad" || BrowserDetect.OS == "iPhone/iPod") ? true : false;
        },
        isApple : function() {
            return (BrowserDetect.OS == "iPad" || BrowserDetect.OS == "iPhone/iPod"  || BrowserDetect.OS == "Mac" ) ? true : false;
        },
        isAndroid : function() {
            return (BrowserDetect.OS == "Android") ? true : false;
        }
    };

    return os;
}
