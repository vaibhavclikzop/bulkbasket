@extends('admin.layouts.main')
@section('main-section')
    @push('title')
        <title> FAQ Main</title>
    @endpush

    <div class="content-inner container-fluid pb-0" id="page_layout">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between">
                    <div>
                        FAQ Main
                    </div>
                    <div>
                        @foreach ($faq_category as $item)
                            <button class="btn btn-md btn-outline-primary cat-btn" type="button"
                                data-id="{{ $item->id }}">
                                {{ $item->name }}
                            </button>
                        @endforeach

                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="table dataTable">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Category</th>
                            <th>Question</th>
                            <th>Answer</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $sno = 1; @endphp
                        @foreach ($data as $item)
                            <tr>
                                <td>{{ $sno++ }}</td>
                                <td>{{ $item->category_name }}</td>
                                <td>{{ $item->question }}</td>
                                <td>{{ $item->answer }}</td>
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
    <form action="{{ route('s1/faqSaveMain') }}" method="POST" class="needs-validation" novalidate
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
                        <label for="" class="mt-3">FAQ Category</label>
                        <select name="faq_cat_id" id="faq_cat_id" class="form-control" required>
                            <option value="">Select Category</option>
                            @foreach ($faq_category as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                        <label for="" class="mt-3">Question</label>
                        <textarea type="text" name="question" id="question" class="form-control" required></textarea>
                        <label for="" class="mt-3">Answer</label>
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
            $("#faq_cat_id").val("");
            $("#question").val("");
            $("#answer").val("");
            $("#modalId").modal("show");
        });

        $(document).on("click", ".edit", function() {
            $("#modalTitleId").text("Edit");
            var data = $(this).data("data");
            $("#id").val(data.id);
            $("#faq_cat_id").val(data.faq_cat_id);
            $("#question").val(data.question);
            $("#answer").val(data.answer);
            $("#modalId").modal("show");
        });
    </script>
    <script>
        let allFaqs = @json($data);
        let defaultCatId = "{{ $faq_category->first()->id ?? '' }}";
        renderTable(defaultCatId);
        $(document).on("click", ".cat-btn", function() {
            let catId = $(this).data("id");
            $(".cat-btn").removeClass("btn-primary").addClass("btn-outline-primary");
            $(this).removeClass("btn-outline-primary").addClass("btn-primary");

            renderTable(catId);
        });
        function renderTable(catId) {
            let tbody = $("table.dataTable tbody");
            tbody.empty();
            let filtered = allFaqs.filter(f => f.faq_cat_id == catId);
            if (filtered.length === 0) {
                tbody.append(`<tr><td colspan="5" class="text-center">No FAQs found</td></tr>`);
                return;
            }
            let sno = 1;
            filtered.forEach(item => {
                tbody.append(`
                <tr>
                    <td>${sno++}</td>
                    <td>${item.category_name}</td>
                    <td>${item.question}</td>
                    <td>${item.answer}</td>
                    <td>
                        <button class="btn btn-primary btn-sm edit" 
                                data-data='${JSON.stringify(item)}'>
                            <i class="fa fa-pencil" aria-hidden="true"></i>
                        </button>
                    </td>
                </tr>
            `);
            });
        }
    </script>
@endsection
