<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseResource;
use App\Http\Responses\Response;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function searchCourse()
    {
        $query = Course::query()->where('is_reviewd', 1);
        if (request('category_id')) {
            $query->where('category_id', request('category_id'));
        }
        if (request('search')) {
            $query->where('title', 'like', '%' . request('search') . '%')
                ->orWhere('description', 'like', '%' . request('search') . '%');
        }
        $searchResult = $query->get();
        return response()->json(['courses' => $searchResult]);
    }

    public function searchUser()
    {
        $query = User::query();
        if (request('search')) {
            $query
                ->where('name', 'like', '%' . request('search') . '%')
                ->orWhere('email', 'like', '%' . request('search') . '%');
        }
        $searchResult = $query->get();
        return response()->json(['users' => $searchResult]);
    }
}
