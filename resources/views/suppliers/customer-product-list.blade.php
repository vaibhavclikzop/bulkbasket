@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title>Customer Products</title>
    @endpush


    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    Customer Products
                </div>
                <div>
                    <a href="/supplier/customer-product-price/{{ $customer_id }}"><button class="btn btn-primary add"
                        type="button">Add Products</button></a>
                </div>
            </div>
        </div>
        <div class="card-body">
            {!! session('msg') !!}
            <table class="table dataTable">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Brand</th>
                        <th>Category</th>
                        <th>Sub Category</th>
                        <th>Name</th>
                        <th>Base Price</th>
                        <th>MRP</th>
                        <th>HSN </th>
                        <th>GST</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->brand_name ?? '-' }}</td>
                            <td>{{ $item->category_name ?? '-' }}</td>
                            <td>{{ $item->sub_category_name ?? '-' }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->base_price }}</td>
                            <td>{{ $item->mrp }}</td>
                            <td>{{ $item->hsn_code }}</td>
                            <td>{{ $item->gst }}%</td>
                            <td>
                                <button class="btn btn-sm btn-primary products" value="{{ $item->cpl_id }}">
                                    Price Tier
                                </button>
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>



    <div class="modal fade" id="customerProductsModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitleId">
                        Product Price Tier
                    </h5>
                    <button class="btn btn-primary" id="addProductPrice" type="button">Add </button>
                </div>
                <div class="modal-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th>Per Piece Price</th>
                                <th>Qty</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="productPriceList">

                        </tbody>
                    </table>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>

                </div>
            </div>
        </div>
    </div>



    <div class="modal fade" id="deleteCustomerPriceModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="modalTitleId">
                        Delete Product Price
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="priceTierID">
                    Are you sure you want to delete this product price?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="button" id="btnDeleteProduct" class="btn btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="addModalCustomerPrice" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitleId">
                        Add product price
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <label for="">Price</label>
                            <input type="number" step="0.01" id="product_price" class="form-control">
                        </div>
                        <div class="col-12 mt-3">
                            <label for="">Qty</label>
                            <input type="number" id="product_qty" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="button" class="btn btn-primary" id="btnAddProductPrice">Save</button>
                </div>
            </div>
        </div>
    </div>




    <script>
        function GetProductPrice(id) {

            $.ajax({
                url: "/supplier/GetCustomerProductPrices",
                type: "POST",
                data: {
                    id: id,
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $("#loader").show();
                },
                success: function(result) {
                    var html = "";
                    var sno = 1;

                    result.forEach(element => {

                        html += `<tr>
                                    <td>${sno++}</td>
                                    <td>${element.base_price}</td>
                                    <td>${element.qty}</td>
                                    <td>
                                    <button class="btn btn-danger btn-sm deleteProduct" type="button" value="${element.id}"> <i class="fa fa-trash" aria-hidden="true"></i> </button>    
                                    </td>
                                    </tr>`;
                    });
                    $("#productPriceList").html(html)
                },
                complete: function() {
                    $("#loader").hide();
                },
                error: function(result) {
                    toastr.error(result.responseJSON.message);
                }
            });
        }
        var customer_product_id = "";
        $(document).on("click", ".products", function() {

            GetProductPrice($(this).val())
            customer_product_id = $(this).val();
            $("#customerProductsModal").modal("show")
        });

        $(document).on("click", ".deleteProduct", function() {
            $("#priceTierID").val($(this).val())
            $("#deleteCustomerPriceModal").modal("show")
        });

        $("#btnDeleteProduct").on("click", function() {
            var id = $("#priceTierID").val()
            $.ajax({
                url: "/supplier/DeleteCustomerProductPrice",
                type: "POST",
                data: {
                    id: id,
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $("#loader").show();
                },
                success: function(result) {
                    if (result.error == "success") {
                        toastr.success(result.msg);
                    }
                    if (result.error == "error") {
                        toastr.success(result.msg);
                    }

                    GetProductPrice(customer_product_id)
                    $("#deleteCustomerPriceModal").modal("hide");
                },
                complete: function() {
                    $("#loader").hide();
                },
                error: function(result) {
                    toastr.error(result.responseJSON.message);
                }
            });

        })

        $("#addProductPrice").on("click", function() {
            $("#addModalCustomerPrice").modal("show");
        });
        $("#btnAddProductPrice").on("click", function() {
            var product_price = $("#product_price").val()
            var product_qty = $("#product_qty").val()

            $.ajax({
                url: "/supplier/AddCustomerProductPrice",
                type: "POST",
                data: {
                    product_price: product_price,
                    product_qty: product_qty,
                    customer_product_id: customer_product_id,
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $("#loader").show();
                },
                success: function(result) {
                    if (result.error == "success") {
                        toastr.success(result.msg);
                    }
                    if (result.error == "error") {
                        toastr.success(result.msg);
                    }
                    GetProductPrice(customer_product_id)
                    $("#addModalCustomerPrice").modal("hide")
                    $("#product_price").val("")
                    $("#product_qty").val("")
                },
                complete: function() {
                    $("#loader").hide();
                },
                error: function(result) {
                    toastr.error(result.responseJSON.message);
                }
            });

        });
    </script>
@endsection
