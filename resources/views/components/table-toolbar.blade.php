@props([
    'action'      => null,
    'placeholder' => 'Cari...',
    'perPage'     => null,
    'sortOptions' => [],
    'sort'        => null,
])

<form method="GET" action="{{ $action }}" class="d-flex flex-wrap align-items-end gap-2 p-3 border-bottom">
    <div class="flex-grow-1" style="min-width:200px">
        <label class="form-label small text-secondary mb-1">Cari</label>
        <div class="input-group">
            <span class="input-group-text"><i class="ti ti-search"></i></span>
            <input type="search" name="q" class="form-control" placeholder="{{ $placeholder }}" value="{{ request('q') }}">
        </div>
    </div>

    {{ $filters ?? '' }}

    @if (! empty($sortOptions))
        <div>
            <label class="form-label small text-secondary mb-1">Urutkan</label>
            <select name="sort" class="form-select" onchange="this.form.submit()" style="min-width:170px">
                @foreach ($sortOptions as $key => $label)
                    <option value="{{ $key }}" @selected($sort === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    @endif

    <div>
        <label class="form-label small text-secondary mb-1">Per halaman</label>
        <select name="per_page" class="form-select" onchange="this.form.submit()" style="width:90px">
            @foreach ([10, 25, 50, 100] as $n)
                <option value="{{ $n }}" @selected((int)($perPage ?? 10) === $n)>{{ $n }}</option>
            @endforeach
        </select>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="ti ti-filter me-1"></i> Terapkan
        </button>
        <a href="{{ $action }}" class="btn btn-link text-secondary">Reset</a>
    </div>
</form>
