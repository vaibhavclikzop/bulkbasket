@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title>Pick Ticket List</title>
    @endpush

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    <h5>Pick Ticket List</h5>
                </div>

            </div>
        </div>


        <div class="card-body">
            <table class="table dataTable">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>PT ID</th>
                        <th>Customer</th>
                        <th>Order ID</th>
                        <th>Invoice Amt.</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sno = 1;
                    @endphp
                    @foreach ($outward as $item)
                        <tr>
                            <td>{{ $sno++ }}</td>
                            <td>{{ $item->outward_id }}</td>
                            <td>{{ $item->customer_name }}</td>
                            <td>{{ $item->e_order_id }}</td>
                            <td>{{ number_format($item->invoice_amount, 2) }}
                            </td>
                            <td>{{ $item->outward_status }}</td>
                            <td>
                                {{-- @if ($item->is_invoice == 0 && $item->status == 'pending') --}}
                                @if ($item->is_invoice == 0 && $item->outward_status == 'pending')
                                    <button class="btn btn-danger btn-sm cancelOrder" type="button"
                                        value="{{ $item->id }}" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Cancel this ticket">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                    <a class="btn btn-success btn-sm"
                                        href="/supplier/outward-stock?out_id={{ $item->id }}" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Edit Ticket">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                @endif
                                @if ($item->dispatch_status == 'pending' && $item->status != 'cancel' && $item->is_invoice == 1)
                                    <button class="btn btn-info btn-sm dispatch" value="{{ $item->id }}"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Send to dispatch">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </button>
                                @endif
                                <a class="btn btn-primary btn-sm" href="/supplier/outward-challan-view/{{ $item->id }}"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="View Ticket">
                                    View Ticket
                                </a>
                                <a class="btn btn-info btn-sm" href="/supplier/invoice-view/{{ $item->id }}"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="View Invoice">View Invoice</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <form action="{{ route('supplier/cancelOutwardChallan') }}" method="POST" class="needs-validation" novalidate>
        @csrf
        <div class="modal fade" id="cancelTicketModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
            role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger">
                        <h5 class="modal-title text-white" id="modalTitleId">
                            Cancel
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="ChallanID">
                        Are you sure you want to cancel this pick ticket? <br>
                        Once cancelled, this action cannot be undone.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-danger">Yes, Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <script>
        $(document).on("click", ".cancelOrder", function() {
            $("#ChallanID").val($(this).val())
            $("#cancelTicketModal").modal("show")
        })
    </script>
@endsection
