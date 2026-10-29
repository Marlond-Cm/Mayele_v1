<?php

namespace App\Http\Controllers;

use App\Models\Nouveaute;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class NouveautesController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $depuis = $user->nouveautes_vues_at ? Carbon::parse($user->nouveautes_vues_at) : $user->created_at;

        $liste = Nouveaute::visiblesPour($user)->latest()->limit(60)->get();

        foreach ($liste as $nouveaute) {
            $nouveaute->nouveau = $nouveaute->created_at->gt($depuis);
        }

        $user->forceFill(['nouveautes_vues_at' => now()])->save();

        return view('nouveautes.index', ['liste' => $liste]);
    }
}