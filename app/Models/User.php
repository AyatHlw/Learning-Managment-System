<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use http\Env\Response;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'image',
        'password',
        'google_id',
        'verification_code',
        'email_verified_at',
        'device_token'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function courses()
    {
        return $this->hasMany(Course::class, 'creator_id')->where('is_reviewed', true);
    }

    public function comments()
    {
        return $this->hasMany(CourseComment::class, 'comment_id');
    }

    public function quizzes(){

    }

    public function quizResults()
    {
        return $this->hasMany(QuizResult::class, 'student_id');
    }

    public function hasPassedQuiz($quizId)
    {
        return $this->quizResults()->where('quiz_id', $quizId)->where('passed', true)->exists();
    }

    public function isPremium()
    {
        return $this->hasOne(PremiumUsers::class, 'user_id')->exists();
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'following_id');
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'following_id', 'follower_id');
    }

    public function workshops()
    {
        return $this->hasMany(Workshop::class, 'teacher_id');
    }

    public function favoritesList()
    {
        return $this->belongsToMany(Course::class, 'favorites')->withTimestamps();
    }

    public function hasFavorite($course_id)
    {
        return self::favoritesList()->where('course_id', $course_id)->exists();
    }

    public function watchLaterList()
    {
        return $this->belongsToMany(Video::class, 'watch_later_lists')->withTimestamps();
    }

    public function hasInWatchLater($video_id)
    {
        return self::watchLaterList()->where('video_id', $video_id)->exists();
    }

    public function enrolledCourses()
    {
        return $this->belongsToMany(Course::class, 'course_enrolls')
            ->withPivot('student_mark', 'is_passed')
            ->withTimestamps();
    }
}
