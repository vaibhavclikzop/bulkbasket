@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title>Challan Edit</title>
    @endpush

    <div class="card">
        <div class="card-header">
            <div class="card-body d-flex justify-content-between align-items-center">

                <div>
                    <h6 class="mb-1">Challan Edit</h6>

                    <small class="text-muted">Order ID</small><br>
                    <span class="fw-bold text-success">
                        {{ $orders->order_id }}
                    </span>
                </div>

                <div class="text-end">
                    <small class="text-muted">Active Amount</small><br>
                    <span class="fw-bold text-warning fs-5">
                        ₹
                        {{ ($orders->wallet ?? 0) - (($orders->hold_amount ?? 0) + ($orders->used_wallet ?? 0)) }}
                    </span>
                </div>
            </div>
        </div>

        <form id="estimateForm" action="{{ route('supplier/updateChallan') }}" method="POST">
            @csrf
            <input type="hidden" name="estimate_id" value="{{ $orders->id }}">
            <input type="hidden" name="supplier_id" value="{{ $orders->supplier_id }}">
            <input type="hidden" name="invoice_no" value="{{ $orders->invoice_no }}">
            <input type="hidden" name="pay_mode" value="{{ $orders->pay_mode }}">
            <input type="hidden" name="name" value="{{ $orders->name }}">
            <input type="hidden" name="city" value="{{ $orders->customer_city }}">
            <input type="hidden" name="supplier_address" value="{{ $orders->supplier_address }}">
            <input type="hidden" name="name" value="{{ $orders->customer_name }}">
            <input type="hidden" name="number" value="{{ $orders->customer_number }}">
            <input type="hidden" name="email" value="{{ $orders->customer_email }}">
            <input type="hidden" name="total_amount" value="{{ $orders->total_amount }}">
            <input type="hidden" name="customer_id" value="{{ $orders->customer_id ?? '' }}">
            <div class="card-body">
                <h5 class="mb-3">Customer Name: {{ $orders->customer_name }}</h5>
                <div class="row">
                    <div class="mt-3">
                        <h6>Billing Address <span class="text-warning customer_gst"> </span></h6>
                        <div class="d-flex ">
                            <div class="">
                                <label for="">Select State</label>
                                <select name="billing_state" class="form-control billing_state" disabled>
                                    <option value="">Select State</option>
                                    @foreach ($state as $item)
                                        <option value="{{ $item->state }}"> {{ $item->state }}</option>
                                    @endforeach

                                </select>
                            </div>
                            <div class="mx-2">
                                <label for="">District</label>
                                <select name="billing_district" class="form-control billing_district" disabled>
                                    <option value="">Select District</option>
                                </select>
                            </div>
                            <div>
                                <label for="">City</label>
                                <input type="text" name="billing_city" class="form-control billing_city" disabled
                                    placeholder="Enter City">
                            </div>
                            <div class="mx-2" style="width: 55%">
                                <label for="">Address</label>
                                <input type="text" placeholder="Enter Address" name="billing_address"
                                    class="form-control billing_address" disabled>
                            </div>

                        </div>
                    </div>
                    <div class="mt-3 mb-4">
                        <h6>Shipping Address</h6>
                        <div class="d-flex ">
                            <div class="">
                                <label for="">Select State</label>
                                <select name="state" id="state" class="form-control shipping_state" required>
                                    <option value="">Select State</option>
                                    @foreach ($state as $item)
                                        <option value="{{ $item->state }}"> {{ $item->state }}</option>
                                    @endforeach

                                </select>
                            </div>
                            <div class="mx-2">
                                <label for="">District</label>
                                <select name="district" id="district" class="form-control shipping_district" required>
                                    <option value="">Select District</option>
                                </select>
                            </div>
                            <div>
                                <label for="">City</label>
                                <input type="text" name="city" id="city" class="form-control shipping_city"
                                    required placeholder="Enter City">
                            </div>
                            <div class="mx-2" style="width: 55%">
                                <label for="">Address</label>
                                <input type="text" placeholder="Enter Address" name="address" id="address"
                                    class="form-control shipping_address"required>
                            </div>

                        </div>
                    </div>
                </div>

                <div>
                    <span>
                        <h5 class="mt-3">Order Estimate List </h5>

                </div>
                <div class="text-end mb-2">
                    <button type="button" class="btn btn-sm btn-success" id="addEstimateProduct">+ Add Product</button>
                </div>

                <table class="w-100 mt-2" id="estimateTable">
                    <thead>
                        <th style="border: solid 1px; padding:2px">S.No</th>
                        <th style="border: solid 1px; padding:2px">Description of goods</th>
                        <th style="border: solid 1px; padding:2px">Price</th>
                        <th style="border: solid 1px; padding:2px">Qty</th>
                        {{-- <th style="border: solid 1px; padding:2px">Taxable</th> --}}
                        <th style="border: solid 1px; padding:2px">Action</th>
                    </thead>
                    <tbody>
                        @foreach ($det as $item)
                            @php
                                $bulkPrices = DB::table('product_price')
                                    ->where('product_id', $item->product_id)
                                    ->orderBy('qty', 'asc')
                                    ->get();
                            @endphp
                            <tr data-base-price="{{ $item->base_price }}"
                                data-bulk-prices='@json($bulkPrices, JSON_HEX_APOS | JSON_HEX_QUOT)'>
                                <td style="border: solid 1px; padding:2px">{{ $loop->iteration }}</td>
                                <td style="border: solid 1px; padding:2px">{{ $item->name }}🟢 Stock:
                                    {{ $item->current_stock }}📊 GST:{{ $item->gst }}%</td>
                                <td style="border: solid 1px; padding:2px">
                                    <input type="number" class="form-control form-control-sm price-input"
                                        value="{{ $item->price }}" name="price[]">
                                </td>
                                <td style="border: solid 1px; padding:2px">
                                    <input type="number" class="qty-input form-control form-control-sm"
                                        value="{{ $item->qty }}" name="qty[]" style="width:80px;">
                                </td>
                                {{-- <td style="border: solid 1px; padding:2px" class="taxable">
                                    {{ number_format($item->price * $item->qty, 2) }}</td> --}}
                                <td style="border: solid 1px; padding:2px">
                                    <button type="button" class="btn btn-sm btn-danger remove-btn">Remove</button>
                                </td>
                                <input type="hidden" name="product_id[]" value="{{ $item->product_id }}">
                                <input type="hidden" name="product_name[]" value="{{ $item->name }}">
                                <input type="hidden" name="description[]" value="{{ $item->description }}">
                                <input type="hidden" name="gst[]" value="{{ $item->gst }}">
                                <input type="hidden" name="cess_tax[]" value="{{ $item->cess_tax }}">
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-primary">Save Challan</button>
                </div>
            </div>
        </form>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tbody = document.querySelector("#estimateTable tbody");
            const addBtn = document.getElementById("addEstimateProduct");

            function updateRowPrice(row, qty) {
                let basePrice = parseFloat(row.dataset.basePrice || 0);
                let bulkPrices = JSON.parse(row.dataset.bulkPrices || "[]");
                let finalPrice = basePrice;
                bulkPrices.forEach(rule => {
                    if (qty >= rule.qty) finalPrice = parseFloat(rule.price);
                });
                row.querySelector(".price-input").value = finalPrice;
                // row.querySelector(".taxable").textContent = (finalPrice * qty).toFixed(2);
            }

            function attachQtyListener(input, row) {
                input.addEventListener("input", function() {
                    const qty = parseInt(this.value) || 0;
                    updateRowPrice(row, qty);
                    resetSerialNumbers();
                });
            }

            function attachRemoveListener(btn) {
                btn.addEventListener("click", function() {
                    if (confirm("Remove this product?")) {
                        this.closest("tr").remove();
                    }
                });
            }

            function resetSerialNumbers() {
                Array.from(tbody.querySelectorAll("tr")).forEach((tr, index) => {
                    tr.cells[0].textContent = index + 1;
                });
            }

            function initProductDropdown(selectEl, row) {
                $(selectEl).select2({
                    width: "100%",
                    placeholder: "-- Select Product --",

                    ajax: {
                        url: "{{ url('/supplier/get-products') }}",
                        dataType: "json",
                        delay: 250,

                        data: params => ({
                            search: params.term || "",
                            page: params.page || 1
                        }),

                        processResults: function(data) {
                            return {
                                results: data.results.map(p => ({
                                    id: p.id,
                                    text: p.name,
                                    base_price: p.base_price,
                                    current_stock: p.current_stock,
                                    gst: p.gst
                                }))
                            };
                        },

                        cache: true
                    },
                    templateResult: function(data) {
                        if (!data.id) return data.text;
                        return $(`
                        <div>
                            <strong>${data.text}</strong><br>
                            <small>
                                Stock: ${data.current_stock ?? 0} |
                                GST: ${data.gst ?? 0}%
                            </small>
                        </div>
                    `);
                    },

                    templateSelection: function(data) {
                        if (!data.id) return data.text;

                        return `${data.text} 🟢 Stock: ${data.current_stock ?? 0} 📊 GST: ${data.gst ?? 0}%`;
                    }
                });
                $(selectEl).on("select2:select", function(e) {
                    const productId = e.params.data.id;
                    const data = e.params.data;
                    row.dataset.productId = productId;
                    row.querySelector('input[name="product_id[]"]').value = productId;
                    row.querySelector('input[name="product_name[]"]').value = data.text;
                    fetch(`/supplier/get-product-details/${productId}`)
                        .then(res => res.json())
                        .then(data => {
                            row.dataset.basePrice = data.product.base_price;
                            row.dataset.bulkPrices = JSON.stringify(data.prices);
                            row.querySelector(".qty-input").value = 1;
                            updateRowPrice(row, 1);
                        });
                });
            }

            addBtn.addEventListener("click", function() {
                let newRow = document.createElement("tr");
                newRow.innerHTML = `
            <td style="border: solid 1px; padding:2px">1</td>
            <td style="border: solid 1px; padding:2px"><select class="form-control form-control-sm product-select"></select></td>
            <td style="border: solid 1px; padding:2px"><input type="number" class="form-control form-control-sm price-input" placeholder="0"   name="price[]"></td>
            <td style="border: solid 1px; padding:2px"><input type="number" class="qty-input form-control form-control-sm" value="1" name="qty[]" style="width:80px;"></td>
             
            <td style="border: solid 1px; padding:2px"><button type="button" class="btn btn-sm btn-danger remove-btn">Remove</button></td>
            <input type="hidden" name="product_id[]" value="">
            <input type="hidden" name="product_name[]" value="">
        `;
                tbody.prepend(newRow);
                attachQtyListener(newRow.querySelector(".qty-input"), newRow);
                attachRemoveListener(newRow.querySelector(".remove-btn"));
                initProductDropdown(newRow.querySelector(".product-select"), newRow);
                resetSerialNumbers();
            });

            tbody.querySelectorAll("tr").forEach(row => {
                attachQtyListener(row.querySelector(".qty-input"), row);
                attachRemoveListener(row.querySelector(".remove-btn"));
                updateRowPrice(row, parseInt(row.querySelector(".qty-input").value));
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            let order = @json($orders);
            // ================= BILLING (CUSTOMERS TABLE) =================
            $('.billing_state').val(order.customer_state);
            $('.billing_district').html(`<option>${order.customer_district ?? ''}</option>`);
            $('.billing_city').val(order.customer_city);
            $('.billing_address').val(order.customer_address);
            // ================= SHIPPING (ORDER_ESTIMATE TABLE) =================
            $('.shipping_state').val(order.supplier_state);
            $('.shipping_district').html(`<option>${order.supplier_district ?? ''}</option>`);
            $('.shipping_city').val(order.supplier_city);
            $('.shipping_address').val(order.supplier_address);
            // ================= GST =================
            $('.customer_gst').html('(GST : ' + (order.gst ?? 'N/A') + ')');
        });
    </script>
@endsection
