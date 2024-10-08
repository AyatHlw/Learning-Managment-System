<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCategoryRequest;
use App\Http\Requests\CreateCourseRequest;
use App\Http\Requests\CreateCourseYoutubeRequest;
use App\Http\Requests\CreateQuizRequest;
use App\Http\Resources\CourseResource;
use App\Http\Resources\QuizResource;
use App\Http\Resources\TeacherResource;
use App\Http\Responses\Response;
use App\Mail\CourseRejectedMail;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\User;
use App\Models\Video;
use App\Services\Course\CourseService;
use App\Services\QuizService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class CourseController extends Controller
{
    private CourseService $courseService;
    private QuizService $quizService;

    public function __construct(CourseService $courseService, QuizService $quizService)
    {
        $this->courseService = $courseService;
        $this->quizService = $quizService;
    }

    /**
     * Display a listing of the resource.
     */
    public function list()
    {
        try {
            $courses = Course::query()->where('is_reviewed', 1)->get();
            return Response::success(__('messages.courses_retrieved'), CourseResource::collection($courses));
        } catch (\Throwable $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function getTopCourses()
    {
        try {
            $data = $this->courseService->getTopCourses();
            return Response::success($data['message'], CourseResource::collection($data['courses']));
        } catch (\Throwable $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function getTeacherCourses($teacher_id)
    {
        try {
            $data = $this->courseService->getTeacherCourses($teacher_id);
            return response()->json([
                'message' => $data['message'],
                'courses' => CourseResource::collection($data['courses'])
            ], 200);
        } catch (\Throwable $exception) {
            return Response::error($exception->getMessage(), 200);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function createCourse(CreateCourseRequest $request)
    {
        try {
            $data = $this->courseService->createCourse($request);
            return response()->json([
                'message' => $data['message'],
                'course' => CourseResource::make($data['course'])
            ], 201);
        } catch (Throwable $throwable) {
            return Response::error($throwable->getMessage());
        }
    }

    public function createCourseWithYouTubeLinks(CreateCourseYoutubeRequest $request): JsonResponse
    {
        try {
            $data = $this->courseService->createCourseWithYouTubeLinks($request);
            return response()->json([
                'message' => $data['message'],
                'course' => $data['course']
            ], 201);
        } catch (Throwable $throwable) {
            return Response::error($throwable->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function showCourseDetails($course_id)
    {
        try {
            $course = $this->courseService->showCourseDetails($course_id);
            return Response::success(__('messages.course_details'), CourseResource::make($course));
        } catch (Throwable $throwable) {
            return Response::error($throwable->getMessage(), 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $course_id)
    {
        try {
            $data = $this->courseService->update($request, $course_id);
            return Response::success($data['message'], CourseResource::make($data['course']));
        } catch (\Throwable $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($course_id)
    {
        try {
            $data = $this->courseService->destroy($course_id);
            return Response::success($data['message']);
        } catch (\Throwable $exception) {
            return Response::error($exception->getMessage(), 422);
        }
    }

    public function getAllCoursesForAdmin(): JsonResponse
    {
        try {
            $courses = $this->courseService->getAllCoursesForAdmin();
            return response()->json([
                'message' => __('messages.courses_retrieved'),
                'courses' => CourseResource::collection($courses)
            ], 200);
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    public function approveCourse($courseId)
    {
        try {
            $course = $this->courseService->approveCourse($courseId);
            return response()->json([
                'message' => __('messages.course_approved'),
                'course' => $course
            ], 200);
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    public function rejectCourse($courseId)
    {
        try {
            $this->courseService->rejectCourse($courseId);
            return response()->json([
                'message' => __('messages.course_approved'),
            ], 200);
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    public function createQuiz(CreateQuizRequest $request)
    {
        try {
            $data = $this->quizService->createQuiz($request);
            return response()->json([
                'message' => $data['message'],
                'quiz' => $data['quiz']
            ], 201);
        } catch (\Throwable $throwable) {
            return Response::error($throwable->getMessage());
        }
    }

    public function showCourseQuiz($course_id){
        try {
            $data = $this->quizService->showCourseQuiz($course_id);
            return Response::success($data['message'], $data['quiz']);
        } catch (Throwable $exception){
            return Response::error($exception->getMessage());
        }
    }

    public function showQuizForTeachers($quizId)
    {
        try {
            $quiz = $this->quizService->showQuizForTeachers($quizId);
            return response()->json([
                'message' => __('messages.quiz_retrieved'),
                'quiz' => new QuizResource($quiz)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e instanceof ModelNotFoundException ? 404 : 500);
        }
    }

    public function showQuizForStudents($quizId)
    {
        try {
            $quiz = $this->quizService->showQuizForStudents($quizId);
            return response()->json([
                'message' => __('messages.quiz_retrieved_for_student'),
                'quiz' => new QuizResource($quiz)
            ], 200);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 404);
        }
    }

    public function checkAnswers(Request $request)
    {
        $validated = $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'answers' => 'required|array',
            'answers.*' => 'required|exists:answers,id',
        ]);

        $student = User::find(Auth::id());

        if (!$student) {
            return response()->json([
                'message' => __('messages.user_not_found')
            ], 404);
        }

        if ($student->hasPassedQuiz($request->input('quiz_id'))) {
            return response()->json([
                'message' => __('messages.already_passed')
            ], 400);
        }

        try {
            $data = $this->quizService->checkAnswers($request);
            return response()->json([
                'message' => $data['message'],
                'correct_answers' => $data['correct_answers'],
                'total_questions' => $data['total_questions'],
                'passed' => $data['passed']
            ], 200);
        } catch (\Throwable $throwable) {
            return Response::error($throwable->getMessage());
        }
    }

    public function updateQuiz(Request $request, $quizId)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'questions' => 'sometimes|array',
            'questions.*.id' => 'sometimes|integer|exists:questions,id',
            'questions.*.question_text' => 'required_with:questions|string',
            'questions.*.answers' => 'sometimes|array',
            'questions.*.answers.*.id' => 'sometimes|integer|exists:answers,id',
            'questions.*.answers.*.answer_text' => 'required_with:questions.*.answers|string',
            'questions.*.answers.*.is_correct' => 'sometimes|boolean'
        ]);
        try {
            $quiz = $this->quizService->updateQuiz($quizId, $validated);
            return response()->json([
                'message' => __('messages.quiz_updated'),
                'quiz' => $quiz
            ], 200);
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    public function deleteQuestion($questionId)
    {
        try {
            $result = $this->quizService->deleteQuestion($questionId);
            return response()->json($result, 200);
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    public function getStudentCourses()
    {
        try {
            $data = $this->courseService->getStudentCourses();
            return Response::success($data['message'], $data['courses']);
        } catch (Throwable $exception) {
            return Response::error($exception->getMessage());
        }
    }

    public function showAllVideos($course_id)
    {
        $user = Auth::user();

        if (!$user->enrolledCourses()->where('course_id', $course_id)->exists()) {
            return response()->json(['message' => 'You are not enrolled in this course.'], 403);
        }

        $videos = Video::where('course_id', $course_id)->get();

        return response()->json(['videos' => $videos]);
    }

    public function showOneVideo($course_id, $video_id)
    {
        $user = Auth::user();

        if (!$user->enrolledCourses()->where('course_id', $course_id)->exists()) {
            return response()->json(['message' => 'You are not enrolled in this course.'], 403);
        }

        $video = Video::where('course_id', $course_id)->where('id', $video_id)->firstOrFail();

        return response()->json(['video' => $video]);
    }
}
