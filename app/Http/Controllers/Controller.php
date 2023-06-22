<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

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

    public function prepareMovie($data) {
        $backdrop = $this->buildBackdropUrl($data['backdrop_path'] ?? '');
        $poster = $this->buildPosterUrl($data['poster_path'] ?? '');
        return array_merge(
            $data,
            [
                'poster' => $poster,
                'poster_small' => $poster,
                'poster_medium' => $poster,
                'poster_large' => $poster,

                'backdrop' => $backdrop,
                'backdrop_small' => $backdrop,
                'backdrop_medium' => $backdrop,
                'backdrop_large' => $backdrop,

                'rating' => $data['vote_average']
            ]
        );
    }
}
