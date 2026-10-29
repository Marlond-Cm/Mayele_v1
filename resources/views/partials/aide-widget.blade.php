@php($lienSupport = app(\App\Services\AideService::class)->lienSupport())
<div class="aide-widget" id="aide-widget"
     data-url-question="{{ route('aide.question') }}"
     data-url-reset="{{ route('aide.reinitialiser') }}"
     data-url-avis="{{ url('/aide/avis') }}"
     data-support="{{ $lienSupport ?: route('aide').'#support' }}"
     data-support-externe="{{ $lienSupport ? '1' : '0' }}">

    <button type="button" class="aide-bouton" id="aide-ouvrir" aria-expanded="false" aria-controls="aide-panneau">💬 Aide</button>

    <section class="aide-panneau" id="aide-panneau" hidden aria-label="Assistant d'aide">
        <header class="aide-entete">
            <strong>Assistant {{ config('app.name') }}</strong>
            <span>
                <button type="button" id="aide-reset" title="Nouvelle conversation" aria-label="Nouvelle conversation">↺</button>
                <button type="button" id="aide-fermer" title="Fermer" aria-label="Fermer">✕</button>
            </span>
        </header>

        <div class="aide-messages" id="aide-messages" aria-live="polite">
            <div class="aide-bulle aide-coach" id="aide-accueil">Salut ! Pose-moi une question sur le fonctionnement du site : inscription, abonnement, paiement, annales, quiz, parrainage…</div>
        </div>

        <div class="aide-puces" id="aide-puces">
            <button type="button" data-q="Comment m'abonner ?">Comment m'abonner ?</button>
            <button type="button" data-q="J'ai payé mais je n'ai pas accès">J'ai payé, pas d'accès</button>
            <button type="button" data-q="Combien coûte l'abonnement ?">Les tarifs</button>
            <button type="button" data-q="Comment déposer une annale ?">Déposer une annale</button>
            <button type="button" data-q="Comment fonctionne le parrainage ?">Le parrainage</button>
        </div>

        <form id="aide-form" class="aide-saisie" autocomplete="off">
            @csrf
            <input type="text" name="question" maxlength="{{ config('aide.assistant.max_caracteres') }}" placeholder="Écris ta question…" required aria-label="Ta question">
            <button type="submit">Envoyer</button>
        </form>

        <a class="aide-lien" href="{{ route('aide') }}">Voir toutes les réponses</a>
    </section>
</div>

<script>
(() => {
    const racine = document.getElementById('aide-widget');
    if (!racine) return;

    const bouton = document.getElementById('aide-ouvrir');
    const panneau = document.getElementById('aide-panneau');
    const messages = document.getElementById('aide-messages');
    const puces = document.getElementById('aide-puces');
    const form = document.getElementById('aide-form');
    const champ = form.querySelector('input[name="question"]');
    const envoi = form.querySelector('button[type="submit"]');

    function ouvrir() {
        panneau.hidden = false;
        bouton.setAttribute('aria-expanded', 'true');
        champ.focus();
    }

    function fermer() {
        panneau.hidden = true;
        bouton.setAttribute('aria-expanded', 'false');
    }

    bouton.addEventListener('click', () => (panneau.hidden ? ouvrir() : fermer()));
    document.getElementById('aide-fermer').addEventListener('click', fermer);
    document.querySelectorAll('[data-ouvrir-aide]').forEach((el) => {
        el.addEventListener('click', (e) => { e.preventDefault(); ouvrir(); });
    });
    if (location.hash === '#poser') ouvrir();

    function bulle(classes, texte) {
        const div = document.createElement('div');
        div.className = 'aide-bulle ' + classes;
        if (texte !== undefined) div.textContent = texte;
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
        return div;
    }

    function lienSupport() {
        const a = document.createElement('a');
        a.className = 'aide-support';
        a.href = racine.dataset.support;
        a.textContent = racine.dataset.supportExterne === '1' ? 'Contacter le support sur WhatsApp' : 'Voir comment contacter le support';
        if (racine.dataset.supportExterne === '1') { a.target = '_blank'; a.rel = 'noopener'; }
        return a;
    }

    function avis(id, conteneur) {
        const zone = document.createElement('div');
        zone.className = 'aide-avis';
        zone.append('Cette réponse t\'a aidé ? ');

        [['👍', 1], ['👎', 0]].forEach(([emoji, valeur]) => {
            const b = document.createElement('button');
            b.type = 'button';
            b.textContent = emoji;
            b.addEventListener('click', async () => {
                const donnees = new FormData(form);
                donnees.set('utile', String(valeur));
                donnees.delete('question');
                try {
                    await fetch(racine.dataset.urlAvis + '/' + id, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: donnees,
                    });
                } catch (e) { /* sans importance */ }
                zone.textContent = 'Merci pour ton avis !';
            });
            zone.appendChild(b);
        });

        conteneur.appendChild(zone);
    }

    async function envoyer(question) {
        bulle('aide-moi', question);
        champ.value = '';
        champ.disabled = true;
        envoi.disabled = true;
        puces.hidden = true;

        const attente = bulle('aide-coach aide-attente', 'Je cherche la réponse…');

        try {
            const donnees = new FormData(form);
            donnees.set('question', question);

            const reponse = await fetch(racine.dataset.urlQuestion, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: donnees,
            });
            const data = await reponse.json().catch(() => ({}));

            if (!reponse.ok) {
                attente.className = 'aide-bulle aide-coach aide-erreur';
                attente.textContent = reponse.status === 429
                    ? 'Tu poses beaucoup de questions à la suite. Patiente une minute puis réessaie.'
                    : ((data.errors && data.errors.question ? data.errors.question[0] : data.message)
                        || 'Une erreur est survenue. Recharge la page et réessaie.');
                return;
            }

            attente.className = 'aide-bulle aide-coach aide-contenu';
            attente.innerHTML = data.html; // HTML déjà nettoyé côté serveur

            if (data.articles && data.articles.length) {
                const liste = document.createElement('div');
                liste.className = 'aide-articles';
                liste.append('Pour en savoir plus : ');
                data.articles.forEach((article) => {
                    const a = document.createElement('a');
                    a.href = article.url;
                    a.textContent = article.question;
                    liste.appendChild(a);
                });
                attente.appendChild(liste);
            }

            if (data.support) attente.appendChild(lienSupport());
            if (data.source === 'ia') avis(data.id, attente);
        } catch (e) {
            attente.className = 'aide-bulle aide-coach aide-erreur';
            attente.textContent = 'Connexion impossible. Vérifie ton réseau et réessaie.';
        } finally {
            champ.disabled = false;
            envoi.disabled = false;
            champ.focus();
            messages.scrollTop = messages.scrollHeight;
        }
    }

    puces.querySelectorAll('button').forEach((b) => b.addEventListener('click', () => envoyer(b.dataset.q)));

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const question = champ.value.trim();
        if (question.length >= 3) envoyer(question);
    });

    document.getElementById('aide-reset').addEventListener('click', async () => {
        try {
            await fetch(racine.dataset.urlReset, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form),
            });
        } catch (e) { /* sans importance */ }
        messages.querySelectorAll('.aide-bulle:not(#aide-accueil)').forEach((el) => el.remove());
        puces.hidden = false;
    });
})();
</script>