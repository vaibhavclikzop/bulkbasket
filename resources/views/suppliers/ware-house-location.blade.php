@extends('suppliers.layouts.main')

<style>
    .custom-dropdown {
        position: relative;
        width: 100%;
    }

    .dropdown-list {
        position: absolute;
        width: 100%;
        max-height: 220px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        z-index: 1056;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
    }

    .dropdown-item {
        padding: 10px 0px 0px 10px !important;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
    }

    .dropdown-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .dropdown-item:hover {
        background: #f8f9fa;
    }

    #selectedProducts {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .selected-badge {
        display: inline-flex;
        align-items: center;
        background: #1AA053;
        color: #fff;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 13px;
        white-space: nowrap;
    }

    .selected-badge span {
        margin-left: 8px;
        cursor: pointer;
        font-weight: bold;
    }

    @media (max-width: 576px) {
        .dropdown-list {
            max-height: 180px;
        }

        .dropdown-item {
            padding: 12px;
            font-size: 15px;
        }

        .selected-badge {
            font-size: 12px;
            padding: 6px 8px;
        }

        .modal-body {
            padding: 15px;
        }
    }
</style>

@section('main-section')
    @push('title')
        <title>Ware House</title>
    @endpush



    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    <h5>{{ $warehouseName->name ?? 'Ware House' }}({{ $warehouseName->code }})</h5>

                </div>
                <div>
                    <a href="{{ route('supplier/warehouseLocationPending', $warehouseName->id) }}"
                        class="btn btn-warning btn-sm">Pending
                        Product Allocation</a>
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                        data-bs-target="#importModalLocation">
                        <i class="fa fa-download" aria-hidden="true"></i> Import Warehouse Location
                    </button>
                    <button class="btn btn-sm btn-primary addwarehouselocation" type="button">Add Ware House
                        Location</button>

                </div>
            </div>
            <div class="row" style="justify-content: center">
                <form method="GET">
                    <div class="row"> 
                        <div class="col-md-3 mt-2">
                            <label>Select Zone</label>
                            <select name="zone_category" class="form-control">
                                <option value="">All Zone</option>

                                @foreach ($warehouseZone as $item)
                                    <option value="{{ $item->id }}"
                                        {{ request('zone_category') == $item->id ? 'selected' : '' }}>
                                        {{ $item->zone_code }}
                                    </option>
                                @endforeach
                            </select>
                        </div> 
                        <div class="col-md-3 mt-2">
                            <label>Search</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                                placeholder="Search Row / Rack / Bin / Code">
                        </div> 
                        <div class="col-md-5 mt-4">
                            <button class="btn btn-primary">Filter</button>
                            <a href="{{ url()->current() }}" class="btn btn-secondary">
                                Reset
                            </a>
                        </div>

                    </div>
                </form>

            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Zone</th>
                        <th>Row</th>
                        <th>Rack</th>
                        <th>Shelf</th>
                        <th>Bin</th>
                        <th>Store</th>
                        <th>Location Code</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sno = 1;
                    @endphp
                    @foreach ($warehouseLocation as $item)
                        <tr>
                            <td>{{ $sno++ }}</td>
                            <td>{{ $item->warehouseZone->zone_code }}</td>
                            <td>{{ $item->row }}</td>
                            <td>{{ $item->rack }}</td>
                            <td>{{ $item->shelf }}</td>
                            <td>{{ $item->bin }}</td>
                            <td>{{ $item->store ?? "--" }}</td>
                            <td>{{ $item->location_code }}</td>
                            <td>
                                @if ($item->is_active == 1)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary editwarehouse" data-id="{{ $item->id }}"
                                    data-zone_id="{{ $item->zone_id }}" data-row="{{ $item->row }}"
                                    data-rack="{{ $item->rack }}" data-shelf="{{ $item->shelf }}"
                                    data-bin="{{ $item->bin }}" data-store="{{ $item->store }}"
                                    data-is_active="{{ $item->is_active }}">
                                    Edit
                                </button>
                                @if ($item->is_active == 1)
                                    <button id="productLocation" data-warehouse="{{ $warehouseName->id }}"
                                        data-location="{{ $item->id }}" class="btn btn-sm btn-info">
                                        Product Allocation
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-3">
                {{ $warehouseLocation->links() }}
            </div>
        </div>
    </div>


    <form action="{{ route('supplier/saveWareHouseLocation') }}" method="POST" class="needs-validation" novalidate>
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
                            <input type="hidden" name="id" id="ware_id">
                            <input type="hidden" name="warehouse_id" value="{{ request()->id }}">
                            <div class="col-md-4 mt-3">
                                <label for="">Zone</label>
                                <select name="zone_id" id="zone_id" class="form-control" required>
                                    <option value="" selected disabled>Select Zone</option>
                                    @foreach ($warehouseZone as $item)
                                        <option value="{{ $item->id }}">{{ $item->zone_code }}</option>
                                    @endforeach

                                </select>
                            </div>

                            <div class="col-md-4 mt-3">
                                <label for="">Row</label>
                                <input type="text" name="row" id="row" placeholder="A" class="form-control"
                                    required>
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="">Rack</label>
                                <input type="text" name="rack" id="rack" placeholder="01" class="form-control"
                                    required>
                            </div>

                            <div class="col-md-4 mt-3">
                                <label for="">Shelf </label>
                                <input type="text" name="shelf" id="shelf" placeholder="L01"
                                    class="form-control">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="">Bin</label>
                                <input type="text" name="bin" id="bin" placeholder="B01"
                                    class="form-control">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="">Store</label>
                                <input type="text" name="store" id="store" placeholder="Store"
                                    class="form-control">
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

    <form id="importForm" action="{{ route('supplier/importWareHouseLocation') }}" method="POST"
        enctype="multipart/form-data">

        @csrf
        <div class="modal fade" id="importModalLocation" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Import Warehouse Location</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <input type="file" name="file" class="form-control" required>
                            </div>
                            <div>
                                <a class="btn btn-success" href="/import-warehouse-location.csv"
                                    download="/import-warehouse-location.csv">Download Sample File</a>
                            </div>

                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="alert alert-danger" role="alert">
                                    <strong>Instructions</strong>
                                </div>
                                <div class="mx-3">
                                    <ul style="list-style:decimal">
                                        <li>First download sample file.</li>
                                        <li>Add your data in sample file.</li>
                                        <li>Before upload please check <span class="text-danger"><u>Ware House
                                                    Name</u></span> exit.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="progress mt-3 d-none" id="progressBox">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                            style="width:0%" id="progressBar">
                            0%
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="importBtn" class="btn btn-dark">
                            Import
                        </button>


                    </div>
                </div>
            </div>
        </div>
    </form>

    <form action="{{ route('supplier/wareHouseProductLocation') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal fade" id="modalproductLocation" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="title">Ware House Product Location</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <input type="hidden" name="warehouse_id" id="warehouse_id">
                        <input type="hidden" name="warehouse_location_id" id="warehouse_location_id">

                        <div class="mb-3">
                            <label class="form-label">Select Products</label>

                            <div class="custom-dropdown">
                                <input type="text" id="productSearch" class="form-control mb-2"
                                    placeholder="Search products..." autocomplete="off">

                                <div id="productDropdown" class="dropdown-list d-none"></div>
                            </div>

                            <!-- Selected products will be appended here -->
                            <div id="selectedProducts" class="mt-2"></div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-dark">
                            Submit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        $(".addwarehouselocation").on("click", function() {
            $("#modalTitleId").text("Add Ware House Location");
            $("form")[0].reset();
            $("#ware_id").val('');
            $("#zone_id").val('');
            $("#row").val('');
            $("#rack").val('');
            $("#shelf").val('');
            $("#bin").val('');
            $("#store").val('');
            $("#is_active").val('');
            $("#modalIdwarehouse").modal("show");
        });
        $(document).on("click", ".editwarehouse", function() {
            $("#modalTitleId").text("Edit Ware House Location");
            $("#ware_id").val($(this).data("id"));
            $("#zone_id").val($(this).data("zone_id"));
            $("#row").val($(this).data("row"));
            $("#rack").val($(this).data("rack"));
            $("#shelf").val($(this).data("shelf"));
            $("#bin").val($(this).data("bin"));
            $("#store").val($(this).data("store"));
            $("#is_active").val($(this).data("is_active"));
            $("#modalIdwarehouse").modal("show");
        });
    </script>

    <script>
        let selectedProducts = {};

        function loadProducts(search = "") {

            fetch(`{{ url('supplier/get-products') }}?search=${search}`)
                .then(res => res.json())
                .then(data => {

                    let html = '';

                    data.results.forEach(p => {
                        const checked = selectedProducts[p.id] ? 'checked' : '';

                        html += `
                    <div class="dropdown-item">
                        <input type="checkbox"
                               value="${p.id}"
                               ${checked}
                               onclick="toggleProduct(${p.id}, '${p.name}')">
                        <span>${p.name}</span>
                    </div>`;
                    });

                    $("#productDropdown").html(html).removeClass('d-none');
                });
        }

        function toggleProduct(id, name) {

            if (selectedProducts[id]) {
                delete selectedProducts[id];
            } else {
                selectedProducts[id] = name;
            }

            renderSelectedProducts();
        }

        function renderSelectedProducts() {
            let html = '';
            for (let id in selectedProducts) {
                html += `
            <span class="selected-badge">
                ${selectedProducts[id]}
                <span onclick="removeProduct(${id})">✕</span>
            </span>
            <input type="hidden" name="product_id[]" value="${id}">
        `;
            }

            $("#selectedProducts").html(html);
        }

        function removeProduct(id) {
            delete selectedProducts[id];
            renderSelectedProducts();
        }
        $("#productSearch").on("keyup", function() {
            loadProducts(this.value);
        });
        $(document).on("click", function(e) {
            if (!$(e.target).closest('.custom-dropdown').length) {
                $("#productDropdown").addClass('d-none');
            }
        });

        function loadAllocatedProducts(warehouseId, locationId) {

            fetch(
                    `{{ url('supplier/get-allocated-products') }}?warehouse_id=${warehouseId}&warehouse_location_id=${locationId}`
                )
                .then(res => res.json())
                .then(data => {

                    selectedProducts = {};

                    data.forEach(p => {
                        selectedProducts[p.id] = p.name;
                    });

                    renderSelectedProducts();
                });
        }
        $(document).on("click", "#productLocation", function() {

            let warehouseId = $(this).data('warehouse');
            let locationId = $(this).data('location');
            $("#warehouse_id").val(warehouseId);
            $("#warehouse_location_id").val(locationId);
            $("#title").text("Product Allocation");
            $("#modalproductLocation").modal("show");
            $("#productSearch").val("");
            loadAllocatedProducts(warehouseId, locationId);
        });
    </script>

    <script>
        document.getElementById("importForm").addEventListener("submit", function(e) {
            e.preventDefault();

            let formData = new FormData(this);

            document.getElementById("importBtn").disabled = true;
            document.getElementById("progressBox").classList.remove("d-none");

            fetch("{{ route('supplier/importWareHouseLocation') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        toastr.success(data.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1500);

                    } else {
                        toastr.error(data.message || 'Import failed!');
                        document.getElementById("importBtn").disabled = false;
                    }
                })
                .catch(err => {
                    toastr.error('Something went wrong!');
                    console.error(err);
                    document.getElementById("importBtn").disabled = false;
                });
            let interval = setInterval(() => {
                fetch("{{ route('supplier/importProgress') }}")
                    .then(res => res.json())
                    .then(data => {
                        let percent = data.percent || 0;
                        let bar = document.getElementById("progressBar");

                        bar.style.width = percent + "%";
                        bar.innerText = percent + "%";

                        if (percent >= 100) {
                            clearInterval(interval);
                        }
                    });
            }, 800);
        });
    </script>
@endsection
