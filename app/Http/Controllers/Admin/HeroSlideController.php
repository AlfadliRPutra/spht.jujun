<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HeroSlideController extends Controller
{
    public function index(): View
    {
        $slides = HeroSlide::orderBy('sort_order')->orderBy('id')->get();
        return view('pages.admin.hero.index', compact('slides'));
    }

    public function create(): View
    {
        $slide = new HeroSlide(['is_active' => true, 'sort_order' => HeroSlide::max('sort_order') + 1]);
        return view('pages.admin.hero.form', compact('slide'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('hero', 'public');
        }

        HeroSlide::create($data);

        return redirect()->route('admin.hero.index')->with('success', 'Hero slide berhasil ditambahkan.');
    }

    public function edit(HeroSlide $slide): View
    {
        return view('pages.admin.hero.form', compact('slide'));
    }

    public function update(Request $request, HeroSlide $slide): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            if ($slide->image && ! str_starts_with($slide->image, 'http')) {
                Storage::disk('public')->delete($slide->image);
            }
            $data['image'] = $request->file('image')->store('hero', 'public');
        }

        $slide->update($data);

        return redirect()->route('admin.hero.index')->with('success', 'Hero slide berhasil diperbarui.');
    }

    public function destroy(HeroSlide $slide): RedirectResponse
    {
        if ($slide->image && ! str_starts_with($slide->image, 'http')) {
            Storage::disk('public')->delete($slide->image);
        }

        $slide->delete();

        return redirect()->route('admin.hero.index')->with('success', 'Hero slide berhasil dihapus.');
    }

    public function toggle(HeroSlide $slide): RedirectResponse
    {
        $slide->update(['is_active' => ! $slide->is_active]);

        return back()->with('success', 'Status hero slide diperbarui.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title'      => ['required', 'string', 'max:255'],
            'subtitle'   => ['nullable', 'string', 'max:500'],
            'image'      => ['nullable', 'image', 'max:4096'],
            'cta_label'  => ['nullable', 'string', 'max:100'],
            'cta_url'    => ['nullable', 'string', 'max:500'],
            'is_active'  => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
