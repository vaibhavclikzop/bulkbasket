@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title> Orders Management</title>
    @endpush



    <div class="card">
        <div class="card-header">

            <div class="d-flex justify-content-between">
                <div>
                    <h5> Orders Management</h5>
                </div>
                {{-- <div class="mb-3">
                    <a href="{{ route('supplier/orders', ['status' => 'all']) }}"
                        class="btn btn-md {{ $status == 'all' ? 'btn-primary' : 'btn-outline-primary' }}">All</a>

                    <a href="{{ route('supplier/orders', ['status' => 'packed']) }}"
                        class="btn btn-md {{ $status == 'packed' ? 'btn-primary' : 'btn-outline-primary' }}">Packed</a>

                    <a href="{{ route('supplier/orders', ['status' => 'dispatch']) }}"
                        class="btn btn-md {{ $status == 'dispatch' ? 'btn-primary' : 'btn-outline-primary' }}">Dispatch</a>

                    <a href="{{ route('supplier/orders', ['status' => 'delivered']) }}"
                        class="btn btn-md {{ $status == 'delivered' ? 'btn-primary' : 'btn-outline-primary' }}">Delivered</a>
                </div> --}}
            </div>
        </div>
        <div class="card-body">
            <table class="table dataTable">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Order ID</th>
                        <th>Invocie No</th>
                        <th>Order Date</th>
                        <th>Name</th>
                        {{-- <th>Email</th> --}}
                        <th>Phone</th>
                        <th>Total</th>
                        <th>Payment</th>
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
                            <td>{{ $item->e_order_id }}</td>
                            {{-- <td>{{ $item->order_status === 'dispatch' ? $item->invoice_no : '-' }}</td> --}}
                            <td>{{ $item->invoice_no }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d M \a\t g:i A') }}</td>
                            <td>{{ $item->customer_name }}</td>
                            {{-- <td>{{ $item->customer_email }}</td> --}}
                            <td>{{ $item->customer_phone }}</td>
                            <td>{{ $item->total_amount }}</td>
                            <td>{{ ucfirst($item->pay_mode) }}</td>
                            <td>{{ ucfirst($item->order_status) }}</td>
                            <td>
                                {{-- <button class="btn btn-dark btn-sm editStatus" type="button"
                                    value="{{ $item->supplier_order_id }}" data-status="{{ $item->status }}"> <i
                                        class="fa fa-pencil" aria-hidden="true"></i>
                                </button> --}}
                                @if ($item->status == 'processing')
                                    <a href="/supplier/order-details/{{ $item->estimate_id }}"
                                        class="btn btn-primary btn-sm">
                                        <i class="fa fa-eye" aria-hidden="true"></i> </a>
                                    <a href="/supplier/outward-stock?customer_id={{ $item->customer_id }}&order_id={{ $item->id }}"
                                        class="btn btn-primary btn-sm">
                                        Rise Pick Ticket</a>
                                @endif
                                @if ($item->status == 'pending')
                                    <a href="/supplier/order-details/{{ $item->estimate_id }}"
                                        class="btn btn-primary btn-sm">
                                        <i class="fa fa-eye" aria-hidden="true"></i> </a>
                                    <a href="/supplier/outward-stock?customer_id={{ $item->customer_id }}&order_id={{ $item->id }}"
                                        class="btn btn-primary btn-sm">
                                        Rise Pick Ticket</a>
                                @endif
                                {{-- @if ($item->status == 'processing')
                                    <a href="/supplier/order-details/{{ $item->estimate_id }}"
                                        class="btn btn-primary btn-sm">
                                        <i class="fa fa-eye" aria-hidden="true"></i> </a>
                                    <a href="/supplier/outward-stock?customer_id={{ $item->customer_id }}&order_id={{ $item->id }}"
                                        class="btn btn-primary btn-sm">
                                        Rise Pick Ticket</a>
                                @endif --}}
                                @if ($item->status == 'complete')
                                    <a href="/supplier/order-details/{{ $item->estimate_id }}"
                                        class="btn btn-primary btn-sm">
                                        <i class="fa fa-eye" aria-hidden="true"></i> </a>
                                @endif
                                {{-- <a href="/supplier/order-details/{{ $item->estimate_id }}" class="btn btn-primary btn-sm">
                                    Pick Ticket List</a> --}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <form action="{{ route('supplier/UpdateOrderStatus') }}" method="POST" class="needs-validation" novalidate>
        @csrf
        <div class="modal fade" id="statusModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
            role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitleId">
                            Update Status
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="statusId">
                        <label for="">Select Status</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="">Select</option>
                            {{-- <option value="pending">Pending</option>
                            <option value="processing">Processing</option> --}}
                            <option value="packed">Packed</option>
                            <option value="dispatch">Dispatch</option>
                            <option value="delivered">Delivered</option>

                        </select>
                        <label for="" class="mt-3">User </label>
                        <select name="user_id" id="" class="form-control" required>
                            <option value="">Select User</option>
                            @foreach ($suppliers as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
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
        $(document).on("click", ".editStatus", function() {
            $("#statusId").val($(this).val())
            $("#status").val($(this).data("status"))
            $("#statusModal").modal("show")
        })
    </script>
@endsection
