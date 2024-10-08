<?php

namespace App\Http\Controllers;

use App\Http\Requests\WorkshopRequest;
use App\Http\Resources\WorkshopResource;
use App\Http\Responses\Response;
use App\Models\Workshop;
use App\Services\WorkShopServices\WorkShopService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkshopController extends Controller
{

    private WorkshopService $workshopService;

    public function __construct(WorkshopService $workshopService)
    {
        $this->workshopService = $workshopService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = $this->workshopService->index();
            return Response::success($data['message'], WorkshopResource::collection($data['workshops']));
        } catch (\Throwable $exception) {
            return Response::error($exception->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function createWorkshop(WorkshopRequest $request)
    {
        try {
            $data = $this->workshopService->createWorkshop($request);
            return Response::success($data['message'], WorkshopResource::make($data['workshop']));
        } catch (\Throwable $exception) {
            return Response::error($exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function showWorkshopDetails($workshop_id)
    {
        try {
            $data = $this->workshopService->showWorkshopDetails($workshop_id);
            return Response::success($data['message'], WorkshopResource::make($data['workshop']));
        } catch (\Throwable $exception) {
            return Response::error($exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $workshop_id)
    {
        try {
            $data = $this->workshopService->update($request, $workshop_id);
            return Response::success($data['message'], $data['workshop']);
        } catch (\Throwable $exception) {
            return Response::error($exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($workshop_id)
    {
        try {
            $data = $this->workshopService->createWorkshop($workshop_id);
            return Response::success($data['message']);
        } catch (\Throwable $exception) {
            return Response::error($exception->getMessage());
        }
    }

    public function workshopEnroll($workshop_id)
    {
        try {
            $data = $this->workshopService->workshopEnroll($workshop_id);
            return Response::success($data['message']);
        } catch (\Throwable $exception) {
            return Response::error($exception->getMessage());
        }
    }

    public function getStudentsByPoints($workshop_id)
    {
        try {
            $data = $this->workshopService->getStudentsByPoints($workshop_id);
            return Response::success($data['message'], $data['order']);
        } catch (\Throwable $exception) {
            return Response::error($exception->getMessage());
        }
    }
}
