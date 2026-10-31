<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Department::class);

        $filters = $request->only('search');

        $departments = Department::withCount('teachers')
            ->filter($filters)
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('departments.index', compact('departments', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        Gate::authorize('create', Department::class);

        return view('departments.create', ['department' => new Department]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DepartmentRequest $request): RedirectResponse
    {
        $department = Department::create($request->validated());

        return redirect()->route('departments.show', $department)
            ->with('success', "Department \"{$department->name}\" was created.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department): View
    {
        Gate::authorize('view', $department);

        $department->load(['teachers' => fn ($query) => $query->orderBy('name')]);

        return view('departments.show', compact('department'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department): View
    {
        Gate::authorize('update', $department);

        return view('departments.edit', compact('department'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DepartmentRequest $request, Department $department): RedirectResponse
    {
        $department->update($request->validated());

        return redirect()->route('departments.show', $department)
            ->with('success', 'Department details were updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * A department that still has teachers cannot be deleted.
     */
    public function destroy(Department $department): RedirectResponse
    {
        Gate::authorize('delete', $department);

        $teacherCount = $department->teachers()->count();

        if ($teacherCount > 0) {
            return back()->with('error', "\"{$department->name}\" still has {$teacherCount} teacher(s). Move them to another department before deleting it.");
        }

        $department->delete();

        return redirect()->route('departments.index')
            ->with('success', "Department \"{$department->name}\" was deleted.");
    }
}
