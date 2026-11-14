<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::where('user_id', $request->user()->id)
            ->with(['property', 'unit']);

        if ($prop = $request->get('property_id')) {
            $query->where('property_id', $prop);
        }
        if ($cat = $request->get('category')) {
            $query->where('category', $cat);
        }
        if ($from = $request->get('from')) {
            $query->where('incurred_on', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->where('incurred_on', '<=', $to);
        }

        $expenses = $query->latest('incurred_on')->paginate(20)->appends($request->query());

        $properties = Property::where('user_id', $request->user()->id)->orderBy('name')->get();

        $totalThisMonth = Expense::where('user_id', $request->user()->id)
            ->whereMonth('incurred_on', now()->month)
            ->whereYear('incurred_on', now()->year)
            ->sum('amount');

        return view('expenses.index', compact('expenses', 'properties', 'totalThisMonth'));
    }

    public function create(Request $request)
    {
        $properties = Property::where('user_id', $request->user()->id)
            ->with('units')->orderBy('name')->get();
        return view('expenses.create', compact('properties'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $property = null;
        if (! empty($data['property_id'])) {
            $property = Property::findOrFail($data['property_id']);
            $this->authorizeProperty($request, $property);
            if (! empty($data['unit_id'])) {
                if ((int) $data['unit_id'] && ! $property->units->contains('id', $data['unit_id'])) {
                    abort(403);
                }
            }
        } else {
            $data['property_id'] = null;
            $data['unit_id'] = null;
        }

        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store('receipts', 'private');
        }

        $data['user_id'] = $request->user()->id;
        $expense = Expense::create($data);

        return redirect()->route('expenses.show', $expense)
            ->with('success', 'Expense recorded.');
    }

    public function show(Request $request, Expense $expense)
    {
        $this->authorizeOwner($request, $expense);
        $expense->load(['property', 'unit']);
        return view('expenses.show', compact('expense'));
    }

    public function edit(Request $request, Expense $expense)
    {
        $this->authorizeOwner($request, $expense);
        $properties = Property::where('user_id', $request->user()->id)
            ->with('units')->orderBy('name')->get();
        return view('expenses.edit', compact('expense', 'properties'));
    }

    public function update(Request $request, Expense $expense)
    {
        $this->authorizeOwner($request, $expense);
        $data = $this->validateData($request);

        if (! empty($data['property_id'])) {
            $property = Property::findOrFail($data['property_id']);
            $this->authorizeProperty($request, $property);
        } else {
            $data['property_id'] = null;
            $data['unit_id'] = null;
        }

        if ($request->hasFile('receipt')) {
            if ($expense->receipt_path) {
                Storage::disk('private')->delete($expense->receipt_path);
            }
            $data['receipt_path'] = $request->file('receipt')->store('receipts', 'private');
        }

        $expense->update($data);

        return redirect()->route('expenses.show', $expense)
            ->with('success', 'Expense updated.');
    }

    public function destroy(Request $request, Expense $expense)
    {
        $this->authorizeOwner($request, $expense);

        if ($expense->receipt_path) {
            Storage::disk('private')->delete($expense->receipt_path);
        }
        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted.');
    }

    /**
     * Download all expenses as CSV (filtered the same way as the index list).
     */
    public function exportCsv(Request $request)
    {
        $query = Expense::where('user_id', $request->user()->id)->with(['property', 'unit']);

        if ($prop = $request->get('property_id')) {
            $query->where('property_id', $prop);
        }
        if ($cat = $request->get('category')) {
            $query->where('category', $cat);
        }
        if ($from = $request->get('from')) {
            $query->where('incurred_on', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->where('incurred_on', '<=', $to);
        }

        $rows = $query->orderBy('incurred_on')->get();

        $csv = Writer::createFromString('');
        $csv->insertOne(['Date', 'Property', 'Unit', 'Category', 'Vendor', 'Description', 'Amount']);

        foreach ($rows as $e) {
            $csv->insertOne([
                $e->incurred_on->format('Y-m-d'),
                $e->property?->name ?? '',
                $e->unit?->label ?? '',
                Expense::CATEGORIES[$e->category] ?? $e->category,
                $e->vendor ?? '',
                $e->description ?? '',
                $e->amount,
            ]);
        }

        return response(stream_get_contents($csv->getContent()), 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="expenses-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'property_id'  => ['nullable', 'exists:properties,id'],
            'unit_id'      => ['nullable', 'exists:units,id'],
            'category'     => ['required', 'string', 'in:mortgage,taxes,insurance,utilities,repair,management_fee,other'],
            'vendor'       => ['nullable', 'string', 'max:160'],
            'description'  => ['nullable', 'string', 'max:500'],
            'incurred_on'  => ['required', 'date'],
            'amount'       => ['required', 'numeric', 'min:0'],
            'is_recurring' => ['nullable', 'boolean'],
            'receipt'      => ['nullable', 'file', 'max:5120'],
        ]);
    }

    protected function authorizeProperty(Request $request, Property $property): void
    {
        if ($property->user_id !== $request->user()->id) {
            abort(403);
        }
    }

    protected function authorizeOwner(Request $request, Expense $expense): void
    {
        if ($expense->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
