(function( jwplayer ) {
 
  var overlay = function( player, config, div ) {
 
    var setupOverlay = function() {
      div.innerHTML = config.text;
    };
 
    var showOverlay = function() {
      setStyle({
        opacity: 1
      });
    };
 
    var hideOverlay = function() {
      setStyle({
        opacity: 0
      });
    };
 
    var setStyle = function( object ) {
      for(var style in object) {
        div.style[ style ] = object[ style ];
      }
    };
 
    // Matches our text container to the size of the player instance
    this.resize = function( width, height ) {
      setStyle({
        position: 'absolute',
        margin: '0',
        padding: '14px 262px 10px',
        background: 'none',
        color: 'white',
        fontSize: '12px',
        width: '100%'
      });
    };
 
    // Bind player events
    player.onReady( setupOverlay );
    //player.onPlay( hideOverlay );
    player.onPause( showOverlay );
    player.onComplete( showOverlay );
    player.onFullscreen( showOverlay );
  };
 
  jwplayer().registerPlugin( 'overlay', overlay );
 
})( jwplayer );