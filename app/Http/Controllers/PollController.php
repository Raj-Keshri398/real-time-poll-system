<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PollController extends Controller
{
    /* POLL LIST */
    public function index()
    {
        return view('polls.index');
    }

    /* LOAD POLL */
    public function show($id)
    {
        $poll = Poll::with('options')->findOrFail($id);

        return response()->json([
            'id' => $poll->id,
            'question' => $poll->question,
            'options' => $poll->options
        ]);
    }

    /* MODULE 2: VOTE */
    public function vote(Request $request)
    {
        $request->validate([
            'poll_id'   => 'required',
            'option_id' => 'required'
        ]);

        $ip = $request->ip();

        $alreadyVoted = Vote::where('poll_id', $request->poll_id)
            ->where('ip_address', $ip)
            ->where('released', false)
            ->exists();

        if ($alreadyVoted) {
            return response()->json([
                'status' => false,
                'message' => 'You have already voted from this IP.'
            ]);
        }

        Vote::create([
            'poll_id' => $request->poll_id,
            'poll_option_id' => $request->option_id,
            'ip_address' => $ip,
            'released' => false
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Vote submitted successfully.'
        ]);
    }

    /* MODULE 3: RESULTS */
    public function results($pollId)
    {
        $results = Vote::select(
                'poll_option_id',
                DB::raw('COUNT(*) as total')
            )
            ->where('poll_id', $pollId)
            ->where('released', false)
            ->groupBy('poll_option_id')
            ->get();

        return response()->json($results);
    }

    /* MODULE 4: IP LIST */
    public function ipList($pollId)
    {
        $ips = Vote::where('poll_id', $pollId)
            ->where('released', false)
            ->select('ip_address')
            ->distinct()
            ->get();

        return response()->json($ips);
    }

    /* MODULE 4: RELEASE IP */
    public function releaseIp(Request $request)
    {
        Vote::where('poll_id', $request->poll_id)
            ->where('ip_address', $request->ip)
            ->where('released', false)
            ->update(['released' => true]);

        return response()->json([
            'status' => true,
            'message' => 'IP released successfully'
        ]);
    }
}
