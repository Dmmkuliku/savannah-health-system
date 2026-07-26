@extends('layouts.hospital')

@section('title', 'Medicine Inventory')
@section('eyebrow', 'Pharmacy')
@section('heading', 'Medicine Inventory')

@section('actions')
    <a href="{{ route('medicines.create') }}" class="mp-btn">Add medicine</a>
@endsection

@section('content')
<div class="mp-card">
    <form method="GET" class="mb-5 flex flex-wrap items-end gap-3">
        <div class="min-w-[240px] flex-1">
            <label class="mp-label" for="search">Search name, code, generic</label>
            <input class="mp-input" type="search" name="search" id="search" value="{{ $search }}">
        </div>
        <button type="submit" class="mp-btn-secondary">Search</button>
    </form>

    <div class="overflow-x-auto">
        <table class="mp-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Form / Strength</th>
                    <th>Stock</th>
                    <th>Unit price</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                @forelse($medicines as $medicine)
                    <tr>
                        <td class="font-semibold text-brand-800">{{ $medicine->code }}</td>
                        <td>{{ $medicine->name }}@if($medicine->generic_name)<br><span class="text-xs text-ink-700/60">{{ $medicine->generic_name }}</span>@endif</td>
                        <td>{{ collect([$medicine->form, $medicine->strength])->filter()->implode(' · ') ?: '—' }}</td>
                        <td>
                            <span class="mp-badge {{ $medicine->stock_qty <= $medicine->reorder_level ? 'bg-red-100 text-red-700' : 'bg-brand-50 text-brand-800' }}">
                                {{ $medicine->stock_qty }} {{ $medicine->unit }}
                            </span>
                        </td>
                        <td>{{ \App\Support\Hospital::money($medicine->unit_price) }}</td>
                        <td>{{ $medicine->is_active ? 'Active' : 'Inactive' }}</td>
                        <td class="text-right"><a href="{{ route('medicines.edit', $medicine) }}" class="font-semibold text-brand-700">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-ink-700/60">No medicines found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($medicines->hasPages())
        <div class="mt-4">{{ $medicines->links() }}</div>
    @endif
</div>
@endsection
