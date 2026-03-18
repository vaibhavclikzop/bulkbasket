@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title>Purchase Order</title>
    @endpush

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    <h5>Purchase Order</h5>
                </div>
                <div>
                    <form action="" class="d-flex">
                        <div>
                            <label for="">From Date</label>
                            <input type="date" name="fromDt" class="form-control" value="{{ request('fromDt') }}"
                               >
                        </div>
                        <div class="mx-2">
                            <label for="">To Date</label>
                            <input type="date" name="toDt" class="form-control" value="{{ request('toDt') }}"
                            >
                        </div>
                          <div class="mx-2">
                           
                          <button class="btn btn-primary mt-4" type="submit">Search</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table dataTable">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Vendor Name</th>
                        <th>PO Name</th>
                        <th>Description</th>
                        <th>PO ID</th>
                        <th>PO Date</th>
                        <th>Created at</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sno = 1;
                    @endphp
                    @foreach ($poList as $item)
                        <tr>
                            <td>{{ $sno++ }}</td>
                            <td>{{ $item->vendor_name }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->description }}</td>
                            <td>{{ $item->po_id }}</td>
                            <td>{{ date('d-m-Y', strtotime($item->po_date)) }}</td>
                            <td>{{ date('d-m-Y', strtotime($item->created_at)) }}</td>
                            <td>
                                @if ($status == 'pending')
                                    <button class="btn btn-sm btn-info editStatus" type="button"
                                        data-id="{{ $item->id }}">Generate PO</button>

                                    <button class="btn btn-danger btn-sm btnDelete" value="{{ $item->id }}"
                                        type="button"><i class="fa fa-trash" aria-hidden="true"></i></button>


                                    <a href="/supplier/generate-po?id={{ $item->id }}"
                                        class="btn btn-success btn-sm"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                                @endif
                                <a class="btn btn-primary btn-sm"
                                    href="/supplier/purchase-order-view/{{ $item->id }}"><i class="fa fa-eye"
                                        aria-hidden="true"></i></a>
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>


    <form action="{{ route('supplier/saveGeneratePO') }}" method="POST" class="needs-validation" novalidate>
        @csrf
        <div class="modal fade" id="modalId">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitleId">
                            Generate PO
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="id">
                        <h4>You are going to generate PO</h4>
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


    <form action="{{ route('supplier/deletePO') }}" method="POST" class="needs-validation" novalidate>
        @csrf
        <div class="modal fade" id="deleteModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
            role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger">
                        <h5 class="modal-title text-white" id="modalTitleId">
                            Delete
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" hidden id="deleteID" name="id">
                        Are you sure you want to delete this PO?
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
            $(document).on("click", ".editStatus", function() {
                $("#id").val($(this).data("id"))
                $("#modalId").modal("show")
            })

            $(document).on("click", ".btnDelete", function() {
                $("#deleteID").val($(this).val())
                $("#deleteModal").modal("show")
            })
        });
    </script>
@endsection
