@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title> Purchase Order </title>
    @endpush



    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    Purchase Order
                </div>
                <button type="button" onclick="printcontent()" class="btn btn-primary"><i class="fa fa-print"
                        aria-hidden="true"></i> Print</button>

            </div>
        </div>
        <div class="card-body" id="PrintOrder">
            <div style="justify-content: space-between; border: solid 1px;margin-top:5px">
                <div style="text-align: center">
                    <h4 class="mt-4" style="font-size: 28px; font-weight: bolder">{{ $orders->name }}</h4>
                    <p style="font-size: 14px">
                        {!! $orders->address !!}
                        <br>
                        <span style="text-transform: none"> Phone : {{ $orders->number }}
                            E-Mail : {{ $orders->email }} </span>
                        <br>
                        GST : {{ $orders->gst }} <br>
                    </p>
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; border: solid 1px; padding: 8px;">
                <div style="padding: 5px; border:solid 1px; width: 50%">
                    <b>{{ $orders->vendor_company }}</b>
                    <p>
                        Dealer Type : {{ \Illuminate\Support\Str::upper($orders->dealer_type) }} <br>
                        GST No : {{ $orders->vendor_gst }} <br>
                        FSSAI No : {{ $orders->fssai_no }} <br>
                        Address :

                        {{ $orders->line_1 }} , {{ $orders->line_2 }} <br>
                        {{ $orders->vendor_city }}, {{ $orders->vendor_state }},
                        {{ $orders->vendor_pincode }} <br>
                    </p>
                </div>
                <div style="padding: 5px; border:solid 1px; width: 50%">
                    PO Details
                    <p>

                        PO No : {{ $orders->po_id }} <br>
                        Date : {{ \Carbon\Carbon::parse($orders->po_date)->format('d-m-Y') }}<br>
                        Expected Delivery Date : {{ \Carbon\Carbon::parse($orders->expected_delivery_date)->format('d-m-Y') }}<br>
                    </p>
                </div>
                <div style="padding: 5px; border:solid 1px; width: 50%">
                    
                    <p>

                        Payment Terms : {{ $orders->payment_term }} Days<br>
                        Remarks : {{ $orders->description }}<br>
                    </p>
                </div>
            </div>


            <div class="">
                <table class="w-100">
                    <thead>
                        <th style="border:  solid 1px; padding:2px">S.No</th>
                        <th style="border:  solid 1px; padding:2px">Description of goods</th>
                        <th style="border:  solid 1px; padding:2px">Article No</th>

                        <th style="border:  solid 1px; padding:2px">Price</th>
                        <th style="border:  solid 1px; padding:2px">Qty</th>

                        <th style="border:  solid 1px; padding:2px">Discount </th>
                        <th style="border:  solid 1px; padding:2px">Taxable</th>
                        <th style="border:  solid 1px; padding:2px">GST (%)</th>
                        <th style="border:  solid 1px; padding:2px">GST</th>
                        <th style="border:  solid 1px; padding:2px">CESS %</th>
                        <th style="border:  solid 1px; padding:2px">Total</th>

                    </thead>
                    <tbody>
                        @php
                            $sno = 1;
                            $total_qty = 0;
                            $total_taxable = 0;
                            $total_gst = 0;
                            $total_cess = 0;
                            $gross_total = 0;
                            $grandTotal = 0;
                            $loadingCharges = 0;
                            $freightCharges = 0;
                            $mainDiscount = 0;
                        @endphp

                        @foreach ($po_det as $item)
                            @php
                                $discount_amt = 0;
                                $subtotal = $item->price * $item->qty;

                                // Calculate discount based on type
                                if ($item->discount_type == '%') {
                                    $discount_amt = ($subtotal * ($item->discount ?? 0)) / 100;
                                }

                                $taxable = $subtotal - $discount_amt;
                                $gst_amt = ($taxable * ($item->gst ?? 0)) / 100;
                                $cess_amt = ($taxable * ($item->cess_tax ?? 0)) / 100;
                                $total = $taxable + $gst_amt + $cess_amt;

                                // Add to totals
                                $total_qty += $item->qty;
                                $total_taxable += $taxable;
                                $total_gst += $gst_amt;
                                $total_cess += $cess_amt;
                                $gross_total += $total;

                            @endphp
                            <tr>
                                <td style="border:1px solid;padding:2px">{{ $sno++ }}</td>
                                <td style="border:1px solid;padding:2px">{{ $item->name }}</td>
                                <td style="border:1px solid;padding:2px">{{ $item->article_no }}</td>

                                <td style="border:1px solid;padding:2px">{{ number_format($item->price, 2) }}</td>
                                <td style="border:1px solid;padding:2px">{{ $item->qty }} {{ $item->uom }}</td>
                                {{-- <td style="border:1px solid;padding:2px">{{ $item->discount_type }}</td> --}}
                                <td style="border:1px solid;padding:2px">{{ number_format($item->discount, 2) }}</td>
                                <td style="border:1px solid;padding:2px">{{ number_format($taxable, 2) }}</td>
                                <td style="border:1px solid;padding:2px">{{ $item->gst }}</td>
                                <td style="border:1px solid;padding:2px">{{ number_format($gst_amt, 2) }}</td>
                                <td style="border:1px solid;padding:2px">{{ $item->cess_tax }}</td>
                                <td style="border:1px solid;padding:2px">{{ number_format($total, 2) }}</td>
                            </tr>
                        @endforeach

                    <tfoot>
                        <tr>
                            <th colspan="4" style="border:1px solid;padding:2px; text-align: right">Sub Total</th>
                            <th style="border:1px solid;padding:2px"></th>
                            <th style="border:1px solid;padding:2px"></th>
                            <th style="border:1px solid;padding:2px">{{ number_format($total_taxable, 2) }}</th>
                            <th style="border:1px solid;padding:2px"></th>
                            <th style="border:1px solid;padding:2px">{{ number_format($total_gst, 2) }}</th>
                            <th style="border:1px solid;padding:2px">{{ number_format($total_cess, 2) }}</th>
                            <th style="border:1px solid;padding:2px">{{ number_format($gross_total, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="10" style="border:1px solid;padding:2px;text-align: right">Freight Charges</th>
                            <th style="border:1px solid;padding:2px">
                                {{ round($orders->freight_charges, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="10" style="border:1px solid;padding:2px;text-align: right">Loading Charges</th>
                            <th style="border:1px solid;padding:2px">
                                {{ round($orders->loading_charges, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="10" style="border:1px solid;padding:2px;text-align: right">
                                Discount @if ($orders->discount_type == 'percentage')
                                    %
                                @else
                                    ₹
                                @endif
                            </th>
                            <th style="border:1px solid;padding:2px">
                                {{ round($orders->discount_value, 2) }}</th>
                        </tr>
                        <tr>
                            @php
                                if ($orders->discount_type == 'percentage') {
                                    $grandTotal =
                                        $gross_total +
                                        $orders->freight_charges +
                                        $orders->loading_charges -
                                        (($gross_total + $orders->freight_charges + $orders->loading_charges) / 100) *
                                            $orders->discount_value;
                                } else {
                                    $grandTotal =
                                        $gross_total +
                                        $orders->freight_charges +
                                        $orders->loading_charges -
                                        $orders->discount_value;
                                }

                            @endphp
                            <th colspan="10" style="border:1px solid;padding:2px;text-align: right">Grand Total
                            </th>
                            <th style="border:1px solid;padding:2px">{{ round($grandTotal, 2, PHP_ROUND_HALF_UP) }}</th>
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
                            {{ $orders->name }}
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
