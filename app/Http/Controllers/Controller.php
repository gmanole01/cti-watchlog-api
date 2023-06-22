<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function unexpectedError() {
        return response()->json([
            'error' => true,
            'error_msg' => 'Unexpected error!'
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
    public function processMovie(array $data, array | Collection $genres = null) {
        $poster = $this->buildPosterUrl($data['poster_path']);
        $backdrop = $this->buildBackdropUrl($data['backdrop_path']);

        return array_merge($data, [
            'poster' => $poster,
            'poster_small' => $poster,
            'poster_medium' => $poster,
            'poster_large' => $poster,

            'backdrop' => $backdrop,
            'backdrop_small' => $backdrop,
            'backdrop_medium' => $backdrop,
            'backdrop_large' => $backdrop,

            'rating' => $data['vote_average'],

            'genres' => $genres ? array_map(function($item) use($genres) {
                return $genres[(int) $item] ?? null;
            }, $data['genre_ids']) : [],
        ]);
    }

    public function processShow($data, $genres = null) {
        $poster = $this->buildPosterUrl($data['poster_path']);
        $backdrop = $this->buildBackdropUrl($data['backdrop_path']);

        return array_merge($data, [
            'poster' => $poster,
            'poster_small' => $poster,
            'poster_medium' => $poster,
            'poster_large' => $poster,

            'backdrop' => $backdrop,
            'backdrop_small' => $backdrop,
            'backdrop_medium' => $backdrop,
            'backdrop_large' => $backdrop,

            'rating' => $data['vote_average'],

            'air_date' => $data['air_date'] ?? ($data['first_air_date'] ?? ''),

            'genres' => $genres ? array_map(function($item) use($genres) {
                return $genres[(int) $item] ?? null;
            }, $data['genre_ids']) : [],
        ]);
    }
}
