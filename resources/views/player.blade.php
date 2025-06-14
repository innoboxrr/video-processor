
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta content="width=device-width, initial-scale=1, maximum-scale=1" name="viewport">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>{{ $video->name }}</title>
    </head>

    <body>

        <link href="/plugins/videojs8/skins/{{ $video->theme }}/videojs.min.css" rel="stylesheet" type="text/css">     
        <script src="/plugins/videojs8/video.min.js"></script>
        <script src="/plugins/videojs8/nuevo.min.js?78"></script>
        <script src="/plugins/videojs8/plugins/videojs.events.js"></script>
        <style type="text/css">
            * {
                margin: 0;
                padding: 0;
            }
            .video-container {
                position: absolute;
                top: 0;
                bottom: 0;
                width: 100%;
                height: 100%; 
                overflow: hidden;
            }
            .video-container .video-js {
                /* Make video to at least 100% wide and tall */
                min-width: 100%; 
                min-height: 100%; 

                /* Setting width & height to auto prevents the browser from stretching or squishing the video */
                width: auto;
                height: auto;

                /* Center the video */
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%,-50%);
            }
        </style>

        <div class="video-container">

            <video 
                id="player_{{ $video->id }}" 
                class="video-js" 
                controls 
                preload="auto" 
                playsinline 
                crossorigin="anonymous"
                @if($video->thumbnail) poster="{{ $video->thumbnail }}" @endif>
                <!-- SOURCE -->
                <source 
                    type="application/x-mpegURL"
                    src="{{ route('videoprocessor.playlist', ['code' => $video->code, 'filename' => 'original.m3u8', 'guest_token' => request()->guest_token]) }}">
                


                <!-- CAPTIONS -->
                @foreach($video->subtitles as $subtitle)

                    <track 
                        kind="captions" 
                        src="{{ $subtitle->vtt_url }}" 
                        srclang="{{ $subtitle->language->code }}" 
                        label="{{ $subtitle->language->name }}" 
                        @if($video->language_id == $subtitle->language_id) default @endif />
                
                @endforeach

                <!-- CHAPTERS -->
                {{--
                <track 
                    kind="chapters" 
                    src="/plugins/videojs8/examples/chapters/test-en.vtt" 
                    srclang="en"/>

                <track 
                    kind="chapters" 
                    src="/plugins/videojs8/examples/chapters/test-es.vtt" 
                    srclang="es" />
                --}}
            </video>
        </div>

        <script>

            var player = videojs('player_{{ $video->id }}');
            
            player.nuevo({ 

                // ** Data **
                @if($video->title) 
                    title: "{{ $video->title }}", 
                @endif

                url: "{{ route('videoprocessor.player', ['code' => $video->code, 'guest_token' => request()->guest_token]) }}",

                @if($video->embed) 
                    embed: '<iframe src="{{ $video->embed }}" width="640" height="360" frameborder="0" allowfullscreen></iframe>',
                @endif

                // ** Logos **
                @if($video->show_watermark)
                    logo: "{{ config('videoprocessor.video_watermark') }}",
                    logocontrolbar: "{{ config('videoprocessor.video_icon') }}", 
                    logourl: "{{ $video->logo_url }}", 
                    logoposition: "{{ $video->logo_position }}", // (LT) RT: Right Top, LT: Left Top, BL: Bottom Left 
                    target: '_blanck', // (_blanck) _self
                    logotitle: "{{ $video->title }}",
                @endif

                // ** Context menu **
                contextMenu: false,

                // ** Video Info **
                // videoInfo: true,
                // infoIcon: "/plugins/videojs8/examples/assets/images/logo_small.png",  // optional
                // infoUrl: "https://www.nuevodevel.com/nuevo/showcase/videoinfo",  // optional
                // infoTitle: "{{ $video->lesson->name }}",
                // infoDescription: "{{ $video?->user?->name }}",
                
                // ** Player setup **
                // relatedMenu: true, // (true)
                shareMenu: false, // (true)
                // rateMenu: true, // (true)
                // zoomMenu: true, // (true)
                // settingsButton: true, // (true)
                // controlbar: true, // (true)
                // iosFullscreen: 'native', // (native) 'pseudo'
                // androidLock: true, 
                // pipButton: true, // Show/Hide PictureInPicture button
                // ccButton: true,
                // qualityMenu: true, // Creo que esto no funciona
                // tooltips: true,
                // hdicon: true, // Mestra la opción de HD en el menú de calidad
                // chapterMarkers: true,
                // touchControls: true,
                // touchRewindForward: true,

                // ** Zoom **
                // zoomInfo: true,
                // zoomWheel: true,

                // ** Rewin/Forward **
                // buttonRewind: true,
                // buttonForward: true,
                // mirrorButton: true,
                // theaterButton: true,
                // rewindforward: 10,

                // ** Start Time **
                // startTime: undefined, // Define el tiempo en segundos donde comenzar el video
                
                // ** Resume **
                video_id: "{{ $video->code }}",
                resume: true, // Permite retomar el video desde donde se quedo.
                // endAction: undefined,
                // related: [], //  javascript array of related videos.

                // ** Sprite **
                // Docs: https://www.nuevodevel.com/nuevo/showcase/sprite
                // slideImage: "/plugins/videojs8/examples/images/sprite.jpg",
                // ghostThumb: true,
            

                // ** Limit Image **
                // limit: 5,
                // limiturl: 'https://laravelers.com',
                // limitimage: '/videojs/examples/images/limit.png',
                // limitmessage: 'Your message text' // optional, 

                // ** Snapshot **
                // snapshot: true,
                // snapshotWatermark: "laravelers.com",

            });

            /*
            player.hotkeys({
                volumeStep: 0.1,
                seekStep: 5
            });
            */

            player.events({ analytics: true });

            player.on('track', (e, data) => {

                switch(data.event) {

                    case 'loaded':

                        let d = {

                            video_id: data.playerId,
                            
                            video_title: data.playerTitle,
                            
                            loadTime: data.initialLoadTime, //always 0 for live video

                        };

                        window.parent.postMessage({event: 'loadPlayer', data: d}, '*');
                                                             
                    break;

                    case 'firstPlay':

                        window.parent.postMessage({event: 'firstPlay', data: data}, '*');

                    break;

                    case 'pause':
                        
                        var pauseCount = data.pauseCount;

                        window.parent.postMessage({event: 'pause', data: pauseCount}, '*');

                    break;

                    case 'resume':
                        
                        var resumeCount = data.resumeCount;

                        window.parent.postMessage({event: 'resume', data: resumeCount}, '*');

                    break;

                    case 'buffered':
                        
                        var bufferTime = data.bufferTime;

                        window.parent.postMessage({event: 'buffered', data: bufferTime}, '*');

                    break;

                    case 'seek':
                        
                        var seekTo = data.seekTo;

                        window.parent.postMessage({event: 'seek', data: seekTo}, '*');

                    break;

                    case '10%':
                        
                        var currentTime = data.currentTime;

                        console.log('Guardar en servidor la posición del video al 10%');

                        window.parent.postMessage({event: '10%', data: currentTime}, '*');

                    break;

                    case '25%':
                        
                        var currentTime = data.currentTime;

                        window.parent.postMessage({event: '25%', data: currentTime}, '*');

                    break;

                    case '50%':
                        
                        var currentTime = data.currentTime;

                        window.parent.postMessage({event: '50%', data: currentTime}, '*');

                    break;

                    case '75%':
                        
                        var currentTime = data.currentTime;
                        
                        window.parent.postMessage({event: '75%', data: currentTime}, '*');

                    break;

                    case '90%':
                        
                        var currentTime = data.currentTime;

                        window.parent.postMessage({event: '90%', data: currentTime}, '*');

                    break;

                    case 'mute':

                        window.parent.postMessage({event: 'mute', data: null}, '*');

                    break;

                    case 'unmute':

                        window.parent.postMessage({event: 'unmute', data: null}, '*');

                    break;

                    case 'rateChange':

                        var currentRate = data.rate;

                        window.parent.postMessage({event: 'rateChange', data: currentRate}, '*');

                    break;

                    case 'enterFullscreen':

                        window.parent.postMessage({event: 'enterFullscreen', data: null}, '*');

                    break;

                    case 'exitFullscreen':

                        window.parent.postMessage({event: 'exitFullscreen', data: null}, '*');

                    break;

                    case 'resolutionChange':

                        
                        var currentResolution = data.res;

                        window.parent.postMessage({event: 'resolutionChange', data: currentResolution}, '*');

                    break;

                    case 'summary':

                        let summary = {

                            pauseCount: data.pauseCount,
                            
                            resumeCount: data.resumeCount,
                            
                            bufferCount: data.bufferCount,
                            
                            videoDuration: data.totalDuration,
                            
                            total_bufferingDuration: data.bufferDuration,
                            
                            real_watch_time: data.watchedDuration,

                        }

                        window.parent.postMessage({event: 'summary', data: summary}, '*');

                    break;

                    case 'default':

                        window.parent.postMessage({event: 'default', data: data}, '*');

                    break;

                }

            });

            /*
            player.chromecast({ 
                overlayButton: true,
                sources: [{
                    src: '{{ route('videoprocessor.playlist', [
                        'code' => $video->code,
                        'filename' => 'master.m3u8',
                        'guest_token' => request()->guest_token
                    ]) }}',
                    type: 'application/x-mpegURL'
                }],
                metaTitle:"Video title", 
                metaSubtitle:"Video subtitle", 
                metaThumbnail:"/plugins/videojs8/examples/images/logo.png" 
            });
            */

            // Escucha los eventos prevenientes de la ventana padre que lo contiene
                
                window.addEventListener('message', onMessageReceived);

                function onMessageReceived(event) {

                    switch (event.data.event) {

                        case 'play':

                            player.play()

                        break;

                        case 'pause':

                            player.pause()

                        break;

                        case 'rewind':

                            player.rewind()

                        break;

                        case 'forward':

                            player.forward()

                        break;

                        case 'mute':

                            player.muted(true)

                        break;

                        case 'unmute':

                            player.muted(false)

                        break;

                        default:

                        break;

                    }

                }

            // Cuanta de consumo de ancho de banda

                let lastTime = new Date().getTime();
                let lastBytes = 0;

                player.on('progress', function(event) {
                  
                    console.log(player.buffered.end(0))

                });

            // Verificación de iFrame
                if (window.self !== window.parent) {
                    
                    // Pendiente: Verificar que se inserte solo dentro de un curso

                } else {

                    // Verificar si el video permite embed @if($video->embed) @endif

                    // Si no se inserta en un espacio, regresar al inicio
                    // location.href = '{{ url('/') }}'

                }

            // Verificar cambios en la visibilidad de la ventana del video
                document.addEventListener("visibilitychange", function() {

                    if (document.visibilityState === 'hidden') {

                        console.log('El usuario podría haber cambiado de pestaña o minimizado la ventana');

                    } else {

                        console.log('El usuario ha regresado a la ventana');

                    }

                });

                // Verificar la inactividad del usuario
                let inactivityTime = 0;

                function resetInactivityTimer() {
                    inactivityTime = 0;
                }

                function checkInactivity() {
                    inactivityTime++;
                    if (inactivityTime > 10) { // Por ejemplo, 10 segundos de inactividad
                        console.log("El usuario podría no estar interactuando con la página");
                    }
                }

                setInterval(checkInactivity, 1000); // Verifica la inactividad cada segundo
                document.addEventListener('mousemove', resetInactivityTimer);
                document.addEventListener('click', resetInactivityTimer);
                document.addEventListener('keypress', resetInactivityTimer);

        </script>
    </body>
</html>