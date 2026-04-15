@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title>Rise Pick Ticket</title>
    @endpush

    <div class="card">
        <div class="card-header">
            <h5>Rise Pick Ticket</h5>
        </div>

        <div class="card-body">
            <script>
                window.editData = @json($outward_det);
                window.editMst = @json($outward_mst);
            </script>
            <script>
                window.req_customer_id = "{{ request('customer_id') }}";
                window.req_order_id = "{{ request('order_id') }}";
            </script>

            <form action="{{ route('supplier/SaveOutwardStock') }}" method="POST" id="frmMain" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <input type="hidden" id="orderID" name="id">

                    <div class="col-md-3">
                        <label>Customer</label>
                        <select name="customer_id" id="customer_id" class="form-control" required>
                            <option value="">Select Customer</option>
                            @foreach ($customers as $item)
                                <option value="{{ $item->id }}">{{ $item->customer_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Order Id</label>
                        <select name="order_id" id="order_id" class="form-control" required>
                            <option value="">Select Order</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label>Ware House</label>
                        <select name="warehouse_id" id="warehouse_id" class="form-control" required>
                            <option value="" selected disabled>Select Warehouse</option>
                            @foreach ($warehouse as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <hr>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th>Product</th>
                                <th>Article</th>
                                <th>Location</th>
                                <th>Actual Qty</th>
                                <th>Out Qty</th>
                                <th>Current Stock</th>
                                <th>Qty</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="productList"></tbody>
                    </table>
                </div>

                <input type="hidden" name="prod_list" id="prod_list">

                <div class="text-center mt-3">
                    <button type="button" id="SaveOutward" class="btn btn-warning">
                        Submit
                    </button>
                </div>

            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            if (window.editMst) {
                $("#customer_id").val(window.editMst.customer_id);
                $("#warehouse_id").val(window.editMst.warehouse_id);
                loadCustomerOrders(
                    window.editMst.customer_id,
                    window.editMst.order_id
                );
                loadEditProducts();
                return;
            }
            if (window.req_customer_id) {
                $("#customer_id").val(window.req_customer_id);
                loadCustomerOrders(
                    window.req_customer_id,
                    window.req_order_id
                );
            }
        });

        function loadCustomerOrders(customer_id, selected_order_id = null) {

            $.post("/GetCustomerOrder", {
                id: customer_id,
                _token: $('meta[name="csrf-token"]').attr('content')
            }, function(res) {

                let html = '<option value="">Select Order</option>';

                res.forEach(r => {
                    html += `<option value="${r.id}">${r.e_order_id}</option>`;
                });

                $("#order_id").html(html);

                if (selected_order_id) {
                    $("#order_id")
                        .val(String(selected_order_id))
                        .trigger("change");

                    console.log("Auto Selected Order =", selected_order_id);
                }
            });
        }
        let product_list = []
        let sno = 1
        let csrf = $('meta[name="csrf-token"]').attr('content')
        $("#customer_id").on("change", function() {
            let cid = $(this).val();
            loadCustomerOrders(cid);
        });


        $("#order_id, #warehouse_id").on("change", function() {
            let id = $("#order_id").val()
            let warehouse_id = $("#warehouse_id").val()
            if (!id || !warehouse_id) return
            $.post("/GetOrderDet", {
                id: id,
                warehouse_id: warehouse_id,
                _token: csrf
            }, function(res) {
                product_list = []
                sno = 1
                let html = ""
                res.forEach(r => {
                    let pending = r.qty - (r.out_qty ?? 0)
                    if (pending <= 0) return
                    let stock = parseInt(r.current_stock ?? 0)
                    let actual = parseInt(r.qty ?? 0)
                    let rowClass = ""
                    if (stock === 0) {
                        rowClass = "table-danger"
                    } else if (stock < actual) {
                        rowClass = "table-warning"
                    } else {
                        rowClass = "table-success"
                    }
                    html += `
                    <tr class="product${r.product_id} ${rowClass}">
                    <td>${sno++}</td>
                    <td>${r.product}</td>
                    <td>${r.article_no}</td>
                    <td>${r.location_code??'-'}</td>
                    <td>${r.qty}</td>
                    <td>${r.out_qty}</td>
                    <td>${r.current_stock??0}</td>
                    <td>
                    <input type="number" class="form-control qty"
                    data-product_id="${r.product_id}"
                    data-actual_qty="${r.qty}"
                    data-out_qty="${r.out_qty??0}"
                    value="${pending}" style="width:70px !important">
                    </td>
                    <td>
                    <button class="btn btn-danger btn-sm remove"
                    data-id="${r.product_id}">
                    <i class="fa fa-trash"></i>
                    </button>
                    </td>
                    </tr>`
                    product_list.push({
                        product_id: r.product_id,
                        qty: pending,
                        price: r.price,
                        location_id: r.location_id
                    })
                })
                $("#productList").html(html)
            })
        })

        function loadEditProducts() {
            let html = "";
            let sno = 1;
            product_list = [];
            window.editData.forEach(r => {
                html += `
                <tr class="product${r.product_id}">
                <td>${sno++}</td>
                <td>${r.product}</td>
                <td>${r.article_no}</td>
                <td>${r.location_code??'-'}</td>
                <td>${r.qty}</td>
                <td>${r.out_qty}</td>
                <td>${r.stock}</td>
                <td>
                <input type="number"
                class="form-control qty"
                data-product_id="${r.product_id}"
                data-actual_qty="${r.qty}"
                data-out_qty="${r.out_qty}"
                value="${r.outward_qty}" style="width:70px">
                </td>
                <td>  
                 <button class="btn btn-danger btn-sm remove"
                    data-id="${r.product_id}">
                    <i class="fa fa-trash"></i>
                    </button>
                    </td>
                </tr>`;
                product_list.push({
                    product_id: r.product_id,
                    qty: r.outward_qty,
                    price: r.price,
                    location_id: r.location_id
                });
            });
            $("#productList").html(html);
        }
        $(document).on("keyup change", ".qty", function() {
            let pid = parseInt($(this).data("product_id"))
            let prod = product_list.find(p => p.product_id === pid)
            if (!prod) return
            if ($(this).hasClass("qty")) {
                let qty = parseInt($(this).val()) || 0
                let actual = parseInt($(this).data("actual_qty")) || 0
                let received = parseInt($(this).data("out_qty")) || 0
                let remaining = actual - received
                if (qty > remaining) {
                    toastr.error("Qty cannot exceed remaining")
                    $(this).val(remaining)
                    qty = remaining
                }
                prod.qty = qty
            }
        })
        $(document).on("click", ".remove", function() {
            let id = parseInt($(this).data("id"))
            $(".product" + id).remove()
            product_list = product_list.filter(p => p.product_id !== id)

        })
        $("#SaveOutward").on("click", function() {
            let final_list = []
            let warehouse_id = $("#warehouse_id").val()
            $("#productList tr").each(function() {
                let row = $(this)
                let qty = parseInt(row.find(".qty").val()) || 0
                let product_id = row.find(".qty").data("product_id")
                let prod = product_list.find(p => p.product_id == product_id)
                if (!prod) return
                if (qty <= 0) return
                if (!prod.location_id) return
                final_list.push({
                    product_id: product_id,
                    qty: qty,
                    price: prod.price || 0,
                    location_id: prod.location_id
                })
            })
            if (final_list.length == 0) {
                toastr.error("No valid products to send")
                return
            }
            $("#prod_list").val(JSON.stringify(final_list))
            $("#frmMain").submit()

        })
    </script>
@endsection
