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
            'data' => $this->prepareMovie($movie)
        ]);
    }

    /**
     * @param array $movie
     * @param array|Collection|null $genres
     * @return array
     */
    public function processMovie(array $movie, array | Collection $genres = null) {
        if(!$genres) {
            $genres = $this->getGenres();
        }

        $poster = $this->buildPosterUrl($movie['poster_path']);
        return array_merge($movie, [
            'poster' => $poster,
            'poster_small' => $poster,
            'poster_medium' => $poster,
            'poster_large' => $poster,
            'genres' => array_map(function($item) use($genres) {
                return $genres[(int) $item] ?? null;
            }, $movie['genre_ids']),
            'rating' => $movie['vote_average']
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
