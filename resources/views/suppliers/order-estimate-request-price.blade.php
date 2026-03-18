@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title>Request For Price  </title>
    @endpush

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <h5>
                    Request For Price  
                </h5>

                {{-- <button type="button" onclick="printcontent()" class="btn btn-primary">
                    <i class="fa fa-print" aria-hidden="true"></i> Print
                </button> --}}
            </div>
        </div>

        <div class="card-body" id="PrintOrder">
            {{-- <div style="display: flex; justify-content: space-between; border: solid 1px; padding: 8px;">
                <div style="padding: 5px; border:solid 1px; width: 50%">
                    (Billed To) {{ $orders->customer_name }}
                    <p>
                        Contact : {{ $orders->customer_number }} <br>
                        Address : {{ $orders->customer_address }}, {{ $orders->customer_district }},
                        {{ $orders->customer_city }}, {{ $orders->customer_state }},
                        {{ $orders->customer_pincode }} <br>
                    </p>
                </div>
                <div style="padding: 5px; border:solid 1px; width: 50%">
                    (Shipped To) {{ $orders->name ?? '-' }}
                    <p>
                        Contact : {{ $orders->number ?? '-' }} <br>
                        Address : {{ $orders->address ?? '-' }}, {{ $orders->district ?? '-' }},
                        {{ $orders->city ?? '-' }},
                        {{ $orders->state ?? '-' }}, {{ $orders->pincode ?? '-' }} <br>
                    </p>
                </div>
            </div> --}}

            <div class="mt-3">
                <table class="w-100 table table-bordered">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Product</th>
                            <th>HSN Code</th>
                            <th>UOM</th>
                            <th>MRP</th>
                            <th>Qty</th>
                            <th>Rate</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $sno = 1; @endphp
                        @foreach ($requests as $request)
                            @foreach ($request->items as $item)
                                <tr>
                                    <td>{{ $sno++ }}</td>
                                    <td>
                                        {{ $item->product_name }}
                                    </td>
                                    <td>{{ $item->hsn_code ?? '-' }}</td>
                                    <td>{{ $item->uom ?? '-' }}</td>
                                    <td>{{ $item->mrp ?? '0.00' }}</td>
                                    <td>{{ $item->qty }}</td>
                                    <td>{{ $item->price }}</td>
                                    <td>{{ number_format($item->qty * $item->price, 2) }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>


        </div>
    </div>
@endsection
