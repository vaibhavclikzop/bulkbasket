@extends('suppliers.layouts.main')

<style>
    .blink-text {
        animation: blink 1s infinite;
        font-weight: bold;
    }

    @keyframes blink {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0;
        }

        100% {
            opacity: 1;
        }
    }

    #pageLoader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }
</style>

@section('main-section')
    @push('title')
        <title>Ware House Pending Products</title>
    @endpush


    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    <h5>{{ $warehouseName->name ?? 'Ware House' }}({{ $warehouseName->code }})</h5>

                    <span class="text-danger blink-text">( Pending Product For Allocation )</span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Loader -->
            <div id="pageLoader" class="text-center my-4">
                <div class="spinner-border text-primary"></div>
                <div>Loading Products...</div>
            </div>

            <!-- Table -->
            <table class="table dataTable">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Image</th>
                        {{-- <th>Brand</th> --}}
                        <th>Category</th>
                        <th>Sub Category</th>
                        <th>Name</th>
                        <th>Base Price</th>
                        <th>Article </th>
                        <th>HSN </th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sno = 1;
                    @endphp
                    @foreach ($pendingProducts as $item)
                        <tr>
                            <td>{{ $sno++ }}</td>
                            <td><img src="{{ asset('product images/' . $item->image) }}"
                                    onerror="this.src='{{ asset('images/dummy.png') }}'"
                                    style="width: 80px; height: 80px; object-fit: cover; aspect-ratio: 1/1;"></td>
                            {{-- <td>{{ $item->brand_name }}</td> --}}
                            <td>{{ $item->category_name }}</td>
                            <td>{{ $item->subcategory_name }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->base_price }}</td>
                            <td>{{ $item->article_no }}</td>
                            <td>{{ $item->hsn_code }}</td>
                            <td>
                                @if ($item->active == 1)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <script>
        $(window).on("load", function() {
            $("#pageLoader").fadeOut();
            $("#tableContent").fadeIn();
        });
    </script>
@endsection
