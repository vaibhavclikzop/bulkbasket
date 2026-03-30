@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title>Create Estimate</title>
    @endpush

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    <h5>Create Estimate</h5>
                </div>

            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('supplier/saveEstimate') }}" method="POST" class="needs-validation" novalidate
                id="frmMain">
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
                        <label for="">Pay Mode</label>
                        <select name="pay_mode" id="pay_mode" class="form-control" required>
                            <option value="">Select Pay mode</option>
                            <option value="Wallet" selected>Wallet</option>
                            <option value="Online">Online</option>
                        </select>
                    </div>
                    <div class="mx-2 " style="width: 60%">
                        <label for="">Remarks</label>
                        <input type="text" name="remarks" id="remarks" class="form-control" required
                            placeholder="Enter Remarks">

                    </div>

                </div>
                <div class="mt-3">
                    <h6>Billing Address</h6>
                    <div class="d-flex ">
                        <div class="">
                            <label for="">Select State</label>
                            <select name="billing_state"   class="form-control state" disabled>
                                <option value="">Select State</option>
                                @foreach ($state as $item)
                                    <option value="{{ $item->state }}"> {{ $item->state }}</option>
                                @endforeach

                            </select>
                        </div>
                        <div class="mx-2">
                            <label for="">District</label>
                            <select name="billing_district"   class="form-control district" disabled>
                                <option value="">Select District</option>
                            </select>
                        </div>
                        <div>
                            <label for="">City</label>
                            <input type="text" name="billing_city"   class="form-control city" disabled
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
                        <div class="col-md-2" style="width: 235px !important"><label for="">Product
                                <button class=" btnRefreshProduct" type="button"
                                    style="outline: none; border:none; padding:0;margin:0"><i class="fa fa-refresh"
                                        aria-hidden="true"></i></button> </label>
                            <select name="product_id" id="product_id" class="form-control" disabled>
                                <option value="">Select Product</option>
                            </select>
                        </div>
                        <div class="mx-2" style="width: 10%"><label for="">Qty</label>
                            <input type="number" name="qty" id="qty" min="1" value="1"
                                class="form-control" placeholder="Enter Qty">
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

                        <div class=""><label for="">Price</label>
                            <input type="number" name="price" id="price" class="form-control"
                                placeholder="Price">
                        </div>
                        <div class="mx-2" style="width: 8%"><label for="">GST</label>
                            <input type="text" name="gst" id="gst" class="form-control" disabled>
                        </div>
                        <div class="mx-2" style="width: 8%"><label for="">Cess Tax</label>
                            <input type="text" name="cess_tax" id="cess_tax" class="form-control" disabled>
                        </div>
                        <div class="" style="width: 125px !important"><label for="">Taxable Amt.</label>
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
                        <div style="text-align: center">
                            <input type="text" name="prod_list" id="prod_list" hidden>
                            <button class="btn btn-primary" type="button" id="saveOrder">Submit</button>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
    <script>
        $(document).ready(function() {
            let sno = 1;
            let product_list = [];
            $("#customer_id, #product_id").select2({
                width: "100%"
            });
            $("#customer_id").on("change", function() {
                $("#product_id").removeAttr("disabled");
                let customer_id = $(this).val();

                if (customer_id == '') {
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
                                        text: item.name
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

            // When product selected
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


                        $("#base_price").val(response.data.price)
                        $("#price").val(response.data.price)
                        $("#gst").val(response.data.gst)
                        $("#cess_tax").val(response.data.cess_tax)
                        $("#taxable").val(response.data.price)

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
                            $("#taxable").val(response.data.price);

                        },
                        complete: function() {
                            $(".btnRefreshProduct").html(
                                "<i class='fa fa-refresh' ></i>");
                        }
                    });

                }, 500); // 500ms after user stops typing

            });

            $("#price").on("keyup", function() {
                $("#taxable").val($(this).val())
            })

            $("#addProduct").on("click", function() {
                mainID = sno;
                let product_id = $("#product_id").val()
                let qty = $("#qty").val()
                let price = $("#price").val()
                let gst = $("#gst").val()
                let cess_tax = $("#cess_tax").val()
                let product_name = $("#product_id").find(":selected").text()

                addProduct(product_id, product_name, qty, price, gst, cess_tax, mainID)
                sno++;


            })

            function addProduct(product_id, product_name, qty, price, gst, cess_tax, mainID) {
                let total = price ^ qty;

                let html = `<tr>
                            <td>${mainID}</td>
                            <td >${product_name}</td>
                            <td>${qty}</td>
                            <td>${price}</td>
                            <td>${gst}</td>
            
                            <td>${total}</td>
                    
                             <td>
                                <spam type="button" class="remove text-danger" data-id="${sno}">
                                    Delete
                                </span>    
                            </td>
                            </tr>`;
                product_list.push({
                    mainID,
                    product_id,
                    qty,

                    price: price,
                    gst,

                    total
                });

                $("#prodList").append(html)
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


            $("#saveOrder").on("click", function() {
                $('#prod_list').val(JSON.stringify(product_list));
                if (!$("#customer_id").val()) {
                    toastr.error("Select Customer");
                    return;
                }



                // if (!$("#name").val()) {
                //     toastr.error("Please Enter Name");
                //     return;
                // }


                if (product_list.length === 0) {
                    toastr.error("Select at least one product");
                    return;
                }


                $('#frmMain').submit()

            })

        })
    </script>
@endsection