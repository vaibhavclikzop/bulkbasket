<?php

namespace App\Http\Controllers\eInvoice;

use App\Http\Controllers\Controller;
use App\Services\EInvoiceService;
use Illuminate\Http\Request;

class EInvoiceController extends Controller
{
   public function generateEInvoice(Request $request, EInvoiceService $service)
    {
        return $service->generate($request->invoice_id);
    }
}
