@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title> Vendor List</title>
    @endpush



    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    Vendor List
                </div>
                <div>
                    <button class="btn btn-primary addVendor" type="button">Add Vendor</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            @php
                $sno = 1;
            @endphp
            <table class="table dataTable">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Vendor Code</th>
                        <th>Company</th>
                        <th>GST</th>
                        <th>PAN NO</th>
                        {{-- <th>Whatsapp Number</th> --}}
                        <th>Email</th>
                        <th>Address</th>
                        <th>Action</th>

                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $item)
                        <tr>
                            <td>{{ $sno++ }}</td>
                            <td>{{ $item->vendor_code }}</td>
                            <td>{{ $item->company }}</td>
                            <td>{{ $item->gst }}</td>
                            <td>{{ $item->pan_no }}</td>
                            {{-- <td>{{ $item->whatsapp_number ?? '--' }}</td> --}}
                            <td>{{ $item->email }}</td>
                            <td>{{ $item->address }}</td>
                            <td>
                                <button class="btn btn-primary btn-sm editVendor" data-data="{{ @json_encode($item) }}"
                                    type="button"><i class="fa fa-pencil" aria-hidden="true"></i></button>
                                <a href="/supplier/vendor-product-list/{{ $item->id }}" class="btn btn-info btn-sm "
                                    type="button"><i class="fa fa-eye" aria-hidden="true"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>

        </div>
    </div>

    <div class="modal fade" id="modalId" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
        aria-labelledby="modalTitleId" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form action="{{ route('supplier/saveVendor') }}" id="vdrForm" method="POST" class="needs-validation"
                    novalidate enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitleId">
                            Modal title
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" name="id" id="id">
                            <div class="col-md-4">
                                <label for="">Vendor Code</label>
                                <input type="text" name="vendor_code" id="vendor_code" class="form-control"
                                    value="{{ $nextVndr }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="">Company Legal Name <span class="text-danger">*</span></label>
                                <input type="text" name="company" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label for="">Type Of Dealer  <span class="text-danger">*</span></label>
                                <select name="dealer_type" id="dealer_type" class="form-control" required>
                                    <option value="">Select Dealer</option>

                                    <option value="registered">Registered</option>
                                    <option value="unregistered">Un Registered</option>
                                    <option value="composition">Composition</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="col-md-4 mt-3">
                                <label for="">GST No</label>
                                <input type="text" name="gst" id="gst" class="form-control">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="">PAN No</label>
                                <input type="text" name="pan_no" id="pan_no" class="form-control">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="">FSSAI NO</label>
                                <input type="text" name="fssai_no" id="fssai_no" class="form-control" re>
                            </div>
                            <div class="col-md-8 mt-3">
                                <label for="">Address 1  <span class="text-danger">*</span></label>
                                <input type="" name="address1" id="address1" class="form-control"
                                    placeholder="Line 1" required>
                            </div>
                            <div class="col-md-8 mt-3">
                                <label for="">Address 2</label>
                                <input type="" name="address2" id="address2" class="form-control"
                                    placeholder="Line 2">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="">State  <span class="text-danger">*</span></label>
                                <select name="state" id="state" class="form-control">
                                    <option value="">Select</option>
                                    @foreach ($state as $item)
                                        <option value="{{ $item->state }}">{{ $item->state }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="">District  <span class="text-danger">*</span></label>
                                <select name="district" id="district" class="form-control" required>
                                    <option value="">Select</option>
                                </select>
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="">City  <span class="text-danger">*</span></label>
                                <input type="" name="city" id="city" class="form-control" required>
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="">Pincode</label>
                                <input type="" name="pincode" class="form-control">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for=""> Contact Person</label>
                                <input type="text" name="name" class="form-control" placeholder="Name">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="">Number</label>
                                <input type="number"  name="number" class="form-control" placeholder="Mob. No">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="">Whatsapp Number</label>
                                <input type="whatsapp_no" name="whatsapp_no" class="form-control">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="">Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script>
        $(".addVendor").on("click", function() {
            $("#modalTitleId").text("Add Vendor")
            let form = $("#vdrForm")[0];
            form.reset();
            $("#id").val("");
            $("#modalId").modal("show")
        });

        $(document).on("click", ".editVendor", function() {
            $("#modalTitleId").text("Edit Vendor")
            let form = $("#vdrForm")[0];
            form.reset();
            var data = $(this).data("data");
            $.each(data, function(i, o) {
                $("input[name=" + i + "]").val(o)
                $("select[name=" + i + "]").val(o)
            });

            $("#modalId").modal("show")
        });
    </script>
    <script>
        $(document).on("input", "#gst", function() {
            let gst = $(this).val().toUpperCase().trim();
            $(this).val(gst);
            if (gst.length === 15) {
                let pan = gst.substring(2, 12);
                $("#pan_no").val(pan);
            } else {
                $("#pan_no").val("");
            }
        });
    </script>
@endsection
