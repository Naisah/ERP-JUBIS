<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Quote::with(['user', 'items.product'])->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return Inertia::render('Admin/Quotes/Index', [
            'quotes' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['status'])
        ]);
    }

    public function show(Quote $quote)
    {
        $quote->load(['user', 'items.product']);
        return Inertia::render('Admin/Quotes/Show', [
            'quote' => $quote
        ]);
    }

    public function update(Request $request, Quote $quote)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,reviewed,approved,rejected',
        ]);

        $quote->update(['status' => $validated['status']]);

        // Auto-generate invoice if approved
        if ($validated['status'] === 'approved') {
            $quote->load('user');
            
            // Check if invoice already exists to prevent duplicates
            $existingInvoice = \App\Models\Invoice::where('quote_id', $quote->id)->first();
            
            if (!$existingInvoice) {
                \App\Models\Invoice::create([
                    'quote_id' => $quote->id,
                    'user_id' => $quote->user_id,
                    'subtotal' => $quote->subtotal,
                    'vat_amount' => $quote->vat_amount,
                    'total_amount' => $quote->total_amount,
                    'amount_paid' => 0,
                    'status' => 'unpaid',
                    'due_date' => now()->addDays(30), // Default Net 30
                    'billing_address' => $quote->user->billing_address ?? 'Not specified',
                ]);
            }
            
            // AUTOMATION: Generate PayMongo Checkout Link
            try {
                $paymongo = app(\App\Services\PayMongoService::class);
                $link = $paymongo->createPaymentLink($existingInvoice ?? \App\Models\Invoice::where('quote_id', $quote->id)->first());
                if ($link && isset($link['checkout_url'])) {
                    \App\Models\Invoice::where('quote_id', $quote->id)->update([
                        'payment_url' => $link['checkout_url'],
                        'payment_reference_id' => $link['id'] ?? null
                    ]);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('PayMongo Auto-Generation Failed: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Quote status updated.');
    }
}


