@extends('layouts.hospital')

@section('title', 'Edit Medicine')
@section('eyebrow', 'Inventory')
@section('heading', 'Edit — '.$medicine->name)

@section('actions')
    <a href="{{ route('medicines.index') }}" class="mp-btn-secondary">Back</a>
@endsection

@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <div class="mp-card lg:col-span-2">
        <form method="POST" action="{{ route('medicines.update', $medicine) }}">
            @csrf
            @method('PUT')
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mp-label" for="code">Code *</label>
                    <input class="mp-input" type="text" name="code" id="code" value="{{ old('code', $medicine->code) }}" required>
                </div>
                <div>
                    <label class="mp-label" for="name">Name *</label>
                    <input class="mp-input" type="text" name="name" id="name" value="{{ old('name', $medicine->name) }}" required>
                </div>
                <div>
                    <label class="mp-label" for="generic_name">Generic name</label>
                    <input class="mp-input" type="text" name="generic_name" id="generic_name" value="{{ old('generic_name', $medicine->generic_name) }}">
                </div>
                <div>
                    <label class="mp-label" for="category">Category</label>
                    <input class="mp-input" type="text" name="category" id="category" value="{{ old('category', $medicine->category) }}">
                </div>
                <div>
                    <label class="mp-label" for="form">Form</label>
                    <input class="mp-input" type="text" name="form" id="form" value="{{ old('form', $medicine->form) }}">
                </div>
                <div>
                    <label class="mp-label" for="strength">Strength</label>
                    <input class="mp-input" type="text" name="strength" id="strength" value="{{ old('strength', $medicine->strength) }}">
                </div>
                <div>
                    <label class="mp-label" for="unit">Unit</label>
                    <input class="mp-input" type="text" name="unit" id="unit" value="{{ old('unit', $medicine->unit) }}">
                </div>
                <div>
                    <label class="mp-label" for="unit_price">Unit price (TZS) *</label>
                    <input class="mp-input" type="number" step="0.01" min="0" name="unit_price" id="unit_price" value="{{ old('unit_price', $medicine->unit_price) }}" required>
                </div>
                <div>
                    <label class="mp-label" for="reorder_level">Reorder level</label>
                    <input class="mp-input" type="number" min="0" name="reorder_level" id="reorder_level" value="{{ old('reorder_level', $medicine->reorder_level) }}">
                </div>
                <div>
                    <label class="mp-label" for="batch_no">Batch no.</label>
                    <input class="mp-input" type="text" name="batch_no" id="batch_no" value="{{ old('batch_no', $medicine->batch_no) }}">
                </div>
                <div>
                    <label class="mp-label" for="expiry_date">Expiry date</label>
                    <input class="mp-input" type="date" name="expiry_date" id="expiry_date" value="{{ old('expiry_date', $medicine->expiry_date?->format('Y-m-d')) }}">
                </div>
                <div class="sm:col-span-2">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $medicine->is_active)) class="rounded border-brand-300 text-brand-700">
                        Active
                    </label>
                </div>
            </div>
            <p class="mt-3 text-sm text-ink-700/60">Current stock: <strong>{{ $medicine->stock_qty }} {{ $medicine->unit }}</strong> (use stock-in form to add)</p>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="mp-btn">Update medicine</button>
            </div>
        </form>
    </div>

    <div class="mp-card">
        <h2 class="font-display text-lg text-ink-900">Stock in</h2>
        <form method="POST" action="{{ route('medicines.stock-in', $medicine) }}" class="mt-4 space-y-3">
            @csrf
            <div>
                <label class="mp-label" for="quantity">Quantity *</label>
                <input class="mp-input" type="number" name="quantity" id="quantity" min="1" required>
            </div>
            <div>
                <label class="mp-label" for="stock_batch_no">Batch no.</label>
                <input class="mp-input" type="text" name="batch_no" id="stock_batch_no">
            </div>
            <div>
                <label class="mp-label" for="stock_expiry_date">Expiry date</label>
                <input class="mp-input" type="date" name="expiry_date" id="stock_expiry_date">
            </div>
            <div>
                <label class="mp-label" for="notes">Notes</label>
                <textarea class="mp-input" name="notes" id="notes" rows="2"></textarea>
            </div>
            <button type="submit" class="mp-btn w-full">Add stock</button>
        </form>
    </div>
</div>
@endsection
