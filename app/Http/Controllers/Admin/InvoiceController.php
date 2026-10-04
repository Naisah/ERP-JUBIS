<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['user', 'quote'])->latest();

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        return Inertia::render('Admin/Invoices/Index', [
            'invoices' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['status'])
        ]);
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['user', 'quote.items.product', 'shipments']);
        
        return Inertia::render('Admin/Invoices/Show', [
            'invoice' => $invoice
        ]);
    }

    public function updatePayment(Request $request, Invoice $invoice)
    {
        $request->validate([
            'amount_paid' => 'required|numeric|min:0',
        ]);

        $newAmount = $invoice->amount_paid + $request->amount_paid;
        
        $status = 'partially_paid';
        if ($newAmount >= $invoice->total_amount) {
            $status = 'paid';
        }

        $invoice->update([
            'amount_paid' => $newAmount,
            'status' => $status
        ]);

        return redirect()->back()->with('success', 'Payment logged successfully.');
    }
}
