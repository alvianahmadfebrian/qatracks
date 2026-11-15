<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Project::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('description', 'like', '%'.$request->search.'%');
        }

        $projects = $query->with('creator')->withCount([
            'bugs',
            'bugs as open_bugs_count' => function ($q) use ($user) {
                $q->whereIn('status', ['new', 'open']);
                if ($user->role === 'developer') {
                    $q->where('developer', $user->name);
                }
            },
            'bugs as in_progress_bugs_count' => function ($q) use ($user) {
                $q->where('status', 'in_progress');
                if ($user->role === 'developer') {
                    $q->where('developer', $user->name);
                }
            },
            'bugs as resolved_bugs_count' => function ($q) use ($user) {
                $q->whereIn('status', ['done_by_development', 'done_by_support_qa', 'fixed', 'closed', 'resolved']);
                if ($user->role === 'developer') {
                    $q->where('developer', $user->name);
                }
            },
        ])->orderBy('name')->get();

        return view('projects.index', compact('projects'));
    }

    public function store(Request $request)
    {
        if ($request->user()->role === 'developer') {
            abort(403, 'Developers are not authorized to create projects.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['created_by'] = $request->user()->id;

        $project = Project::create($validated);

        return redirect()->route('projects.show', $project)->with('success', "Project folder '{$project->name}' created successfully.");
    }

    public function show(Request $request, Project $project)
    {
        $project->load('creator');
        $user = $request->user();
        $query = $project->bugs();

        if ($user->role === 'developer') {
            $query->where('developer', $user->name);
        }

        if ($request->filled('status')) {
            if ($request->status === 'new') {
                $query->whereIn('status', ['new', 'open']);
            } elseif ($request->status === 'done_by_development') {
                $query->whereIn('status', ['done_by_development', 'fixed', 'resolved']);
            } elseif ($request->status === 'done_by_support_qa') {
                $query->whereIn('status', ['done_by_support_qa', 'retest']);
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('developer') && $user->role !== 'developer') {
            $query->where('developer', $request->developer);
        }

        $bugs = $query->orderByRaw("CASE priority WHEN 'p1' THEN 1 WHEN 'p2' THEN 2 WHEN 'p3' THEN 3 ELSE 4 END ASC")
            ->orderBy('created_at', 'desc')
            ->get();
        $developers = User::where('role', 'developer')->orderBy('name')->get();

        return view('projects.show', compact('project', 'bugs', 'developers'));
    }

    public function destroy(Request $request, Project $project)
    {
        if ($request->user()->role !== 'super_admin' && $project->created_by !== $request->user()->id) {
            abort(403, 'Unauthorized.');
        }

        $projectName = $project->name;
        $project->delete();

        return redirect()->route('projects.index')->with('success', "Project folder '{$projectName}' deleted successfully.");
    }
}
