@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title>Material Receipt Note</title>
    @endpush

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    <h5>Material Receipt Note</h5>
                </div>
                <div>
                    <form action="" class="d-flex">
                        <div>
                            <label for="">From Date</label>
                            <input type="date" name="fromDt" class="form-control" value="{{ request('fromDt') }}">
                        </div>
                        <div class="mx-2">
                            <label for="">To Date</label>
                            <input type="date" name="toDt" class="form-control" value="{{ request('toDt') }}">
                        </div>
                        <div class="mx-2">

                            <button class="btn btn-primary mt-4" type="submit">Search</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 text-end">
                    <div class="mb-3">
                        <a class="btn btn-secondary btn-sm" href="?">All</a>
                        <a class="btn btn-success btn-sm " href="?status=approved">Approved</a>
                        <a class="btn btn-warning btn-sm " href="?status=unapproved">Unapproved</a>
                    </div>
                </div>
            </div>
            <div class="custom-table-effect table-responsive  border rounded">
                <table class="table mb-0" id="datatable" data-toggle="data-table">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>PO ID</th>
                            <th>PO Name</th>
                            <th>Vendor</th>
                            <th>Invoice</th>
                            <th>Invoice Document</th>
                            <th>Invoice Date</th>
                            <th>R.M Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $sno = 1;
                        @endphp
                        @foreach ($stock_inward_mst as $item)
                            <tr data-status="{{ $item->is_current_stock }}">
                                <td>{{ $sno++ }}</td>
                                <td>{{ $item->po__id }}</td>
                                <td>{{ $item->po_name }}</td>
                                <td>{{ $item->vendor }}</td>
                                <td>{{ $item->invoice_no }}</td>
                                <td>
                                    @if (!empty($item->invoice_file))
                                        <a href="{{ asset('/invoice-pdf/' . $item->invoice_file) }}" target="_blank">
                                            View File
                                        </a>
                                    @else
                                        No File
                                    @endif
                                </td>
                                <td>{{ $item->invoice_date }}</td>
                                <td>{{ $item->received_material_date }}</td>
                                <td>
                                    @if ($item->is_current_stock == 0)
                                        <button class="btn btn-sm btn-warning purchaseApprove"
                                            value="{{ $item->id }}">Approve</button>
                                    @endif
                                    <a class="btn btn-success btn-sm" href="/supplier/inward-stock/{{ $item->id }}">
                                        <i class="fa fa-pen" aria-hidden="true"></i>
                                    </a>

                                    <a class="btn btn-info btn-sm"
                                        href="/supplier/inward-report-view/{{ $item->id }}"><i class="fa fa-eye"
                                            aria-hidden="true"></i></a>
                                    <a class="btn btn-primary btn-sm"
                                        href="/supplier/inward-report-slip/{{ $item->id }}">WH Slip</a>

                                    {{-- <button class="btn btn-danger btn-sm btnDelete" type="button" value="{{ $item->id }}"><i
                                        class="fa fa-trash" aria-hidden="true"></i></button> --}}
                                </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <form action="{{ route('supplier/deleteStockInward') }}" method="POST" class="needs-validation" novalidate>
        @csrf
        <div class="modal fade" id="deleteModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
            role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitleId">
                            Delete
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" hidden name="id" id="id">
                        You are going to delete this MRN
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


    <form action="{{ route('supplier/approveStockInward') }}" method="POST" class="needs-validation" novalidate>
        @csrf
        <div class="modal fade" id="purchaseApproveModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
            role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">
                            MRN Approval Confirmation
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <input type="number" hidden name="id" id="pid">
                        <p class="mb-2">
                            You are about to approve this <strong>Material Receipt Note (MRN)</strong>.
                        </p>
                        <div class="alert alert-warning mb-0">
                            <strong>Important Notice:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Stock will be updated in the current inventory.</li>
                                <li>This MRN will become <strong>locked</strong> after approval.</li>
                                <li>No further edits or updates will be allowed.</li>
                                <li>This action cannot be undone.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            Approve MRN
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </form>



    <script>
        $(document).on("click", ".btnDelete", function() {
            $("#id").val($(this).val())
            $("#deleteModal").modal("show")
        });

        $(document).on("click", ".purchaseApprove", function() {
            $("#pid").val($(this).val())
            $("#purchaseApproveModal").modal("show")
        });
    </script>
@endsection
