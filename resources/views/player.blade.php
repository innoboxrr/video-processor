<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta content="width=device-width, initial-scale=1, maximum-scale=1" name="viewport">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>{{ $video->name }}</title>

        <!-- Video.js + Nuevo (skins y plugins) -->
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
                /* Make video at least 100% wide and tall */
                min-width: 100%;
                min-height: 100%;

                /* Prevent stretching/squishing */
                width: auto;
                height: auto;

                /* Center the video */
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
            }
        </style>
    </head>

    <body>
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

                <!-- CHAPTERS (referencia) -->
                {{-- 
                <track 
                    kind="chapters" 
                    src="/plugins/videojs8/examples/chapters/test-en.vtt" 
                    srclang="en" />

                <track 
                    kind="chapters" 
                    src="/plugins/videojs8/examples/chapters/test-es.vtt" 
                    srclang="es" />
                --}}
            </video>
        </div>

        <script>
            /**
             * Envío seguro al parent via postMessage
             */
            function postParent(event, data) {
                try {
                    window.parent.postMessage({ event, data }, '*');
                } catch (e) {
                    console.error('postMessage error:', e);
                }
            }

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
                    logoposition: "{{ $video->logo_position }}", // RT: Right Top, LT: Left Top, BL: Bottom Left
                    target: '_blank', // (_blank) _self
                    logotitle: "{{ $video->title }}",
                @endif

                // ** Context menu **
                contextMenu: false,

                // ** Video Info (referencia) **
                // videoInfo: true,
                // infoIcon: "/plugins/videojs8/examples/assets/images/logo_small.png",  // optional
                // infoUrl: "https://www.nuevodevel.com/nuevo/showcase/videoinfo",     // optional
                // infoTitle: "{{ $video->lesson->name }}",
                // infoDescription: "{{ $video?->user?->name }}",

                // ** Player setup (referencia) **
                // relatedMenu: true,               // (true)
                shareMenu: false,                   // (true)
                // rateMenu: true,                   // (true)
                // zoomMenu: true,                   // (true)
                // settingsButton: true,             // (true)
                // controlbar: true,                 // (true)
                // iosFullscreen: 'native',          // (native) 'pseudo'
                // androidLock: true,
                // pipButton: true,                  // Show/Hide PictureInPicture button
                // ccButton: true,
                // qualityMenu: true,                // (nota: podría no funcionar en esta build)
                // tooltips: true,
                // hdicon: true,                     // Muestra la opción de HD en el menú de calidad
                // chapterMarkers: true,
                // touchControls: true,
                // touchRewindForward: true,

                // ** Zoom (referencia) **
                // zoomInfo: true,
                // zoomWheel: true,

                // ** Rewind/Forward (referencia) **
                // buttonRewind: true,
                // buttonForward: true,
                // mirrorButton: true,
                // theaterButton: true,
                // rewindforward: 10,

                // ** Start Time (referencia) **
                // startTime: undefined,             // Define el segundo de inicio

                // ** Resume **
                video_id: "{{ $video->code }}",
                resume: true,                       // Retoma desde donde se quedó
                // endAction: undefined,
                // related: [],                      // array de videos relacionados

                // ** Sprite (referencia) **
                // Docs: https://www.nuevodevel.com/nuevo/showcase/sprite
                // slideImage: "/plugins/videojs8/examples/images/sprite.jpg",
                // ghostThumb: true,

                // ** Limit Image (referencia) **
                // limit: 5,
                // limiturl: 'https://laravelers.com',
                // limitimage: '/videojs/examples/images/limit.png',
                // limitmessage: 'Your message text', // optional

                // ** Snapshot (referencia) **
                // snapshot: true,
                // snapshotWatermark: "laravelers.com",
            });

            /*
            // Hotkeys (referencia)
            player.hotkeys({
                volumeStep: 0.1,
                seekStep: 5
            });
            */

            // Activa tracking de eventos (plugin videojs.events)
            player.events({ analytics: true });

            // Router de eventos del plugin "nuevo"
            player.on('track', (e, data) => {
                switch (data.event) {
                    case 'loaded': {
                        const d = {
                            video_id: data.playerId,
                            video_title: data.playerTitle,
                            loadTime: data.initialLoadTime, // always 0 for live video
                        };
                        // Conserva nombre original del evento para compatibilidad
                        postParent('loadPlayer', d);
                        break;
                    }
                    case 'firstPlay': {
                        postParent('firstPlay', data);
                        break;
                    }
                    case 'pause': {
                        postParent('pause', data.pauseCount);
                        break;
                    }
                    case 'resume': {
                        postParent('resume', data.resumeCount);
                        break;
                    }
                    case 'buffered': {
                        postParent('buffered', data.bufferTime);
                        break;
                    }
                    case 'seek': {
                        postParent('seek', data.seekTo);
                        break;
                    }
                    case '10%': {
                        // Referencia: aquí podrías persistir progreso al 10%
                        postParent('10%', data.currentTime);
                        break;
                    }
                    case '25%': {
                        postParent('25%', data.currentTime);
                        break;
                    }
                    case '50%': {
                        postParent('50%', data.currentTime);
                        break;
                    }
                    case '75%': {
                        postParent('75%', data.currentTime);
                        break;
                    }
                    case '90%': {
                        postParent('90%', data.currentTime);
                        break;
                    }
                    case 'mute': {
                        postParent('mute', null);
                        break;
                    }
                    case 'unmute': {
                        postParent('unmute', null);
                        break;
                    }
                    case 'rateChange': {
                        postParent('rateChange', data.rate);
                        break;
                    }
                    case 'enterFullscreen': {
                        postParent('enterFullscreen', null);
                        break;
                    }
                    case 'exitFullscreen': {
                        postParent('exitFullscreen', null);
                        break;
                    }
                    case 'resolutionChange': {
                        postParent('resolutionChange', data.res);
                        break;
                    }
                    case 'summary': {
                        const summary = {
                            pauseCount: data.pauseCount,
                            resumeCount: data.resumeCount,
                            bufferCount: data.bufferCount,
                            videoDuration: data.totalDuration,
                            total_bufferingDuration: data.bufferDuration,
                            real_watch_time: data.watchedDuration,
                        };
                        postParent('summary', summary);
                        break;
                    }
                    default: {
                        postParent('default', data);
                        break;
                    }
                }
            });

            // Evento nativo de fin de reproducción (nuevo)
            player.on('ended', () => {
                const payload = {
                    endedAt: player.currentTime(),
                    duration: player.duration(),
                    video_id: "{{ $video->code }}",
                };
                postParent('ended', payload);
            });

            // Escucha comandos del contenedor (ventana padre)
            window.addEventListener('message', onMessageReceived);

            function onMessageReceived(event) {
                // Si deseas validar el origin explícitamente, reemplaza '*' y compara event.origin
                const cmd = event?.data?.event;
                switch (cmd) {
                    case 'play':
                        player.play();
                        break;
                    case 'pause':
                        player.pause();
                        break;
                    case 'rewind':
                        // Si tu plugin expone player.rewind() úsalo; aquí se hace fallback a -10s
                        if (typeof player.rewind === 'function') {
                            player.rewind();
                        } else {
                            player.currentTime(Math.max(0, player.currentTime() - 10));
                        }
                        break;
                    case 'forward':
                        if (typeof player.forward === 'function') {
                            player.forward();
                        } else {
                            player.currentTime(Math.min(player.duration(), player.currentTime() + 10));
                        }
                        break;
                    case 'mute':
                        player.muted(true);
                        break;
                    case 'unmute':
                        player.muted(false);
                        break;
                    default:
                        break;
                }
            }

            // Cuenta de consumo de ancho de banda (referencia / debug)
            let lastTime = new Date().getTime();
            let lastBytes = 0;

            player.on('progress', function () {
                try {
                    // Puede lanzar si aún no hay rango bufferizado
                    const end = player.buffered().end(0);
                    // console.log("end: " + end);
                } catch (e) {
                    // Silenciar errores cuando no hay buffer aún
                }
            });

            // Verificación de iFrame (referencia)
            if (window.self !== window.parent) {
                // Pendiente: Verificar que se inserte solo dentro de un curso
            } else {
                // Verificar si el video permite embed @if($video->embed) @endif
                // Si no se inserta en un espacio, regresar al inicio
                // location.href = '{{ url('/') }}'
            }

            // Visibilidad de la pestaña (referencia)
            document.addEventListener('visibilitychange', function () {
                if (document.visibilityState === 'hidden') {
                    postParent('visibilitychange', { hidden: true });
                    // console.log('El usuario podría haber cambiado de pestaña o minimizado la ventana');
                } else {
                    postParent('visibilitychange', { hidden: false });
                    // console.log('El usuario ha regresado a la ventana');
                }
            });

            // Inactividad del usuario (referencia)
            let inactivityTime = 0;

            function resetInactivityTimer() {
                inactivityTime = 0;
            }

            function checkInactivity() {
                inactivityTime++;
                if (inactivityTime > 10) { // Por ejemplo, 10s de inactividad
                    postParent('inactivity', { inactive: true });
                    // console.log('El usuario podría no estar interactuando con la página');
                } else {
                    postParent('inactivity', { inactive: false });
                }
            }

            setInterval(checkInactivity, 1000); // Verifica cada segundo
            document.addEventListener('mousemove', resetInactivityTimer);
            document.addEventListener('click', resetInactivityTimer);
            document.addEventListener('keypress', resetInactivityTimer);

            /*
            // Chromecast (referencia)
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
                metaTitle: "Video title",
                metaSubtitle: "Video subtitle",
                metaThumbnail: "/plugins/videojs8/examples/images/logo.png"
            });
            */
        </script>
    </body>
</html>
