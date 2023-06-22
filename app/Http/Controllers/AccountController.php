<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
{

    public function __construct() {
        $this->middleware('auth:api');
    }

    function base64_to_jpeg( $base64_string, $output_file ) {
        $ifp = fopen( $output_file, "wb" );
        fwrite( $ifp, base64_decode( $base64_string) );
        fclose( $ifp );
        return( $output_file );
    }

    public function newProfilePicture(Request $request) {
        $me = auth()->user();
        $picture = $request->get('profile_picture');
        $extension = $request->get('extension');

        if(!$picture || !$extension) {
            return $this->unexpectedError();
        }

        $filename = $me->id . '.' . $extension;
        $output = storage_path('app/public/' . $filename);
        $this->base64_to_jpeg($picture, $output);

        $url = asset('storage/' . $filename);

        $me->profile_picture = $url;
        $me->save();

        return response()->json([
            'error' => false,
            'data' => [
                'url' => $url
            ],
        ]);
    }

}
