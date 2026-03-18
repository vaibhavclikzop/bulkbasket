@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title>Generate Po</title>
    @endpush



    {{-- <x-product-modal modalId="productModal1" :brand="$brand" :category="$category" :product_uom="$product_uom" /> --}}

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    <h5>Generate Po (Up Coming PO-ID : {{ $nextPoId }} )</h5>
                    <div id="lastPODate"></div>
                </div>
                <div>
                    <Button class="btn btn-sm btn-info addVendor">Add Vendor</Button>
                    {{-- <Button class="btn btn-sm btn-warning addProduct">Add Product</Button> --}}
                </div>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('supplier/savePo') }}" method="POST" id="frmMain" class="needs-validation" novalidate>
                @csrf
                <div class="d-flex">
                    <input type="hidden" id="orderID" name="id">
                    <div class="" style="width: 10%">
                        <label for="">PO ID</label>
                        <input type="text" name="po_id" id="po_id" value="{{ $nextPoId }}" class="form-control"
                            disabled>
                    </div>
                    <div class="mx-2" style="width: 20%">
                        <label>Vendor</label>
                        <select name="vendor_id" id="vendor_id" class="form-control" required>
                            <option value="">Select Vendor</option>
                            @foreach ($vendor as $item)
                                <option value="{{ $item->id }}">{{ $item->company }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="">
                        <label for="">PO Date</label>
                        <input type="date" name="po_date" id="po_date" value="{{ date('Y-m-d') }}"
                            class="form-control">
                    </div>
                    <div class="mx-2">
                        <label for="">Expected Date</label>
                        <input type="date" name="expected_delivery_date" id="expected_delivery_date"
                            value="{{ date('Y-m-d') }}" class="form-control">
                    </div>
                    <div class="">
                        <label>Payment Terms</label>
                        <select name="payment_term" id="payment_term" class="form-control" required>
                            <option value="">Select Payment</option>
                            <option value="advance">Advance</option>
                            <option value="1">1 Day</option>
                            <option value="3">3 Days</option>
                            <option value="7">7 Days</option>
                            <option value="15">15 Days</option>
                        </select>
                    </div>
                    <div class="mx-2" style="width: 25%">
                        <label for="">Remarks</label>
                        <input type="text" name="description" id="description" class="form-control"
                            placeholder="Enter Remarks">
                    </div>
                </div>

                <hr>
                <div class="col-md-12 ">
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
                        <div class="" style="display: none"><label for="">Article </label>
                            <input type="text" step="0.01" name="article_no" id="article_no" class="form-control"
                                placeholder="MRP">
                        </div>
                        <div class="" style="width: 118px !important"><label for="">MRP <span
                                    id="lastPriceInfo"></span></label>
                            <input type="text" step="0.01" name="mrp" id="mrp" class="form-control"
                                placeholder="MRP" disabled>
                        </div>
                        <div class="mx-2" style="width: 10%"><label for="">Discount (%)</label>
                            <input type="number" name="discount" id="discount" min="0" max="95"
                                class="form-control" placeholder="Enter Discount" value="0" disabled>
                        </div>

                        <div class=""><label for="">Price</label>
                            <input type="number" name="price" id="price" class="form-control"
                                placeholder="Price">
                        </div>
                        <div class="mx-2" style="width: 8%"><label for="">GST</label>
                            <input type="text" name="gst" id="gst" class="form-control" disabled>
                        </div>
                        <div class="" style="width: 125px !important"><label for="">Taxable Amt.</label>
                            <input type="text" name="total" id="total" class="form-control"
                                placeholder="Total" disabled>
                        </div>
                        <div class="mx-2">
                            <button class="btn btn-primary mt-4" type="button" id="addProduct">Add</button>
                        </div>
                    </div>
                    <hr>
                    {{-- <div class="row mb-4">
                        <div class="col-md-4">
                            <label>Upload Requirement File<span class="text-danger"> *Only CSV File*</span></label>
                            <input type="file" id="file">
                        </div>

                        <div class="col-md-4">
                            <button class="btn btn-dark" type="button" id="BtnUpload">Upload</button>
                        </div>
                        <div class="col-md-4">
                            <a href="import-po-requirement-list.csv" class="btn btn-success btn-sm"
                                download="import-po-requirement-list.csv">Download sample file</a>
                        </div>
                    </div> --}}

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th colspan="2">Product Name</th>
                                    <th>Qty</th>
                                    <th>MRP</th>
                                    <th>Discount (%)</th>
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
                                    <td colspan="3" style="text-align:right;">Total Qty :</td>
                                    <td id="totalItem">0</td>
                                    <td colspan="4" style="text-align:right;">Subtotal :</td>
                                    <td id="subTotalAmount">0.00</td>
                                    <td></td>
                                </tr>

                                <tr style="font-weight:bold; background:#f8f9fa;">

                                    <td colspan="8" style="text-align:right;">GST ₹</td>
                                    <td id="totalGst">0.00</td>
                                    <td>
                                        <div id="gstBifurcation">

                                        </div>
                                    </td>
                                </tr>
                                <tr style="font-weight:bold; background:#f8f9fa;">
                                    <td colspan="8" style="text-align:right;">Total :</td>
                                    <td id="totalamt">0.00</td>
                                    <td></td>
                                </tr>

                                <tr style="font-weight:bold; background:#f8f9fa;">
                                    <td colspan="8" style="text-align:right;">
                                        Freight Charges
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" id="freight_charges"
                                            name="freight_charges">

                                    </td>
                                    <td></td>
                                </tr>
                                <tr style="font-weight:bold; background:#f8f9fa;">
                                    <td colspan="8" style="text-align:right;">




                                        Loading Charges



                                    </td>
                                    <td>
                                        <input type="number" step="0.01" id="loading_charges"
                                            name="loading_charges">

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
                                </tr>
                                <tr style="font-weight:bold; background:#f8f9fa;">
                                    <td colspan="8" style="text-align:right;">

                                        Total
                                    </td>
                                    <td>
                                        <span id="totalBeforeRound">0.00</span>

                                    </td>
                                    <td></td>
                                </tr>
                                <tr style="font-weight:bold; background:#f8f9fa;">
                                    <td colspan="8" style="text-align:right;">

                                        Round OFF
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="roundOFF" id="roundOFF"
                                            min="0" class="" placeholder="Round OFF" value="0">

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
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <input type="hidden" name="prod_list" id="prod_list" value="">
                    <div class="text-center col-md-12 mt-3">
                        <button type="button" id="SavePO" name="btnSubmit" class="btn btn-warning">Submit</button>
                    </div>
                </div>
            </form>
        </div>

    </div>

    <form method="POST" class="needs-validation" id="vendorForm" novalidate enctype="multipart/form-data">
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
                                <label for="">Type Of Dealer <span class="text-danger">*</span></label>
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
                                <label for="">Address 1 <span class="text-danger">*</span></label>
                                <input type="" name="address1" id="address1" class="form-control"
                                    placeholder="Line 1" required>
                            </div>
                            <div class="col-md-8 mt-3">
                                <label for="">Address 2</label>
                                <input type="" name="address2" id="address2" class="form-control"
                                    placeholder="Line 2">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="">State <span class="text-danger">*</span></label>
                                <select name="state" id="state" class="form-control">
                                    <option value="">Select</option>
                                    @foreach ($state as $item)
                                        <option value="{{ $item->state }}">{{ $item->state }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="">District <span class="text-danger">*</span></label>
                                <select name="district" id="district" class="form-control" required>
                                    <option value="">Select</option>
                                </select>
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="">City <span class="text-danger">*</span></label>
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
                                <input type="number" name="number" class="form-control" placeholder="Mob. No">
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
        $(document).ready(function() {
            document.getElementById("discountType").addEventListener("change", function() {
                let symbol = document.getElementById("symbol");

                if (this.checked) {
                    $(symbol).html("&nbsp %")
                } else {
                    $(symbol).html("&nbsp ₹")
                }
            });

            var product_list = [];
            $("#product_id").select2({
                width: '100%',
                dropdownAutoWidth: true
            });
            $("#vendor_id").select2({
                width: '100%'
            });
            $("#discount_type").select2({
                width: '100%'
            });

            function focusNext(selector) {
                $(selector).focus().select();
            }

            $("#qty").on("keydown", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    if (document.querySelector("#mrp").hasAttribute("disabled")) {
                        focusNext("#discount");
                    } else {
                        focusNext("#mrp");
                    }


                }
            });
            $("#mrp").on("keydown", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    focusNext("#price");
                }
            });
            $("#discount").on("keydown", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    focusNext("#price");
                }
            });

            $("#price").on("keydown", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    $("#addProduct").click();
                }
            });
            $(".btnRefreshProduct").hide();

            $("#vendor_id").on("change", function() {
                $.ajax({
                    url: "/GetVendorProducts",
                    type: "POST",
                    data: {
                        id: $(this).val(),
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        $(".btnRefreshProduct").text("Processing...");
                    },
                    success: function(result) {
                        var html = "";
                        console.log(result);
                        html += '<option value="">----Select Products----</option>';
                        result.product.forEach(element => {

                            html += '<option value="' + element.id + '" data-mrp="' +
                                element
                                .mrp + '"   data-gst="' +
                                element
                                .gst + '"  data-article_no="' +
                                element
                                .article_no + '"> ' + element.article_no + ' (' +
                                element
                                .name +
                                ')</option>';
                        });
                        if (result.po) {
                            $("#lastPODate").html(
                                "<span class=' badge bg-success' style='font-size:14px'> Last PO ID & Date :  " +
                                result.po.po_id +
                                " /  " + result.po.po_date + "</span>")
                        } else {
                            $("#lastPODate").html(
                                "<span class=' badge bg-danger' style='font-size:14px'> Last PO ID & Date :  No PO Found</span>"
                            )
                        }

                        $("#product_id").removeAttr("disabled")

                        $("#product_id").html(html).trigger("change");
                        $(".btnRefreshProduct").show();
                    },
                    complete: function() {
                        $(".btnRefreshProduct").html("<i class='fa fa-refresh' ></i>");
                    },
                    error: function(result) {
                        toastr.error(result.responseJSON.message);
                    }
                });

            });

            $(".btnRefreshProduct").on("click", function() {
                $("#vendor_id").trigger("change");
            })
            $("#product_id").on("change", function() {

                let product_id = $(this).val();
                let vendor_id = $("#vendor_id").val();

                if (!product_id) {
                    $("#mrp").val('');
                    $("#article_no").val('');
                    $("#gst").val('');
                    $("#lastPriceInfo").html('');
                    return;
                }
                let selected = $(this).find(":selected");
                let mrp = parseFloat(selected.data("mrp")) || 0;
                let article_no = selected.data("article_no") || '';
                let gst = parseFloat(selected.data("gst")) || 0;

                $("#mrp").val(mrp.toFixed(2));
                $("#article_no").val(article_no);
                $("#gst").val(gst);
                if (mrp == 0) {
                    $("#discount").attr("disabled", "disabled");
                    $("#mrp").removeAttr("disabled");
                } else {
                    $("#discount").removeAttr("disabled");
                    $("#mrp").attr("disabled", "disabled");
                }
                calculateTotal();
                if (!vendor_id) {
                    $("#lastPriceInfo").html('');
                    return;
                }
                $.ajax({
                    url: "/GetLastVendorPrice",
                    type: "POST",
                    data: {
                        vendor_id: vendor_id,
                        product_id: product_id
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        let price = res && res.price ? parseFloat(res.price).toFixed(2) : null;
                        if (!price) {
                            $("#lastPriceInfo").html(
                                '<span style="color:red;">N/A</span>'
                            );
                        } else {
                            $("#lastPriceInfo").html(
                                '<span style="color:#4caf50;">' + price + '</span>'
                            );
                        }
                    },
                    error: function() {
                        $("#lastPriceInfo").html(
                            '<span style="color:red;">Error</span>'
                        );
                    }
                });
            });

            function resetSno() {
                $("#prodList tr").each(function(index) {
                    $(this).find("td:first").text(index + 1);
                });
            }
            var sno = 1;
            var mainID = 1;
            let roundedTotal = 0;

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




                $("#roundOFF").val(parseFloat(actualTotal).toFixed(2));
                $("#grandTotal").text(parseFloat(roundedTotal).toFixed(2));
            }
            $("#freight_charges, #loading_charges,  #totalDiscount, #discountType").on("keyup click", function() {
                updateFooter();
            });
            $("#roundOFF").on("keyup", function() {


                let roundOFF = parseFloat($("#roundOFF").val()) || 0;
                let grandTotal = roundedTotal;
                $("#grandTotal").text(parseFloat(grandTotal + roundOFF).toFixed(2));

            })


            $(document).on("input", "#discount-footer", function() {
                updateFooter();
            });
            $("#addProduct").on("click", function() {
                var product_id = parseInt($("#product_id").val());
                var product_name = $("#product_id").find(":selected").text();
                let article_no = $("#article_no").val();
                let qty = parseFloat($("#qty").val()) || 0;
                let mrp = parseFloat($("#mrp").val()) || 0;
                let price = parseFloat($("#price").val()) || 0;
                let discount = parseFloat($("#discount").val()) || 0;
                let gst = parseFloat($("#gst").val()) || 0;

                if (!product_id || isNaN(product_id)) {
                    toastr.error("Select Product");
                    return;
                }
                if (!qty || qty <= 0) {
                    toastr.error("Enter Qty");
                    return;
                }
                let finalPrice = 0;
                if (price > 0) {
                    finalPrice = price;
                } else if (mrp > 0) {
                    finalPrice = mrp - ((mrp * discount) / 100);
                } else {
                    toastr.error("Enter MRP or Price");
                    return;
                }
                addProduct(product_id, product_name, qty, mrp, discount, finalPrice, gst, mainID);
                mainID++;
                $("#product_id").val(null).trigger("change");
                setTimeout(() => {
                    $("#product_id").select2("open");
                    setTimeout(() => {
                        let searchField = document.querySelector(
                            ".select2-container--open .select2-search__field"
                        );
                        if (searchField) searchField.focus();
                    }, 50);

                }, 100);
                $("#qty").val("1");
                $("#mrp").val("");
                $("#discount").val("0");
                $("#price").val("");
                $("#total").val("");


            });

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
                let id = parseInt($(this).data("id"));
                $(`.product${id}`).remove();
                product_list = product_list.filter(item => item.mainID !== id);
                resetSno();
                updateFooter();
                gstBifurcation();
                console.log(product_list);
            });

            $("#SavePO").on("click", function() {
                $('#prod_list').val(JSON.stringify(product_list));
                if (!$("#vendor_id").val()) {
                    toastr.error("Select Vendor");
                    return;
                }

                if (!$("#po_date").val()) {
                    toastr.error("Select PO Date");
                    return;
                }

                if (!$("#payment_term").val()) {
                    toastr.error("Select Payment Term");
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


            function addProduct(product_id, product_name, qty, mrp, discount, finalPrice, gst, mainID) {

                let disabled = "disabled";
                let mrpDisabled = "";
                if (mrp != 0) {
                    disabled = "";
                    mrpDisabled = "disabled"
                }

                let total = finalPrice * qty;
                var html = `<tr class="product${mainID}">
                <td>${sno++}</td>    
                        <td colspan="2"  >
                    ${product_name}
                </td>
 
                <td> <input type="number"  value="${qty}" class="updateValues" data-name="qty" data-product_id="${mainID}"> </td>     
            <td> <input type="number"  value="${mrp}" class="updateValues" data-name="mrp" data-product_id="${mainID}" ${mrpDisabled}> </td>   
               <td> <input type="number"  value="${discount}" class="updateValues" data-name="discount" data-product_id="${mainID}" ${disabled}> </td>    
            <td> 
                <input type="number"  value="${finalPrice}" class="updateValues" data-name="price" data-product_id="${mainID}"> 
            </td>    
                <td>${gst}</td> 
                <td class="rowTotal">${total.toFixed(2)}</td>    
                <td>
                    <spam type="button" class="remove text-danger" data-id="${mainID}">
                        Delete
                    </span>
                </td>    
                </tr>`;
                $("#prodList").append(html);
                product_list.push({
                    mainID,
                    product_id,
                    qty,
                    mrp,
                    price: finalPrice,
                    gst,
                    discount,
                    total
                });

                updateFooter();
                gstBifurcation();
                resetSno();

            }
            $(document).on("keyup", ".updateValues", function() {

                let product_id = parseInt($(this).data("product_id"))
                let value = parseFloat($(this).val())
                let valueName = $(this).data("name")

                var product = product_list.find(item => item.mainID === product_id);
                if (product) {

                    product[valueName] = value;


                }
                let row = $(this).closest("tr");
                let qty = parseFloat(product.qty) || 0;
                let price = parseFloat(product.price) || 0;
                let gst = parseFloat(product.gst) || 0;
                let mrp = parseFloat(product.mrp) || 0;
                let discount = parseFloat(product.discount) || 0;
                let total = price * qty;
                let afterDiscount = mrp - price;
                let discountPercentage = afterDiscount / mrp * 100;
                if (discountPercentage < 0) {
                    discountPercentage = 0;
                }

                row.find(".rowTotal").text(total.toFixed(2));
                if (valueName == "price") {
                    product.discount = discountPercentage;
                    row.find("td").eq(4).find("input").val(discountPercentage.toFixed(2));
                } else {

                    let price = mrp - mrp / 100 * discount;
                    product.price = price;
                    row.find("td").eq(5).find("input").val(price.toFixed(2));
                }


                updateFooter();
                gstBifurcation();
                resetSno();



            })
            let mst = {!! json_encode($data) !!};
            let det = {!! json_encode($det) !!};


            if (mst && Object.keys(mst).length > 0) {
                console.log("exists");

                let total = 0;
                $.each(mst, function(i, o) {
                    $("input[name=" + i + "]").val(o)
                    $("select[name=" + i + "]").val(o)
                    if (i == "invoice_no") {
                        $("#invoice_no").html(`<option value="${o}">${o}</option>`)
                    }
                })
                $("#orderID").val(mst.id)
                $("#vendor_id").trigger("change");
                let currency = mst.currency;
                det.forEach(element => {
                    let product_id = element.product_id;
                    let product_name = element.name;
                    let catalog_no = element.catalog_no;
                    let article_no = element.article_no;
                    let po_no = element.po_no
                    let qty = parseInt(element.qty);
                    let finalPrice = parseFloat(element.price);
                    let mrp = parseFloat(element.mrp);
                    let gst = parseFloat(element.gst);

                    let discount = element.discount;


                    addProduct(product_id, product_name, qty, mrp, discount, finalPrice, gst, mainID)

                    mainID++;
                });
                setTimeout(() => {

                    $("#roundOFF").val(mst.round_off)
                    $("#grandTotal").text(mst.round_off);
                }, 500);
            }

            $("#product_id").on("select2:select", function() {
                setTimeout(() => {
                    $("#qty").focus().select();
                }, 100);
            });
            $("#BtnUpload").on("click", function() {

                let fileInput = document.getElementById('file');
                let file = fileInput.files[0];
                if (file) {
                    // Create a new FormData object
                    let formData = new FormData();
                    formData.append('file', file);

                    $.ajax({
                        url: "/UploadPORequirementList",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(result) {
                            var row = "";
                            let sno = 1;
                            data = JSON.parse(result)
                            data.data.forEach(element => {
                                row += `
                            <tr class="prod${element.id}">
                            <td>${sno++}</td>
                            <td colspan="2">${element.name}</td>
                            <td>${element.qty}</td>
                                <td>${element.mrp}</td>
                                <td>${element.gst}</td>
                                <td>${element.base_price*element.qty}</td>
                                <td><button onclick="removeItem(${element.qty})" class="btn btn-sm btn-danger" type="button"><i class="fa fa-trash" aria-hidden="true"></i></button></td>
                            </tr>
                        `;
                                let product_id = element.id;
                                let qty = element.qty;
                                let price = element.mrp;
                                let gst = element.gst;

                                product_list.push({
                                    product_id,
                                    qty,
                                    price,
                                    gst,
                                });
                            });

                            $('#prodList').append(row);
                            console.log(product_list);

                        },
                        error: function(data) {
                            console.log(data);

                        }
                    });
                } else {
                    toastr.error("Select CSV file for upload");
                }

            });
        });

        function calculateTotal(source = null) {
            let qty = parseFloat($("#qty").val()) || 0;
            let mrp = parseFloat($("#mrp").val()) || 0;
            let price = parseFloat($("#price").val()) || 0;
            let discount = parseFloat($("#discount").val()) || 0;
            if (discount > 95) {
                discount = 95;
                $("#discount").val(95);
            }
            let gst = parseFloat($("#gst").val()) || 0;
            if (qty <= 0) return;
            if (source === "discount" && mrp > 0) {
                let discountedPrice = mrp - ((mrp * discount) / 100);
                $("#price").val(discountedPrice.toFixed(2));
                price = discountedPrice;
            }
            if (source === "price" && mrp > 0) {
                if (price <= mrp) {
                    let discountPercent = ((mrp - price) / mrp) * 100;
                    $("#discount").val(discountPercent.toFixed(2));
                } else {
                    $("#discount").val("0.00");
                }
            }
            // let base = price * qty;
            // let gstAmount = (base * gst) / 100;
            let total = price * qty;

            $("#total").val(total.toFixed(2));
        }
        $("#discount").on("input", function() {
            calculateTotal("discount");
        });
        $("#price").on("input", function() {
            calculateTotal("price");
        });
        $("#qty, #mrp, #gst").on("input", function() {
            calculateTotal();
        });
    </script>

    <script>
        $(".addVendor").on("click", function() {
            $("#modalTitleId").text("Add Vendor")
            $("#id").val("");
            $("#modalId").modal("show")
        });
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $("#vendorForm").on("submit", function(e) {
                e.preventDefault();
                let form = this;
                let formData = new FormData(form);

                $.ajax({
                    url: "{{ route('supplier/saveVendorAjax') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $(".btn-primary").prop("disabled", true).text("Saving...");
                    },
                    success: function(res) {
                        $(".btn-primary").prop("disabled", false).text("Save");
                        if (res.status) {
                            toastr.success(res.message);
                            $("#modalId").modal("hide");
                            form.reset();
                            let newVendorId = res.vendor_id;
                            loadVendors(newVendorId);
                        }
                    },
                    error: function(xhr) {

                        $(".btn-primary").prop("disabled", false).text("Save");

                        if (xhr.status === 422) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            toastr.error("Something went wrong");
                        }
                    }
                });

            });

            function loadVendors(selectedId = null) {

                $.ajax({
                    url: "{{ route('supplier/vendor-ajax') }}",
                    type: "GET",
                    success: function(res) {

                        if (res.status) {

                            let options = '<option value="">Select Vendor</option>';

                            $.each(res.data, function(index, vendor) {
                                options += `<option value="${vendor.id}">
                                    ${vendor.company}
                                </option>`;
                            });

                            $("#vendor_id").html(options);

                            if (selectedId) {
                                $("#vendor_id").val(selectedId);
                            }
                        }
                    }
                });
            }


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
