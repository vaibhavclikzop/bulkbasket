@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title>Purchases</title>
    @endpush

    <style>
        td,
        th {
            border-color: black;
            border: solid 1px gray;

        }

        .table th,
        .table td {
            padding: 2px 8px !important;
        }
    </style>
    <div class="card">
        <div class="card-header">
            <h4>Purchase</h4>
        </div>

        <div class="card-body">
            <form action="{{ route('supplier/SaveInwardStock') }}" method="POST" id="frmMain" class="needs-validation"
                novalidate enctype="multipart/form-data">
                @csrf
                <div class="d-flex">
                    <input type="hidden" value="{{ request('id') }}" name="id">
                    <div class="">
                        <label>Vendor</label>
                        <select name="vendor_id" id="vendor_id" class="form-control" required>
                            <option value="">Select Vendor</option>
                            @foreach ($vendor as $item)
                                <option value="{{ $item->id }}">{{ $item->company }} </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mx-2" style="width: 150px">
                        <label>PO</label> <br>
                        <select name="po_id" id="po_id" class="form-control" required>
                            <option value="">Select</option>
                        </select>
                    </div>
                    <div class="">
                        <label id="invoiceCheck"> Invoice No</label>
                        <input type="text" name="invoice_no" id="invoice_no" class="form-control"
                            placeholder="Enter Invoice No." required>

                    </div>

                    <div class="mx-2">
                        <label>Invoice Date</label>
                        <input type="date" name="invoice_date" id="invoice_date" class="form-control" required>
                    </div>
                    <div class="">
                        <label>R.M Date</label>
                        <input type="date" name="received_material_date" id="received_material_date" class="form-control"
                            required>
                    </div>
                    <div class="mx-2">
                        <label>Description</label>
                        <input type="text" name="description" id="description" class="form-control"
                            placeholder="Enter Description">
                    </div>

                    <div class="">
                        <label>Invoice Document <span style="color: red">(Max : 1MB)</span></label>
                        <input type="file" name="invoice_file" id="invoice_file" class="form-control">
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
                        <table class="w-100 table">
                            <thead>
                                <tr>
                                    <td>S.No</td>
                                    <td>Product Name</td>
                                    <td data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="When the checkbox is selected and a location is chosen, the system will automatically update all records where the location is currently marked as 'NA">
                                        Check</td>
                                    <td data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Warehouse, Zone, Row, Rack, Shelf, Bin, Store Location">Location</td>
                                    <td data-bs-toggle="tooltip" data-bs-placement="top" title="Actual Quantity">Actual
                                        Qty</td>
                                    <td data-bs-toggle="tooltip" data-bs-placement="top" title="Received Quantity">R. Qty
                                    </td>
                                    <td style="min-width: 4.5rem" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Inward Quantity">Qty </td>
                                    <td style="min-width: 4.5rem" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Product MRP">MRP</td>
                                    <td style="min-width: 4.5rem" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Discount you want to given">Discount (%)</td>
                                    <td style="min-width: 4.5rem" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Final Price">Price</td>
                                    <td data-bs-toggle="tooltip" data-bs-placement="top" title="GST">GST(%)</td>
                                    <td style="min-width: 4.5rem" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Total">Total </td>
                                    <td>Action</td>
                                </tr>
                            </thead>
                            <tbody id="prodList">
                            </tbody>
                            <tfoot>
                                <tr style="font-weight:bold; background:#f8f9fa;">
                                    <td colspan="6" style="text-align:right;min-width:">Total Qty :</td>
                                    <td id="totalItem">0</td>
                                    <td colspan="4" style="text-align:right;">Subtotal :</td>
                                    <td id="subTotalAmount">0.00</td>
                                    <td></td>
                                </tr>

                                <tr style="font-weight:bold; background:#f8f9fa;">
                                    <td colspan="10">
                                        <div id="gstBifurcation" style="text-align: right">

                                        </div>
                                    </td>

                                    <td style="text-align:right;">GST ₹</td>
                                    <td id="totalGst">0.00</td>
                                    <td></td>

                                </tr>
                                <tr style="font-weight:bold; background:#f8f9fa;">
                                    <td colspan="11" style="text-align:right;">Total :</td>
                                    <td id="totalamt">0.00</td>
                                    <td></td>
                                </tr>

                                <tr style="font-weight:bold; background:#f8f9fa;">
                                    <td colspan="11" style="text-align:right;">
                                        Freight Charges
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" id="freight_charges" name="freight_charges"
                                            style="width: 5rem">

                                    </td>
                                    <td></td>
                                </tr>
                                <tr style="font-weight:bold; background:#f8f9fa;">
                                    <td colspan="11" style="text-align:right;">




                                        Loading Charges



                                    </td>
                                    <td>
                                        <input type="number" step="0.01" id="loading_charges" name="loading_charges"
                                            style="width: 5rem">

                                    </td>
                                    <td></td>
                                </tr>

                                <tr style="font-weight:bold; background:#f8f9fa;">
                                    <td colspan="11" style="text-align:right;">
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
                                            min="0" class="totalDiscount" placeholder="Discount" value="0"
                                            style="width: 5rem">
                                    </td>
                                    <td></td>
                                </tr>
                                <tr style="font-weight:bold; background:#f8f9fa;">
                                    <td colspan="11" style="text-align:right;">

                                        Total
                                    </td>
                                    <td>
                                        <span id="totalBeforeRound">0.00</span>

                                    </td>
                                    <td></td>
                                </tr>
                                <tr style="font-weight:bold; background:#f8f9fa;">
                                    <td colspan="11" style="text-align:right;">

                                        Round OFF
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="roundOFF" id="roundOFF"
                                            min="0" class="" placeholder="Round OFF" value="0"
                                            style="width: 5rem">

                                    </td>
                                    <td></td>
                                </tr>
                                <tr style="font-weight:bold; background:#f8f9fa;">
                                    <td colspan="11" style="text-align:right;">

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


    <div class="modal fade" id="invoiceAlert" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="modalTitleId">
                        Alert
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <span id="invoiceNO"> </span> <br> invoice no. already added.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>

                </div>
            </div>
        </div>
    </div>



    <script>
        $(document).ready(function() {

            $("#invoice_no").on("keyup", function() {
                if (!$(this).val()) {
                    $("#invoiceCheck").html("<div class='text-danger'>Invoice No</div>");
                    return;
                }
                let invoice_no = $(this).val()
                $.ajax({
                    url: "/checkInvoiceNo",
                    type: "POST",
                    data: {
                        invoice_id: $(this).val(),
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        $(".btnRefreshProduct").text("Processing...");
                    },
                    success: function(result) {
                        if (result == true) {
                            $("#invoiceCheck").html("<div class='text-danger'>Invoice No</div>")
                            $("#invoiceNO").text(invoice_no)
                            var audio = new Audio('/alert.wav'); // path of sound file
                            audio.volume = 1.0; // full volume
                            audio.play();
                            $("#invoiceAlert").modal("show")

                        } else {
                            $("#invoiceCheck").html(
                                "<div class='text-success'>Invoice No</div>")

                        }
                    },
                    complete: function() {
                        $(".btnRefreshProduct").html("<i class='fa fa-refresh' ></i>");
                    },
                    error: function(result) {
                        toastr.error(result.responseJSON.message);
                    }
                });

            })

            $("#SavePO").attr("disabled", "disabled")

            document.getElementById("discountType").addEventListener("change", function() {
                let symbol = document.getElementById("symbol");

                if (this.checked) {
                    $(symbol).html("&nbsp %")
                } else {
                    $(symbol).html("&nbsp ₹")
                }
            });

            var product_list = [];
            $("#product_id, #po_id, #vendor_id").select2({});



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
                // product_list = [];
                // $("#prodList").html("")
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

                        html += '<option value="">----Select Products----</option>';
                        result.product.forEach(element => {

                            html += '<option value="' + element.id + '" data-mrp="' +
                                element
                                .mrp + '"   data-gst="' +
                                element
                                .gst + '"  data-article_no="' +
                                element
                                .article_no + '" data-warehouse_id="' + element
                                .warehouse_id + '" data-warehouse="' + element
                                .location_code + '" data-location_id="' + element
                                .location_id + '"> ' + element.article_no + ' (' +
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
                        let options = "<option value=''>---Select PO----</option>";

                        result.po_mst.forEach(element => {
                            options +=
                                `<option value="${element.id}">${element.po_id}</option>`;
                        });
                        $("#po_id").html(options)
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


            let isAutoSelecting = false;
            $("#po_id").on("change", function() {
                if (isAutoSelecting) {
                    isAutoSelecting = false; // reset

                    return; // stop ajax

                }

                $.ajax({
                    url: "/GetPODet",
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
                        product_list = [];
                        $("#prodList").html("")

                        result.forEach(element => {

                            let product_id = element.product_id;
                            let product_name = element.product_name;
                            let qty = element.qty - element.received_qty;
                            let mrp = element.mrp;
                            let discount = element.discount;
                            let finalPrice = element.price;
                            let gst = element.gst;
                            let warehouse_id = element.warehouse_id;
                            let warehouse = element.location_code;
                            let location_id = element.location_id;
                            let po_det_id = element.id;
                            let received_qty = element.received_qty;
                            let actual_qty = element.qty;
                            let stock_inward_det_id = 0;
                            if (qty > 0) {



                                addProduct(product_id, product_name, qty, mrp, discount,
                                    finalPrice,
                                    gst, mainID, warehouse_id, warehouse,
                                    location_id,
                                    po_det_id, actual_qty, received_qty,
                                    stock_inward_det_id)
                                mainID++;
                            }

                        });

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


                }


                grandTotal = totalAmount - discountAmount;

                $("#subTotalAmount").text(subTotal.toFixed(2));
                $("#totalItem").text(totalItem);
                $("#totalGst").text(totalGstAmount.toFixed(2));

                $("#totalamt").text(totalAfterGst);
                $("#totalBeforeRound").text(grandTotal)


                let actualTotal = parseFloat(grandTotal);

                // Round to nearest rupee
                let roundedTotal = Math.round(actualTotal);




                $("#roundOFF").val(roundedTotal);
                $("#grandTotal").text(roundedTotal);


            }
            $("#freight_charges, #loading_charges,  #totalDiscount, #discountType").on("keyup click", function() {
                updateFooter();
            });

            $("#roundOFF").on("keyup", function() {
                let roundOFF = parseFloat($("#roundOFF").val()) || 0;
                $("#grandTotal").text(roundOFF);
            })

            $(document).on("input", "#discount-footer", function() {
                updateFooter();
            });
            $("#addProduct").on("click", function() {
                var product_id = parseInt($("#product_id").val());
                var product_name = $("#product_id").find(":selected").text();
                let article_no = $("#article_no").val();
                let qty = parseFloat($("#qty").val()) || 0;
                let actual_qty = 0;
                let received_qty = 0;
                let mrp = parseFloat($("#mrp").val()) || 0;
                let price = parseFloat($("#price").val()) || 0;
                let discount = parseFloat($("#discount").val()) || 0;
                let gst = parseFloat($("#gst").val()) || 0;
                var warehouse_id = $("#product_id").find(":selected").data("warehouse_id");
                var warehouse = $("#product_id").find(":selected").data("warehouse");
                var location_id = $("#product_id").find(":selected").data("location_id");

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
                let po_det_id = 0;
                let stock_inward_det_id = 0;
                addProduct(product_id, product_name, qty, mrp, discount, finalPrice, gst, mainID,
                    warehouse_id, warehouse, location_id, po_det_id, actual_qty, received_qty,
                    stock_inward_det_id);
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

                if (!$("#invoice_date").val()) {
                    toastr.error("Select Invoice Date");
                    return;
                }

                if (!$("#received_material_date").val()) {
                    toastr.error("Select Received Material Date");
                    return;
                }
                if (!$("#invoice_no").val()) {
                    toastr.error("Enter Invoice No.");
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


            function addProduct(product_id, product_name, qty, mrp, discount, finalPrice, gst, mainID, warehouse_id,
                warehouse, location_id, po_det_id, actual_qty, received_qty, stock_inward_det_id) {


                let disabled = "disabled";
                let mrpDisabled = "";
                if (mrp != 0) {
                    disabled = "";
                    mrpDisabled = "disabled"
                }
                let warehouseTd = "";
                let checks = "";
                if (warehouse == null) {
                    checks = `<input type="checkbox" class="selectAll">`;
                    warehouseTd = `<td class="location_id" data-row="${mainID}" >NA</td>`;
                } else {
                    checks = `<input type="checkbox" class="selectAll">`;
                    warehouseTd = `<td class="location_id" data-row="${mainID}" >${warehouse}</td>`;
                }

                let total = finalPrice * qty;
                var html = `<tr class="product${mainID}" data-mainID="${mainID}">
                <td>${sno++}</td>    
                <td class="changeProductID" data-row="${mainID}"  style="width:100%" >${product_name}</td>
                <td>${checks}</td>
                    ${warehouseTd}
                    <td>${actual_qty}</td>
                    <td>${received_qty}</td>
 
                <td> <input type="number"  style="width:100%" value="${qty}" class="updateValues" data-name="qty" data-product_id="${mainID}"> </td>     
                    <td> <input type="number" style="width:100%" value="${mrp}" class="updateValues" data-name="mrp" data-product_id="${mainID}" ${mrpDisabled}> </td>   
                    <td> <input type="number"style="width:100%"  value="${discount}" class="updateValues" data-name="discount" data-product_id="${mainID}" ${disabled}> </td>    
                    <td> 
                        <input type="number" style="width:100%"  value="${finalPrice}" class="updateValues" data-name="price" data-product_id="${mainID}"> 
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
                    total,
                    warehouse_id,
                    location_id,
                    po_det_id,
                    stock_inward_det_id
                });

                updateFooter();
                gstBifurcation();
                resetSno();
                checkAllWarehouseID();
                console.log(product_list);

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
                console.log(product_list);



            })
            let mst = {!! json_encode($mst) !!};
            let det = {!! json_encode($det) !!};


            if (mst && Object.keys(mst).length > 0) {


                let total = 0;
                $.each(mst, function(i, o) {

                    $("select[name=" + i + "]").val(o)

                })
                $("#invoice_no").val(mst.invoice_no)
                $("#invoice_date").val(mst.invoice_date)
                $("#received_material_date").val(mst.received_material_date)
                $("#description").val(mst.description)
                $("#vendor_id").trigger("change");
                setTimeout(() => {
                    isAutoSelecting = true;
                    $("#po_id").val(mst.po_id)

                    $("#po_id").trigger("change")
                }, 300);





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
                    let warehouse_id = element.warehouse_id;
                    let warehouse = element.warehouse;
                    let location_id = element.location_id;
                    let po_det_id = element.po_det_id;
                    let actual_qty = element.qty;
                    let received_qty = "NA";
                    let stock_inward_det_id = element.id

                    addProduct(product_id, product_name, qty, mrp, discount, finalPrice, gst, mainID,
                        warehouse_id, warehouse, location_id, po_det_id, actual_qty, received_qty,
                        stock_inward_det_id);

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
            // $("#BtnUpload").on("click", function() {

            //     let fileInput = document.getElementById('file');
            //     let file = fileInput.files[0];
            //     if (file) {
            //         // Create a new FormData object
            //         let formData = new FormData();
            //         formData.append('file', file);

            //         $.ajax({
            //             url: "/UploadPORequirementList",
            //             type: "POST",
            //             data: formData,
            //             processData: false,
            //             contentType: false,
            //             headers: {
            //                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            //             },
            //             success: function(result) {
            //                 var row = "";
            //                 let sno = 1;
            //                 data = JSON.parse(result)
            //                 data.data.forEach(element => {
            //                     row += `
        //                 <tr class="prod${element.id}">
        //                 <td>${sno++}</td>
        //                 <td colspan="2">${element.name}</td>
        //                 <td>${element.qty}</td>
        //                     <td>${element.mrp}</td>
        //                     <td>${element.gst}</td>
        //                     <td>${element.base_price*element.qty}</td>
        //                     <td><button onclick="removeItem(${element.qty})" class="btn btn-sm btn-danger" type="button"><i class="fa fa-trash" aria-hidden="true"></i></button></td>
        //                 </tr>
        //             `;
            //                     let product_id = element.id;
            //                     let qty = element.qty;
            //                     let price = element.mrp;
            //                     let gst = element.gst;

            //                     product_list.push({
            //                         product_id,
            //                         qty,
            //                         price,
            //                         gst,
            //                     });
            //                 });

            //                 $('#prodList').append(row);
            //                 console.log(product_list);

            //             },
            //             error: function(data) {
            //                 console.log(data);

            //             }
            //         });
            //     } else {
            //         toastr.error("Select CSV file for upload");
            //     }

            // });


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


            let currentProductEditing = null;
            let currentEditing = null; // { rowId, td, originalText, originalProductId, selectEl }

            $(document).on("click", ".location_id", function(ev) {
                ev.stopPropagation();

                let td = $(this);
                let rowId = td.data("row");
                let product_id = rowId;

                let tr = td.closest("tr");
                let isChecked = tr.find("td").eq(2).find("input[type='checkbox']").prop("checked");




                // If we clicked the same cell being edited, do nothing
                if (currentEditing && currentEditing.rowId === rowId) return;

                // If another cell is open, close it and restore text
                if (currentEditing) {
                    closeAndRestoreCurrent();
                }

                // Save original text so we can restore it if needed
                const originalText = td.text().trim();

                // Put an empty select into TD (no preselected option)
                td.html(
                    `<select id="catalog_select_${rowId}" class="catalog_select" style="width:300%"></select>`
                );

                const selectEl = $(`#catalog_select_${rowId}`);

                // remember current editing state
                currentEditing = {
                    rowId,
                    td,
                    originalText,
                    originalProductId: product_id,
                    selectEl,
                    isChecked
                };

                initSelect2InTd(rowId, td, product_id, selectEl, isChecked);
            });

            // helper to close current select2 and restore original text
            function closeAndRestoreCurrent() {
                if (!currentEditing) return;

                try {
                    // destroy select2 if initialized
                    if (currentEditing.selectEl && currentEditing.selectEl.data('select2')) {
                        currentEditing.selectEl.select2('destroy');
                    }


                } catch (e) {
                    console.warn('Error destroying select2', e);
                }

                // restore plain text
                currentEditing.td.html(currentEditing.originalText);

                // clear state
                currentEditing = null;
            }

            // click outside to cancel and restore
            $(document).on('click', function(e) {
                // if clicking inside a select2 dropdown or inside the currently editing td, ignore
                if (!currentEditing) return;


                const $target = $(e.target);
                if ($target.closest('.select2-container').length) return;
                if ($target.closest(currentEditing.td).length) return;

                // otherwise close and restore
                closeAndRestoreCurrent();
            });

            $(document).on('click', function(e) {
                // if clicking inside a select2 dropdown or inside the currently editing td, ignore
                if (!currentProductEditing) return;


                const $target = $(e.target);
                if ($target.closest('.select2-container').length) return;
                if ($target.closest(currentProductEditing.td).length) return;

                // otherwise close and restore
                closeAndRestoreCurrent();
            });

            // robust init function
            function initSelect2InTd(rowId, tdElement, product_id, selectEl, isChecked) {
                // make sure we have a jQuery object for dropdownParent
                const dropdownParent = tdElement;

                selectEl.select2({
                    placeholder: "Search Location",
                    minimumInputLength: 1,
                    dropdownParent: dropdownParent,
                    allowClear: true,
                    ajax: {
                        url: "/getLocationPurchase",
                        dataType: "json",
                        delay: 250,
                        processResults: function(data) {
                            // Expect data as array of { id, text, name, price } — adjust if your API differs
                            return {
                                results: data.map(item => ({
                                    id: item.location_id,
                                    text: item.location_code,
                                    name: item.location_code,

                                    warehouse_id: item.warehouse_id
                                }))
                            };
                        },
                        error: function(xhr, status, err) {
                            console.error("Select2 AJAX error", status, err);
                        }
                    },
                    templateResult: function(data) {
                        return data && data.text ? data.text : null;
                    },
                    templateSelection: function(data) {
                        return data && data.text ? data.text : null;
                    }
                });

                // open the dropdown and focus the search input
                selectEl.select2('open');

                // ensure search input is focused (Select2 sometimes needs this)
                setTimeout(() => {
                    const search = $('.select2-container--open').find('input.select2-search__field')
                        .first();
                    if (search.length) search.focus().select();
                }, 50);

                // when user selects an item
                selectEl.on("select2:select", function(e) {
                    const product = e.params.data;

                    // replace select box with plain text (catalog_no)
                    tdElement.html(product.text);

                    // put product name into the next TD (as you used earlier)
                    // tdElement.next().text(product.name || '');

                    // tdElement.next().next().next().text(product.price || '');

                    // update product_list array: find by original product id stored in currentEditing
                    if (currentEditing) {

                        let origId = currentEditing.originalProductId;

                        let pl = product_list.find(x => x.mainID === origId);
                        if (pl) {
                            pl.location_id = product.id;
                            pl.warehouse_id = product.warehouse_id;

                            if (isChecked === true) {

                                product_list.forEach(item => {
                                    if (item.warehouse_id == null && item.location_id == null) {
                                        item.warehouse_id = product.warehouse_id;
                                        item.location_id = product.id;

                                        let row = $("tr[data-mainID='" + item.mainID + "']");
                                        console.log(row.find(".location_id"));
                                        row.find(".location_id").html(product.text);
                                    }
                                });


                                console.log("Updated all null warehouse/location rows", product_list);
                            }
                            checkAllWarehouseID();
                        } else {
                            // fallback: try to match by rowId if your product_list stores row info
                            console.warn("Could not find product in product_list by original id", origId);
                        }
                    }

                    // clear currentEditing
                    if (selectEl.data('select2')) selectEl.select2('destroy');
                    currentEditing = null;
                });

                // optional: if user clears or closes without selection, restore original
                selectEl.on('select2:clear', function() {
                    closeAndRestoreCurrent();
                });
            }

            function checkAllWarehouseID() {

                let isInvalid = product_list.some(item =>
                    item.product_id == null ||
                    item.warehouse_id == null
                );

                if (isInvalid) {
                    $("#SavePO").prop("disabled", true);
                } else {
                    $("#SavePO").prop("disabled", false);
                }
            }

            // { rowId, td, originalText, originalProductId, selectEl }

            $(document).on("click", ".changeProductID", function(ev) {
                ev.stopPropagation();

                let td = $(this);
                let rowId = td.data("row");
                let product_id = rowId;

                let tr = td.closest("tr");
                let isChecked = tr.find("td").eq(2).find("input[type='checkbox']").prop("checked");




                // If we clicked the same cell being edited, do nothing
                if (currentProductEditing && currentProductEditing.rowId === rowId) return;

                // If another cell is open, close it and restore text
                if (currentProductEditing) {
                    closeAndRestoreCurrent();
                }

                // Save original text so we can restore it if needed
                const originalText = td.text().trim();

                // Put an empty select into TD (no preselected option)
                td.html(
                    `<select id="productID${rowId}" class="productID" style="width:150%"></select>`
                );

                const selectEl = $(`#productID${rowId}`);

                // remember current editing state
                currentProductEditing = {
                    rowId,
                    td,
                    originalText,
                    originalProductId: product_id,
                    selectEl,
                    isChecked
                };

                initSelect2ProductInTd(rowId, td, product_id, selectEl, isChecked);
            });


            function initSelect2ProductInTd(rowId, tdElement, product_id, selectEl, isChecked) {
                // make sure we have a jQuery object for dropdownParent
                const dropdownParent = tdElement;

                selectEl.select2({
                    placeholder: "Search Product",
                    minimumInputLength: 1,
                    dropdownParent: dropdownParent,
                    allowClear: true,
                    ajax: {
                        url: "/getPOProducts",
                        dataType: "json",
                        delay: 250,
                        processResults: function(data) {
                            // Expect data as array of { id, text, name, price } — adjust if your API differs
                            return {
                                results: data.map(item => ({
                                    id: item.product_id,
                                    text: item.product_name + " / " + item.location_code,
                                    name: item.product_name + " / " + item.location_code,

                                    warehouse_id: item.warehouse_id,
                                    product_id: item.product_id,
                                    location_id: item.location_id
                                }))
                            };
                        },
                        error: function(xhr, status, err) {
                            console.error("Select2 AJAX error", status, err);
                        }
                    },
                    templateResult: function(data) {
                        return data && data.text ? data.text : null;
                    },
                    templateSelection: function(data) {
                        return data && data.text ? data.text : null;
                    }
                });

                // open the dropdown and focus the search input
                selectEl.select2('open');

                // ensure search input is focused (Select2 sometimes needs this)
                setTimeout(() => {
                    const search = $('.select2-container--open').find('input.select2-search__field')
                        .first();
                    if (search.length) search.focus().select();
                }, 50);

                // when user selects an item
                selectEl.on("select2:select", function(e) {
                    const product = e.params.data;

                    // replace select box with plain text (catalog_no)
                    tdElement.html(product.text);

                    // put product name into the next TD (as you used earlier)
                    // tdElement.next().text(product.name || '');



                    // update product_list array: find by original product id stored in currentEditing
                    if (currentProductEditing) {

                        let origId = currentProductEditing.originalProductId;
                        tdElement.next().html('<td><input type="checkbox" class="selectAll"></td>');
                        tdElement.next().next().html("<td class='location_id' data-row='" + origId +
                            "' >NA</td>");

                        let pl = product_list.find(x => x.mainID === origId);
                        if (pl) {
                            pl.location_id = product.location_id;
                            pl.warehouse_id = product.warehouse_id;
                            pl.product_id = product.product_id;


                            console.log(product_list);
                        } else {
                            // fallback: try to match by rowId if your product_list stores row info
                            console.warn("Could not find product in product_list by original id", origId);
                        }
                    }
                    checkAllWarehouseID();
                    // clear currentEditing
                    if (selectEl.data('select2')) selectEl.select2('destroy');
                    currentProductEditing = null;
                });

                // optional: if user clears or closes without selection, restore original
                selectEl.on('select2:clear', function() {
                    closeAndRestoreCurrent();
                });
            }
        });
    </script>
@endsection
