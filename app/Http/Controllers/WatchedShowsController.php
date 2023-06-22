<?php

namespace App\Http\Controllers;

use App\Models\WatchedShow;
use Illuminate\Http\Request;
use Tmdb\Laravel\Facades\Tmdb;

class WatchedShowsController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api');
    }

    public function all(Request $request) {
        $me = auth()->user();

        $all = WatchedShow::query()
            ->where('user_id', $me->id)
            ->get();

        $all = $all->groupBy('show_id');

        $shows = $all->map(function($watched, $key) use($me) {
            $show = Tmdb::getTvApi()->getTvshow($key);

            $seasons = collect($show['seasons'] ?? []);
            $episodeCount = $seasons->reduce(function($acc, $value, $key) {
                if($acc == null) {
                    return (int) $value['episode_count'];
                }
                return (int) $acc + (int) $value['episode_count'];
            });

            $watchedCount = WatchedShow::query()
                ->where('user_id', $me->id)
                ->where('show_id', $key)
                ->count();

            return array_merge($this->processShow($show), [
                'watched_episodes_count' => $watchedCount,
                'episode_count' => $episodeCount
            ]);
        })->filter();

        return response()->json([
            'error' => false,
            'tv_shows' => $shows->values(),
        ]);
    }

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
