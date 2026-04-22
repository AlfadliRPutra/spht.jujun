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
    public function index(Request $request): View
    {
        $sortMap = [
            'sort_order' => ['sort_order', 'asc',  'Urutan'],
            'latest'     => ['created_at', 'desc', 'Terbaru'],
            'oldest'     => ['created_at', 'asc',  'Terlama'],
            'title_asc'  => ['title',      'asc',  'Judul A-Z'],
        ];
        $sortOptions = array_map(fn ($v) => $v[2], $sortMap);
        $sort = array_key_exists($request->input('sort'), $sortMap) ? $request->input('sort') : 'sort_order';
        [$sortCol, $sortDir] = $sortMap[$sort];

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100], true)
            ? (int) $request->input('per_page') : 10;

        $items = HeroSlide::query()
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.$request->input('q').'%'))
            ->when($request->input('active') === '1', fn ($q) => $q->where('is_active', true))
            ->when($request->input('active') === '0', fn ($q) => $q->where('is_active', false))
            ->orderBy($sortCol, $sortDir)
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('pages.admin.hero.index', compact('items', 'sort', 'sortOptions', 'perPage'));
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
