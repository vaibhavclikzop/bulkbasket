@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title>Ware House Zone</title>
    @endpush



    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    Ware House Zone
                </div>
                <div>
                    <button class="btn btn-primary add" type="button">Add Ware House Zone</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            @php
                $sno = 1;
            @endphp
            <table class="table dataTable">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Area</th>
                        {{-- <th>Description</th> --}}
                        <th>Zone Code</th>
                        <th>Status</th>
                        <th>Action</th>

                    </tr>
                </thead>
                <tbody>
                    @foreach ($warehouseZone as $item)
                        <tr>
                            <td>{{ $sno++ }}</td>
                            <td>{{ $item->area }}</td>
                            {{-- <td>{{ $item->description }}</td> --}}
                            <td>{{ $item->zone_code }}</td>
                            <td>
                                @if ($item->is_active == 1)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-primary btn-sm edit" data-data="{{ @json_encode($item) }}"
                                    type="button"><i class="fa fa-pencil" aria-hidden="true"></i></button>
                            </td>


                        </tr>
                    @endforeach
                </tbody>

            </table>

        </div>
    </div>
    <form action="{{ route('supplier/updateWareHouseZone') }}" method="POST" class="needs-validation" novalidate
        enctype="multipart/form-data">
        @csrf
        <div class="modal fade" id="modalId" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
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
                            <input type="hidden" name="id" id="id">
                            <div class="col-md-4">
                                <label for="">Area</label>
                                <input type="text" name="area" id="area" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label for="">Zone Code</label>
                                <input type="text" name="zone_code" id="zone_code" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label for="">Status</label>
                                <select name="is_active" id="is_active" class="form-control" required>
                                    <option value="">Select</option>
                                    <option value="1">Active</option>
                                    <option value="0">InActive</option>
                                </select>
                            </div>
                            <div class="col-md-8 mt-3">
                                <label for="">Description</label>
                                <textarea type="text" name="description" id="description" class="form-control"></textarea>
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

    <script>
        $(".add").on("click", function() {
            $("#modalTitleId").text("Add Zone")
            $("#id").val("");
            $("#modalId").modal("show")
        });

        $(document).on("click", ".edit", function() {
            $("#modalTitleId").text("Edit Zone")
            var data = $(this).data("data");
            $.each(data, function(i, o) {
                $("input[name=" + i + "]").val(o)
                $("select[name=" + i + "]").val(o)
            });
            $("#modalId").modal("show")
        });
    </script>
@endsection
