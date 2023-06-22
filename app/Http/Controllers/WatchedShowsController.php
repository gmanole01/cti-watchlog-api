<?php

namespace App\Http\Controllers;

use App\Models\WatchedShow;
use Illuminate\Http\Request;

class WatchedShowsController extends Controller
{

    public function isWatched(int $showId, int $seasonNumber, int $episodeNumber) {
        $me = auth()->user();
        return WatchedShow::query()
            ->where('user_id', $me->id)
            ->where('show_id', $showId)
            ->where('season_number', $seasonNumber)
            ->where('episode_number', $episodeNumber)
            ->exists();
    }

    public function markEpisodeWatched(int $showId, int $seasonNumber, int $episodeNumber) {
        $me = auth()->user();

        if($this->isWatched($showId, $seasonNumber, $episodeNumber)) {
            return response()->json([
                'error' => false
            ]);
        }

        $new = new WatchedShow();
        $new->user_id = $me->id;
        $new->show_id = $showId;
        $new->season_number = $seasonNumber;
        $new->episode_number = $episodeNumber;
        $new->save();

        return response()->json([
            'error' => false
        ]);
    }

    public function markEpisodeUnwatched(int $showId, int $seasonNumber, int $episodeNumber) {
        $me = auth()->user();
        if(!$this->isWatched($showId, $seasonNumber, $episodeNumber)) {
            return response()->json([
                'error' => false
            ]);
        }

        // delete
        WatchedShow::query()
            ->where('user_id', $me->id)
            ->where('show_id', $showId)
            ->where('season_number', $seasonNumber)
            ->where('episode_number', $episodeNumber)
            ->delete();

        return response()->json([
            'error' => false
        ]);
    }

}
