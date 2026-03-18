<?php

namespace App\Http\Controllers\exports;

use App\Exports\ProductsExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;

class excelExport extends Controller
{
    public function export(Request $request)
    {
        return Excel::download(
            new ProductsExport($request->user['supplier_id']),
            'products.xlsx'
        );
    }
}
