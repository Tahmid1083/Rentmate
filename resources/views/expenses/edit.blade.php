@extends('layouts.app')
@section('title', 'Edit expense')
@section('content')
<div class="card" style="max-width:680px;">
    <form method="post" action="{{ route('expenses.update', $expense) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="grid cols-2">
            <div class="form-group">
                <label for="property_id">Property</label>
                <select name="property_id" id="property_id">
                    <option value="">(general)</option>
                    @foreach ($properties as $p)
                        <option value="{{ $p->id }}" @selected(old('property_id', (string) $expense->property_id) === (string) $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="unit_id">Unit (optional)</label>
                <select name="unit_id" id="unit_id">
                    <option value="">(none)</option>
                    @foreach ($properties as $p)
                        @foreach ($p->units as $u)
                            <option value="{{ $u->id }}" @selected(old('unit_id', (string) $expense->unit_id) === (string) $u->id)>{{ $p->name }} / {{ $u->label }}</option>
                        @endforeach
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="category">Category *</label>
                <select name="category" id="category">
                    @foreach (\App\Models\Expense::CATEGORIES as $k => $v)
                        <option value="{{ $k }}" @selected(old('category', $expense->category) === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="vendor">Vendor / Payee</label>
                <input type="text" name="vendor" id="vendor" value="{{ old('vendor', $expense->vendor) }}">
            </div>
            <div class="form-group">
                <label for="incurred_on">Date *</label>
                <input type="date" name="incurred_on" id="incurred_on" value="{{ old('incurred_on', $expense->incurred_on->format('Y-m-d')) }}" required>
            </div>
            <div class="form-group">
                <label for="amount">Amount ($) *</label>
                <input type="number" name="amount" id="amount" step="0.01" min="0" value="{{ old('amount', $expense->amount) }}" required>
            </div>
            <div class="form-group" style="grid-column:1/-1;">
                <label for="description">Description</label>
                <input type="text" name="description" id="description" value="{{ old('description', $expense->description) }}">
            </div>
            <div class="form-group" style="grid-column:1/-1;">
                <label style="display:flex;align-items:center;gap:6px;">
                    <input type="checkbox" name="is_recurring" value="1" @checked(old('is_recurring', $expense->is_recurring)) style="width:auto;">
                    Recurring monthly expense
                </label>
            </div>
            <div class="form-group" style="grid-column:1/-1;">
                <label for="receipt">Replace receipt (optional)</label>
                <input type="file" name="receipt" id="receipt">
            </div>
        </div>
        <div class="row">
            <button class="btn" type="submit">Save changes</button>
            <a href="{{ route('expenses.show', $expense) }}" class="btn secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
