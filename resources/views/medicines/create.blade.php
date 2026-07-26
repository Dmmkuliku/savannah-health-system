@extends('layouts.hospital')

@section('title', 'Add Medicine')
@section('eyebrow', 'Inventory')
@section('heading', 'Add Medicine')

@section('actions')
    <a href="{{ route('medicines.index') }}" class="mp-btn-secondary">Back</a>
@endsection

@section('content')
<div class="mp-card max-w-3xl">
    <form method="POST" action="{{ route('medicines.store') }}">
        @csrf
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mp-label" for="code">Code *</label>
                <input class="mp-input" type="text" name="code" id="code" value="{{ old('code') }}" required>
            </div>
            <div>
                <label class="mp-label" for="name">Name *</label>
                <input class="mp-input" type="text" name="name" id="name" value="{{ old('name') }}" required>
            </div>
            <div>
                <label class="mp-label" for="generic_name">Generic name</label>
                <input class="mp-input" type="text" name="generic_name" id="generic_name" value="{{ old('generic_name') }}">
            </div>
            <div>
                <label class="mp-label" for="category">Category</label>
                <input class="mp-input" type="text" name="category" id="category" value="{{ old('category') }}">
            </div>
            <div>
                <label class="mp-label" for="form">Form</label>
                <input class="mp-input" type="text" name="form" id="form" value="{{ old('form') }}" placeholder="Tablet, syrup...">
            </div>
            <div>
                <label class="mp-label" for="strength">Strength</label>
                <input class="mp-input" type="text" name="strength" id="strength" value="{{ old('strength') }}">
            </div>
            <div>
                <label class="mp-label" for="unit">Unit</label>
                <input class="mp-input" type="text" name="unit" id="unit" value="{{ old('unit', 'tablet') }}">
            </div>
            <div>
                <label class="mp-label" for="unit_price">Unit price (TZS) *</label>
                <input class="mp-input" type="number" step="0.01" min="0" name="unit_price" id="unit_price" value="{{ old('unit_price') }}" required>
            </div>
            <div>
                <label class="mp-label" for="stock_qty">Initial stock</label>
                <input class="mp-input" type="number" min="0" name="stock_qty" id="stock_qty" value="{{ old('stock_qty', 0) }}">
            </div>
            <div>
                <label class="mp-label" for="reorder_level">Reorder level</label>
                <input class="mp-input" type="number" min="0" name="reorder_level" id="reorder_level" value="{{ old('reorder_level', 10) }}">
            </div>
            <div>
                <label class="mp-label" for="batch_no">Batch no.</label>
                <input class="mp-input" type="text" name="batch_no" id="batch_no" value="{{ old('batch_no') }}">
            </div>
            <div>
                <label class="mp-label" for="expiry_date">Expiry date</label>
                <input class="mp-input" type="date" name="expiry_date" id="expiry_date" value="{{ old('expiry_date') }}">
            </div>
            <div class="sm:col-span-2">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-brand-300 text-brand-700">
                    Active
                </label>
            </div>
        </div>
        <div class="mt-6 flex gap-3">
            <button type="submit" class="mp-btn">Save medicine</button>
            <a href="{{ route('medicines.index') }}" class="mp-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
