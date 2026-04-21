@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title>Mode Of Transport</title>
    @endpush



    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    Mode Of Transport
                </div>
                <div>
                    <button class="btn btn-primary btn-sm addTransport">Add Transport</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table dataTable">
                <thead>
                    <tr>
                        <th>S.no</th>
                        <th> Name</th>
                        <th> Number</th>
                        <th>Vehicle Name</th>
                        <th>Vehicle No</th>
                        <th>Username</th>
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
                            <td>{{ $item->number }}</td>
                            <td>{{ $item->vehicle_name }}</td>
                            <td>{{ $item->vehicle_no }}</td>
                            <td>{{ $item->user_name }}</td>
                            <td><button class="btn btn-primary btn-sm edit" type="button"
                                    data-data="{{ @json_encode($item) }}"><i class="fa fa-pencil"
                                        aria-hidden="true"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <form action="{{ route('supplier/SaveModeOfTransport') }}" method="POST" class="needs-validation" novalidate
        enctype="multipart/form-data">
        @csrf
        <div class="modal fade" id="modalId" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
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
                        <div class="row">
                            <div class="col-md-6">
                                <label for="">Name</label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label for="">Number</label>
                                <input type="number" name="number" id="number" class="form-control" required>
                            </div>

                            <div class="col-md-6 mt-2">
                                <label for="">Vehicle Name.</label>
                                <input type="" name="vehicle_name" id="vehicle_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mt-2">
                                <label for="vehicle_no">
                                    Vehicle No.
                                    <span class="text-danger">*</span>
                                    <small class="text-muted">(Format: UK07AB1234)</small>
                                </label>

                                <input type="text" name="vehicle_no" id="vehicle_no" class="form-control"
                                    placeholder="e.g. UK07AB1234" required>
                            </div>

                            <div class="col-md-6 mt-2">
                                <label for="">Username</label>
                                <input type="" name="user_name" id="user_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mt-2">
                                <label for="">Password</label>
                                <input type="" name="password" id="password" class="form-control" required>
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
        $(".addTransport").on("click", function() {
            $("#modalTitleId").text("Add Transport")
            $("#id").val("");
            $("#modalId").modal("show")
        });

        $(document).on("click", ".edit", function() {
            $("#modalTitleId").text("Edit Transport")
            var data = $(this).data("data");
            $.each(data, function(i, o) {
                $("input[name=" + i + "]").val(o)
                $("select[name=" + i + "]").val(o)
            });

            $("#modalId").modal("show")
        });
    </script>
@endsection
