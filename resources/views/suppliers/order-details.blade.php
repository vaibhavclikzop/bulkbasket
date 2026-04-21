@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title> Invoice</title>
    @endpush



    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    Invoice
                </div>


                <button type="button" onclick="printcontent()" class="btn btn-primary"><i class="fa fa-print"
                        aria-hidden="true"></i> Print</button>

            </div>
        </div>
        <div class="card-body" id="PrintOrder">

            <div style="justify-content: space-between; border: solid 1px;margin-top:5px">
                <div style="text-align: center">
                    <h4 class="mt-4" style="font-size: 28px; font-weight: bolder">
                        {{ $setting->company_name ?? 'Bulk Basket India' }} </h4>
                    <p style="font-size: 14px">
                        {!! $setting->address ?? 'SCF 179 sector 26 grain market' !!}
                        <br>
                        <span style="text-transform: none"> Phone : {{ $setting->number ?? '9876521909' }}
                            E-Mail : {{ $setting->email ?? 'bulkbasketindia@gmail.com' }} </span>
                        <br>
                        GST : {{ $data->gst ?? '04AHFPK8992H1ZZ' }} <br>
                    </p>
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; border: solid 1px; padding: 8px;">
                <div style="padding: 5px; border:solid 1px; width: 50%">
                    (Billed To) {{ $orders->customer_name }}
                    <p>
                        Contact : {{ $orders->customer_number }} <br>
                        Address : {{ $orders->customer_address }}, {{ $orders->customer_district }},
                        {{ $orders->customer_city }}, {{ $orders->customer_state }},
                        {{ $orders->customer_pincode }} <br>
                        GST : {{ $orders->gst ?? 'N/A' }} <br>
                    </p>
                </div>
                <div style="padding: 5px; border:solid 1px; width: 50%">
                    (Shipped To) {{ $orders->name }}
                    <p>

                        Contact : {{ $orders->number }} <br>
                        Address : {{ $orders->address }}, {{ $orders->district }}, {{ $orders->city }},
                        {{ $orders->state }},
                        {{ $orders->pincode }} <br>
                    </p>
                </div>
            </div>
            <div class="">
                <table class="w-100">
                    <thead>
                        <th style="border:  solid 1px; padding:2px">S.No</th>

                        <th style="border:  solid 1px; padding:2px">Description of goods</th>
                        <th style="border:  solid 1px; padding:2px">Article No</th>
                        <th style="border:  solid 1px; padding:2px">UOM</th>
                        <th style="border:  solid 1px; padding:2px">MRP</th>
                        <th style="border:  solid 1px; padding:2px">Actual Qty</th>
                        <th style="border:  solid 1px; padding:2px">Out Qty</th>
                        <th style="border:  solid 1px; padding:2px">Scrap Qty</th>
                        <th style="border:  solid 1px; padding:2px">Rate</th>
                        <th style="border:  solid 1px; padding:2px">Taxable</th>
                        <th style="border:  solid 1px; padding:2px">GST (%)</th>
                        <th style="border:  solid 1px; padding:2px">GST</th>
                        <th style="border:  solid 1px; padding:2px">CESS %</th>
                        <th style="border:  solid 1px; padding:2px">CESS</th>
                        <th style="border:  solid 1px; padding:2px">Total</th>

                    </thead>
                    <tbody>
                        @php
                            $sno = 1;
                            $total = 0;
                            $total_taxable = 0;
                            $total_gst = 0;
                            $total_cess = 0;
                            $gross_total = 0;
                            $qty = 0;
                            $out = 0;
                            $pending = 0;
                            $igst_amt = 0;
                            $cgst_amt = 0;
                            $sgst_amt = 0;
                            $total_mrp = 0;
                        @endphp

                        @foreach ($det as $item)
                            @php

                                // if ($item->gst_type == 'Outer GST') {
                                //     $igst_amt += (($item->price * $item->qty) / 100) * $item->gst;
                                // } else {
                                //     $cgst_amt += ((($item->price * $item->qty) / 100) * $item->gst) / 2;
                                //     $sgst_amt += ((($item->price * $item->qty) / 100) * $item->gst) / 2;
                                // }

                                $total =
                                    $item->price * $item->qty +
                                    (($item->price * $item->qty) / 100) * $item->gst +
                                    (($item->price * $item->qty) / 100) * $item->cess_tax;
                                $qty += $item->qty;
                                $out += $item->out_qty;
                                $pending += $item->qty - $item->out_qty;
                                $total_taxable += $item->price * $item->qty;
                                $total_gst += (($item->price * $item->qty) / 100) * $item->gst;
                                $total_cess += (($item->price * $item->qty) / 100) * $item->cess_tax;
                                $gross_total += $total;
                                $total_mrp += $item->price * $item->qty;
                            @endphp
                            <tr>
                                <td style="border:  solid 1px; padding:2px">{{ $sno++ }}</td>

                                <td style="border:  solid 1px; padding:2px">{{ $item->name }}</td>
                                <td style="border:  solid 1px; padding:2px">{{ $item->article_no }}</td>
                                <td style="border:  solid 1px; padding:2px">{{ $item->uom }}</td>
                                <td style="border:  solid 1px; padding:2px">{{ $item->price }}</td>
                                <td style="border:  solid 1px; padding:2px">{{ $item->qty }}</td>
                                <td style="border:  solid 1px; padding:2px">{{ $item->out_qty }}</td>
                                <td style="border:  solid 1px; padding:2px"> {{ $item->qty - $item->out_qty }} </td>
                                <td style="border:  solid 1px; padding:2px">{{ $item->price }}</td>
                                <td style="border:  solid 1px; padding:2px">{{ $item->price * $item->qty }}</td>
                                <td style="border:  solid 1px; padding:2px">{{ $item->gst }} </td>
                                <td style="border:  solid 1px; padding:2px">
                                    {{ (($item->price * $item->qty) / 100) * $item->gst }} </td>
                                <td style="border:  solid 1px; padding:2px">{{ $item->cess_tax }} </td>
                                <td style="border:  solid 1px; padding:2px">
                                    {{ (($item->price * $item->qty) / 100) * $item->cess_tax }} </td>
                                <td style="border:  solid 1px; padding:2px">{{ $total }}</td>

                            </tr>
                        @endforeach


                    </tbody>
                    <tfoot>
                        <tr>
                            <th style="border:  solid 1px; padding:2px" colspan="5">Total</th>
                            <th style="border:  solid 1px; padding:2px">{{ $qty }}</th>
                            <th style="border:  solid 1px; padding:2px">{{ $out }}</th>
                            <th style="border:  solid 1px; padding:2px">{{ $pending }}</th>
                            <th style="border:  solid 1px; padding:2px"></th>
                            <th style="border:  solid 1px; padding:2px">{{ $total_taxable }}</th>
                            <th style="border:  solid 1px; padding:2px"></th>
                            <th style="border:  solid 1px; padding:2px">{{ $total_gst }}</th>
                            <th style="border:  solid 1px; padding:2px"></th>
                            <th style="border:  solid 1px; padding:2px">{{ $total_cess }}</th>
                            <th style="border:  solid 1px; padding:2px">{{ $gross_total }}</th>
                        </tr>
                    </tfoot>

                </table>
            </div>
            <div style="display: flex; justify-content: space-between;  ">
                <div style="padding: 5px; border:solid 1px; width: 50%">
                    <table class="w-100">
                        <tr>

                            </th>
                        </tr>
                        <tr>
                            <th style="border:  solid 1px; padding:2px" colspan="2">Bank Details : </th>
                        </tr>
                        <tr>
                            <th style="border:  solid 1px; padding:2px">Bank Name </th>
                            <th style="border:  solid 1px; padding:2px">NA</th>
                        </tr>
                        <tr>
                            <th style="border:  solid 1px; padding:2px">Branch Name </th>
                            <th style="border:  solid 1px; padding:2px">NA</th>
                        </tr>
                        <tr>
                            <th style="border:  solid 1px; padding:2px">Bank Account Number </th>
                            <th style="border:  solid 1px; padding:2px">NA</th>
                        </tr>
                        <tr>
                            <th style="border:  solid 1px; padding:2px">Bank Branch IFSC : </th>
                            <th style="border:  solid 1px; padding:2px">NA</th>
                        </tr>
                        <tr>
                            <th style="border:  solid 1px; padding:2px" colspan="2">
                                Amount In Words :
                                <b> <u>{{ numberToWords(round($gross_total)) }}</u></b>
                            </th>
                        </tr>
                    </table>
                    <div>
                        <span style="font-size: 15px; color: black; margin-bottom:0px;"> Remarks :</b></span>
                        <span style="font-size: 13px; color: black; margin-bottom:0px;">
                            {{ $orders->remarks }}
                        </span>
                    </div>
                </div>
                <div style="padding: 5px; border:solid 1px; width: 50%">
                    <table class="w-100" style="text-align: right">
                        <tr>
                            <th style="border:  solid 1px; padding:2px">Total Amount Before Tax</th>
                            <th style="border:  solid 1px; padding:2px">{{ $total_taxable }}</th>
                        </tr>
                        {{-- <tr>
                            <th style="border:  solid 1px; padding:2px">Add CGST</th>
                            <th style="border:  solid 1px; padding:2px">{{ $cgst_amt }}</th>
                        </tr>
                        <tr>
                            <th style="border:  solid 1px; padding:2px">Add SGST</th>
                            <th style="border:  solid 1px; padding:2px">{{ $sgst_amt }}</th>
                        </tr>
                        <tr>
                            <th style="border:  solid 1px; padding:2px">Add IGST</th>
                            <th style="border:  solid 1px; padding:2px">{{ $igst_amt }}</th>
                        </tr> --}}
                        <tr>
                            <th style="border:  solid 1px; padding:2px">Tax Amt. GST</th>
                            <th style="border:  solid 1px; padding:2px">{{ $total_gst }}</th>
                        </tr>
                        <tr>
                            <th style="border:  solid 1px; padding:2px">Tax Amt. Cess</th>
                            <th style="border:  solid 1px; padding:2px">{{ $total_cess }}</th>
                        </tr>
                        <tr>
                            <th style="border:  solid 1px; padding:2px">Amount After Tax</th>
                            <th style="border:  solid 1px; padding:2px">{{ $gross_total }}</th>
                        </tr>
                        <tr>
                            <th style="border:  solid 1px; padding:2px">TCS Charges</th>
                            <th style="border:  solid 1px; padding:2px">00</th>
                        </tr>
                        <tr>
                            <th style="border:  solid 1px; padding:2px">Round OFF Total</th>
                            <th style="border:  solid 1px; padding:2px">{{ round($gross_total, 0, PHP_ROUND_HALF_UP) }}
                            </th>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
