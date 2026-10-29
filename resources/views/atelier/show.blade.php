@extends('layouts.app')

@section('titre', $exercice->titre)

@section('contenu')
@php
    $estSql = $exercice->langage === 'sql';
    $estHtml = $exercice->langage === 'html';
    $estIo = ! $estSql && ! $estHtml;
    $premierVisible = collect($exercice->tests)->firstWhere('visible', true);
@endphp

<div class="conteneur section">
    <p class="fil-ariane">
        <a href="{{ route('atelier.index') }}">Atelier de code</a> › {{ $exercice->titre }}
    </p>

    <div class="atelier">
        <section>
            <div class="carte">
                <div class="tags" style="margin-bottom:.8rem">
                    <span class="tag tag-ok">{{ \App\Models\Exercice::LANGAGES[$exercice->langage] }}</span>
                    <span class="tag">{{ \App\Models\Exercice::DIFFICULTES[$exercice->difficulte] }}</span>
                    @if ($exercice->matiere)<span class="tag">{{ $exercice->matiere->nom }}</span>@endif
                    @if ($progression?->reussi)<span class="tag tag-ok">✓ Réussi</span>@endif
                </div>

                <h1 style="font-size:1.6rem">{{ $exercice->titre }}</h1>
                <div class="contenu">{!! \Illuminate\Support\Str::markdown($exercice->enonce, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>

                @if ($estIo && $exercice->langage === 'javascript')
                    <p class="muted"><small>En JavaScript, lis l'entrée avec <code>lire()</code> et affiche avec <code>console.log()</code>.</small></p>
                @endif

                @if ($exercice->indice)
                    <details style="margin-top:1rem">
                        <summary><strong>💡 Besoin d'un indice ?</strong></summary>
                        <div class="contenu" style="margin-top:.5rem">{!! \Illuminate\Support\Str::markdown($exercice->indice, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>
                    </details>
                @endif

                @if ($exercice->solution)
                    <div style="margin-top:1rem">
                        <button type="button" class="btn btn-contour btn-sm" id="btn-solution">Voir la solution</button>
                        <small class="muted" id="aide-solution"></small>
                        <pre class="atelier-sortie" id="zone-solution" hidden></pre>
                    </div>
                @endif
            </div>

            @if ($exercice->chapitre)
                <p style="margin-top:1rem"><a class="btn btn-contour btn-sm" href="{{ route('chapitres.show', [$exercice->chapitre->matiere->slug ?? $exercice->matiere?->slug ?? '', $exercice->chapitre->slug]) }}">← Revoir le chapitre</a></p>
            @endif
        </section>

        <section>
            <div class="atelier-barre">
                <button type="button" class="btn btn-contour btn-sm" id="btn-executer">▶ Exécuter</button>
                <button type="button" class="btn btn-primaire btn-sm" id="btn-valider">✓ Valider</button>
                <button type="button" class="btn btn-contour btn-sm" id="btn-reset">↺ Recommencer</button>
                <span class="statut" id="statut-env">Prêt</span>
            </div>

            <textarea id="code" spellcheck="false" autocapitalize="off" autocomplete="off" aria-label="Éditeur de code">{{ $codeInitial }}</textarea>

            @if ($estIo)
                <details style="margin-top:.6rem">
                    <summary>Entrée du programme (pour « Exécuter »)</summary>
                    <textarea id="entree-perso" rows="3" style="width:100%;margin-top:.4rem;font-family:monospace">{{ $premierVisible['entree'] ?? '' }}</textarea>
                </details>
            @endif

            @if ($estHtml)
                <iframe id="apercu" class="apercu" sandbox="allow-scripts" title="Aperçu de ta page"></iframe>
            @endif

            <div class="atelier-sortie" id="sortie">Clique sur « Exécuter » pour voir le résultat de ton code.</div>

            <div class="bandeau-reussite" id="bandeau" hidden></div>
            <div id="resultats"></div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script type="application/json" id="atelier-config">{!! Illuminate\Support\Js::encode($client) !!}</script>
<script>
(() => {
    const cfg = JSON.parse(document.getElementById('atelier-config').textContent);
    const $ = (id) => document.getElementById(id);
    const zoneCode = $('code');
    const sortie = $('sortie');
    const resultats = $('resultats');
    const statut = $('statut-env');
    const bandeau = $('bandeau');
    const btnExec = $('btn-executer');
    const btnValider = $('btn-valider');
    const btnReset = $('btn-reset');
    const btnSolution = $('btn-solution');
    const zoneSolution = $('zone-solution');
    const aideSolution = $('aide-solution');
    const entree = $('entree-perso');
    const apercu = $('apercu');
    const cleBrouillon = 'atelier-' + cfg.id;
    const estIo = cfg.langage === 'python' || cfg.langage === 'javascript';
    let solutionPossible = cfg.solutionPossible;

    // ---------- Utilitaires ----------
    const el = (balise, classe, texte) => {
        const n = document.createElement(balise);
        if (classe) n.className = classe;
        if (texte !== undefined) n.textContent = texte;
        return n;
    };
    const normaliser = (s) => String(s).replace(/\r/g, '').split('\n').map((l) => l.replace(/\s+$/, '')).join('\n').trim();
    const verrouiller = (oui) => { [btnExec, btnValider, btnReset].forEach((b) => { b.disabled = oui; }); };
    const MSG_TEMPS = 'Temps dépassé : ton programme tourne trop longtemps (boucle infinie ?).';

    // ---------- Brouillon local et éditeur ----------
    try {
        const brouillon = localStorage.getItem(cleBrouillon);
        if (brouillon !== null && !cfg.aProgression) zoneCode.value = brouillon;
    } catch (e) { /* stockage indisponible */ }

    zoneCode.addEventListener('input', () => {
        try { localStorage.setItem(cleBrouillon, zoneCode.value); } catch (e) { /* sans importance */ }
    });
    zoneCode.addEventListener('keydown', (e) => {
        if (e.key === 'Tab') {
            e.preventDefault();
            zoneCode.setRangeText('    ', zoneCode.selectionStart, zoneCode.selectionEnd, 'end');
        }
    });

    // ---------- JavaScript : exécution isolée dans un Worker, avec délai maximum ----------
    const sourceJs = `
self.onmessage = (e) => {
    const code = e.data.code;
    const lignes = e.data.entree === '' ? [] : e.data.entree.split('\\n');
    let i = 0;
    let sortie = '';
    const fmt = (v) => {
        if (typeof v === 'string') return v;
        try { return JSON.stringify(v); } catch (_) { return String(v); }
    };
    const ecrire = (...a) => { sortie += a.map(fmt).join(' ') + '\\n'; };
    const console2 = { log: ecrire, info: ecrire, warn: ecrire, error: ecrire };
    const lire = () => (i < lignes.length ? lignes[i++] : '');
    try {
        new Function('console', 'lire', 'prompt', 'fetch', 'XMLHttpRequest', 'importScripts', code)(console2, lire, lire);
        self.postMessage({ sortie: sortie, erreur: null });
    } catch (err) {
        self.postMessage({ sortie: sortie, erreur: String(err && err.message ? err.message : err) });
    }
};`;

    function lancerWorkerJs(message) {
        return new Promise((resolve) => {
            const url = URL.createObjectURL(new Blob([sourceJs], { type: 'text/javascript' }));
            const worker = new Worker(url);
            let minuteur = null;
            const fin = (resultat) => {
                clearTimeout(minuteur);
                worker.terminate();
                URL.revokeObjectURL(url);
                resolve(resultat);
            };
            minuteur = setTimeout(() => fin({ sortie: '', erreur: MSG_TEMPS }), cfg.delai);
            worker.onmessage = (e) => fin(e.data);
            worker.onerror = (e) => fin({ sortie: '', erreur: e.message || 'Erreur d\'exécution.' });
            worker.postMessage(message);
        });
    }

    // ---------- Python : Pyodide dans un Worker (téléchargé à la première utilisation) ----------
    const sourcePython = `
let pyodide = null;
self.onmessage = async (e) => {
    const m = e.data;
    if (m.type === 'init') {
        try {
            importScripts(m.url);
            pyodide = await loadPyodide();
            self.postMessage({ type: 'pret' });
        } catch (err) {
            self.postMessage({ type: 'echec', message: String(err && err.message ? err.message : err) });
        }
        return;
    }
    if (m.type === 'run') {
        let sortie = '';
        const lignes = m.entree === '' ? [] : m.entree.split('\\n');
        let i = 0;
        pyodide.setStdout({ batched: (s) => { sortie += s + '\\n'; } });
        pyodide.setStderr({ batched: () => {} });
        pyodide.setStdin({ stdin: () => (i < lignes.length ? lignes[i++] : undefined) });
        const globals = pyodide.globals.get('dict')();
        try {
            await pyodide.runPythonAsync(m.code, { globals: globals });
            self.postMessage({ type: 'resultat', id: m.id, sortie: sortie, erreur: null });
        } catch (err) {
            self.postMessage({ type: 'resultat', id: m.id, sortie: sortie, erreur: String(err && err.message ? err.message : err) });
        } finally {
            globals.destroy();
        }
    }
};`;

    let pyWorker = null;
    let pyPret = null;

    function initialiserPython() {
        if (pyPret) return pyPret;
        pyPret = new Promise((resolve, reject) => {
            const url = URL.createObjectURL(new Blob([sourcePython], { type: 'text/javascript' }));
            pyWorker = new Worker(url);
            pyWorker.onmessage = (e) => {
                if (e.data.type === 'pret') resolve();
                else if (e.data.type === 'echec') reject(new Error(e.data.message));
            };
            pyWorker.onerror = (e) => reject(new Error(e.message || 'Chargement de Python impossible.'));
            pyWorker.postMessage({ type: 'init', url: cfg.pyodideUrl });
        });
        pyPret.catch(() => { pyPret = null; pyWorker = null; });
        return pyPret;
    }

    function nettoyerErreurPython(message) {
        const l = String(message).split('\n');
        const i = l.findIndex((x) => x.includes('File "<exec>"'));
        return (i >= 0 ? ['Traceback (most recent call last):'].concat(l.slice(i)) : l.slice(-4)).join('\n').trim();
    }

    async function executerPython(code, texteEntree) {
        statut.textContent = pyPret ? 'Exécution…' : 'Chargement de Python (première fois : environ 10 Mo)…';
        await initialiserPython();
        statut.textContent = 'Exécution…';

        return new Promise((resolve) => {
            const id = Math.random().toString(36).slice(2);
            const minuteur = setTimeout(() => {
                pyWorker.terminate();
                pyWorker = null;
                pyPret = null;
                resolve({ sortie: '', erreur: MSG_TEMPS });
            }, cfg.delai);

            pyWorker.onmessage = (e) => {
                if (e.data.type === 'resultat' && e.data.id === id) {
                    clearTimeout(minuteur);
                    resolve({ sortie: e.data.sortie, erreur: e.data.erreur ? nettoyerErreurPython(e.data.erreur) : null });
                }
            };
            pyWorker.postMessage({ type: 'run', id: id, code: code, entree: texteEntree });
        });
    }

    function executerEntreeSortie(code, texteEntree) {
        return cfg.langage === 'python'
            ? executerPython(code, texteEntree)
            : lancerWorkerJs({ code: code, entree: texteEntree });
    }

    // ---------- SQL : SQLite dans le navigateur (sql.js) ----------
    let moduleSql = null;
    async function chargerSql() {
        if (moduleSql) return moduleSql;
        statut.textContent = 'Chargement de SQL…';
        await new Promise((resolve, reject) => {
            const s = document.createElement('script');
            s.src = cfg.sqljs + 'sql-wasm.js';
            s.onload = resolve;
            s.onerror = () => reject(new Error('Chargement de SQL impossible. Vérifie ta connexion.'));
            document.head.appendChild(s);
        });
        moduleSql = await initSqlJs({ locateFile: (f) => cfg.sqljs + f });
        return moduleSql;
    }

    async function executerSql(code, test) {
        const SQL = await chargerSql();
        const base = new SQL.Database();
        try {
            base.exec(test.preparation);
            const res = base.exec(code);
            if (!res.length) return { colonnes: [], lignes: [], erreur: null };
            const dernier = res[res.length - 1];
            return { colonnes: dernier.columns, lignes: dernier.values, erreur: null };
        } catch (err) {
            return { colonnes: [], lignes: [], erreur: String(err && err.message ? err.message : err) };
        } finally {
            base.close();
        }
    }

    const texteCellule = (v) => (v === null ? 'NULL' : String(v));

    function memesLignes(obtenues, attendues, ordre) {
        const a = obtenues.map((r) => JSON.stringify(r.map(texteCellule)));
        const b = attendues.map((r) => JSON.stringify(r.map(texteCellule)));
        if (a.length !== b.length) return false;
        if (!ordre) { a.sort(); b.sort(); }
        return a.every((v, i) => v === b[i]);
    }

    // ---------- HTML : vérification sans exécuter le code (analyse du texte) ----------
    function verifierHtml(code, test) {
        const doc = new DOMParser().parseFromString(code, 'text/html');
        let elements = [];
        try { elements = Array.from(doc.querySelectorAll(test.selecteur)); } catch (e) { return false; }
        const texte = (test.texte || '').toLowerCase();
        const bons = texte ? elements.filter((x) => x.textContent.toLowerCase().includes(texte)) : elements;
        return bons.length >= (test.min || 1);
    }

    function descriptionHtml(test) {
        return 'Un élément « ' + test.selecteur + ' »'
            + (test.texte ? ' contenant « ' + test.texte + ' »' : '')
            + ((test.min || 1) > 1 ? ' (au moins ' + test.min + ')' : '');
    }

    // ---------- Évaluation d'un test ----------
    async function evaluer(code, test) {
        if (test.type === 'io') {
            const r = await executerEntreeSortie(code, test.entree);
            return { ok: !r.erreur && normaliser(r.sortie) === normaliser(test.sortie), obtenu: r.sortie, erreur: r.erreur };
        }
        if (test.type === 'sql') {
            const r = await executerSql(code, test);
            return { ok: !r.erreur && memesLignes(r.lignes, test.attendu, test.ordre), lignes: r.lignes, erreur: r.erreur };
        }
        return { ok: verifierHtml(code, test) };
    }

    function afficherTest(numero, test, r) {
        const bloc = el('div', 'test ' + (r.ok ? 'test-ok' : 'test-ko'));
        bloc.appendChild(el('strong', '', (r.ok ? '✓ ' : '✗ ') + 'Test ' + numero + (test.visible ? '' : ' (caché)')));

        if (test.visible) {
            if (test.type === 'io') {
                if (test.entree !== '') bloc.appendChild(el('pre', '', 'Entrée :\n' + test.entree));
                bloc.appendChild(el('pre', '', 'Attendu :\n' + test.sortie));
                if (!r.ok) bloc.appendChild(el('pre', '', 'Obtenu :\n' + (r.obtenu || '') + (r.erreur ? '\n' + r.erreur : '')));
            } else if (test.type === 'sql') {
                if (!r.ok) {
                    bloc.appendChild(el('pre', '', r.erreur ? r.erreur : 'Obtenu :\n' + r.lignes.map((l) => l.map(texteCellule).join(' | ')).join('\n')));
                    bloc.appendChild(el('pre', '', 'Attendu :\n' + test.attendu.map((l) => l.join(' | ')).join('\n')));
                }
            } else {
                bloc.appendChild(el('pre', '', descriptionHtml(test)));
            }
        } else if (!r.ok && r.erreur) {
            bloc.appendChild(el('pre', '', r.erreur));
        }

        resultats.appendChild(bloc);
    }

    // ---------- Boutons ----------
    function afficherTableau(r) {
        sortie.className = 'atelier-sortie';
        if (r.erreur) { sortie.className = 'atelier-sortie atelier-erreur'; sortie.textContent = r.erreur; return; }
        if (!r.colonnes.length) { sortie.textContent = 'La requête n\'a renvoyé aucune ligne.'; return; }
        const table = el('table');
        const tete = el('tr');
        r.colonnes.forEach((c) => tete.appendChild(el('th', '', c)));
        table.appendChild(tete);
        r.lignes.forEach((ligne) => {
            const tr = el('tr');
            ligne.forEach((v) => tr.appendChild(el('td', '', texteCellule(v))));
            table.appendChild(tr);
        });
        sortie.replaceChildren(table);
    }

    async function executer() {
        verrouiller(true);
        resultats.replaceChildren();
        bandeau.hidden = true;
        sortie.className = 'atelier-sortie';
        const code = zoneCode.value;

        try {
            if (cfg.langage === 'html') {
                apercu.srcdoc = code;
                sortie.textContent = 'Aperçu mis à jour ci-dessus.';
            } else if (cfg.langage === 'sql') {
                afficherTableau(await executerSql(code, cfg.tests[0]));
            } else {
                const r = await executerEntreeSortie(code, entree ? entree.value : '');
                sortie.textContent = (r.sortie || '') + (r.erreur ? (r.sortie ? '\n' : '') + r.erreur : '') || '(Aucun affichage)';
                if (r.erreur) sortie.className = 'atelier-sortie atelier-erreur';
            }
        } catch (err) {
            sortie.className = 'atelier-sortie atelier-erreur';
            sortie.textContent = String(err && err.message ? err.message : err);
        } finally {
            verrouiller(false);
            statut.textContent = 'Prêt';
        }
    }

    async function enregistrer(reussis, total) {
        const donnees = new FormData();
        donnees.append('_token', cfg.csrf);
        donnees.append('code', zoneCode.value);
        donnees.append('tests_reussis', String(reussis));
        donnees.append('tests_total', String(total));

        try {
            const reponse = await fetch(cfg.urlValider, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: donnees,
            });
            return reponse.ok ? await reponse.json() : null;
        } catch (e) {
            return null;
        }
    }

    async function valider() {
        verrouiller(true);
        resultats.replaceChildren();
        bandeau.hidden = true;
        sortie.className = 'atelier-sortie';
        sortie.textContent = 'Vérification de ta solution…';

        const code = zoneCode.value;
        const total = cfg.tests.length;
        let reussis = 0;

        try {
            for (let i = 0; i < total; i++) {
                const r = await evaluer(code, cfg.tests[i]);
                if (r.ok) reussis++;
                afficherTest(i + 1, cfg.tests[i], r);
            }
        } catch (err) {
            sortie.className = 'atelier-sortie atelier-erreur';
            sortie.textContent = String(err && err.message ? err.message : err);
            verrouiller(false);
            statut.textContent = 'Prêt';
            return;
        }

        const tout = reussis === total;
        sortie.textContent = reussis + ' test' + (reussis > 1 ? 's' : '') + ' réussi' + (reussis > 1 ? 's' : '') + ' sur ' + total + '.';
        sortie.className = 'atelier-sortie' + (tout ? '' : ' atelier-erreur');

        const bilan = await enregistrer(reussis, total);

        if (tout) {
            bandeau.hidden = false;
            bandeau.replaceChildren(el('strong', '', '🎉 Bravo, exercice réussi !'));
            if (cfg.urlSuivant) {
                const a = el('a', 'btn btn-primaire btn-sm', 'Exercice suivant →');
                a.href = cfg.urlSuivant;
                a.style.marginLeft = '.8rem';
                bandeau.appendChild(a);
            }
        }

        if (bilan && bilan.solutionPossible) solutionPossible = true;
        majBoutonSolution();

        verrouiller(false);
        statut.textContent = 'Prêt';
    }

    function majBoutonSolution() {
        if (!btnSolution) return;
        btnSolution.disabled = !solutionPossible;
        aideSolution.textContent = solutionPossible ? '' : ' Disponible après 2 essais de validation.';
    }

    async function voirSolution() {
        const donnees = new FormData();
        donnees.append('_token', cfg.csrf);
        try {
            const reponse = await fetch(cfg.urlSolution, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: donnees,
            });
            const data = await reponse.json().catch(() => ({}));
            zoneSolution.hidden = false;
            zoneSolution.textContent = reponse.ok ? data.solution : (data.message || 'Solution indisponible.');
        } catch (e) {
            zoneSolution.hidden = false;
            zoneSolution.textContent = 'Connexion impossible.';
        }
    }

    btnExec.addEventListener('click', executer);
    btnValider.addEventListener('click', valider);
    btnReset.addEventListener('click', () => {
        if (confirm('Recommencer avec le code de départ ? Ton code actuel sera effacé.')) {
            zoneCode.value = cfg.codeDepart;
            try { localStorage.setItem(cleBrouillon, zoneCode.value); } catch (e) { /* sans importance */ }
        }
    });
    if (btnSolution) {
        btnSolution.addEventListener('click', voirSolution);
        majBoutonSolution();
    }

    if (cfg.langage === 'html') apercu.srcdoc = zoneCode.value;
    if (cfg.langage === 'python') statut.textContent = 'Python se charge à la première exécution';
})();
</script>
@endpush