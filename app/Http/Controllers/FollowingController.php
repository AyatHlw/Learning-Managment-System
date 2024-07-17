<?php

namespace App\Http\Controllers;

use App\Http\Responses\Response;
use App\Models\Follower;
use App\Models\User;
use App\Services\FollowingService;
use Egulias\EmailValidator\Result\Reason\AtextAfterCFWS;
use FontLib\OpenType\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowingController extends Controller
{
    private FollowingService $followingService;

    public function __construct(FollowingService $followingService)
    {
        $this->followingService = $followingService;
    }

    public function follow(Request $request)
    {
        try {
            $data = $this->followingService->follow($request);
            return Response::success($data['message']);
        } catch (\Throwable $exception) {
            return Response::error($exception->getMessage());
        }
    }

    public function followers($teacher_id)
    {
        try {
            $data = $this->followingService->followers($teacher_id);
            return Response::success($data['message'], $data['followers']);
        } catch (\Throwable $exception) {
            return Response::error($exception->getMessage());
        }
    }

    public function following($student_id)
    {
        try {
            $data = $this->followingService->following($student_id);
            return Response::success($data['message'], $data['following']);
        } catch (\Throwable $exception) {
            return Response::error($exception->getMessage());
        }
    }

    public function unFollow($teacher_id)
    {
        try {
            $data = $this->followingService->unFollow($teacher_id);
            return Response::success($data['message']);
        } catch (\Throwable $exception) {
            return Response::error($exception->getMessage());
        }
    }
}
