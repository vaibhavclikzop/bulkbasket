@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title>Create Challan</title>
    @endpush

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">

                <!-- LEFT SIDE -->
                <div>
                    <h5 class="mb-0">Create Challan</h5>
                    <small class="text-success">
                        Upcoming Order ID :
                        {{ $order_id->order_series }}{{ $order_id->order_id + 1 }}
                    </small>
                </div>

                <!-- RIGHT SIDE -->
                <div class="d-flex align-items-center gap-3">
                    <a href="/supplier/orders-challan-draft/pending"><button class="btn btn-md btn-warning">View
                            Drafts</button></a>
                    <span class="fw-semibold">
                        Today : {{ now()->format('d M Y') }}
                    </span>
                </div>

            </div>
        </div>
        <div class="card-body">
            <form action="#" method="POST" class="needs-validation" novalidate id="frmMain">
                @csrf
                <div class="d-flex">
                    <div>
                        <label for="">Select Customer</label>
                        <select name="customer_id" id="customer_id" class="form-control " required>
                            <option value="">Select Customer</option>
                            @foreach ($data as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mx-2">
                        <label for="">Delivery Date</label>
                        <input type="date" value="{{ date('Y-m-d') }}" name="delivery_date" id="delivery_date"
                            class="form-control" required>
                    </div>
                    <div>
                        <label for="">Pay Mode <span class="active_amount text-success"></span></label>
                        <select name="pay_mode" id="pay_mode" class="form-control" required>
                            <option value="">Select Pay mode</option>
                            <option value="wallet" selected>Wallet</option>
                            <option value="upi">UPI</option>
                            <option value="card">Debit / Credit Card</option>
                            <option value="net_banking">Net Banking</option>
                            <option value="cod">Cash on Delivery (COD)</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mx-2 " style="width: 40%">
                        <label for="">Remarks</label>
                        <input type="text" name="remarks" id="remarks" class="form-control" required
                            placeholder="Enter Remarks">

                    </div>

                </div>
                <div class="mt-3">
                    <h6>Billing Address <span class="text-warning customer_gst"> </span></h6>
                    <div class="d-flex ">
                        <div class="">
                            <label for="">Select State</label>
                            <select name="billing_state" class="form-control state" disabled>
                                <option value="">Select State</option>
                                @foreach ($state as $item)
                                    <option value="{{ $item->state }}"> {{ $item->state }}</option>
                                @endforeach

                            </select>
                        </div>
                        <div class="mx-2">
                            <label for="">District</label>
                            <select name="billing_district" class="form-control district" disabled>
                                <option value="">Select District</option>
                            </select>
                        </div>
                        <div>
                            <label for="">City</label>
                            <input type="text" name="billing_city" class="form-control city" disabled
                                placeholder="Enter City">
                        </div>
                        <div class="mx-2" style="width: 55%">
                            <label for="">Address</label>
                            <input type="text" placeholder="Enter Address" name="billing_address"
                                class="form-control address" disabled>
                        </div>

                    </div>
                </div>
                <div class="mt-3 mb-4">
                    <h6>Shipping Address</h6>
                    <div class="d-flex ">
                        <div class="">
                            <label for="">Select State</label>
                            <select name="state" id="state" class="form-control state" required>
                                <option value="">Select State</option>
                                @foreach ($state as $item)
                                    <option value="{{ $item->state }}"> {{ $item->state }}</option>
                                @endforeach

                            </select>
                        </div>
                        <div class="mx-2">
                            <label for="">District</label>
                            <select name="district" id="district" class="form-control district" required>
                                <option value="">Select District</option>
                            </select>
                        </div>
                        <div>
                            <label for="">City</label>
                            <input type="text" name="city" id="city" class="form-control city" required
                                placeholder="Enter City">
                        </div>
                        <div class="mx-2" style="width: 55%">
                            <label for="">Address</label>
                            <input type="text" placeholder="Enter Address" name="address" id="address"
                                class="form-control address"required>
                        </div>

                    </div>
                </div>
                <div class="mt-2">
                    <div class="d-flex ">
                        <div class="col-md-2" style="width: 298px !important"><label for="">Product
                                <button class=" btnRefreshProduct" type="button"
                                    style="outline: none; border:none; padding:0;margin:0"><i class="fa fa-refresh"
                                        aria-hidden="true"></i></button> <span id="current_stock"
                                    class="text-warning"></span></label>
                            <select name="product_id" id="product_id" class="form-control" disabled>
                                <option value="">Select Product</option>
                            </select>
                        </div>
                        <div class="mx-2" style="width: 10%"><label for="">Qty</label>
                            <input type="number" name="qty" id="qty" min="1" value="1"
                                class="form-control" placeholder="Enter Qty">
                        </div>

                        <div class="mx-2" style="width: 8%"><label for="">Unit</label>
                            <input type="text" name="unit" id="unit" class="form-control" disabled>
                        </div>

                        <div class="d-none" style="width: 118px !important"><label for="">Base Price <span
                                    id="lastPriceInfo"></span></label>
                            <input type="text" step="0.01" name="base_price" id="base_price" class="form-control"
                                placeholder="Base Price" disabled>
                        </div>
                        <div class="mx-2 d-none" style="width: 10%"><label for="">Discount (%)</label>
                            <input type="number" name="discount" id="discount" min="0" class="form-control"
                                placeholder="Enter Discount" value="0">
                        </div>

                        <div class=""><label for="">MRP</label>
                            <input type="number" name="mrp" id="mrp" class="form-control" disabled
                                placeholder="MRP">
                        </div>

                        <div class="mx-2"><label for="">Price</label>
                            <input type="number" name="price" id="price" class="form-control"
                                placeholder="Price">
                        </div>

                        <div class="mx-2 d-none" style="width: 8%"><label for="">GST</label>
                            <input type="text" name="gst" id="gst" class="form-control" disabled>
                        </div>
                        <div class="mx-2 d-none" style="width: 8%"><label for="">Cess Tax</label>
                            <input type="text" name="cess_tax" id="cess_tax" class="form-control" disabled>
                        </div>
                        <div class="mx-2" style="width: 125px !important"><label for="">Taxable Amt.</label>
                            <input type="text" name="taxable" id="taxable" class="form-control"
                                placeholder="Total" disabled>
                        </div>
                        <div class="mx-2">
                            <button class="btn btn-primary mt-4" type="button" id="addProduct">Add</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Product Name</th>
                                    <th>Qty</th>


                                    <th>Price</th>
                                    <th>GST(%)</th>
                                    <th>Total </th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="prodList">
                            </tbody>
                            <tfoot>
                                <tr style="font-weight:bold; background:#f8f9fa;">
                                    <td colspan="2" style="text-align:right;">Total Qty :</td>
                                    <td id="totalItem">0</td>
                                    <td colspan="2" style="text-align:right;">Subtotal :</td>
                                    <td id="subTotalAmount">0.00</td>
                                    <td></td>
                                </tr>

                                <tr style="font-weight:bold; background:#f8f9fa;">

                                    <td colspan="5" style="text-align:right;">GST ₹</td>
                                    <td id="totalGst">0.00</td>
                                    <td>
                                        <div id="gstBifurcation">

                                        </div>
                                    </td>
                                </tr>
                                <tr style="font-weight:bold; background:#f8f9fa;">
                                    <td colspan="5" style="text-align:right;">Total :</td>
                                    <td id="totalamt">0.00</td>
                                    <td></td>
                                </tr>

                                {{-- <tr style="font-weight:bold; background:#f8f9fa;">
                                <td colspan="8" style="text-align:right;">
                                    Freight Charges
                                </td>
                                <td>
                                    <input type="number" step="0.01" id="freight_charges" name="freight_charges">

                                </td>
                                <td></td>
                            </tr>
                            <tr style="font-weight:bold; background:#f8f9fa;">
                                <td colspan="8" style="text-align:right;">


                                    Loading Charges

                                </td>
                                <td>
                                    <input type="number" step="0.01" id="loading_charges" name="loading_charges">

                                </td>
                                <td></td>
                            </tr>

                            <tr style="font-weight:bold; background:#f8f9fa;">
                                <td colspan="8" style="text-align:right;">
                                    <div class="float-end d-flex">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                id="discountType" name="discount_type" value="1">

                                        </div>
                                        Discount <span class="" id="symbol">&nbsp; ₹</span>
                                    </div>

                                </td>
                                <td>
                                    <input type="number" step="0.01" name="totalDiscount" id="totalDiscount"
                                        min="0" class="totalDiscount" placeholder="Discount" value="0">
                                </td>
                                <td></td>
                            </tr> --}}
                                <tr style="font-weight:bold; background:#f8f9fa;">
                                    <td colspan="5" style="text-align:right;">

                                        Total
                                    </td>
                                    <td>
                                        <span id="totalBeforeRound">0.00</span>

                                    </td>
                                    <td></td>
                                </tr>
                                {{-- <tr style="font-weight:bold; background:#f8f9fa;">
                                <td colspan="8" style="text-align:right;">

                                    Round OFF
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="roundOFF" id="roundOFF" min="0"
                                        class="" placeholder="Round OFF" value="0">

                                </td>
                                <td></td>
                            </tr>
                            <tr style="font-weight:bold; background:#f8f9fa;">
                                <td colspan="8" style="text-align:right;">

                                    Grand Total
                                </td>
                                <td>
                                    <span id="grandTotal">0.00</span>

                                </td>
                                <td></td>
                            </tr> --}}
                            </tfoot>
                        </table>
                        <input type="hidden" name="prod_list" id="prod_list">

                        <div class="d-flex" style="justify-content: center">
                            <div class="mx-3">
                                <button class="btn btn-warning" type="button" id="saveOrderDraft">
                                    Save Draft
                                </button>
                            </div>

                            <div>
                                <button class="btn btn-primary" type="button" id="saveOrder">
                                    Submit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="orderSuccessModal">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title orderModalTitle">Order Challan Created Successfully</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderSuccessBody">
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editProductModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Edit Product</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_mainID">
                    <input type="hidden" id="edit_gst">
                    <input type="hidden" id="edit_stock">
                    <div class="row">
                        <div class="col-12">
                            <label for="">Product</label>
                            <select name="product_name" id="edit_product_id" class="form-control">
                                <option value="">Select Product</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <div class="mb-2">
                                <label>Qty</label>
                                <input type="number" id="edit_qty" class="form-control">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-2">
                                <label>Price</label>
                                <input type="number" id="edit_price" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary" id="updateProduct">Update</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let sno = 1;
            let product_list = [];
            $("#customer_id, #product_id").select2({
                width: "100%"
            });
            $("#edit_product_id").select2({
                width: "100%",
                placeholder: "Select Product"
            });
            function focusNext(selector) {
                setTimeout(() => {
                    $(selector).focus().select();
                }, 50);
            }
            $(document).on("keydown", ".select2-search__field", function(e) {
                if (e.key === "Enter") {
                    let productId = $("#product_id").val();

                    if (!productId) {
                        toastr.error("Select product first");
                        return;
                    }

                    e.preventDefault();
                    focusNext("#qty");
                }
            });
            $(document).on("keydown", "#qty", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();

                    if (!$("#qty").val() || $("#qty").val() <= 0) {
                        toastr.error("Enter valid quantity");
                        return;
                    }

                    focusNext("#price");
                }
            });
            $(document).on("keydown", "#price", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();

                    if (!$("#price").val() || $("#price").val() <= 0) {
                        toastr.error("Enter valid price");
                        return;
                    }
                    $("#addProduct").click();
                }
            });
            $("#addProduct").on("click", function() {

                setTimeout(() => {

                    $("#qty").val(1);
                    $("#price").val("");

                    $("#product_id").val(null).trigger("change");
                    setTimeout(() => {
                        $("#product_id").select2("open");
                        setTimeout(() => {
                            document.querySelector(".select2-search__field")
                            ?.focus();
                        }, 150);

                    }, 200);

                }, 200);

            });
            $("#customer_id").on("change", function() {
                $("#product_id").removeAttr("disabled");
                let customer_id = $(this).val();
                if (customer_id == '') {
                    $('.active_amount').html('');
                    $('.state').val('');
                    $('.district').val('');
                    $('.city').val('');
                    $('.address').val('');
                    return;
                }
                $.ajax({
                    url: '/customer-address/' + customer_id,
                    type: 'GET',
                    success: function(response) {

                        if (response.status) {
                            let gst = response.customer_gst;
                            $('.customer_gst').html('(GST : ' + (gst ? gst : 'N/A') + ')');
                            $('.active_amount').html('(' + '₹' + +response.active_amount + ')');
                            $('.state').val(response.state);
                            $('.district').html(
                                `<option value="${response.district}">${response.district}</option>`
                            );
                            $('.city').val(response.city);
                            $('.address').val(response.address);

                        } else {
                            alert(response.message);
                        }
                    }
                });

                function toggleActiveAmount() {
                    let payMode = $('#pay_mode').val();
                    if (payMode === 'wallet') {
                        $('.active_amount').show();
                    } else {
                        $('.active_amount').hide();
                    }
                }
                $('#pay_mode').on('change', function() {
                    toggleActiveAmount();
                });
                toggleActiveAmount();
            })
            $("#product_id").select2({
                width: "100%",
                placeholder: "Search Product",
                minimumInputLength: 2,
                ajax: {
                    url: "/supplier/getOrderProducts",
                    type: "POST",
                    delay: 300,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: function(params) {
                        return {
                            search: params.term,
                            customer_id: $("#customer_id").val()
                        };
                    },
                    processResults: function(response) {
                        if (response.error == false) {


                            return {
                                results: response.data.map(function(item) {
                                    return {
                                        id: item.id,
                                        text: `${item.name} 🟢 Stock: ${item.current_stock} 📊 GST: ${parseFloat(item.gst).toFixed(2)}%`,
                                        stock: item.current_stock,
                                        gst: item.gst,
                                        price: item.base_price
                                    };
                                })
                            };
                        } else {
                            return {
                                results: [{
                                    id: '',
                                    text: response.msg
                                }]
                            };
                        }
                    },
                    cache: true
                }
            });
            $("#product_id").on("select2:select", function(e) {
                let productId = e.params.data.id;
                let customer_id = parseInt($("#customer_id").val());
                $.ajax({
                    url: "/supplier/getProductPrice",
                    type: "POST",
                    data: {
                        product_id: productId,
                        customer_id: customer_id,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        $(".btnRefreshProduct").text("Processing...");
                    },
                    success: function(response) {
                        $("#current_stock").html('(' + 'Stock : ' + +response.data
                            .current_stock + ')')
                        $("#base_price").val(response.data.price)
                        $("#price").val(response.data.price)
                        $("#gst").val(response.data.gst)
                        $("#mrp").val(response.data.mrp)
                        $("#cess_tax").val(response.data.cess_tax)
                        $("#unit").val(response.data.unit)
                        // $("#taxable").val(response.data.price)
                        updateTaxable();
                    },
                    complete: function() {
                        $(".btnRefreshProduct").html("<i class='fa fa-refresh' ></i>");
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || "Something went wrong");
                    }
                });

            });
            let priceTimer;
            $("#qty").on("keyup", function() {
                clearTimeout(priceTimer);
                let qty = $(this).val();
                let product_id = $("#product_id").val();
                let customer_id = $("#customer_id").val();
                priceTimer = setTimeout(function() {
                    $.ajax({
                        url: "/supplier/getProductQtyWisePrice",
                        type: "POST",
                        data: {
                            product_id: product_id,
                            qty: qty,
                            customer_id: customer_id,
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        beforeSend: function() {
                            $(".btnRefreshProduct").text("Processing...");
                        },
                        success: function(response) {
                            $("#base_price").val(response.data.price);
                            $("#price").val(response.data.price);
                            // $("#taxable").val(response.data.price);
                            updateTaxable();
                        },
                        complete: function() {
                            $(".btnRefreshProduct").html(
                                "<i class='fa fa-refresh' ></i>");
                        }
                    });
                }, 500);
            });
            $("#price").on("keyup", function() {
                // $("#taxable").val($(this).val())
                updateTaxable();
            })

            function updateTaxable() {
                let qty = parseFloat($("#qty").val()) || 0;
                let price = parseFloat($("#price").val()) || 0;
                let taxable = qty * price;
                $("#taxable").val(taxable.toFixed(2));
            }
            $("#addProduct").on("click", function() {
                mainID = sno;
                let selectedData = $("#product_id").select2('data')[0];
                if (!selectedData) {
                    alert("Please select product");
                    return;
                }
                let product_id = selectedData.id;
                let product_name = selectedData.text;
                let stock = parseFloat(selectedData.stock || 0);
                let gst = parseFloat(selectedData.gst || 0);

                let qty = parseFloat($("#qty").val());
                let price = parseFloat($("#price").val());

                if (!qty || qty <= 0) {
                    alert("Enter valid qty");
                    toastr.error("Enter qty greater than 0");
                    return;
                }

                if (!price || price <= 0) {
                    toastr.error("Enter valid price greater than 0");
                    return;
                }

                addProduct(product_id, product_name, qty, price, gst, 0, mainID, stock);
                sno++;
            });

            $("#updateProduct").on("click", function() {
                let id = $("#edit_mainID").val();
                let qty = parseFloat($("#edit_qty").val()) || 0;
                let price = parseFloat($("#edit_price").val()) || 0;
                let selectedData = $("#edit_product_id").select2("data")[0];
                if (!selectedData) {
                    toastr.error("Select product");
                    return;
                }
                let product_id = selectedData.id;
                let product_name = selectedData.text;
                let stock = parseFloat(selectedData.stock || $("#edit_stock").val());
                let gst = parseFloat(selectedData.gst || $("#edit_gst").val());
                console.log(gst);
                if (qty <= 0) {
                    toastr.error("Invalid qty");
                    return;
                }
                if (price <= 0) {
                    toastr.error("Invalid price");
                    return;
                }
                let itemIndex = product_list.findIndex(p => p.mainID == id);
                if (itemIndex === -1) return;
                let total = qty * price;
                let rowClass = "table-success";
                if (stock <= 0) {
                    rowClass = "table-danger";
                } else if (qty > stock) {
                    rowClass = "table-warning";
                }
                product_list[itemIndex] = {
                    ...product_list[itemIndex],
                    product_id,
                    product_name,
                    qty,
                    price,
                    gst,
                    stock,
                    total
                };
                console.log(product_list);
                let row = $("#prodList")
                    .find(`span.edit-product[data-id='${id}']`)
                    .closest("tr");
                if (!row.length) return;
                row.removeClass("table-danger table-warning table-success");
                row.addClass(rowClass);
                let cells = row.find("td");

                $(cells[1]).text(product_name);
                $(cells[2]).text(qty);
                $(cells[3]).text(price);
                $(cells[4]).text(gst);
                $(cells[5]).text(total.toFixed(2));
                $("#editProductModal").modal("hide");
                updateFooter();
                gstBifurcation();
                toastr.success("Product updated");
            });

            function addProduct(product_id, product_name, qty, price, gst, cess_tax, mainID, stock) {
                qty = parseFloat(qty);
                price = parseFloat(price);
                stock = parseFloat(stock || 0);
                let total = price * qty;
                let rowClass = "";
                let stockMsg = "";
                if (stock <= 0) {
                    rowClass = "table-danger";
                    stockMsg = "Out of Stock";
                } else if (qty > stock) {
                    rowClass = "table-warning";
                    stockMsg = `Low Stock (${stock})`;
                } else {
                    rowClass = "table-success";
                    stockMsg = `In Stock (${stock})`;
                }
                let html = `<tr class="${rowClass}">
                    <td>${mainID}</td>
                    <td>${product_name}</td>
                    <td>${qty}</td>
                    <td>${price}</td>
                    <td>${gst}</td>
                    <td>${total}</td> 
                    <td>
                        <span class="edit-product text-info mx-3" data-id="${mainID}">
                            Edit
                        </span> 
                         <span class="remove text-danger" data-id="${mainID}">
                            Delete
                        </span>    
                    </td>
                </tr>`;
                product_list.push({
                    mainID,
                    product_id,
                    product_name,
                    qty,
                    price,
                    gst,
                    total,
                    stock
                });
                $("#prodList").append(html);
                updateFooter();
                gstBifurcation();
            }

            function updateFooter() {
                let subTotal = 0;
                let totalItem = 0;
                let discountAmount = 0;
                let totalGstAmount = 0;
                let grandTotal = 0;
                let freightCharges = parseFloat($("#freight_charges").val()) || 0;
                let loadingCharges = parseFloat($("#loading_charges").val()) || 0;

                product_list.forEach(item => {
                    let price = parseFloat(item.price);
                    let qty = parseFloat(item.qty);
                    let gst = parseFloat(item.gst);
                    let rowTotal = price * qty;
                    let rowGst = (rowTotal * gst) / 100;
                    subTotal += rowTotal;
                    totalItem += qty;
                    totalGstAmount += rowGst;
                });
                let discountPercent = parseFloat($("#totalDiscount").val()) || 0;




                let totalAfterGst = parseFloat(subTotal + totalGstAmount);
                let totalAmount = parseFloat(subTotal + totalGstAmount + freightCharges + loadingCharges);

                if ($("#discountType").prop("checked")) {
                    discountAmount = (totalAmount / 100) * discountPercent;
                } else {
                    discountAmount = parseFloat(discountPercent);
                    console.log(discountAmount);

                }


                grandTotal = totalAmount - discountAmount;

                $("#subTotalAmount").text(subTotal.toFixed(2));
                $("#totalItem").text(parseFloat(totalItem).toFixed(2));
                $("#totalGst").text(totalGstAmount.toFixed(2));

                $("#totalamt").text(parseFloat(totalAfterGst).toFixed(2));
                $("#totalBeforeRound").text(grandTotal.toFixed(2));




                let actualTotal = parseFloat(grandTotal);
                $("#totalamt").text(actualTotal);



                // Round to nearest rupee
                // roundedTotal = Math.round(actualTotal);
                roundedTotal = actualTotal;




                $("#roundOFF").val(0);
                $("#grandTotal").text(0);

            }

            function gstBifurcation() {

                let gstSummary = {};
                let subTotal = 0;
                let totalItem = 0;
                let totalGstAmount = 0;

                product_list.forEach(item => {

                    let price = parseFloat(item.price) || 0;
                    let qty = parseFloat(item.qty) || 0;
                    let gst = parseFloat(item.gst) || 0;

                    let rowTotal = price * qty;
                    let rowGst = (rowTotal * gst) / 100;

                    subTotal += rowTotal;
                    totalItem += qty;
                    totalGstAmount += rowGst;

                    // Group by GST %
                    if (!gstSummary[gst]) {
                        gstSummary[gst] = 0;
                    }

                    gstSummary[gst] += rowGst;
                });

                // Build HTML
                let html = "";

                for (let rate in gstSummary) {
                    html += `
                        <div>
                            GST ${rate}% : ₹ ${gstSummary[rate].toFixed(2)}
                        </div>
                    `;
                }

                $("#gstBifurcation").html(html);
            }

            $(document).on("click", ".remove", function() {
                let index = $(this).data("id");
                product_list = product_list.filter(item => item.mainID != index);
                $(this).closest("tr").remove();
                updateSerialNumbers();
                updateFooter();
                gstBifurcation();
            });

            $(document).on("click", ".edit-product", function() {
                let id = $(this).data("id");
                let item = product_list.find(p => p.mainID == id);
                if (!item) return;
                $("#edit_mainID").val(id);
                $("#edit_qty").val(item.qty);
                $("#edit_price").val(item.price);
                $("#edit_gst").val(item.gst);
                $("#edit_stock").val(item.stock);
                let option = new Option(item.product_name, item.product_id, true, true);
                $("#edit_product_id").html('').append(option).trigger("change");
                $("#editProductModal").modal("show");
            });

            $("#edit_product_id").select2({
                width: "100%",
                placeholder: "Search Product",
                minimumInputLength: 2,
                dropdownParent: $("#editProductModal"),
                ajax: {
                    url: "/supplier/getOrderProducts",
                    type: "POST",
                    delay: 300,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: function(params) {
                        return {
                            search: params.term,
                            customer_id: $("#customer_id").val()
                        };
                    },
                    processResults: function(response) {
                        if (response.error == false) {
                            return {
                                results: response.data.map(function(item) {
                                    return {
                                        id: item.id,
                                        text: `${item.name} 🟢 Stock: ${item.current_stock} 📊 GST: ${parseFloat(item.gst).toFixed(2)}%`,
                                        stock: item.current_stock,
                                        gst: item.gst,
                                        price: item.base_price,
                                        name: item.name
                                    };
                                })
                            };
                        } else {
                            return {
                                results: [{
                                    id: '',
                                    text: response.msg
                                }]
                            };
                        }
                    },
                    cache: true
                }
            });

            function updateSerialNumbers() {
                $("#prodList tr").each(function(index) {
                    $(this).find("td:first").text(index + 1);
                });
            }
            $("#saveOrder").on("click", function(e) {
                e.preventDefault();

                $('#prod_list').val(JSON.stringify(product_list));

                if (!$("#customer_id").val()) {
                    toastr.error("Select Customer");
                    return;
                }

                if (product_list.length === 0) {
                    toastr.error("Select at least one product");
                    return;
                }
                let payMode = $("#pay_mode").val();
                if (payMode === "wallet") {

                    let active_amount = parseFloat($(".active_amount").text().replace(/[^\d.-]/g, "")) || 0;
                    let totalAmount = parseFloat($("#totalBeforeRound").text().replace(/[^\d.-]/g, "")) ||
                        0;
                    if (active_amount <= 0) {
                        toastr.error("Insufficient wallet balance");
                        return;
                    }
                    if (totalAmount > active_amount) {
                        toastr.error("Wallet amount is less than Order Amount");
                        return;
                    }
                }
                let formData = new FormData($("#frmMain")[0]);
                $.ajax({
                    url: "{{ route('supplier/saveEstimate') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    success: function(res) {
                        if (!res.error) {
                            $(".orderModalTitle").text("Order Challan Created Successfully");
                            let html = `
                                <div class="row">
                                    <div class="col-md-6">
                                        <b>Order ID:</b> ${res.data.order_id ?? ''}<br>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <b>Total Amount:</b> ₹${res.data.total_amount ?? 0}<br>
                                    </div>
                                </div>
                                <hr>
                                <div>
                                    <b>Shipping Address:</b><br>
                                    ${res.data.customer_address ?? ''}, 
                                    ${res.data.customer_city ?? ''}, 
                                    ${res.data.customer_state ?? ''}
                                </div>
                            `;
                            $("#orderSuccessBody").html(html);
                            $("#orderSuccessModal").modal("show");
                            $("#orderSuccessModal").on("hidden.bs.modal", function() {
                                location.reload();
                            });
                            toastr.success(res.message);
                        } else {
                            toastr.error(res.message);
                        }
                    },

                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || "Something went wrong");
                    }
                });
            });
            $("#saveOrderDraft").on("click", function(e) {
                e.preventDefault();

                $('#prod_list').val(JSON.stringify(product_list));

                if (!$("#customer_id").val()) {
                    toastr.error("Select Customer");
                    return;
                }

                if (product_list.length === 0) {
                    toastr.error("Select at least one product");
                    return;
                }
                let payMode = $("#pay_mode").val();
                if (payMode === "wallet") {

                    let active_amount = parseFloat($(".active_amount").text().replace(/[^\d.-]/g, "")) || 0;
                    let totalAmount = parseFloat($("#totalBeforeRound").text().replace(/[^\d.-]/g, "")) ||
                        0;
                    if (active_amount <= 0) {
                        toastr.error("Insufficient wallet balance");
                        return;
                    }
                    if (totalAmount > active_amount) {
                        toastr.error("Wallet amount is less than Order Amount");
                        return;
                    }
                }
                let formData = new FormData($("#frmMain")[0]);
                $.ajax({
                    url: "{{ route('supplier/saveEstimateDraft') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,

                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    success: function(res) {
                        if (!res.error) {
                            $(".orderModalTitle").text("Order Draft Created Successfully");
                            let html = `
                                <div class="row">
                                    <div class="col-md-6">
                                        <b>Order ID:</b> ${res.data.order_id ?? ''}<br>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <b>Total Amount:</b> ₹${res.data.total_amount ?? 0}<br>
                                    </div>
                                </div>
                                <hr>
                                <div>
                                    <b>Shipping Address:</b><br>
                                    ${res.data.customer_address ?? ''}, 
                                    ${res.data.customer_city ?? ''}, 
                                    ${res.data.customer_state ?? ''}
                                </div>
                            `;
                            $("#orderSuccessBody").html(html);
                            $("#orderSuccessModal").modal("show");
                            $("#orderSuccessModal").on("hidden.bs.modal", function() {
                                location.reload();
                            });
                            toastr.success(res.message);
                        } else {
                            toastr.error(res.message);
                        }
                    },

                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || "Something went wrong");
                    }
                });
            });
        })
    </script>
@endsection
