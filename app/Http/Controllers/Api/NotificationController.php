<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * List authenticated user's notifications.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $limit = max(1, min(50, (int) $request->get('limit', 20)));

        $notifications = Notification::where('for', 'user')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($limit);

        return response()->json([
            'status' => true,
            'data' => $notifications->map(fn ($notification) => [
                'id' => $notification->id,
                'title' => (string) $notification->title,
                'icon' => (string) ($notification->icon ?? 'bell'),
                'action_url' => $notification->action_url,
                'read' => (bool) $notification->read,
                'created_at' => (string) ($notification->created_at ?? ''),
                'time_ago' => $notification->created_at ? $notification->created_at->diffForHumans() : '',
            ]),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'unread' => Notification::where('for', 'user')
                    ->where('user_id', $user->id)
                    ->where('read', 0)
                    ->count(),
            ],
        ]);
    }

    /**
     * Mark one notification as read.
     */
    public function read(Request $request, int $id)
    {
        $notification = Notification::where('for', 'user')
            ->where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        if (! $notification->read) {
            $notification->read = 1;
            $notification->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'Notification marked as read.',
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function readAll(Request $request)
    {
        Notification::where('for', 'user')
            ->where('user_id', $request->user()->id)
            ->where('read', 0)
            ->update(['read' => 1]);

        return response()->json([
            'status' => true,
            'message' => 'All notifications marked as read.',
        ]);
    }
}

