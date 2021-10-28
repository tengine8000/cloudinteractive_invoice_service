<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Invoice;
use App\Models\LineItem;


class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with('lineitems')->where('user_id', Auth::user()->id)->get();
        if($invoices->isEmpty()){
            return response()->json(['message' => 'You have no invoices!'], 200);
        }
        return response()->json([
            'summary' => [
                'invoice_count' => $invoices->count(),
                'total_invoice_amount' => $invoices->sum('total'),
            ],
            'data' => $invoices], 200);
    }

    public function store(Request $request)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $validator = Validator::make($data, [
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|min:10',
            'items.*.rate' => 'required|integer|min:1',
            'items.*.quantity' => 'required|integer|min:1',
            'billingAddress' => 'required|string|min:20',
        ]);

        if($validator->fails()){
            return response()->json(['errors'=> $validator->errors()], 400);   
        }
        $validated = $validator->validated();
        $subtotal = $this->getSubTotal($validated['items']);
        $tax_rate = 10;
        $tax = $this->apply_tax_rate($subtotal, $tax_rate, null);
        $total = $subtotal + $tax;

        $invoice = Invoice::create([
            'user_id' => Auth::user()->id,
            'status' => 'pending',
            'subtotal' => $subtotal,
            'tax_rate' => $tax_rate,
            'total' => $total,
            'notes' => "Sample invoice",
        ]);

        $invoice->save();
        // Save the line items
        $this->saveLineItems($validated['items'], $invoice->id);
               
        return response()->json(['data' => $invoice], 200);
    }

    private function getSubTotal($items)
    {
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['rate'] * $item['quantity'];
        }
        return $subtotal;
    }

    private function saveLineItems($items, $invoice_id)
    {
        foreach ($items as $item) {
            $lineItem = LineItem::create([
                'invoice_id' => $invoice_id,
                'description' => $item['description'],
                'rate' => $item['rate'],
                'quantity' => $item['quantity'],
            ]);
            $lineItem->save();
        }
    }

    private function apply_tax_rate($subtotal, $tax_rate, $user_callback_fxn=null)
    {
        if(is_null($user_callback_fxn) && !is_callable($user_callback_fxn)){
            return (int) ($subtotal * $tax_rate) / 100;
        }else{
            return (int) call_user_func([$this, $user_callback_fxn], $subtotal);
        }
    }
    // Sample callback tax function 
    private function tax_callback($subtotal)
    {
        return ($subtotal * 50) / 100;
    }
}
