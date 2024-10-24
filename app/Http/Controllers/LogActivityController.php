<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Session;
use App\Models\LogActivity;
use Illuminate\Http\Request;
use Laravel\Jetstream\Agent;
use Illuminate\Support\Facades\Auth;

class LogActivityController extends Controller
{
    public function index()
    {
        $activities = LogActivity::orderBy('created_at', 'desc')->paginate(10);

        // Get active sessions and filter out logged-out users
        $activeSessions = Session::where('last_activity', '>=', now()->subMinutes(5)->timestamp)
            ->where('user_id', '<>', null) // Ensure user_id is present
            ->get(); // Adjust time as needed

        // Initialize an array to store active sessions with agent details
        $activeSessionsWithAgent = [];

        foreach ($activeSessions as $session) {
            // Get user details
            $user = User::find($session->user_id);

            // Check if the user is authenticated and matches the user_id from the session
            if ($user && $session->user_id === Auth::id()) {
                $agent = new Agent();
                $agent->setUserAgent($session->user_agent);

                $activeSessionsWithAgent[] = [
                    'user' => $user->name,
                    'ip_address' => $session->ip_address,
                    'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                    'platform' => $agent->platform(),
                    'browser' => $agent->browser(),
                ];
            }
        }

        return view('pages.log_activities.index', compact('activities', 'activeSessionsWithAgent'));
    }
}
