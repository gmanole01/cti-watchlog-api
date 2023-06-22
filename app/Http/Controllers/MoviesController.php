<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Tmdb\Laravel\Facades\Tmdb;

class MoviesController extends Controller
{

    public function discover(Request $request) {
        $genres = $this->getGenres();

        $page = (int) $request->get('page', 1);
        $movies = Tmdb::getDiscoverApi()->discoverMovies([
            'page' => $page
        ]);
        $movies = $movies['results'] ?: [];
        $movies = collect($movies)
            ->map(function($movie) use($genres) {
                return $this->processMovie($movie, $genres);
            });

        return response()->json([
            'error' => false,
            'page' => $page,
            'movies' => $movies
        ]);
    }

    public function topRated(Request $request) {
        $genres = $this->getGenres();

        $page = (int) $request->get('page', 1);
        $movies = Tmdb::getMoviesApi()->getTopRated([
            'page' => $page
        ]);
        $movies = $movies['results'] ?: [];
        $movies = collect($movies)
            ->map(function($movie) use($genres) {
                return $this->processMovie($movie, $genres);
            });

        return response()->json([
            'error' => false,
            'page' => $page,
            'movies' => $movies
        ]);
    }

    public function genres() {
        return response()->json([
            'error' => false,
            'genres' => $this->getGenres()->values()
        ]);
    }

    public function get(int $id) {
        $movie = Tmdb::getMoviesApi()->getMovie($id);

        return response()->json([
            'error' => false,
            'data' => $this->processMovie($movie)
        ]);
    }

    /**
     * @return Collection
     */
    public function getGenres(): Collection {
        $genres = Tmdb::getGenresApi()->getMovieGenres();
        if(!$genres) {
            return collect();
        }
        return collect($genres['genres'] ?? [])
            ->keyBy(function($item) {
                return (int) $item['id'];
            });
    }

}
