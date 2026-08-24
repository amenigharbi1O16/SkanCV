<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * NotificationController
 *
 * MISSION : expose au frontend React les notifications stockées en base pour
 * le HR actuellement authentifié. Ne contient aucune logique métier —
 * il ne fait que lire ce que le trait Notifiable (sur User) lui donne déjà.
 *
 * RELATION : appelé par le frontend React (ex: une cloche 🔔 qui poll cet
 * endpoint), lit les notifications insérées par CvAnalysisStatusNotification::toArray()
 * depuis CvController (pending) et ProcessCvAnalysis (processing/completed/failed).
 */
class NotificationController extends Controller
{
    /**
     * Retourne toutes les notifications du HR connecté, triées récentes d'abord.
     */
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->notifications()->latest()->get()
        );
    }

    /**
     * Retourne uniquement les notifications non lues — utile pour le badge
     * de compteur affiché sur la cloche dans l'interface.
     */
    public function unread(Request $request)
    {
        return response()->json(
            $request->user()->unreadNotifications()->latest()->get()
        );
    }

    /**
     * Marque une notification précise comme lue quand le HR clique dessus.
     */
    public function markAsRead(Request $request, string $notificationId)
    {
        $notification = $request->user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        return response()->json(['status' => 'marked_as_read']);
    }
}
