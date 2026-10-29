@extends('layouts.app')

@section('titre', 'Parrainage')

@section('contenu')
@php($lien = route('inscription', ['ref' => $user->code_parrainage]))
<div class="conteneur section">
    <h1>Parraine tes amis, gagne des mois gratuits</h1>
    <p class="muted">
        Pour chaque ami qui s'inscrit avec ton code et s'abonne pour la première fois, tu gagnes
        <strong>{{ config('mayele.parrainage_jours') }} jours</strong> d'abonnement.
        Tu gagnes aussi <strong>{{ config('mayele.annale_jours') }} jours</strong> pour chaque annale que tu déposes et que l'équipe valide
        (jusqu'à {{ config('mayele.annale_max') }} annales).
    </p>

    <div class="carte" style="max-width:640px">
        <p class="muted" style="margin:0">Ton code de parrainage</p>
        <div class="code-parrainage">{{ $user->code_parrainage }}</div>

        <div class="champ">
            <label for="lien">Ton lien d'invitation</label>
            <input id="lien" readonly value="{{ $lien }}">
        </div>

        <div class="actions-chapitre" style="margin-bottom:0">
            <button type="button" class="btn btn-primaire" id="copier">Copier le lien</button>
            <a class="btn btn-contour" target="_blank" rel="noopener"
               href="https://wa.me/?text={{ urlencode('Je prépare mes examens sur '.config('app.name').' : cours, annales et quiz. Inscris-toi avec mon lien : '.$lien) }}">Partager sur WhatsApp</a>
        </div>
    </div>

    <div class="stats" style="margin-top:1.5rem">
        <div class="carte stat"><small class="muted">Amis inscrits</small><strong>{{ $filleuls->count() }}</strong></div>
        <div class="carte stat"><small class="muted">Amis abonnés</small><strong>{{ $nbPayants }}</strong></div>
        <div class="carte stat"><small class="muted">Jours gagnés</small><strong>{{ $joursGagnes }}</strong></div>
        <div class="carte stat"><small class="muted">Annales récompensées</small><strong>{{ $annalesRecompensees }} / {{ config('mayele.annale_max') }}</strong></div>
    </div>

    <div class="grille" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr))">
        <div class="carte" style="overflow-x:auto">
            <h2>Mes filleuls</h2>
            <table class="tableau">
                <thead><tr><th>Ami</th><th>Inscrit le</th><th>Statut</th></tr></thead>
                <tbody>
                    @forelse ($filleuls as $filleul)
                        <tr>
                            <td>{{ $filleul->nom_public }}</td>
                            <td>{{ $filleul->created_at->translatedFormat('d/m/Y') }}</td>
                            <td><span class="badge {{ $filleul->a_paye ? 'badge-ok' : 'badge-attente' }}">{{ $filleul->a_paye ? 'Abonné' : 'Inscrit' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="muted">Personne n'a encore utilisé ton code.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="carte" style="overflow-x:auto">
            <h2>Mes récompenses</h2>
            <table class="tableau">
                <thead><tr><th>Date</th><th>Origine</th><th>Jours</th></tr></thead>
                <tbody>
                    @forelse ($recompenses as $recompense)
                        <tr>
                            <td>{{ $recompense->created_at->translatedFormat('d/m/Y') }}</td>
                            <td>{{ $recompense->type === 'parrainage' ? 'Parrainage' : 'Annale validée' }}</td>
                            <td>+{{ $recompense->jours }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="muted">Aucune récompense pour le moment.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <p class="muted"><small>Les jours gagnés s'ajoutent à ton abonnement.</small></p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('copier').addEventListener('click', async (e) => {
        const champ = document.getElementById('lien');
        try {
            await navigator.clipboard.writeText(champ.value);
        } catch (err) {
            champ.select();
            document.execCommand('copy');
        }
        e.target.textContent = 'Lien copié ✓';
    });
</script>
@endpush