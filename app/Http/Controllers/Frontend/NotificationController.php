<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $filter = $request->string('filter', 'all')->toString();

        $notifications = $user->notifications()
            ->when($filter === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('frontend.notifications.index', [
            'notifications' => $notifications,
            'filter' => $filter,
            'unread' => $user->unreadNotifications()->count(),
        ]);
    }

    public function read(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()->notifications()->findOrFail($notification);
        $item->markAsRead();

        $url = $item->data['url'] ?? null;

        return $url
            ? redirect($url)
            : redirect()->route('notifications.index')->with('success', 'Notification marked as read.');
    }

    public function markAllRead(Request $request): RedirectResponse|JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok', 'unread' => 0]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    public function destroy(Request $request, string $notification): RedirectResponse
    {
        $request->user()->notifications()->findOrFail($notification)->delete();

        return back()->with('success', 'Notification removed.');
    }

    public function destroyAll(Request $request): RedirectResponse
    {
        $request->user()->notifications()->delete();

        return back()->with('success', 'Notification inbox cleared.');
    }

    /**
     * Lightweight poll endpoint for the navbar badge.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'unread' => $user->unreadNotifications()->count(),
        ]);
    }
}
