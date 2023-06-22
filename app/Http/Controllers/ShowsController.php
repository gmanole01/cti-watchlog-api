<?php

namespace App\Http\Controllers;

use App\Models\WatchedShow;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Tmdb\Laravel\Facades\Tmdb;

class ShowsController extends Controller
{
    public function discover(Request $request) {
        $genres = $this->getGenres();

        $page = (int) $request->get('page', 1);
        $shows = Tmdb::getDiscoverApi()->discoverTv([
            'page' => $page
        ]);
        $shows = $shows['results'] ?: [];
        $shows = collect($shows)
            ->map(function($show) use($genres) {
                return $this->processShow($show, $genres);
            });

        return response()->json([
            'error' => false,
            'page' => $page,
            'tv_shows' => $shows
        ]);
    }

    public function get(int $id) {
        $me = auth()->user();
        $show = Tmdb::getTvApi()->getTvshow($id);

        $show = $this->processShow($show);

        $seasons = $show['seasons'] ?? [];
        $seasons = array_map(function($season) use($me, $id) {
            $episodeCount = (int) $season['episode_count'];
            $watchedCount = WatchedShow::query()
                ->where('user_id', $me->id)
                ->where('show_id', $id)
                ->where('season_number', $season['season_number'])
                ->count();

            return array_merge($season, [
                'is_watched' => $episodeCount == $watchedCount,
                'watched_episodes' => $watchedCount,
            ]);
        }, $seasons);

        $show = array_merge($show, [
            'seasons' => $seasons
        ]);

        return response()->json([
            'error' => false,
            'data' => $show
        ]);
    }

    public function getSeason(int $showId, int $seasonNumber) {
        $me = auth()->user();
        $season = Tmdb::getTvSeasonApi()->getSeason($showId, $seasonNumber);

        $episodes = collect($season['episodes'] ?? [])
            ->map(function($episode) use($me, $showId, $seasonNumber) {
                return array_merge($episode, [
                    'is_watched' => WatchedShow::query()
                        ->where('user_id', $me->id)
                        ->where('show_id', $showId)
                        ->where('season_number', $seasonNumber)
                        ->where('episode_number', $episode['episode_number'])
                        ->exists(),
                ]);
            });

        return response()->json([
            'error' => false,
            'episodes' => $episodes,
        ]);
    }

    /**
     * @return Collection
     */
    public function getGenres(): Collection {
        $genres = Tmdb::getGenresApi()->getTvGenres();
        if(!$genres) {
            return collect();
        }
        return collect($genres['genres'] ?? [])
            ->keyBy(function($item) {
                return (int) $item['id'];
            });
    }
}
