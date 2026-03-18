@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title>Current Stock</title>
    @endpush

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    <h5>Current Stock</h5>
                </div>

            </div>
        </div>
        <div class="card-body">
            <div class="custom-table-effect table-responsive  border rounded">
                <table class="table mb-0" id="datatable" data-toggle="data-table">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Product</th>
                            <th>Article No</th>
                            <th>Warehouse</th>
                            <th>Location</th>
                            <th>Current Stock</th>
                            {{-- <th>Action</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @php $sno = 1; @endphp
                        @foreach ($current_stock as $item)
                            <tr>
                                <td>{{ $sno++ }}</td>
                                <td>{{ $item->product }}</td>
                                <td>{{ $item->article_no }}</td>
                                <td>{{ $item->warehouse }}</td>
                                <td>{{ $item->location_code }}</td>
                                <td>{{ $item->total_stock }}</td>
                                {{-- <td><a href="/supplier/current-stock-history/{{ $item->id }}" class="btn btn-sm btn-info">Vew
                                    History</a></td> --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
