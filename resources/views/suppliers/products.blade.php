@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title> Products</title>
    @endpush

    <style>
        #product_suggestions {
            max-height: 250px;
            overflow-y: auto;
            display: none;
        }

        .dropdown-menu {
            z-index: 1000 !important;
        }
    </style>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div class="">
                    <h5><b>Total Products : {{ $productCount }}</b></h5>
                </div>


                <div class="">
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="fa fa-download" aria-hidden="true"></i> Import Products
                    </button>
                    <button class="btn btn-primary btn-sm btnAdd" type="button">Add Product</button>
                    <a class="btn btn-primary btn-sm " href="/supplier/export-products" type="button">Export Products</a>

                </div>
            </div>
            <div class=" mt-3">
                <form method="GET" action="{{ url()->current() }}">
                    <div class="d-flex mb-3">
                        <div class="">
                            <select name="search_brand_id" id="search_brand_id" class="form-control"
                                onchange="this.form.submit()">
                                <option value="">Select Brand</option>
                                @foreach ($brand as $item)
                                    <option value="{{ $item->id }}"
                                        {{ $item->id == request('search_brand_id') ? 'selected' : '' }}>{{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="">
                            <select name="search_category_id" id="search_category_id" class="form-control"
                                onchange="this.form.submit()">
                                <option value="">Select Category</option>
                                @foreach ($category as $item)
                                    <option value="{{ $item->id }}"
                                        {{ $item->id == request('search_category_id') ? 'selected' : '' }}>
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="">
                            <input type="text" name="search" class="form-control"
                                placeholder="Search Product / Brand / Article" value="{{ request('search') }}">
                        </div>
                        <div class="">
                            <button class="btn btn-primary">Search</button>
                            <a href="{{ url()->current() }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover table-responsive" style="overflow: scroll">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Active</th>
                            <th>Image</th>
                            <th>Brand</th>
                            <th>Category</th>
                            <th>Sub Category</th>
                            <th>Name</th>
                            <th>Base Price</th>
                            <th>MRP</th>
                            <th>Article </th>
                            <th>Min Stock </th>
                            <th>UOM </th>
                            <th>GST</th>
                            <th>Status</th>
                            <th>Updated at</th>
                            <th>Action</th>

                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $sno = ($data->currentPage() - 1) * $data->perPage() + 1;
                        @endphp
                        @foreach ($data as $item)
                            <tr>
                                <td>{{ $sno++ }}</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-3">
                                        <div class="form-check form-switch d-flex align-items-center gap-1 m-0">
                                            <input class="form-check-input is_home" type="checkbox"
                                                value="{{ $item->id }}" role="switch"
                                                {{ $item->is_home == 1 ? 'checked' : '' }}>
                                            <label class="mb-0 small">Home</label>
                                        </div>

                                        <div class="form-check form-switch d-flex align-items-center gap-1 m-0">
                                            <input class="form-check-input is_deal" type="checkbox"
                                                value="{{ $item->id }}" role="switch"
                                                {{ $item->is_deal == 1 ? 'checked' : '' }}>
                                            <label class="mb-0 small">Deal</label>
                                        </div>

                                        <div class="form-check form-switch d-flex align-items-center gap-1 m-0">
                                            <input class="form-check-input is_discount" type="checkbox"
                                                value="{{ $item->id }}" role="switch"
                                                {{ $item->is_discount == 1 ? 'checked' : '' }}>
                                            <label class="mb-0 small">Discount</label>
                                        </div>

                                        <div class="form-check form-switch d-flex align-items-center gap-1 m-0">
                                            <input class="form-check-input is_active" type="checkbox"
                                                value="{{ $item->id }}" role="switch"
                                                {{ $item->active == 1 ? 'checked' : '' }}>
                                            <label class="mb-0 small">Active</label>
                                        </div>

                                    </div>
                                </td>
                                <td>
                                    <div style="height:50px; width:50px;">
                                        <img src="{{ !empty($item->image) ? asset('product images/' . $item->image) : asset('images/dummy.png') }}"
                                            style="height:100%; width:100%; object-fit:cover; aspect-ratio:1/1;"
                                            alt="product">
                                    </div>
                                </td>
                                <td>{{ \Illuminate\Support\Str::upper($item->brand) }}</td>
                                <td>{{ \Illuminate\Support\Str::upper($item->category) }}</td>
                                <td>{{ \Illuminate\Support\Str::upper($item->sub_category) }}</td>
                                <td>{{ \Illuminate\Support\Str::upper($item->name) }}</td>
                                <td style="width:120px;">
                                    <input type="number" step="0.01" class="form-control form-control-sm base_price"
                                        data-id="{{ $item->id }}" value="{{ $item->base_price }}">
                                </td>
                                <td>{{ $item->mrp }}</td>
                                <td>{{ $item->article_no }}</td>
                                <td>{{ $item->min_stock }}</td>
                                <td>{{ $item->uom }}</td>
                                <td>{{ $item->gst }}</td>
                                <td>
                                    @if ($item->active == 1)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">In Active</span>
                                    @endif
                                </td>
                                <td>{{ $item->updated_at }}</td>
                                <td>
                                    <div class="dropdown">
                                        <span class="dropdown-toggle" id="dropdownMenuButton7" data-bs-toggle="dropdown"
                                            aria-expanded="false" role="button"> Click Here
                                        </span>
                                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton7">
                                            <div> <button class="btn btn-primary btn-sm edit"
                                                    data-data="{{ @json_encode($item) }}" type="button"
                                                    data-category="{{ $item->category }}"
                                                    data-sub_category="{{ $item->sub_category }}">Edit Product</button>
                                            </div>
                                            <div class="mt-2"><button class="btn btn-secondary btn-sm products"
                                                    type="button" value="{{ $item->id }}">Product Tier</button>
                                            </div>
                                            <div class="mt-2">
                                                <button class="btn btn-dark btn-sm uploadImages" type="button"
                                                    value="{{ $item->id }}">Upload Images</button>
                                            </div>
                                            <div class="mt-2">
                                                <button class="btn btn-info btn-sm wareHouseAllocation"
                                                    data-id="{{ $item->id }}">
                                                    Ware House Allocation
                                                </button>
                                            </div>
                                            <div class="mt-2">
                                                <button class="btn btn-warning btn-sm vendorAllocation"
                                                    data-id="{{ $item->id }}">
                                                    Vendor Allocation
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex justify-content-end">
                {{ $data->links() }}
            </div>
        </div>
    </div>

    {{--   Product Add Edit --}}
    <form class="needs-validation" novalidate enctype="multipart/form-data">
        @csrf
        <div class="modal fade" id="modalId" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
            role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="d-flex align-items-center w-100 justify-content-between">
                            <div>
                                <h5 class="modal-title" id="modalTitleId">
                                    Modal title
                                </h5>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-primary addBrand" type="button">Brand</button>
                                <button class="btn btn-sm btn-info addCategory" type="button">Category</button>
                                <button class="btn btn-sm btn-warning addSubcategory" type="button">Sub Category</button>
                                <button class="btn btn-sm btn-warning addSubSubcategory" type="button">Sub Sub
                                    Category</button>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="id" name="id">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="">Image<span style="color: red"> Size (500*500)px</span></label>
                                <input type="file" name="file" id="file" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="">Brand <span style="color:red">*</span></label>
                                <select name="brand_id" id="brand_id" class="form-control">
                                    <option value="">Select Brand </option>
                                    @foreach ($brand as $item)
                                        <option value="{{ $item->id }}">
                                            {{ \Illuminate\Support\Str::upper($item->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="">Product Type <span style="color:red">*</span></label>
                                <select name="product_type_id" id="product_type_id" class="form-control">
                                    <option value="">Select Type </option>
                                    @foreach ($productType as $item)
                                        <option value="{{ $item->id }}">
                                            {{ \Illuminate\Support\Str::upper($item->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="">Category <span style="color:red">*</span></label>
                                <select name="category_id" id="category_id" class="form-control" required>
                                    <option value="">Select Category </option>
                                    @foreach ($category as $item)
                                        <option value="{{ $item->id }}">
                                            {{ \Illuminate\Support\Str::upper($item->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="">Sub Category <span style="color:red">*</span></label>
                                <select name="sub_category_id" id="sub_category_id" class="form-control" required>
                                    <option value="">Select Sub Category</option>
                                </select>
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="">Sub Sub Category </label>
                                <select name="product_sub_sub_category" id="product_sub_sub_category"
                                    class="form-control" required>
                                    <option value="">Select Sub Category</option>
                                </select>
                            </div>
                            <div class="col-md-8 mt-3">
                                <label for="">Product Name <span style="color:red">*</span></label>
                                <input type="text" name="name" id="product_name" class="form-control"
                                    autocomplete="off" required>
                                <div id="product_suggestions" class="list-group position-absolute" style="z-index:1000;">
                                </div>
                            </div>

                            <div class="col-md-4 mt-3">
                                <label for="">Unit Type <span style="color:red">*</span></label>
                                <select name="uom_id" id="uom_id" class="form-control" required>
                                    <option value="">Select Unit Type</option>
                                    @foreach ($product_uom as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                             <div class="col-md-4 mt-3">
                                <label for="">Per UOM(Qty)</label>
                                <input type="number" step="0.01" name="per_uom" id="per_uom" 
                                    class="form-control">
                            </div>

                            <div class="col-md-4 mt-3">
                                <label for="">GST <span style="color:red">*</span></label>
                                <select name="gst" id="gst" class="form-control" required>
                                    <option value="">Select GST</option>
                                    @foreach ($gst as $item)
                                        <option value="{{ $item->gst }}">{{ $item->gst }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mt-3">
                                <label for="">Cess Tax</label>
                                <input type="number" step="0.01" name="cess_tax" id="cess_tax" value="0"
                                    class="form-control">
                            </div>

                            <div class="col-md-4 mt-3">
                                <label for="">HSN Code <span style="color:red">*</span></label>
                                <input type="" step="0.01" name="hsn_code" id="hsn_code"
                                    class="form-control">
                            </div>

                            <div class="col-md-4 mt-3">
                                <label for="">MRP </label>
                                <input type="number" step="0.01" name="mrp" id="mrp" class="form-control"
                                    required>
                            </div>

                            <div class="col-md-4 mt-3">
                                <label for="">Discount (%)</label>
                                <input type="number" step="0.01" name="discount" id="discount"
                                    class="form-control">
                            </div>

                            <div class="col-md-4 mt-3">
                                <label for="">Sale Price <span style="color:red">*</span></label>
                                <input type="number" step="0.01" name="base_price" id="base_price"
                                    class="form-control" required>
                            </div>

                            <div class="col-md-4 mt-3" style="display: none">
                                <label for="">Article No</label>
                                <input type="" step="0.01" name="article_no" id="article_no"
                                    class="form-control">
                            </div>
                            {{-- <div class="col-md-4 mt-3">
                                <label for="">Qty</label>
                                <input type="number" step="" name="qty" id="qty"
                                    class="form-control">
                            </div> --}}
                            <div class="col-md-4 mt-3">
                                <label for="">Min Stock <span style="color:red">*</span></label>
                                <input type="number" step="0.01" name="min_stock" id="min_stock"
                                    class="form-control" value="0">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="">Active <span style="color:red">*</span></label>
                                <select name="active" id="active" class="form-control" required>
                                    <option value="1">Active</option>
                                    <option value="0">In Active</option>
                                </select>

                            </div>
                            <div class="col-md-12 mt-3">
                                <label for="">Description</label>
                                <textarea name="description" id="description" class="form-control"></textarea>
                            </div>
                            <div class="col-md-12 mt-3">
                                <label for="">Tags (Enter Tag comma separated)</label>
                                <textarea name="tags" id="tags" class="form-control" placeholder="Tags1, tags2, tags3, tags4"></textarea>
                            </div>

                            <div class="col-md-12 mt-3">
                                <label for="">Video Link</label>
                                <input type="text" name="video_link" id="video_link" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button type="submit" id="SaveProduct" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{--  Warehouse Product Allocation --}}
    <form class="needs-validation" novalidate enctype="multipart/form-data">
        @csrf
        <div class="modal fade" id="warehouseallocation" tabindex="-1" data-bs-backdrop="static"
            data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="warehouseAllocationTitle">
                            Modal title
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="productId" name="productId">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Warehouse</label>
                                <select name="warehouse_id" id="warehouse_id" class="form-control">
                                    <option value="">Select Warehouse</option>
                                    @foreach ($warehouse as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>Zone</label>
                                <select name="zone_id" id="zone_id" class="form-control">
                                    <option value="">Select Zone</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>Location Code</label>
                                <select name="location_id" id="location_id" class="form-control">
                                    <option value="">Select Location</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button type="submit" id="SaveProductAllocation" class="btn btn-primary">Save</button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered ">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Warehouse</th>
                                    <th>Zone</th>
                                    <th>Location</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="allocationWareHouseTableBody"></tbody>
                        </table>

                        <p id="noAllocationMsg" class="text-danger d-none">No Allocation Found</p>

                    </div>
                </div>
            </div>
        </div>
    </form>

    {{--  Vendor Product Allocation --}}
    <form class="needs-validation" novalidate enctype="multipart/form-data">
        @csrf
        <div class="modal fade" id="vendorAllocation" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
            role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="vendorAllocationTitle">
                            Vendor Allocate
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="productId" name="productId">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Vendor</label>

                                <div class="dropdown w-100">
                                    <button class="btn btn-light border w-100 text-start dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">
                                        Select Vendor
                                    </button>

                                    <div class="dropdown-menu w-100 p-2" style="max-height:250px; overflow:auto;">

                                        <input type="text" class="form-control mb-2" id="vendorSearch"
                                            placeholder="Search Vendor...">

                                        @foreach ($vendor as $item)
                                            <div class="form-check">
                                                <input class="form-check-input vendor_checkbox" type="checkbox"
                                                    value="{{ $item->id }}" id="vendor{{ $item->id }}">

                                                <label class="form-check-label" for="vendor{{ $item->id }}">
                                                    {{ $item->name }} / {{ $item->company }}
                                                </label>
                                            </div>
                                        @endforeach

                                    </div>
                                </div>
                            </div>

                            <div class="col-6 mt-4">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Close
                                </button>
                                <button type="submit" id="SaveVendorProductAllocation"
                                    class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered ">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Vendor</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="vendorAllocationTableBody"></tbody>
                        </table>

                        <p id="noVendorAllocationMsg" class="text-danger d-none">No Allocation Found</p>

                    </div>
                </div>
            </div>
        </div>
    </form>

    {{--  Import Product --}}
    <form action="{{ route('supplier/importGDriveProducts') }}" method="POST" class="needs-validation" novalidate
        enctype="multipart/form-data">
        @csrf
        <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Import Products</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <input type="file" name="file" class="form-control" required>
                            </div>
                            <div>
                                <a class="btn btn-success" href="/import-products.csv"
                                    download="/import-products.csv">Download Sample File</a>
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
                                        <li>Before upload please remove header raw.</li>

                                        <li>Article number must be unique.</li>

                                    </ul>
                                </div>

                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-dark">Import</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{--  Product Price Tier --}}
    <div class="modal fade" id="productsModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
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

    {{-- Delete Product Price --}}
    <div class="modal fade" id="deletePriceModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
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


    {{-- Product Price --}}
    <div class="modal fade" id="addModalPrice" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
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

    {{-- Multiple Image --}}
    <form action="{{ route('supplier/uploadMultipleImages', ['id' => 1]) }}" method="POST" class="needs-validation"
        novalidate enctype="multipart/form-data">
        @csrf
        <div class="modal fade" id="uploadImagesModal" tabindex="-1" data-bs-backdrop="static"
            data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
            <div class="modal-dialog  modal-dialog-scrollable modal-dialog-centered " role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitleId">
                            Upload Images
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" id="product_img_id" name="id">
                            <div class="col-md-8">
                                <label for="">Choose Multiple Images</label>
                                <input type="file" name="files[]" class="form-control" required accept="image/*"
                                    multiple>


                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-primary mt-4" type="submit">Upload</button>
                            </div>

                            <div class="col-md-12">
                                <table class="table ">
                                    <thead>
                                        <tr>
                                            <td>S.No</td>
                                            <td>Image</td>
                                            <td>Action</td>
                                        </tr>
                                    </thead>
                                    <tbody id="imageList">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Close
                        </button>

                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Add Brand --}}
    <form method="POST" class="needs-validation" novalidate enctype="multipart/form-data" id="brandFrom">
        @csrf
        <div class="modal fade" id="modalIdBrand" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
            role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitleId">
                            Modal title
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="id" name="id">
                        <label for="">File <span style="color: red">Img Size (330*310)px</span></label>
                        <input type="file" name="file" id="file" class="form-control">

                        <label for="" class="mt-3">Name</label>
                        <input type="text" name="name" id="name" class="form-control" required>
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

    {{-- Add Category --}}
    <form method="POST" class="needs-validation" novalidate enctype="multipart/form-data" id="categoryForm">
        <div class="modal fade" id="modalIdCategory" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
            role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitleId">
                            Modal title
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="id" name="id">
                        <label for="">File <span style="color: red">Img Size (330*310)px</span></label>
                        <input type="file" name="file" id="file" class="form-control">
                        <label for="" class="mt-3">Name</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                        <label for="" class="mt-3">Sequence</label>
                        <input type="number" name="seq" id="seq" class="form-control" required>
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

    {{-- Add Sub-Category --}}
    <form id="subCategoryForm" method="POST" class="needs-validation" novalidate enctype="multipart/form-data">
        <div class="modal fade" id="modalIdSubCategory" tabindex="-1" data-bs-backdrop="static"
            data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitleId">
                            Modal title
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="id" name="id">
                        <label for="">File <span style="color: red">Img Size (340*190)px</span></label>
                        <input type="file" name="file" id="file" class="form-control">
                        <label for="" class="mt-3">Category</label>
                        <select name="category_id" id="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            @foreach ($category as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                        <label for="" class="mt-3">Name</label>
                        <input type="text" name="name" id="name" class="form-control" required>
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

    {{-- Add Sub Sub Category --}}
    <form id="subSubCategoryFrom" method="POST" class="needs-validation" novalidate enctype="multipart/form-data">
        <div class="modal fade" id="modalIdSubSubCategory" tabindex="-1" data-bs-backdrop="static"
            data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitleId">
                            Modal title
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="id" name="id">
                        <label for="">File <span style="color: red">Img Size (340*190)px</span></label>
                        <input type="file" name="file" id="file" class="form-control">


                        <label class="mt-3">Category</label>
                        <select name="category_id" id="ssc_category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            @foreach ($category as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>

                        <label class="mt-3">Sub Category</label>
                        <select name="sub_category_id" id="ssc_sub_category_id" class="form-control" required>
                            <option value="">Select Sub Category</option>
                            @foreach ($subCategories as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>

                        <label for="" class="mt-3">Name</label>
                        <input type="text" name="name" id="name" class="form-control" required>
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
            $("#search_brand_id, #search_category_id").select2();
        })

        $(".btnAdd").on("click", function() {
            $("#modalTitleId").text("Add")
            $("#id").val("");
            $("form.needs-validation")[0].reset();
            $("#sub_category_id").html('<option value="">Select Sub Category</option>');
            $("#product_sub_sub_category").html('<option value="">Select Sub Sub Category</option>');

            $("#modalId").modal("show");
        });

        $(document).on("click", ".edit", function() {
            $("#modalTitleId").text("Edit");
            let category = $(this).data("category");
            let sub_category = $(this).data("sub_category");
            let sub_sub_category = $(this).data(
                "sub_sub_category");
            let data = $(this).data("data");
            $.each(data, function(i, o) {
                $("[name=" + i + "]").val(o);
            });
            if (data.category_id) {
                $.ajax({
                    url: "/supplier/GetProductSubCategory",
                    type: "POST",
                    data: {
                        category_id: data.category_id
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(result) {
                        let html = '<option value="">----Select Sub Category----</option>';
                        result.forEach(element => {
                            html +=
                                `<option value="${element.id}" ${element.id == data.sub_category_id ? 'selected' : ''}>${element.name}</option>`;
                        });
                        $("#sub_category_id").html(html);
                        if (data.sub_category_id) {
                            $.ajax({
                                url: "/supplier/GetProductSubSubCategory",
                                type: "POST",
                                data: {
                                    sub_category_id: data.sub_category_id
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(subResult) {
                                    let subHtml =
                                        '<option value="">----Select Sub Sub Category----</option>';
                                    subResult.forEach(element => {
                                        subHtml +=
                                            `<option value="${element.id}" ${element.id == data.product_sub_sub_category ? 'selected' : ''}>${element.name}</option>`;
                                    });
                                    $("#product_sub_sub_category").html(subHtml);
                                }
                            });
                        } else {
                            $("#product_sub_sub_category").html(
                                '<option value="">Select Sub Sub Category</option>');
                        }
                    }
                });
            }
            $("#modalId").modal("show");
        });



        $(document).on("click", "#SaveProduct", function(e) {
            e.preventDefault();
            let form = $("form.needs-validation")[0];
            let formData = new FormData(form);
            $.ajax({
                url: "{{ route('supplier/SaveProducts') }}",
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $(".btn-primary").prop("disabled", true).text("Saving...");
                },
                success: function(res) {
                    $(".btn-primary").prop("disabled", false).text("Save");
                    toastr.success("Product saved successfully!");
                    $("#modalId").modal("hide");
                    let form = $("form.needs-validation")[0];
                    form.reset();
                    $("#id").val("");
                    $("#product_type_id").val("");
                    $("#sub_category_id").html('<option value="">Select Sub Category</option>');
                    $("#product_sub_sub_category").html(
                        '<option value="">Select Sub Sub Category</option>');
                    $("select").val("").trigger("change");
                    $("#product_suggestions").hide().html("");
                    $("#cess_tax").val(0);
                    $("#per_uom").val("");
                    $("#min_stock").val(0);
                    $("#active").val(1);
                    loadProducts();
                },
                error: function(xhr) {
                    $(".btn-primary").prop("disabled", false).text("Save");

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error("Something went wrong!");
                    }
                }
            });
        });

        $("#category_id").on("change", function() {
            $.ajax({
                url: "/supplier/GetProductSubCategory",
                type: "POST",
                data: {
                    category_id: $(this).val(),
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $("#loader").show();
                },
                success: function(result) {
                    let html = '<option value="">----Select Sub Category----</option>';
                    result.forEach(element => {
                        html += `<option value="${element.id}">${element.name}</option>`;
                    });
                    $("#sub_category_id").html(html);
                    $("#product_sub_sub_category").html(
                        '<option value="">Select Sub Sub Category</option>'
                    ); // reset sub-sub when category changes
                },
                complete: function() {
                    $("#loader").hide();
                },
                error: function(result) {
                    toastr.error(result.responseJSON.message);
                }
            });
        });

        // Load Sub Sub Categories based on Sub Category
        $("#sub_category_id").on("change", function() {
            $.ajax({
                url: "/supplier/GetProductSubSubCategory",
                type: "POST",
                data: {
                    sub_category_id: $(this).val(), // ✅ Correct parameter name
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $("#loader").show();
                },
                success: function(result) {
                    let html = '<option value="">----Select Sub Sub Category----</option>';
                    result.forEach(element => {
                        html += `<option value="${element.id}">${element.name}</option>`;
                    });
                    $("#product_sub_sub_category").html(html); // ✅ target correct dropdown
                },
                complete: function() {
                    $("#loader").hide();
                },
                error: function(result) {
                    toastr.error(result.responseJSON.message);
                }
            });
        });

        function GetProductPrice(id) {

            $.ajax({
                url: "/supplier/GetProductPrices",
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
                                    <td>${element.price}</td>
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
        var product_id = "";
        $(document).on("click", ".products", function() {

            GetProductPrice($(this).val())
            product_id = $(this).val();
            $("#productsModal").modal("show")
        });

        $(document).on("click", ".deleteProduct", function() {
            $("#priceTierID").val($(this).val())
            $("#deletePriceModal").modal("show")
        });
        $("#btnDeleteProduct").on("click", function() {
            var id = $("#priceTierID").val()
            $.ajax({
                url: "/supplier/DeleteProductPrice",
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

                    GetProductPrice(product_id)
                    $("#deletePriceModal").modal("hide");
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
            $("#addModalPrice").modal("show");
        });
        $("#btnAddProductPrice").on("click", function() {
            var product_price = $("#product_price").val()
            var product_qty = $("#product_qty").val()

            $.ajax({
                url: "/supplier/AddProductPrice",
                type: "POST",
                data: {
                    product_price: product_price,
                    product_qty: product_qty,
                    product_id: product_id,
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
                    GetProductPrice(product_id)
                    $("#addModalPrice").modal("hide")
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
        $(document).on("click", ".uploadImages", function() {
            $("#product_img_id").val($(this).val())
            getMultipleImages($(this).val());
            $("#uploadImagesModal").modal("show")
        })

        function getMultipleImages(id) {
            $.ajax({
                url: "/supplier/getMultipleImages",
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
                        html += `
                                <tr>
                                    <td>${sno++}</td>
                                    <td></a>
                                       <a href="/product images/${element.image}" target="_blank"> <img src="/product images/${element.image}" width="46"></a>
                                    </td>
                                    <td>
                                        <button class="btn btn-danger btn-sm deleteImage" value="${element.id}" data-product_id="${element.product_id}"> <i class="fa fa-trash" aria-hidden="true"></i> </button>
                                    </td>
                                </tr>
                            `;
                    });
                    $("#imageList").html(html)
                },
                complete: function() {
                    $("#loader").hide();
                },
                error: function(result) {
                    toastr.error(result.responseJSON.message);
                }
            });
        }

        $(document).on("click", ".deleteImage", function() {
            var product_id = $(this).data("product_id")
            $.ajax({
                url: "/supplier/deleteImage",
                type: "POST",
                data: {
                    id: $(this).val(),

                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $("#loader").show();
                },
                success: function(result) {

                    toastr.success("Save successfully");


                    getMultipleImages(product_id)

                },
                complete: function() {
                    $("#loader").hide();
                },
                error: function(result) {
                    toastr.error(result.responseJSON.message);
                }
            });
        });

        $(document).ready(function() {

            $(document).on("click", ".is_active", function() {
                var active = 0;
                var id = $(this).val()
                if ($(this).prop("checked")) {
                    active = 1;
                }
                $.ajax({
                    url: "/supplier/UpdateProductStatus",
                    type: "POST",
                    data: {
                        active: active,
                        id: id,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        $("#loader").show();
                    },
                    success: function(result) {

                    },
                    complete: function() {
                        $("#loader").hide();
                    },
                    error: function(result) {
                        toastr.error(result.responseJSON.message);
                    }
                });
            })
            $(document).on("click", ".is_deal", function() {
                var is_deal = 0;
                var id = $(this).val()
                if ($(this).prop("checked")) {
                    is_deal = 1;
                }
                $.ajax({
                    url: "/supplier/UpdateProductIsdeal",
                    type: "POST",
                    data: {
                        is_deal: is_deal,
                        id: id,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        $("#loader").show();
                    },
                    success: function(result) {

                    },
                    complete: function() {
                        $("#loader").hide();
                    },
                    error: function(result) {
                        toastr.error(result.responseJSON.message);
                    }
                });
            })

                $(document).on("click", ".is_home", function() {
                var is_home = 0;
                var id = $(this).val()
                if ($(this).prop("checked")) {
                    is_home = 1;
                }
                $.ajax({
                    url: "/supplier/UpdateProductIsHome",
                    type: "POST",
                    data: {
                        is_home: is_home,
                        id: id,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        $("#loader").show();
                    },
                    success: function(result) {

                    },
                    complete: function() {
                        $("#loader").hide();
                    },
                    error: function(result) {
                        toastr.error(result.responseJSON.message);
                    }
                });
            })

            $(document).on("click", ".is_discount", function() {
                var is_discount = 0;
                var id = $(this).val()
                if ($(this).prop("checked")) {
                    is_discount = 1;
                }
                $.ajax({
                    url: "/supplier/UpdateProductDiscount",
                    type: "POST",
                    data: {
                        is_discount: is_discount,
                        id: id,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        $("#loader").show();
                    },
                    success: function(result) {

                    },
                    complete: function() {
                        $("#loader").hide();
                    },
                    error: function(result) {
                        toastr.error(result.responseJSON.message);
                    }
                });
            })
        });
    </script>

    <script>
        $(document).on("click", ".wareHouseAllocation", function() {
            let productId = $(this).data("id");
            $("#productId").val(productId);
            $("#warehouseallocation").modal("show");
            loadProductWarehouseAllocation(productId);
        });
        $("#warehouse_id").change(function() {
            let warehouse_id = $(this).val();
            $("#zone_id").html('<option>Loading...</option>');
            $("#location_id").html('<option>Select Location</option>');
            $.post("/get-zones", {
                warehouse_id: warehouse_id,
                _token: $('meta[name="csrf-token"]').attr('content')
            }, function(res) {

                let html = '<option value="">Select Zone</option>';

                res.forEach(zone => {
                    html += `<option value="${zone.id}">${zone.zone_code}</option>`;
                });

                $("#zone_id").html(html);
            });

        });
        $("#zone_id").change(function() {

            let zone_id = $(this).val();
            let warehouse_id = $("#warehouse_id").val();

            $("#location_id").html('<option>Loading...</option>');

            $.post("/get-locations", {
                zone_id: zone_id,
                warehouse_id: warehouse_id,
                _token: $('meta[name="csrf-token"]').attr('content')
            }, function(res) {

                let html = '<option value="">Select Location</option>';

                res.forEach(loc => {
                    html += `<option value="${loc.id}">${loc.location_code}</option>`;
                });

                $("#location_id").html(html);
            });

        });

        $("#SaveProductAllocation").click(function(e) {
            e.preventDefault();

            $.post("/save-allocation", {
                product_id: $("#productId").val(),
                warehouse_id: $("#warehouse_id").val(),
                location_id: $("#location_id").val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            }, function(res) {

                if (res.status) {
                    toastr.success(res.message);
                    $("#warehouse_id").val("").trigger("change");
                    $("#location_id").val("").trigger("change");
                    $("#zone_id").val("").trigger("change");
                    $("#productId").val("").trigger("change");
                    $("#warehouseallocation").modal("hide");
                }

            });

        });

        function loadProductWarehouseAllocation(productId) {
            $.get("/get-product-allocation", {
                product_id: productId,
                _token: $('meta[name="csrf-token"]').attr('content')
            }, function(res) {

                let html = "";

                if (res.length > 0) {
                    res.forEach(row => {

                        html += `
                <tr>
                    <td style="text-wrap: auto;">${row.product_name}</td>
                    <td style="text-wrap: auto;">${row.warehouse_name}</td>
                    <td>${row.zone_code}</td>
                    <td>${row.location_code}</td>
                    <td>
                        <button class="btn btn-danger btn-sm removeAllocation"
                            data-id="${row.id}">
                            Remove
                        </button>
                    </td>
                </tr>
                `;
                    });

                    $("#allocationWareHouseTableBody").html(html);
                    $("#noAllocationMsg").addClass("d-none");
                } else {
                    $("#allocationWareHouseTableBody").html("");
                    $("#noAllocationMsg").removeClass("d-none");
                }

            });
        }
        $(document).on("click", ".removeAllocation", function() {

            let id = $(this).data("id");

            $.post("/remove-allocation", {
                id: id,
                _token: $('meta[name="csrf-token"]').attr('content')
            }, function(res) {

                if (res.status) {
                    toastr.success(res.message);

                    loadProductAllocation($("#productId").val());
                }

            });

        });
    </script>

    <script>
        $(document).on("click", ".vendorAllocation", function() {

            let productId = $(this).data("id");

            $("#productId").val(productId);

            $("#vendorAllocation").modal("show");

            loadProductAllocation(productId);
        });
        $("#SaveVendorProductAllocation").click(function(e) {
            e.preventDefault();

            let vendors = [];

            $(".vendor_checkbox:checked").each(function() {
                vendors.push($(this).val());
            });

            $.post("/supplier/save-vendor-allocation", {
                product_id: $("#productId").val(),
                vendor_id: vendors,
                _token: $('meta[name="csrf-token"]').attr('content')
            }, function(res) {
                if (res.status) {
                    toastr.success(res.message);
                    loadProductAllocation($("#productId").val());
                }
            });
        });
        $("#vendorSearch").on("keyup", function() {
            let value = $(this).val().toLowerCase();
            $(".dropdown-menu .form-check").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        function loadProductAllocation(productId) {

            $.get("/supplier/get-vendor-product-allocation", {
                product_id: productId
            }, function(res) {
                let html = "";
                if (res.length > 0) {
                    res.forEach(row => {
                        html += `
                <tr>
                    <td>${row.product_name}</td>
                    <td>${row.vendor_name}</td>
                    <td>
                        <button class="btn btn-danger btn-sm removeVendorAllocation"
                            data-id="${row.id}">
                            Remove
                        </button>
                    </td>
                </tr>`;
                    });

                    $("#vendorAllocationTableBody").html(html);
                    $("#noVendorAllocationMsg").addClass("d-none");

                } else {
                    $("#vendorAllocationTableBody").html("");
                    $("#noVendorAllocationMsg").removeClass("d-none");

                }
            });
        }
        $(document).on("click", ".removeVendorAllocation", function() {
            let id = $(this).data("id");
            $.post("/supplier/remove-vendor-allocation", {
                id: id,
                _token: $('meta[name="csrf-token"]').attr('content')
            }, function(res) {
                if (res.status) {
                    toastr.success(res.message);
                    loadProductAllocation($("#productId").val());
                }
            });
        });
    </script>

    <script>
        $(document).on("keyup change", ".base_price", function() {

            let id = $(this).data("id");
            let price = $(this).val();

            $.ajax({
                url: "/supplier/update-base-price",
                type: "POST",
                data: {
                    id: id,
                    base_price: price
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function() {
                    toastr.success("Base price updated");
                },
                error: function() {
                    toastr.error("Update failed");
                }
            });

        });
    </script>

    <script>
        $(document).on("keyup", "#product_name", function() {

            let query = $(this).val().trim();

            if (query.length < 2) {
                $("#product_suggestions").hide().html("");
                return;
            }

            $.ajax({
                url: "{{ route('supplier.searchProduct') }}",
                type: "GET",
                data: {
                    q: query
                },

                success: function(data) {

                    if (data.length > 0) {

                        let html = "";

                        $.each(data, function(index, item) {
                            html += `
                        <a href="#" 
                           class="list-group-item list-group-item-action product-item"
                           data-id="${item.id}"
                           data-name="${item.name}">
                           ${item.name}
                        </a>
                    `;
                        });
                        $("#product_suggestions").html(html).show();
                    } else {
                        $("#product_suggestions").hide().html("");

                    }
                }
            });
        });

        $(document).on("dblclick", ".product-item", function(e) {
            e.preventDefault();
            let name = $(this).data("name");
            $("#product_name").val(name);
            $("#product_suggestions").hide();
        });
    </script>

    <script>
        $(".addBrand").on("click", function() {
            $("#modalTitleId").text("Add")
            $("#id").val("");
            $("#modalIdBrand").modal("show")
        });

        $(".addCategory").on("click", function() {
            $("#modalTitleId").text("Add")
            $("#id").val("");
            $("#modalIdCategory").modal("show")
        });
        $(".addSubcategory").on("click", function() {
            $("#modalTitleId").text("Add")
            $("#id").val("");
            $("#modalIdSubCategory").modal("show")
        });
        $(".addSubSubcategory").on("click", function() {
            $("#modalTitleId").text("Add")
            $("#id").val("");
            $("#modalIdSubSubCategory").modal("show")
        });
    </script>

    <script>
        $(document).on("submit", "#categoryForm", function(e) {

            e.preventDefault();

            let form = this;
            let formData = new FormData(form);

            $.ajax({
                url: "{{ route('supplier.SaveProductCategory') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }, // ✅ comma added here
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $("#modalIdCategory .btn-primary")
                        .prop("disabled", true)
                        .text("Saving...");
                },
                success: function(res) {

                    $("#modalIdCategory .btn-primary")
                        .prop("disabled", false)
                        .text("Save");

                    if (res.status) {

                        toastr.success(res.message);

                        $("#modalIdCategory").modal("hide");

                        form.reset();

                        loadCategories(res.id);
                    }
                },
                error: function(xhr) {

                    $("#modalIdCategory .btn-primary")
                        .prop("disabled", false)
                        .text("Save");

                    if (xhr.status === 422) {

                        let errors = xhr.responseJSON.errors;

                        $.each(errors, function(key, value) {
                            toastr.error(value[0]);
                        });
                    }
                }
            });

        });


        function loadCategories(selectedId = null) {

            $.ajax({
                url: "{{ route('supplier.getCategories') }}",
                type: "GET",
                success: function(res) {
                    let options = '<option value="">Select Category</option>';
                    $.each(res.data, function(i, item) {
                        options += `<option value="${item.id}">
                                ${item.name}
                            </option>`;
                    });
                    $("#category_id").html(options);
                    if (selectedId) {
                        $("#category_id").val(selectedId);
                    }
                }
            });
        }
    </script>

    <script>
        $(document).on("submit", "#brandFrom", function(e) {
            e.preventDefault();
            let form = this;
            let formData = new FormData(form);
            $.ajax({
                url: "{{ route('supplier/SaveProductBrand-ajax') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $("#modalIdBrand .btn-primary")
                        .prop("disabled", true)
                        .text("Saving...");
                },
                success: function(res) {
                    $("#modalIdBrand .btn-primary")
                        .prop("disabled", false)
                        .text("Save");
                    if (res.status) {
                        toastr.success(res.message);
                        $("#modalIdBrand").modal("hide");
                        form.reset();
                        loadBrands(res.id);
                    }
                },
                error: function(xhr) {
                    $("#modalIdBrand .btn-primary")
                        .prop("disabled", false)
                        .text("Save");
                    if (xhr.status == 422) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error("Something went wrong");
                    }
                }
            });

        });

        function loadBrands(selectedId = null) {

            $.ajax({
                url: "{{ route('supplier.getBrands-ajax') }}",
                type: "GET",
                success: function(res) {

                    let options = '<option value="">Select Brand</option>';

                    $.each(res.data, function(i, item) {
                        options += `<option value="${item.id}">
                                ${item.name}
                            </option>`;
                    });

                    $("#brand_id").html(options);

                    if (selectedId) {
                        $("#brand_id").val(selectedId);
                    }
                }
            });
        }
    </script>

    <script>
        $(document).on("submit", "#subCategoryForm", function(e) {

            e.preventDefault();

            let form = this;
            let formData = new FormData(form);

            $.ajax({
                url: "{{ route('supplier/SaveProductSubCategoryAjax') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $("#modalIdSubCategory .btn-primary")
                        .prop("disabled", true)
                        .text("Saving...");
                },
                success: function(res) {

                    $("#modalIdSubCategory .btn-primary")
                        .prop("disabled", false)
                        .text("Save");

                    if (res.status) {

                        toastr.success(res.message);

                        $("#modalIdSubCategory").modal("hide");

                        form.reset();

                        loadSubCategories(res.id); // dropdown reload
                    }
                },
                error: function(xhr) {

                    $("#modalIdSubCategory .btn-primary")
                        .prop("disabled", false)
                        .text("Save");

                    if (xhr.status == 422) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error("Something went wrong");
                    }
                }
            });

        });

        function loadSubCategories(selectedId = null) {
            $.ajax({
                url: "{{ route('supplier.getSubCategoriesAjax') }}",
                type: "GET",
                success: function(res) {
                    let options = '<option value="">Select Sub Category</option>';
                    $.each(res.data, function(i, item) {
                        options += `<option value="${item.id}">
                                ${item.name}
                            </option>`;
                    });
                    $("#sub_category_id").html(options);

                    if (selectedId) {
                        $("#sub_category_id").val(selectedId);
                    }
                }
            });
        }
    </script>

    <script>
        $(document).on("submit", "#subSubCategoryFrom", function(e) {
            e.preventDefault();
            let form = this;
            let formData = new FormData(form);
            $.ajax({
                url: "{{ route('supplier/SaveProductSubSubCategoryAjax') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $("#modalIdSubSubCategory .btn-primary")
                        .prop("disabled", true)
                        .text("Saving...");
                },
                success: function(res) {

                    $("#modalIdSubSubCategory .btn-primary")
                        .prop("disabled", false)
                        .text("Save");

                    if (res.status) {

                        toastr.success(res.message);

                        $("#modalIdSubSubCategory").modal("hide");

                        form.reset();
                        loadSubSubCategories(res.id);
                    }
                },
                error: function(xhr) {

                    $("#modalIdSubSubCategory .btn-primary")
                        .prop("disabled", false)
                        .text("Save");

                    if (xhr.status == 422) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error("Something went wrong");
                    }
                }
            });
        });
        $("#ssc_category_id").on("change", function() {
            let categoryId = $(this).val();
            $.ajax({
                url: "{{ route('supplier/getSubCategoriesByCategoryAjax') }}",
                type: "GET",
                data: {
                    category_id: categoryId
                },
                success: function(res) {
                    let options = '<option value="">Select Sub Category</option>';
                    $.each(res.data, function(i, item) {
                        options += `<option value="${item.id}">
                                ${item.name}
                            </option>`;
                    });
                    $("#ssc_sub_category_id").html(options);
                }
            });
        });

        function loadSubSubCategories(selectedId = null) {

            $.ajax({
                url: "{{ route('supplier/getSubSubCategoriesAjax') }}",
                type: "GET",
                success: function(res) {

                    let options = '<option value="">Select Sub Sub Category</option>';

                    $.each(res.data, function(i, item) {
                        options += `<option value="${item.id}">
                                ${item.name}
                            </option>`;
                    });
                    $("#product_sub_sub_category").html(options);
                    if (selectedId) {
                        $("#product_sub_sub_category").val(selectedId);
                    }
                }
            });
        }
    </script>

    <script>
        function calculateSalePrice() {

            let mrp = parseFloat($("#mrp").val()) || 0;
            let discount = parseFloat($("#discount").val()) || 0;

            if (discount > 100) {
                discount = 100;
                $("#discount").val(100);
            }

            let salePrice = mrp - ((mrp * discount) / 100);

            $("#base_price").val(salePrice.toFixed(2));
        }

        // MRP change
        $(document).on("input", "#mrp", function() {
            calculateSalePrice();
        });

        // Discount change
        $(document).on("input", "#discount", function() {
            calculateSalePrice();
        });
    </script>
@endsection
