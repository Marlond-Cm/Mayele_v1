<!DOCTYPE html>
<html lang="fr" data-theme="{{ $theme }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $classe->titre }} · {{ config('app.name') }}</title>
    @include('partials.head-marque')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="salle">
    <header class="salle-entete">
        <div>
            <strong>{{ $classe->titre }}</strong>
            @if ($options['audio'])<span class="tag">🎧 Mode audio</span>@endif
            @if ($options['moderateur'])<span class="tag tag-ok">Modérateur</span>@endif
        </div>
        <a class="btn btn-contour btn-sm" href="{{ route('direct.show', $classe) }}">Quitter</a>
    </header>

    <div id="visio" data-options="{{ json_encode($options) }}"></div>

    <div id="visio-erreur" class="alerte" hidden style="margin:1rem">
        Impossible de charger la visioconférence. Vérifie ta connexion, désactive le bloqueur de publicités pour ce site, puis recharge la page.
    </div>

    <form id="presence-form" hidden>@csrf</form>

    <script src="{{ $options['script'] }}" onerror="document.getElementById('visio-erreur').hidden = false"></script>
    <script>
        (function () {
            const options = JSON.parse(document.getElementById('visio').dataset.options);

            if (typeof JitsiMeetExternalAPI === 'undefined') {
                document.getElementById('visio-erreur').hidden = false;
                return;
            }

            const api = new JitsiMeetExternalAPI(options.domain, {
                roomName: options.room,
                parentNode: document.getElementById('visio'),
                width: '100%',
                height: '100%',
                jwt: options.jwt || undefined,
                lang: 'fr',
                userInfo: { displayName: options.nom },
                configOverwrite: {
                    subject: options.sujet,
                    defaultLanguage: 'fr',
                    disableDeepLinking: true,      // reste dans le navigateur sur téléphone
                    startWithAudioMuted: true,
                    startWithVideoMuted: options.audio,
                    startAudioOnly: options.audio, // mode audio : économise la data
                    prejoinPageEnabled: true,
                },
                interfaceConfigOverwrite: {
                    MOBILE_APP_PROMO: false,
                    SHOW_JITSI_WATERMARK: false,
                },
            });

            api.addListener('readyToClose', () => {
                window.location.href = "{{ route('direct.show', $classe) }}";
            });

            // Signal de présence toutes les minutes
            const envoyerPresence = () => {
                const donnees = new FormData(document.getElementById('presence-form'));
                fetch("{{ route('direct.presence', $classe) }}", {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: donnees,
                }).catch(() => {});
            };
            setInterval(envoyerPresence, 60000);
        })();
    </script>
</body>
</html>