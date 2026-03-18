@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title> Product For Request</title>
    @endpush



    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    Product For Request List
                </div>

            </div>
        </div>
        <div class="card-body">
            <table class="table dataTable">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Product Name</th>
                        <th>Remark</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sno = 1;
                    @endphp
                    @foreach ($data as $item)
                        <tr>
                            <td>{{ $sno++ }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->phone }}</td>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->remarks }}</td>
                            <td>{{ $item->status }}</td>
                            <td>
                                <button class="btn btn-dark btn-sm editStatus" type="button" value="{{ $item->id }}"
                                    data-status="{{ $item->status }}"> <i class="fa fa-pencil" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <form action="{{ route('supplier/UpdateProductRequestStatus') }}" method="POST" class="needs-validation" novalidate>
        @csrf
        <div class="modal fade" id="statusModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
            role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitleId">
                            status Status
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="statusId">
                        <label for="">Select Status</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="">Select</option>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>

                        </select>
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
        $(document).on("click", ".editStatus", function() {
            $("#statusId").val($(this).val())
            $("#status").val($(this).data("status"))
            $("#statusModal").modal("show")
        })
    </script>
@endsection
