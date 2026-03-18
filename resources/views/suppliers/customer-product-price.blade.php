@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title>Customers Product </title>
    @endpush



    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    Customer Product
                </div>
                <div>
                    <a href="/supplier/customer-product-list/{{ $id }}"><button class="btn btn-primary add"
                            type="button">View Products</button></a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form id="productForm">
                @csrf
                <input type="hidden" id="customer_id" value="{{ $id }}">

                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label>Select Product</label>
                        <select id="product_id" class="form-control select2"></select>
                    </div>

                    <div class="col-md-2">
                        <label>MRP</label>
                        <input type="number" id="mrp" class="form-control" readonly>
                    </div>

                    <div class="col-md-2">
                        <label>Base Price</label>
                        <input type="number" id="base_price" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <button type="button" id="addProduct" class="btn btn-primary w-100">
                            Add
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body">
            <table class="table table-bordered mt-4" id="productTable">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>MRP</th>
                        <th>Base Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <button class="btn btn-success d-none" id="saveAll">Save All</button>
        </div>

    </div>

    <script>
        let products = [];

        $(document).ready(function() {

            // Select2 AJAX
            $('#product_id').select2({
                placeholder: "Search product",
                minimumInputLength: 1,
                ajax: {
                    url: "{{ url('product-search') }}",
                    dataType: 'json',
                    delay: 300,
                    data: params => ({
                        q: params.term
                    }),
                    processResults: data => ({
                        results: data.map(item => ({
                            id: item.id,
                            text: item.name,
                            mrp: item.mrp,
                            base_price: item.base_price
                        }))
                    })
                }
            });

            // Auto MRP
            $('#product_id').on('select2:select', function(e) {
                $('#mrp').val(e.params.data.mrp);
                $('#base_price').val(e.params.data.base_price);
            });

            // Add Product
            $('#addProduct').on('click', function() {
                let productId = $('#product_id').val();
                let productText = $('#product_id option:selected').text();
                let mrp = $('#mrp').val();
                let basePrice = $('#base_price').val();

                if (!productId || !basePrice) {
                    alert('Please fill all fields');
                    return;
                }

                // prevent duplicate
                if (products.find(p => p.product_id == productId)) {
                    alert('Product already added');
                    return;
                }

                products.push({
                    product_id: productId,
                    product_name: productText,
                    mrp: mrp,
                    base_price: basePrice,
                });
                renderTable();
                $('#product_id').val(null).trigger('change');
                $('#mrp').val('');
                $('#base_price').val('');
            });
            $(document).on('click', '.removeRow', function() {
                let index = $(this).data('index');
                products.splice(index, 1);
                renderTable();
            });

            $('#saveAll').on('click', function() {
                $.ajax({
                    url: "{{ route('customer-product-save') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        customer_id: $('#customer_id').val(),
                        products: products
                    },
                    success: function(res) {
                        alert('Products saved successfully');
                        products = [];
                        renderTable();
                    }
                });
            });

            function renderTable() {
                let html = '';

                products.forEach((p, i) => {
                    html += `
            <tr>
                <td>${p.product_name}</td>
                <td>${p.mrp}</td>
                <td>${p.base_price}</td>
                <td>
                    <button class="btn btn-danger btn-sm removeRow" data-index="${i}">
                        Remove
                    </button>
                </td>
            </tr>
        `;
                });

                $('#productTable tbody').html(html);
                if (products.length > 0) {
                    $('#saveAll').removeClass('d-none');
                } else {
                    $('#saveAll').addClass('d-none');
                }
            }
        });
    </script>
@endsection
