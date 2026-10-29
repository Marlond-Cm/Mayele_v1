<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CoachConversation;
use App\Models\CoachMessage;
use Illuminate\View\View;

class CoachController extends Controller
{
    public function __invoke(): View
    {
        $depuis30 = now()->subDays(30);

        $stats = [
            'aujourdhui' => CoachMessage::where('role', 'user')->where('created_at', '>=', now()->startOfDay())->count(),
            'sept_jours' => CoachMessage::where('role', 'user')->where('created_at', '>=', now()->subDays(7))->count(),
            'trente_jours' => CoachMessage::where('role', 'user')->where('created_at', '>=', $depuis30)->count(),
            'tokens_entree' => (int) CoachMessage::where('created_at', '>=', $depuis30)->sum('tokens_entree'),
            'tokens_sortie' => (int) CoachMessage::where('created_at', '>=', $depuis30)->sum('tokens_sortie'),
            'conversations' => CoachConversation::where('created_at', '>=', $depuis30)->count(),
        ];

        $top = CoachMessage::query()
            ->join('coach_conversations as c', 'c.id', '=', 'coach_messages.conversation_id')
            ->join('users as u', 'u.id', '=', 'c.user_id')
            ->where('coach_messages.role', 'user')
            ->where('coach_messages.created_at', '>=', now()->subDays(7))
            ->groupBy('u.id', 'u.name', 'u.email')
            ->selectRaw('u.id, u.name, u.email, COUNT(*) as total')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return view('admin.coach.index', [
            'stats' => $stats,
            'top' => $top,
            'modele' => config('mayele.coach.modele'),
            'limite' => (int) config('mayele.coach.messages_par_jour'),
        ]);
    }
}