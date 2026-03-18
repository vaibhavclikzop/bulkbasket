@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title> Vendor Product List</title>
    @endpush



    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="mt-2">
                        <h5>Vendor Product List</h5><br>
                        <strong>Name :</strong> {{ $vendorDetail->name ?? '-' }} |<br>
                        <strong>Number :</strong> {{ $vendorDetail->number ?? '-' }} |<br>
                        <strong>Email :</strong> {{ $vendorDetail->email ?? '-' }} |<br>
                        <strong>GST :</strong> {{ $vendorDetail->gst ?? '-' }}
                    </div>
                </div>
                <div>
                    <button class="btn btn-primary add" type="button" id="AddProduct">Allocate Product</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table dataTable">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th> Brand</th>
                        <th> Category</th>
                        <th> Subcategory</th>
                        <th> Name</th>
                        <th> Base Price</th>
                        <th> HSN No</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sno = 1;
                    @endphp
                    @foreach ($vendorProducts as $item)
                        <tr>
                            <td>{{ $sno++ }}</td>
                            <td>{{ $item->brand_name }}</td>
                            <td>{{ $item->category_name }}</td>
                            <td>{{ $item->subcategory_name }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->base_price }}</td>
                            <td>{{ $item->hsn_code }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <form method="POST" action="{{ route('supplier/AllocateProduct') }}">
            @csrf
            <div class="modal fade" id="modalId">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered  modal-xl">

                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTitleId">
                                Add Vender Products
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <table class="table dataTable">
                                <input type="hidden" name="vendor_id" value="{{ $vendorDetail->id }}">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" class="product_id" id="selectall"></th>
                                        <th>Brand</th>
                                        <th>Category</th>
                                        <th>Sub category</th>
                                        <th> Name</th>
                                        <th> HSN No</th>
                                        <th> Base Price</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach ($products as $item)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="product_id" name="product_ids[]"
                                                    value="{{ $item->id }}">
                                            </td>
                                            <td>{{ $item->brand_name }}</td>
                                            <td>{{ $item->category_name }}</td>
                                            <td>{{ $item->subcategory_name }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ $item->hsn_code }}</td>
                                            <td>{{ $item->base_price }}</td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>

                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Submit</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                        </div>
                    </div>

                </div>
            </div>
    </div>

    </form>
    <script>
        $("#AddProduct").on("click", function() {
            $("#modalId").modal("show");
        })
        $(document).ready(function() {
            $('#selectall').on('click', function() {
                if ($(this).prop("checked")) {
                    let table = $('.dataTable').DataTable();

                    let isChecked = $(this).prop('checked');

                    // Select checkboxes from ALL pages
                    table.rows({
                            search: 'applied'
                        }).nodes().to$().find('input.product_id')
                        .prop('checked', isChecked);
                } else {
                    let table = $('.dataTable').DataTable();

                    let isChecked = $(this).prop('checked', false);

                    // Select checkboxes from ALL pages
                    table.rows({
                            search: 'applied'
                        }).nodes().to$().find('input.product_id')
                        .prop('checked', false);
                }
            });
        });
    </script>
@endsection
