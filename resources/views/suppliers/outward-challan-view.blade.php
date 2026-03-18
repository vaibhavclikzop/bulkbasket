@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title> Pick Ticket View </title>
    @endpush



    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    Pick Ticket View
                </div>
                <button type="button" onclick="printcontent()" class="btn btn-primary"><i class="fa fa-print"
                        aria-hidden="true"></i> Print</button>

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
                    </thead>
                    <tbody>
                        @php

                            $sno = 1;

                        @endphp
                        @foreach ($order_det as $item)
                            <tr>
                                <td style="border:  solid 1px; padding:2px">{{ $sno++ }}</td>
                                <td style="border:  solid 1px; padding:2px">{{ $item->product }}</td>
                                <td style="border:  solid 1px; padding:2px">{{ $item->article_no }}</td>
                                <td style="border:  solid 1px; padding:2px">{{ $item->qty }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
                        <h6 style="font-size: 15px">This is computer generated Purchase order no signature required</h6>
                    </div>

                </div>
            </div>

        </div>
    </div>
@endsection
