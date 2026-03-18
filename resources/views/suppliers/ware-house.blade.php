@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title>Ware House</title>
    @endpush



    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    <h5>Ware House List</h5>
                </div>
                <div>
                    <button class="btn btn-primary add" type="button">Add Ware House</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table dataTable">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Name</th>
                        <th>Ware House Code</th>
                        <th>Address</th>
                        {{-- <th>Country</th> --}}
                        <th>State</th>
                        <th>City</th>
                        <th>Pincode</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sno = 1;
                    @endphp
                    @foreach ($warehouse as $item)
                        <tr>
                            <td>{{ $sno++ }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->code }}</td>
                            <td>{{ $item->address }}</td>
                            {{-- <td>{{ $item->country }}</td> --}}
                            <td>{{ $item->state }}</td>
                            <td>{{ $item->city }}</td>
                            <td>{{ $item->pincode }}</td>
                            <td>
                                @if ($item->is_active == 1)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                @if ($item->is_active == 1)
                                    <a href="{{ route('supplier/warehouseLocation', $item->id) }}"
                                        class="btn btn-sm btn-info">
                                        Locations
                                    </a>
                                @endif
                                <button class="btn btn-sm btn-primary" id="editwarehouse" data-id="{{ $item->id }}"
                                    data-name="{{ $item->name }}" data-code="{{ $item->code }}"
                                    data-country="{{ $item->country }}" data-address="{{ $item->address }}"
                                    data-state="{{ $item->state }}" data-city="{{ $item->city }}"
                                    data-pincode="{{ $item->pincode }}" data-is_active="{{ $item->is_active }}">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>


    <form action="{{ route('supplier/saveWareHouse') }}" method="POST" class="needs-validation" novalidate>
        @csrf
        <div class="modal fade" id="modalIdwarehouse" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
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
                        <div class="row">
                            <input type="hidden" name="id" id="warehouse_id">
                            <div class="col-md-4 mt-3">
                                <label for="">Name</label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="">Code <span class="text-danger">(Code Always Unique)</span></label>
                                <input type="text" name="code" id="code" class="form-control" required>
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="">Country </label>
                                <input type="text" name="country" id="country" class="form-control">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="">Address</label>
                                <input type="" name="address" id="address" class="form-control">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="">State</label>
                                <input type="text" name="state" id="state" class="form-control">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="">city</label>
                                <input type="text" name="city" id="city" class="form-control">
                            </div>

                            <div class="col-md-4 mt-3">
                                <label for="">Pincode</label>
                                <input type="" name="pincode" id="pincode" class="form-control">
                            </div>

                            <div class="col-md-4 mt-3">
                                <label for="">Status</label>
                                <select name="is_active" id="is_active" class="form-control" required>
                                    <option value=""> Select</option>
                                    <option value="1">Active</option>
                                    <option value="0">In Active</option>
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
        $(".add").on("click", function() {
            $("#modalTitleId").text("Add Ware House");
            $("form")[0].reset();
            $("#warehouse_id").val('');
            $("#name").val('');
            $("#code").val('');
            $("#country").val('');
            $("#address").val('');
            $("#state").val('');
            $("#city").val('');
            $("#pincode").val('');
            $("#is_active").val('');
            $("#modalIdwarehouse").modal("show");
        });
        $(document).on("click", "#editwarehouse", function() {
            $("#modalTitleId").text("Edit Ware House");
            $("#warehouse_id").val($(this).data("id"));
            $("#name").val($(this).data("name"));
            $("#code").val($(this).data("code"));
            $("#country").val($(this).data("country"));
            $("#address").val($(this).data("address"));
            $("#state").val($(this).data("state"));
            $("#city").val($(this).data("city"));
            $("#pincode").val($(this).data("pincode"));
            $("#is_active").val($(this).data("is_active"));
            $("#modalIdwarehouse").modal("show");
        });
    </script>
@endsection
