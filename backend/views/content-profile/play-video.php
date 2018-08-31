<?php
/**
 * Created by PhpStorm.
 * User: TuanPham
 * Date: 5/11/2017
 * Time: 3:08 PM
 */
?>
<div class="run-video" id="player">

</div>
<script src="<?= Yii::$app->request->baseUrl ?>/js/jquery.js"></script>
<script src="<?= Yii::$app->request->baseUrl ?>/js/jwplayer/jwplayer.js"></script>
<script src="<?= Yii::$app->request->baseUrl ?>/js/ng_player.js"></script>
<script src="<?= Yii::$app->request->baseUrl ?>/js/ng_swfobject.js"></script>
<script src="<?= Yii::$app->request->baseUrl ?>/js/ParsedQueryString.js"></script>
<script>
    $(document).ready(function () {
        var url = '<?= $url ?>';
        var subUrl = '<?= $subtitle ?>';
        loadPlayer(url, subUrl, '');
    });
    function loadPlayer(url, subUrl, image) {
        jwplayer("player").setup({
            modes: [
                {
                    type: 'flash',
                    src: '<?= Yii::$app->request->baseUrl; ?>/js/lives/5.3.swf.disable',
                    config: {
                        provider: '<?= Yii::$app->request->baseUrl; ?>/js/lives/adaptiveProvider.swf',
                        image: image,
                        file: url
                    }
                }
            ],
            flashplayer: "<?= Yii::$app->request->baseUrl; ?>/js/jwplayer/player.swf",
            autostart: 'true',
            value: "netstreambasepath",
            quality: 'false',
            stretching: "exactfit",
            screencolor: "000000",
            provider: 'http',
            'http.startparam': 'start',
            controlbar: 'over',
            icons: 'true',
            image: '<?= Yii::$app->request->baseUrl; ?>/images/Playerss.jpg',
            skin: "<?= Yii::$app->request->baseUrl; ?>/js/jwplayer/skins/modieus/modieus.zip",
            display: {
                icons: 'true'
            },
            dock: 'false',
            width: "640px",
            height: "360px",
            aspectratio: "16:9",
            plugins: {
                "<?= Yii::$app->request->baseUrl; ?>/js/jwplayer/captions.js": {
                    file: subUrl,
                    fontSize: 15,
                    pluginmode: "HYBRID"
                },

                "ova-jw": {
                    "player": {
                        "modes": {
                            "linear": {
                                "controls": {
                                    "enableFullscreen": true,
                                    "enablePlay": true,
                                    "enablePause": true,
                                    "enableMute": true,
                                    "enableVolume": true
                                }
                            }
                        }
                    }
                },
                '<?= Yii::$app->request->baseUrl; ?>/js/overlay.js': {
                    text: null//'<img src="<?php //echo Yii::app()->theme->baseUrl; ?>/images/logo2.png"/>'
                }
            }
        });
    }
</script>
