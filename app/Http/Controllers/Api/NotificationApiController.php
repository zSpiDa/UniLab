<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Task;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Publication;
use Illuminate\Http\Request;

class NotificationApiController extends Controller
{
    /**
     * GET /api/v1/notifications
     * Ritorna tutte le notifiche dell'utente loggato
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(1, min($perPage, 100));
        
        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);

        $mapped = collect($notifications->items())->map(function (Notification $notification) {
            $item = $notification->toArray();
            $item['target_url'] = $this->targetUrl($notification);
            return $item;
        })->values();

        return response()->json([
            'data' => $mapped,
            'unread_count' => Notification::where('user_id', $user->id)->unread()->count(),
            'pagination' => [
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
            ]
        ]);
    }

    /**
     * GET /api/v1/notifications/unread
     * Solo notifiche non lette
     */
    public function unread(Request $request)
    {
        $user = $request->user();
        
        $notifications = Notification::where('user_id', $user->id)
            ->unread()
            ->latest()
            ->get();

        $mapped = $notifications->map(function (Notification $notification) {
            $item = $notification->toArray();
            $item['target_url'] = $this->targetUrl($notification);
            return $item;
        })->values();

        return response()->json([
            'data' => $mapped,
            'count' => $notifications->count(),
        ]);
    }

    /**
     * PATCH /api/v1/notifications/{notification}/read
     * Marca una notifica come letta
     */
    public function markAsRead(Request $request, Notification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read',
            'notification' => $notification,
        ]);
    }

    /**
     * DELETE /api/v1/notifications/{notification}
     * Elimina una notifica
     */
    public function destroy(Request $request, Notification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json(['message' => 'Notification deleted']);
    }

    /**
     * PATCH /api/v1/notifications/read-all
     * Marca tutte le notifiche come lette
     */
    public function markAllAsRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    private function targetUrl(Notification $notification): ?string
    {
        if ($notification->notifiable_type === Task::class && $notification->notifiable_id) {
            return route('tasks.show', $notification->notifiable_id);
        }

        if ($notification->notifiable_type === Milestone::class && $notification->notifiable_id) {
            $milestone = Milestone::find($notification->notifiable_id);
            if ($milestone) {
                return route('projects.show', $milestone->project_id) . '#milestone-' . $milestone->id;
            }
        }

        if ($notification->notifiable_type === Project::class && $notification->notifiable_id) {
            return route('projects.show', $notification->notifiable_id);
        }

        if ($notification->notifiable_type === Publication::class && $notification->notifiable_id) {
            return route('publications.show', $notification->notifiable_id);
        }

        return null;
    }
}