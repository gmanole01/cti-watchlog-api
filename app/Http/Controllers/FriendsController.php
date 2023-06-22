<?php

namespace App\Http\Controllers;

use App\Models\Friend;
use App\Models\FriendRequest;
use App\Models\User;
use App\Notifications\FriendRequestAccepted;
use DateTime;
use Illuminate\Http\Request;

class FriendsController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api');
    }

    public function all() {
        // Get friends.
        $user = auth()->user();
        $friends = Friend::query()
            ->where('user_id', $user->id)
            ->get();
        return response()->json([
            'error' => false,
            'friends' => $friends->map(function($friend) {
                /** @var DateTime $createdAt */
                $createdAt = $friend->created_at;
                return [
                    'id' => $friend->id,
                    'timestamp' => $createdAt->getTimestamp(),
                    'user_data' => User::find($friend->friend_id)
                ];
            })
        ]);
    }

    public function requests() {
        // Get friends.
        $user = auth()->user();
        $requests = FriendRequest::query()
            ->where('receiver', $user->id)
            ->get();
        return response()->json([
            'error' => false,
            'friend_requests' => $requests->map(function($request) {
                /** @var DateTime $createdAt */
                $createdAt = $request->created_at;
                return [
                    'id' => $request->id,
                    'timestamp' => $createdAt->getTimestamp(),
                    'user_data' => User::find($request->sender)
                ];
            })
        ]);
    }

    public function acceptRequest(Request $request) {
        $id = $request->get('id');
        if(!$id) {
            return response()->json([
                'error' => true,
                'error_msg' => 'No ID provided!'
            ]);
        }

        /** @var FriendRequest $friendRequest */
        $friendRequest = FriendRequest::find($id);
        if(!$friendRequest) {
            return response()->json([
                'error' => true,
                'error_msg' => 'No friend request found!'
            ]);
        }

        $me = auth()->user();
        if($friendRequest->receiver != $me->id) {
            return response()->json([
                'error' => true,
                'error_msg' => 'Unexpected error!'
            ]);
        }

        $alreadyFriend = Friend::query()
            ->where('user_id', $me->id)
            ->where('friend_id', $friendRequest->sender)
            ->exists();
        if($alreadyFriend) {
            return response()->json([
                'error' => true,
                'error_msg' => 'Already friend!'
            ]);
        }

        // Remove friend request.
//        $friendRequest->delete();
//
//        Friend::query()->create([
//            'user_id' => $me->id,
//            'friend_id', $friendRequest->sender
//        ]);
//        Friend::query()->create([
//            'user_id' => $friendRequest->sender,
//            'friend_id', $me->id
//        ]);

        /** @var User $sender */
        $sender = User::find($friendRequest->sender);
        $receiver = User::find($friendRequest->receiver);

        // The receiver accepts the friend requests, so the sender receives a notification
        //   that says the receiver accepted.
        $sender->notify(new FriendRequestAccepted($receiver));

        return response()->json([
            'error' => false
        ]);
    }

    public function refuseRequest(Request $request) {
        $id = $request->get('id');
        if(!$id) {
            return response()->json([
                'error' => true,
                'error_msg' => 'No ID provided!'
            ]);
        }

        /** @var FriendRequest $friendRequest */
        $friendRequest = FriendRequest::find($id);
        if(!$friendRequest) {
            return response()->json([
                'error' => true,
                'error_msg' => 'No friend request found!'
            ]);
        }

        $me = auth()->user();
        if($friendRequest->receiver != $me->id) {
            return response()->json([
                'error' => true,
                'error_msg' => 'Unexpected error!'
            ]);
        }

        // Remove friend request.
        $friendRequest->delete();

        return response()->json([
            'error' => false
        ]);
    }

    public function add(Request $request) {
        $username = $request->get('username');
        if(!$username) {
            return response()->json([
                'error' => true,
                'error_msg' => 'Unexpected error!'
            ]);
        }

        $me = auth()->user();
        $user = User::query()->where('username', $username)->first();
        if(!$user) {
            return response()->json([
                'error' => true,
                'error_msg' => 'No user found matching this username!'
            ]);
        }

        $alreadyFriend = Friend::query()
            ->where('user_id', $me->id)
            ->where('friend_id', $user->id)
            ->exists();

        if($alreadyFriend) {
            return response()->json([
                'error' => true,
                'error_msg' => 'Already friend!'
            ]);
        }

        $alreadySent = FriendRequest::query()
            ->where('sender', $me->id)
            ->where('receiver', $user->id)
            ->exists();

        if($alreadySent) {
            return response()->json([
                'error' => true,
                'error_msg' => 'Friend request already sent!'
            ]);
        }

        // Send request.
        $newRequest = new FriendRequest();
        $newRequest->sender = $me->id;
        $newRequest->receiver = $user->id;
        $newRequest->save();

        return response()->json([
            'error' => false
        ]);
    }

}
