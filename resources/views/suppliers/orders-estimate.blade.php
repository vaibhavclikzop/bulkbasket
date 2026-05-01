@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title> Orders Challan </title>
    @endpush


    <style>
        .dropdown-menu {
            z-index: 1000 !important;
        }
    </style>
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    Orders Challan {{ ucfirst($status) }}
                </div>

            </div>
        </div>
        <div class="card-body">
           <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover table-responsive" style="overflow: scroll">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Customer Name</th>
                        <th>Order Id</th>
                        <th>Order Value</th>
                        <th>Pay mode</th>
                        <th>Delivery Date</th>
                        <th>Platform</th>
                        {{-- <th>Status</th> --}}
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
                            <td>{{ $item->order_id }}</td>
                            <td>{{ $item->subtotal }}</td>
                            <td>{{ ucfirst($item->pay_mode) }}</td>
                            <td>{{ $item->delivery_date }}</td>
                            <td>--</td>
                            {{-- <td>{{ $item->order_status }}</td> --}}
                            <td>
                                <div class="dropdown">
                                    <span class="dropdown-toggle" id="dropdownMenuButton7" data-bs-toggle="dropdown"
                                        aria-expanded="false" role="button"> Click Here
                                    </span>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton7"
                                        >
                                        @if (strtolower($item->order_status) == 'pending')
                                            <div class="mt-2">
                                                <button class="btn btn-primary btn-sm estimate-edit"
                                                    data-data='@json(['id' => $item->id, 'order_status' => $item->order_status])'>
                                                    Status
                                                </button>
                                            </div>
                                            <div class="mt-2">
                                                <a href="{{ url('/supplier/edit-challan/' . $item->id) }}"
                                                    class="btn btn-warning btn-sm">
                                                    Edit Challan
                                                </a>
                                            </div>
                                            <div class="mt-2">
                                                <a href="{{ url('/supplier/order-estimate-details/' . $item->id) }}"
                                                    class="btn btn-info btn-sm">
                                                    View Challan
                                                </a>
                                            </div>
                                        @elseif (strtolower($item->order_status) == 'processing')
                                            <div class="mt-2">
                                                <a href="{{ url('/supplier/order-estimate-edit/' . $item->id) }}"
                                                    class="btn btn-primary btn-sm">
                                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                            <div class="mt-2">
                                                <a href="{{ url('/supplier/order-estimate-details/' . $item->id) }}"
                                                    class="btn btn-primary btn-sm">
                                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                        @elseif(strtolower($item->order_status) == 'complete')
                                            <div class="mt-2">
                                                <a href="{{ url('/supplier/order-estimate-details/' . $item->id) }}"
                                                    class="btn btn-primary btn-sm">
                                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                            <div class="mt-2">
                                        @endif
                                    </div>
                                </div>
                            </td>
                            {{-- <td>
                                @if (strtolower($item->order_status) == 'pending')
                                    <button class="btn btn-primary btn-sm estimate-edit"
                                        data-data='@json(['id' => $item->id, 'order_status' => $item->order_status])'>
                                        Status
                                    </button>
                                    <a href="{{ url('/supplier/edit-challan/' . $item->id) }}"
                                        class="btn btn-info btn-sm">
                                       <i class="fa fa-pencil" aria-hidden="true"></i>
                                    </a>
                                    <a href="{{ url('/supplier/order-estimate-details/' . $item->id) }}"
                                        class="btn btn-primary btn-sm">
                                        <i class="fa fa-eye" aria-hidden="true"></i>
                                    </a>
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
                            </td> --}}
                        </tr>
                    @endforeach
                </tbody>
            </table>
           </div>
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
