@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title>MRN Product Wise</title>
    @endpush


    <style>
        td,
        th {
            border-color: black;
            border: solid 1px gray;

        }

        .table th,
        .table td {
            padding: 2px 12px !important;
        }
    </style>
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    <h5>MRN Product Wise</h5>
                </div>

                <div>
                    <form action="" class="d-flex">
                        <div>
                            <label for="">Product</label>
                            <select name="product_id" id="product_id">
                                <option value="">Select</option>
                                @foreach ($product as $item)
                                    <option value="{{ $item->id }}"
                                        {{ request('product_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="">Vendor</label>
                            <select name="vendor_id" id="vendor_id">
                                <option value="">Select</option>
                                @foreach ($vendor as $item)
                                    <option value="{{ $item->id }}"
                                        {{ request('vendor_id') == $item->id ? 'selected' : '' }}>{{ $item->company }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mx-2">
                            <label for="">From Date</label>
                            <input type="date" name="fromDt" class="form-control" value="{{ request('fromDt') }}">
                        </div>
                        <div class="mx-2">
                            <label for="">To Date</label>
                            <input type="date" name="toDt" class="form-control" value="{{ request('toDt') }}">
                        </div>
                        <div class="mx-2">

                            <button class="btn btn-primary mt-4" type="submit">Search</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="custom-table-effect table-responsive  border rounded">
                <table class="table mb-0  " id="myDataTable">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>PO</th>
                            <th>Invoice Date</th>
                            <th>Invoice</th>
                            <th>Article No</th>
                            <th>Product Name</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Vendor</th>
                            <th>Warehouse</th>
                            <th>Location</th>

                            <th>R.M Date</th>

                            <th>Description</th>
                            {{-- <th>Created at</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @php $sno = 1; @endphp
                        @foreach ($data as $row)
                            <tr>
                                <td>{{ $sno++ }}</td>
                                <td>{{ $row->po_name }}</td>
                                <td>{{ $row->invoice_date }}</td>
                                <td>{{ $row->invoice_no }}</td>
                                <td>{{ $row->article_no }}</td>
                                <td>{{ $row->product_name }}</td>
                                <td>{{ $row->qty }}</td>
                                <td>{{ $row->price }}</td>
                                <td>{{ $row->total }}</td>
                                <td>{{ $row->vendor }}</td>
                                <td>{{ $row->warehouse }}</td>
                                <td>{{ $row->location }}</td>

                                <td>{{ $row->received_material_date }}</td>

                                <td>{{ $row->description }}</td>
                                {{-- <td>{{ \Carbon\Carbon::parse($row->created_at)->format('d-m-Y') }}</td> --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $("#myDataTable").DataTable({
                "responsive": false,
                "lengthChange": true,
                "autoWidth": false,
                "ordering": true,
                "buttons": ["excel", 'csv'],
                "pageLength": 100,
                "lengthMenu": [
                    [100, 250, 500, -1],
                    [100, 250, 500, "All"]
                ],
            }).buttons().container().appendTo('.col-md-6:eq()');
            $("#product_id").select2({
                width: "100%"
            });
            $("#vendor_id").select2({
                width: "100%"
            });
        })
    </script>
@endsection
