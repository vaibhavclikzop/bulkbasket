@extends('admin.layouts.main')
@section('main-section')
    @push('title')
        <title>Dashboard Suppliers</title>
    @endpush


    <div class="content-inner container-fluid pb-0" id="page_layout">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between">
                    <div>
                        Suppliers
                    </div>
                    <div>
                        <button class="btn btn-primary add" type="button">Add</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="table dataTable">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Name</th>
                            <th>Number</th>
                            <th>Email</th>
                            <th>GST</th>
                            <th>Address</th>
                            <th>State</th>
                            <th>District</th>
                            <th>City</th>
                            <th>Pincode</th>
                            <th>Active</th>
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
                                <td>{{ $item->email }}</td>
                                <td>{{ $item->gst }}</td>
                                <td>{{ $item->address }}</td>
                                <td>{{ $item->state }}</td>
                                <td>{{ $item->district }}</td>
                                <td>{{ $item->city }}</td>
                                <td>{{ $item->pincode }}</td>
                                <td><button type="button" class="btn btn-sm btn-primary rounded-pill Edit"
                                        data-data='@json($item)'>
                                        Edit
                                    </button>
                                    <a href="/s1/supplier-users/{{ $item->id }}"><button
                                            class="btn btn-sm btn-info rounded-pill">View</button></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <form action="{{ route('s1/SaveSuppliers') }}" method="POST" class="needs-validation" novalidate>
        @csrf
        <div class="modal fade" id="modalId" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
            role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitleId">
                            Modal title
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h5>Company Details</h5>
                        <div class="row">
                            <input type="hidden" name="id" id="supplier_id">
                            <div class="col-md-4">
                                <label for="">Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label for="">Number</label>
                                <input type="number" name="number" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label for="">Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="">GST</label>
                                <input type="text" name="gst" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="">Address</label>
                                <input type="" name="address" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="">State</label>
                                <select name="state" id="state" class="form-control">
                                    <option value="">Select</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="">District</label>
                                <select name="district" id="district" class="form-control">
                                    <option value="">Select</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="">City</label>
                                <input type="" name="city" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="">Pincode</label>
                                <input type="" name="pincode" class="form-control">
                            </div>
                            <div class="col-md-4 mt-2">
                                <label for="">Email Template</label>
                                <select name="email_temp_id" id="email_temp_id" class="form-control">
                                    <option value="">Select</option>
                                    @foreach ($emailTemp as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                        <div id="contactSection">
                            <h5 class="mt-2">Contact Person Details</h5>
                            <div class="row mt-2">
                                <div class="col-md-4">
                                    <label for="">Name</label>
                                    <input type="text" name="name" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label for="">Number</label>
                                    <input type="number" name="number" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label for="">Email</label>
                                    <input type="email" name="email" class="form-control">
                                </div>
                                <div class="col-md-8 mt-2">
                                    <label for="">Address</label>
                                    <input type="" name="address" class="form-control">
                                </div>
                                <div class="col-md-4 mt-2">
                                    <label for="">State</label>
                                    <select name="state" id="state" class="form-control">
                                        <option value="">Select</option>
                                        @foreach ($state as $item)
                                            <option value="{{ $item->state }}">{{ $item->state }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mt-2">
                                    <label for="">District</label>
                                    <select name="district" id="district" class="form-control">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mt-2">
                                    <label for="">City</label>
                                    <input type="" name="city" id="city" class="form-control">
                                </div>
                                <div class="col-md-4 mt-2">
                                    <label for="">Pincode</label>
                                    <input type="" name="pincode" class="form-control">
                                </div>
                                <div class="col-md-4 mt-2">
                                    <label for="">Password</label>
                                    <input type="" name="password" class="form-control">
                                </div>

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
        $(".add").on("click", function() {
            $("#modalTitleId").text("Add");
            $("#supplier_id").val("");
            $("form")[0].reset();
            $("#contactSection").show();
            $("#modalId").modal("show");
        });

        $(document).on("click", ".Edit", function() {
            $("#modalTitleId").text("Edit");
            var data = $(this).data("data");
            $("#supplier_id").val(data.id);
            $("input[name='name']").val(data.name);
            $("input[name='number']").val(data.number);
            $("input[name='email']").val(data.email);
            $("input[name='gst']").val(data.gst);
            $("input[name='address']").val(data.address);
            $("select[name='state']").val(data.state);
            $("select[name='district']").val(data.district);
            $("input[name='city']").val(data.city);
            $("input[name='pincode']").val(data.pincode);
            $("select[name='email_temp_id']").val(data.email_temp_id);
            $("#contactSection").hide();
            $("#modalId").modal("show");
        });
    </script>
@endsection
