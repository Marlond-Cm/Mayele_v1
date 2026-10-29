<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AideQuestion;
use Illuminate\View\View;

class AideController extends Controller
{
    public function __invoke(): View
    {
        $depuis = now()->subDays(30);
        $periode = AideQuestion::where('created_at', '>=', $depuis);

        $stats = [
            'questions' => (clone $periode)->count(),
            'ia' => (clone $periode)->where('source', 'ia')->count(),
            'support' => (clone $periode)->where('besoin_support', true)->count(),
            'utiles' => (clone $periode)->where('utile', true)->count(),
            'pas_utiles' => (clone $periode)->where('utile', false)->count(),
            'tokens_entree' => (int) (clone $periode)->sum('tokens_entree'),
            'tokens_sortie' => (int) (clone $periode)->sum('tokens_sortie'),
        ];

        return view('admin.aide.index', [
            'stats' => $stats,
            'sansReponse' => AideQuestion::where('besoin_support', true)->latest()->limit(30)->get(),
            'pasUtiles' => AideQuestion::where('utile', false)->latest()->limit(30)->get(),
            'recentes' => AideQuestion::with('user')->latest()->paginate(30),
        ]);
    }
}