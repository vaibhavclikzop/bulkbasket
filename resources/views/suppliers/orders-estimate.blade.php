@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title> Orders Challan </title>
    @endpush



    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    Orders Challan {{ ucfirst($status) }}
                </div>

            </div>
        </div>
        <div class="card-body">
            <table class="table dataTable">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Customer Name</th>
                        <th>Customer Number</th>
                        <th>Order Id</th>
                        <th>Delivery Date</th>
                        {{-- <th>Email</th> --}}
                        {{-- <th>Address</th>  --}}
                        <th>Order Value</th>
                        <th>Invoice Value</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sno = 1;
                    @endphp
                    @foreach ($data as $item)
                        <tr>
                            <td>{{ $sno++ }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->number }}</td>
                            <td>#{{ $item->id }}</td>
                            <td>{{ $item->delivery_date }}</td>
                            {{-- <td>{{ $item->email }}</td> --}}
                            {{-- <td>{{ $item->address }}, {{ $item->city }}, {{ $item->district }}, {{ $item->state }},
                                {{ $item->pincode }}</td> --}}
                            <td>{{ $item->subtotal }}</td>
                            <td>{{ $item->total_amount ?? '0.00' }}</td>
                            <td>{{ $item->order_status }}</td>
                            <td>
                                @if (strtolower($item->order_status) == 'pending')
                                    <button class="btn btn-primary btn-sm estimate-edit"
                                        data-data='@json(['id' => $item->id, 'order_status' => $item->order_status])'>
                                        <i class="fa fa-pencil" aria-hidden="true"></i>
                                    </button>
                                    <a href="{{ url('/supplier/order-estimate-details/' . $item->id) }}"
                                        class="btn btn-primary btn-sm">
                                        <i class="fa fa-eye" aria-hidden="true"></i>
                                    </a>
                                    {{-- <a href="{{ url('/supplier/order-estimate-request-price/' . $item->id) }}"
                                        class="btn btn-info btn-sm">
                                        Request For Price
                                    </a> --}}
                                @elseif (strtolower($item->order_status) == 'processing')
                                    <a href="{{ url('/supplier/order-estimate-edit/' . $item->id) }}"
                                        class="btn btn-primary btn-sm">
                                        <i class="fa fa-pencil" aria-hidden="true"></i>
                                    </a>
                                    <a href="{{ url('/supplier/order-estimate-details/' . $item->id) }}"
                                        class="btn btn-primary btn-sm">
                                        <i class="fa fa-eye" aria-hidden="true"></i>
                                    </a>
                                @elseif(strtolower($item->order_status) == 'complete')
                                    <a href="{{ url('/supplier/order-estimate-details/' . $item->id) }}"
                                        class="btn btn-primary btn-sm">
                                        <i class="fa fa-eye" aria-hidden="true"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <form action="{{ route('supplier/EditEstimateOrder') }}" method="POST" class="needs-validation" novalidate>
        @csrf
        <div class="modal fade" id="modalId" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
            role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitleId">Edit Order Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="id" name="id">
                        <label for="order_status" class="mt-3">Select Status</label>
                        <select name="order_status" id="order_status" class="form-control" required>
                            <option value="">Select</option>
                            <option value="Processing">Processing</option>
                            <option value="Cancel">Cancel</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        $(document).on("click", ".estimate-edit", function() {
            var data = $(this).data("data");
            $("#id").val(data.id);
            $("#modalId").modal("show");
        });
    </script>
@endsection
