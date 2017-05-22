<?php

namespace App\Http\Controllers;

use App\Category;
use App\Comment;
use App\Filters;
use App\Message;
use App\Report;
use App\Submission;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    use Filters;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Loads the dashboard page for administrator page with the latest statistics.
     *
     * @return Illuminate\Support\Collection
     */
    public function index()
    {
        abort_unless($this->mustBeVotenAdministrator(), 403);

        $usersTotal = User::all()->count();
        $usersToday = User::where('created_at', '>=', Carbon::now()->subDay())->count();

        $categoriesTotal = Category::all()->count();
        $categoriesToday = Category::where('created_at', '>=', Carbon::now()->subDay())->count();

        $submissionsTotal = Submission::all()->count();
        $submissionsToday = Submission::where('created_at', '>=', Carbon::now()->subDay())->count();

        $commentsTotal = Comment::all()->count();
        $commentsToday = Comment::where('created_at', '>=', Carbon::now()->subDay())->count();

        $messagesTotal = Message::all()->count();
        $messagesToday = Message::where('created_at', '>=', Carbon::now()->subDay())->count();

        $reportsTotal = Report::all()->count();
        $reportsToday = Report::where('created_at', '>=', Carbon::now()->subDay())->count();

        $submissionVotesTotal = 0;
        $submissionVotesToday = 0;

        return collect([
            'usersTotal'           => $usersTotal,
            'usersToday'           => $usersToday,
            'categoriesTotal'      => $categoriesTotal,
            'categoriesToday'      => $categoriesToday,
            'submissionsTotal'     => $submissionsTotal,
            'submissionsToday'     => $submissionsToday,
            'commentsTotal'        => $commentsTotal,
            'commentsToday'        => $commentsToday,
            'messagesTotal'        => $messagesTotal,
            'messagesToday'        => $messagesToday,
            'submissionVotesTotal' => $submissionVotesTotal,
            'submissionVotesToday' => $submissionVotesToday,
            'reportsTotal'         => $reportsTotal,
            'reportsToday'         => $reportsToday,
        ])->all();
    }

    /**
     * Returns the latest submissions.
     *
     * @return Illuminate\Support\Collection
     */
    public function submissions(Request $request)
    {
        abort_unless($this->mustBeVotenAdministrator(), 403);

        return Submission::orderBy('id', 'desc')->simplePaginate(10);
    }

    /**
     * Returns the latest submitted comments.
     *
     * @return Illuminate\Support\Collection
     */
    public function comments()
    {
        abort_unless($this->mustBeVotenAdministrator(), 403);

        return $this->withoutChildren(Comment::orderBy('id', 'desc')->simplePaginate(30));
    }

    /**
     * Returns the latest created categories.
     *
     * @return Illuminate\Support\Collection
     */
    public function categories()
    {
        abort_unless($this->mustBeVotenAdministrator(), 403);

        return Category::orderBy('id', 'desc')->simplePaginate(30);
    }

    /**
     * Returns the latest registered users.
     *
     * @return Illuminate\Support\Collection
     */
    public function indexUsers()
    {
        abort_unless($this->mustBeVotenAdministrator(), 403);

        return User::orderBy('id', 'desc')->simplePaginate(30);
    }

    /**
     * searches through users by username.
     *
     * @return Illuminate\Support\Collection
     */
    public function searchUsers(Request $request)
    {
        abort_unless($this->mustBeVotenAdministrator(), 403);

        return User::where('username', 'like', '%'.$request->username.'%')
                    ->select('username')->take(100)->get()->pluck('username');
    }

    /**
     * Indexes the reported submissions.
     *
     * @param Illuminate\Http\Request $request
     *
     * @return Illuminate\Support\Collection
     */
    public function reportedSubmissions(Request $request)
    {
        $this->validate($request, [
            'type' => 'required',
        ]);

        abort_unless($this->mustBeVotenAdministrator(), 403);

        if ($request->type == 'solved') {
            return Report::onlyTrashed()->where([
                'reportable_type' => 'App\Submission',
            ])->with('reporter', 'submission')->orderBy('created_at', 'desc')->simplePaginate(50);
        }

        // default type which is "unsolved"
        return Report::where([
            'reportable_type' => 'App\Submission',
        ])->with('reporter', 'submission')->orderBy('created_at', 'desc')->simplePaginate(50);
    }

    /**
     * Indexes the reported comments.
     *
     * @param Illuminate\Http\Request $request
     *
     * @return Illuminate\Support\Collection
     */
    public function reportedComments(Request $request)
    {
        $this->validate($request, [
            'type' => 'required',
        ]);

        abort_unless($this->mustBeVotenAdministrator(), 403);

        if ($request->type == 'solved') {
            return Report::onlyTrashed()->where([
                'reportable_type' => 'App\Comment',
            ])->with('reporter', 'comment')->orderBy('created_at', 'desc')->simplePaginate(50);
        }

        // default type which is "unsolved"
        return Report::where([
            'reportable_type' => 'App\Comment',
        ])->with('reporter', 'comment')->orderBy('created_at', 'desc')->simplePaginate(50);
    }

    /**
     * searches the categories.
     *
     * @param Illuminate\Http\Request $request
     *
     * @return Illuminate\Support\Collection
     */
    public function getCategories(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        abort_unless($this->mustBeVotenAdministrator(), 403);

        return Category::where('name', 'like', '%'.$request->name.'%')
                    ->select('id', 'name')->take(100)->get()->pluck('name');
    }
}
