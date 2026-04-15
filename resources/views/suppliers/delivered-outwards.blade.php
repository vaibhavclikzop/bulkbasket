@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title>Dispatch Orders</title>
    @endpush

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    <h5>Dispatch Orders</h5>
                </div>

            </div>
        </div>

        <div class="card-body">
            <table class="table dataTable">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Invoice No </th>
                        {{-- <th>PT ID</th> --}}
                        <th>Customer</th>
                        <th>Order ID</th>
                        <th>Invoice Amt.</th>
                        <th>Vehicle No</th>
                        <th>Drive Name</th>
                        <th>Drive No</th>
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
                            <td>{{ $item->invoice_id }}</td>
                            {{-- <td>{{ $item->outward_id }}</td> --}}
                            <td>{{ $item->customer_name }}</td>
                            <td>#{{ $item->order_id }}</td>
                            <td>{{ round($item->total_amount) }}.00</td>
                            <td>{{$item->vehicle_number ?? "N/A"}}</td>
                            <td>{{$item->driver_name ?? "N/A"}}</td>
                            <td>{{$item->driver_no ?? "N/A"}}</td>
                            <td>{{ $item->status }}</td>
                            <td>
                                <a class="btn btn-info btn-sm" title="View Invoice"
                                    href="/supplier/invoice-view/{{ $item->id }}"><i class="fa fa-eye"
                                        aria-hidden="true"></i></a>
                                {{-- <button class="btn btn-warning btn-sm orderStatus" value="{{ $item->id }}"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Order Status">
                                    Order Status
                                </button> --}}
                                 @if ($item->is_e_invoice == 1)
                                    <a class="btn btn-success btn-sm" title="View E-Invoice" href="{{ $item->EinvoicePdf }}"
                                        target="_blank">E-Invoice</a>
                                @endif
                                @if ((int) $item->is_e_billing === 1 && (int) $item->is_e_invoice === 1 && !empty($item->eway_bill_url))
                                    <a class="btn btn-warning btn-sm" title="View E-Billing"
                                        href="{{ $item->eway_bill_url }}" target="_blank">E-Billing</a>
                                @endif

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <form action="{{ route('supplier/DispatchTransport') }}" method="POST">
        @csrf
        <div class="modal fade" id="dispatchModal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitleId">
                            Allocate Vehicle
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" id="dispatch_id" name="id">
                            <div class="col-md-6">
                                <label>Select Transport</label>
                                <select name="transport_id" id="transport_id" class="form-control">
                                    <option value="">Select vehicle </option>
                                    @foreach ($transport as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}/{{ $item->vehicle_no }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="">Select Date</label>
                                <input type="date" name="transport_date" id="transport_date" class="form-control">
                            </div>
                            <div class="col-md-12 mt-4">
                                <label for="">Remarks</label>
                                <input type="text" name="transport_remarks" id="transport_remarks" class="form-control">
                            </div>
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


    <form action="#" method="POST">
        <div class="modal fade" id="eBillingModal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitleId">
                            Convert E-Invoice To E-Billing
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="invoice_ids" name="invoice_id">
                        Are You Sure You Want E-Billing
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-primary generateEWB">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <form action="{{ route('supplier/DispatchOrderStatus') }}" method="POST">
        @csrf
        <div class="modal fade" id="orderStatusModal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitleId">
                            Order Status
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" id="order_id" name="order_id">
                            <div class="col-md-6">
                                <label>Order Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">Select Status </option>
                                    <option value="delivered">Delivered
                                    </option>
                                    <option value="cancel">Cancel
                                    </option>
                                </select>
                            </div>

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
        $(document).on("click", ".dispatchTransport", function() {
            $("#dispatch_id").val($(this).val())
            $("#dispatchModal").modal("show");
        });
    </script>

    <script>
        let selectedBtns = null;

        // open modal
        $(document).on("click", ".sendEBilling", function() {
            let invoice_id = $(this).val();
            $("#invoice_ids").val(invoice_id);
            selectedBtns = $(this);
            $("#eBillingModal").modal("show");
        });


        // generate EWB
        $(document).on("click", ".generateEWB", function() {

            let confirmBtn = $(this);
            let invoice_id = $("#invoice_ids").val(); // ✅ FIXED

            $.ajax({
                url: "{{ route('/supplier/generateEwayBill') }}",
                type: "POST",
                data: {
                    invoice_id: invoice_id
                },
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },

                beforeSend: function() {
                    confirmBtn.prop("disabled", true).text("Processing...");
                },

                success: function(res) {

                    if (res.status === true) {

                        toastr.success(res.message || "Success");

                        $("#eBillingModal").modal("hide");
                        confirmBtn.prop("disabled", false).text("Save");
                        if (selectedBtns) {
                            selectedBtns
                                .prop("disabled", true)
                                .removeClass("btn-info")
                                .addClass("btn-success")
                                .html('<i class="fa-solid fa-check"></i> EWB Done');
                        }

                    } else {

                        let msg =
                            res?.error?.results?.errorMessage ||
                            res?.message ||
                            "Error";

                        toastr.error(msg);

                        confirmBtn.prop("disabled", false).text("Save");
                    }
                },

                error: function(xhr) {

                    let msg =
                        xhr.responseJSON?.error?.results?.errorMessage ||
                        xhr.responseJSON?.message ||
                        "Something went wrong";

                    toastr.error(msg);

                    confirmBtn.prop("disabled", false).text("Save");
                }
            });
        });
    </script>

    <script>
        $(document).on("click", ".orderStatus", function() {
            $("#order_id").val($(this).val())
            $("#orderStatusModal").modal("show");
        });
    </script>
@endsection
