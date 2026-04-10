@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title>Invoices List</title>
    @endpush

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    <h5>Invoices List</h5>
                </div>

            </div>
        </div>

        <div class="card-body">
            <table class="table dataTable">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Invoice No </th>
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
                            <td>{{ $item->invoice_id }}</td>
                            <td>{{ $item->outward_id }}</td>
                            <td>{{ $item->customer_name }}</td>
                            <td>#{{ $item->order_id }}</td>
                            <td>{{ round($item->total_amount) }}.00</td>
                            <td>{{ $item->status }}</td>
                            <td>
                                @if ($item->is_invoice == 0)
                                    <button class="btn btn-primary btn-sm convertInvoice" type="button"
                                        value="{{ $item->id }}">Convert to Invoice</button>
                                @endif
                                <a class="btn btn-info btn-sm" title="View Invoice"
                                    href="/supplier/invoice-view/{{ $item->id }}"><i class="fa fa-eye"
                                        aria-hidden="true"></i></a>
                                @if ($item->is_e_invoice == 0)
                                    <button class="btn btn-success btn-sm sendEInvoice" value="{{ $item->id }}"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Convert Invoice to E-Invoice">
                                        <i class="fa-solid fa-file"></i>
                                    </button>
                                @endif
                                @if ($item->dispatch_status == 'pending' && $item->status != 'cancel' && $item->is_invoice == 1)
                                    <button class="btn btn-warning btn-sm dispatch" value="{{ $item->id }}"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Send to dispatch">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </button>
                                @endif
                                @if ($item->is_e_invoice == 1)
                                    <a class="btn btn-info btn-sm" title="View E-Invoice" href="{{ $item->EinvoicePdf }}"
                                        target="_blank">E-Invoice</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <form action="{{ route('supplier/DispatchChallan') }}" method="POST">
        @csrf
        <div class="modal fade" id="dispatchModal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitleId">
                            Send to dispatch plan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="dispatch_id" name="id">
                        You are going to send this order to dispatch plan.
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
        <div class="modal fade" id="eInvocieModal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitleId">
                            Convert Invoice To E-Invoice
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="invoice_id" name="invoice_id">
                        Are You Sure You Want Convert Invoice to E-Invoice
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-primary confirmEInvoice">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        $(document).on("click", ".dispatch", function() {
            $("#dispatch_id").val($(this).val())
            $("#dispatchModal").modal("show");
        });
    </script>

    <script>
        let selectedBtn = null;

        $(document).on("click", ".sendEInvoice", function() {
            $("#invoice_id").val($(this).val());
            selectedBtn = $(this);
            $("#eInvocieModal").modal("show");
        });

        $(document).on("click", ".confirmEInvoice", function() {

            let invoice_id = $("#invoice_id").val();
            let btn = selectedBtn;
            let confirmBtn = $(this);

            $.ajax({
                url: "{{ route('/supplier/generateEInvoice') }}",
                type: "POST",
                data: {
                    invoice_id: invoice_id,
                },
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },

                beforeSend: function() {
                    confirmBtn.prop("disabled", true).text("Processing...");
                },

                success: function(res) {
                    if (res.status === true) {
                        toastr.success(res.message || "Success", "success");
                        $("#eInvocieModal").modal("hide");
                        confirmBtn.prop("disabled", false).text("Save");
                    } else {

                        let msg =
                            res?.error?.results?.errorMessage ||
                            res?.message ||
                            "Something went wrong";

                        toastr.error(msg, "error");

                        confirmBtn.prop("disabled", false).text("Save");
                        if (btn) btn.prop("disabled", false);
                    }
                },

                error: function(xhr) {

                    let msg =
                        res?.error?.results?.errorMessage ||
                        res?.message ||
                        "Something went wrong";

                    toastr.error(msg, "error");
                    confirmBtn.prop("disabled", false).text("Save");
                    if (btn) btn.prop("disabled", false);
                }
            });
        });
    </script>
@endsection
