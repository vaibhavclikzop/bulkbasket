@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title> Supplier Expense Sub Category</title>
    @endpush



    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    Supplier Expense Sub Category
                </div>
                <div>
                    <button class="btn btn-primary add" type="button">Add</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table dataTable">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Expense Category </th>
                        <th>Name</th>
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
                            <td>{{ $item->expense_category }}</td>
                            <td>{{ $item->name }}</td>
                            <td>
                                <button class="btn btn-primary btn-sm edit" data-id="{{ $item->id }}"
                                    data-name="{{ $item->name }}" data-expense_cat_id="{{ $item->expense_cat_id }}"
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


    <form action="{{ route('supplier/expenseSaveSubCategory') }}" method="POST" class="needs-validation" novalidate
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
                        <label for="" class=" ">Expense Category</label>
                        <select name="expense_cat_id" id="expense_cat_id" class="form-control" required>
                            <option value="">Select Category</option>
                            @foreach ($supplierExpCat as $item)
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
        // Add New
        $(".add").on("click", function() {
            $("#modalTitleId").text("Add Sub Category");
            $("#id").val("");
            $("#name").val("");
            $("#expense_cat_id").val("");
            $("#modalId").modal("show");
        });

        // Edit
        $(document).on("click", ".edit", function() {
            $("#modalTitleId").text("Edit Sub Category");
            $("#id").val($(this).data("id"));
            $("#name").val($(this).data("name"));
            $("#expense_cat_id").val($(this).data("expense_cat_id"));
            $("#modalId").modal("show");
        });
    </script>
@endsection
