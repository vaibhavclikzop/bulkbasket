@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title> Material Receipt Note </title>
    @endpush



    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    Material Receipt Note
                </div>
                <button type="button" onclick="printcontent()" class="btn btn-primary"><i class="fa fa-print"
                        aria-hidden="true"></i> Print</button>

            </div>
        </div>
        <div class="card-body" id="PrintOrder">
            <div style="justify-content: space-between; border: solid 1px;margin-top:5px">
                <div style="text-align: center">
                    <h4 class="mt-4" style="font-size: 28px; font-weight: bolder">{{ $stock_inward_mst->supplier_name }}
                    </h4>
                    <p style="font-size: 14px">
                        {!! $stock_inward_mst->supplier_address !!}
                        <br>
                        <span style="text-transform: none"> Phone : {{ $stock_inward_mst->supplier_phone }}
                            E-Mail : {{ $stock_inward_mst->supplier_email }} </span>
                        <br>
                        GST : {{ $stock_inward_mst->supplier_gst }} <br>
                    </p>
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; border: solid 1px; padding: 8px;">
                <div style="padding: 5px; border:solid 1px; width: 50%">
                    (Vendor Details)
                    <p><b>Name</b> : {{ $stock_inward_mst->vendor_name }}</b><br>
                        <b> Contact </b>: {{ $stock_inward_mst->vendor_number }} <br>
                        <b> Address </b>: {{ $stock_inward_mst->vendor_address }} ,
                        {{ $stock_inward_mst->vendor_city }}, {{ $stock_inward_mst->vendor_state }},
                        {{ $stock_inward_mst->vendor_pincode }} <br>
                        <b> GST No </b>: {{ $stock_inward_mst->vendor_gst ?? 'N/A ' }} <br>
                    </p>
                </div>
                <div style="padding: 5px; border:solid 1px; width: 50%">
                    Order Detail
                    <p>
                        <b>Challan/Bill No</b> : {{ $stock_inward_mst->id }} <br>
                        <b>Invoice No</b> : {{ $stock_inward_mst->invoice_no }} <br>
                        <b>Challan/Bill Date</b> : {{ date('d-m-Y', strtotime($stock_inward_mst->invoice_date)) }} <br>
                        <b>R.M Date</b> : {{ date('d-m-Y', strtotime($stock_inward_mst->received_material_date)) }} <br>
                    </p>
                </div>
            </div>


            <div class="">
                <table class="w-100">
                    <thead>
                        <tr>
                            <th style="border:1px solid; padding:2px">S.No</th>
                            <th style="border:1px solid; padding:2px">Description of goods</th>
                            <th style="border:1px solid; padding:2px">Article No</th>
                            <th style="border:1px solid; padding:2px">Warehouse</th>
                            <th style="border:1px solid; padding:2px">Location Code</th>
                            {{-- <th style="border:1px solid; padding:2px">UOM</th> --}}
                            <th style="border:1px solid; padding:2px">Base Price</th>
                            <th style="border:1px solid; padding:2px">Qty</th>
                            {{-- <th style="border:1px solid; padding:2px">Discount Type</th> --}}
                            <th style="border:1px solid; padding:2px">Discount</th>
                            <th style="border:1px solid; padding:2px">SubTotal</th>
                            <th style="border:1px solid; padding:2px">GST (%)</th>
                            <th style="border:1px solid; padding:2px">Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php
                            $sno = 1;
                            $total_taxable = 0;
                            $gross_total = 0;
                            $qty = 0;
                        @endphp

                        @foreach ($stock_inward_det as $item)
                            @php
                                $subtotal = $item->price * $item->qty;
                                $discount = $item->discount ?? 0;
                                $discount_type = $item->discount_type ?? 'percent';
                                if ($discount_type == 'percent') {
                                    $discount_amt = ($subtotal * $discount) / 100;
                                } else {
                                    $discount_amt = $discount;
                                }
                                $taxable = $subtotal - $discount_amt;
                                $gst_amt = ($taxable * ($item->gst ?? 0)) / 100;
                                $cess_amt = ($taxable * ($item->cess_tax ?? 0)) / 100;
                                $total = $taxable + $gst_amt + $cess_amt;
                                $qty += $item->qty;
                                $total_taxable += $taxable;
                                $gross_total += $total;
                            @endphp

                            <tr>
                                <td style="border:1px solid; padding:2px">{{ $sno++ }}</td>
                                <td style="border:1px solid; padding:2px">{{ $item->product_name }}</td>
                                <td style="border:1px solid; padding:2px">{{ $item->article_no }}</td>
                                <td style="border:1px solid; padding:2px">{{ $item->warehouse_name }}</td>
                                <td style="border:1px solid; padding:2px">{{ $item->location_code }}</td>
                                {{-- <td style="border:1px solid; padding:2px">{{ $item->uom }}</td> --}}
                                <td style="border:1px solid; padding:2px">{{ number_format($item->price, 2) }}</td>
                                <td style="border:1px solid; padding:2px">{{ $item->qty }}</td>
                                {{-- <td style="border:1px solid; padding:2px">{{ $item->discount_type  ?? "N/A" }}</td> --}}
                                <td style="border:1px solid; padding:2px">{{ $item->discount }}
                                    ({{ $item->discount_type ?? 'N/A' }})</td>
                                <td style="border:1px solid; padding:2px">{{ number_format($taxable, 2) }}</td>
                                <td style="border:1px solid; padding:2px">{{ $item->gst }}</td>
                                <td style="border:1px solid; padding:2px">{{ number_format($total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr>
                            <th style="border:1px solid; padding:2px" colspan="7"><b>Total</b></th>
                            <th style="border:1px solid; padding:2px">{{ $qty }}</th>
                            {{-- <th style="border:1px solid; padding:2px"></th> --}}
                            <th style="border:1px solid; padding:2px">{{ number_format($total_taxable, 2) }}</th>
                            <th style="border:1px solid; padding:2px"></th>
                            <th style="border:1px solid; padding:2px">{{ number_format($gross_total, 2) }}</th>
                        </tr>

                        <tr>
                            <th style="border:1px solid; padding:2px" colspan="10"><b>R/O Total Amount</b></th>
                            <th style="border:1px solid; padding:2px">
                                {{ round($gross_total, 0, PHP_ROUND_HALF_UP) }}
                            </th>
                        </tr>
                    </tfoot>
                </table>

            </div>
            <div style="display: flex; justify-content: space-between;  ">
                <div style="padding: 5px; border:solid 1px; width: 100%">

                    <div class="" style="display: flex; justify-content: space-between">
                        <div>

                        </div>
                        <div>
                            {{ $stock_inward_mst->supplier_name }}
                            <br>
                            <br>
                            <br>
                            Auth. Signatory
                        </div>
                    </div>
                    <div style="margin-top:20px; text-align: center">
                        <h6 style="font-size: 15px">This is computer generated Purchase order no signature required</h6>
                    </div>

                </div>
            </div>

        </div>
    </div>
@endsection
