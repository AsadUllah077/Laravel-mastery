<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Tags', ['tags' => Tag::withCount('lessons')->orderBy('name')->paginate(30)]);
    }

    public function save(Request $request, ?Tag $tag = null): RedirectResponse
    {
        if (is_string($request->input('name'))) {
            $request->merge(['name' => mb_strtolower(trim($request->input('name')))]);
        }
        $data = $request->validate(['name' => ['required', 'string', 'max:50', Rule::unique('tags')->ignore($tag)]]);
        ($tag ?? new Tag)->fill($data)->save();

        return back()->with('success', 'Tag saved.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return back()->with('success', 'Tag removed. Lesson content is preserved.');
    }
}
