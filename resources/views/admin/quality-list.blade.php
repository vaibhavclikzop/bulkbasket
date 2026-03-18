@extends('admin.layouts.main')
@section('main-section')
    @push('title')
        <title> Quality List</title>
    @endpush

    <div class="content-inner container-fluid pb-0" id="page_layout">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between">
                    <div>
                        Quality List
                    </div>
                    {{-- <div>
                        <button class="btn btn-primary add" type="button">Add</button>
                    </div> --}}
                </div>
            </div>
            <div class="card-body">
                <table class="table dataTable">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $sno = 1; @endphp
                        @foreach ($data as $item)
                            <tr>
                                <td>{{ $sno++ }}</td>
                                <td>
                                    <img src="/quality-images/{{ $item->image }}" width="80px">
                                </td>
                                <td>{{ $item->question }}</td>
                               <td>{{ Str::limit($item->answer, 50) }}</td>
                                <td>
                                    <button class="btn btn-primary btn-sm edit" data-data="{{ @json_encode($item) }}"
                                        type="button">
                                        <i class="fa fa-pencil" aria-hidden="true"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <form action="{{ route('s1/qulitySaveMain') }}" method="POST" class="needs-validation" novalidate
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
                        <label for="" class="mt-3">File <span style="color: red">Img Size
                                (550*540)px</span></label>
                        <input type="file" name="file" id="file" class="form-control">
                        <label for="" class="mt-3">Title</label>
                        <textarea type="text" name="question" id="question" class="form-control" required></textarea>
                        <label for="" class="mt-3">Description</label>
                        <textarea type="text" name="answer" id="answer" class="form-control" required></textarea>

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
            $("#modalTitleId").text("Add");
            $("#id").val(""); 
            $("#question").val("");
            $("#file").val("");
            $("#answer").val("");
            $("#modalId").modal("show");
        });

        $(document).on("click", ".edit", function() {
            $("#modalTitleId").text("Edit")
            var data = $(this).data("data");
            $.each(data, function(i, o) {
                $("input[name=" + i + "]").val(o)
                $("textarea[name=" + i + "]").val(o)
            });
            $("#modalId").modal("show")
        });
    </script>
@endsection
