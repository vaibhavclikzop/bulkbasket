@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title>Invoice View </title>
    @endpush



    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    Invoice View
                </div>
                <div>
                    @if ($data->is_invoice == 0 && $data->status != 'cancel')
                        <button class="btn btn-dark convertInvoice" type="button" value="{{ $data->id }}"
                            data-bs-toggle="tooltip" data-bs-placement="top" title="Convert this ticket to invoice">
                            <i class="fa fa-shuffle" aria-hidden="true"></i>
                        </button>
                    @endif
                    <button type="button" onclick="printcontent()" class="btn btn-primary"><i class="fa fa-print"
                            aria-hidden="true"></i> Print</button>
                </div>
            </div>
        </div>
        <div class="card-body" id="PrintOrder">
            <div style="justify-content: space-between; border: solid 1px;margin-top:5px">
                <div style="text-align: center">
                    <h4 class="mt-4" style="font-size: 28px; font-weight: bolder">{{ $data->supplier_name }}</h4>
                    <p style="font-size: 14px">
                        {!! $data->supplier_address !!}
                        <br>
                        <span style="text-transform: none"> Phone :{{ $data->supplier_number }}
                            E-Mail : {{ $data->supplier_email }} </span>
                        <br>
                        GST : {{ $data->supplier_gst }} <br>
                    </p>
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; border: solid 1px; padding: 8px;">
                <div style="padding: 5px; border:solid 1px; width: 50%">

                    <strong>BILL TO : </strong>
                    <p style=" ">{{ $data->customer_name }}

                        <br>
                        {{ $data->address }}, <br>
                        {{ $data->city }},
                        {{ $data->state }},
                        {{ $data->pincode }}
                        <br>
                        Contact : {{ $data->number }} <br>
                        GST/UID : {{ $data->gst }}
                    </p>
                </div>
                <div style="padding: 5px; border:solid 1px; width: 50%">
                    <strong>DELIVERY TO : </strong>
                    <p style=" ">{{ $data->customer_name }}

                        <br>
                        {{ $data->address }}, <br>
                        {{ $data->city }},
                        {{ $data->state }},
                        {{ $data->pincode }}
                        <br>
                        Contact : {{ $data->number }} <br>
                        GST/UID : {{ $data->gst }}
                    </p>
                </div>
            </div>


            <div class="">
                <table class="w-100">
                    <thead>
                        <th style="border:  solid 1px; padding:2px">S.No</th>
                        <th style="border:  solid 1px; padding:2px">Description of goods</th>
                        <th style="border:  solid 1px; padding:2px">Article No</th>
                        <th style="border:  solid 1px; padding:2px">Qty</th>
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
                            $igst_amt = 0;
                            $cgst_amt = 0;
                            $sgst_amt = 0;
                            $total_mrp = 0;
                        @endphp
                        @foreach ($order_det as $item)
                            @php
                                $total =
                                    $item->price * $item->qty +
                                    (($item->price * $item->qty) / 100) * $item->gst +
                                    (($item->price * $item->qty) / 100) * $item->cess_tax;
                                $qty += $item->qty;
                                $total_taxable += $item->price * $item->qty;
                                $total_gst += (($item->price * $item->qty) / 100) * $item->gst;
                                $total_cess += (($item->price * $item->qty) / 100) * $item->cess_tax;
                                $gross_total += $total;
                                $total_mrp += $item->price * $item->qty;
                            @endphp
                            <tr>
                                <td style="border:  solid 1px; padding:2px">{{ $sno++ }}</td>
                                <td style="border:  solid 1px; padding:2px">{{ $item->product }}</td>
                                <td style="border:  solid 1px; padding:2px">{{ $item->article_no }}</td>
                                <td style="border:  solid 1px; padding:2px">{{ $item->qty }}</td>
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
                            <td style="border:  solid 1px; padding:2px"></td>
                            <th style="border:  solid 1px; padding:2px" colspan="2">Total</th>
                            <th style="border:  solid 1px; padding:2px">{{ $qty }}</th>
                            <th style="border:  solid 1px; padding:2px"></th>
                            <th style="border:  solid 1px; padding:2px">{{ $total_taxable }}</th>
                            <th style="border:  solid 1px; padding:2px"></th>
                            <th style="border:  solid 1px; padding:2px">{{ $total_gst }}</th>
                            <th style="border:  solid 1px; padding:2px"></th>
                            <th style="border:  solid 1px; padding:2px">{{ $total_cess }}</th>
                            <th style="border:  solid 1px; padding:2px"> {{ round($gross_total, 0, PHP_ROUND_HALF_UP) }}
                            </th>
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
            <div style="display: flex; justify-content: space-between;  ">
                <div style="padding: 5px; border:solid 1px; width: 100%">

                    <div class="" style="display: flex; justify-content: space-between">
                        <div>

                        </div>
                        <div>
                            {{ $data->supplier_name }}
                            <br>
                            <br>
                            <br>
                            Auth. Signatory
                        </div>
                    </div>
                    <div style="margin-top:20px; text-align: center">
                        <h6 style="font-size: 15px">This is computer generated Proforma Invoice no signature required</h6>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <form action="{{ route('supplier/convertToInvoice') }}" method="POST">
        @csrf
        <div class="modal fade" id="invoiceModal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitleId">
                            Convert To Invoice
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="invoiceID" name="id">
                        You are going to convert this Ticket to invoice <br>
                        <div class="d-none">
                            <label for="">Invoice Amount</label>
                            <input type="text" id="invoiceAmt" disabled class="form-control">
                            <label for="" class="mt-3">Additional Discount</label>
                            <input type="number" step="0.01" name="discount" class="form-control" required
                                value="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        $(document).on("click", ".convertInvoice", function() {
            $("#invoiceID").val($(this).val())
            $("#invoiceModal").modal("show");
        });
    </script>
@endsection
