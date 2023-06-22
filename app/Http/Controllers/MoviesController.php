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

        $backdrop = $this->buildBackdropUrl($movie['backdrop_path'] ?? '');
        $poster = $this->buildPosterUrl($movie['poster_path'] ?? '');

        return response()->json([
            'error' => false,
            'data' => array_merge(
                $movie,
                [
                    'poster' => $poster,
                    'poster_small' => $poster,
                    'poster_medium' => $poster,
                    'poster_large' => $poster,

                    'backdrop' => $backdrop,
                    'backdrop_small' => $backdrop,
                    'backdrop_medium' => $backdrop,
                    'backdrop_large' => $backdrop,

                    'rating' => $movie['vote_average']
                ]
            )
        ]);
    }

    public function buildBackdropUrl(?string $backdrop): string {
        return 'https://image.tmdb.org/t/p/original/' . ltrim($backdrop ?: '', '/');
    }

    public function buildPosterUrl(?string $poster): string {
        return 'https://image.tmdb.org/t/p/original/' . ltrim($poster ?: '', '/');
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
