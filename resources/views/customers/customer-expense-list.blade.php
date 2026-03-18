@extends('customers.layouts.main')
@section('main-section')
    @push('title')
        <title> Customer Expense List</title>
    @endpush


    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    Customer Expense List
                </div>
                <div>
                    <button class="btn btn-outline-primary add" type="button">Add</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table dataTable">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Category</th>
                        <th>Sub Category</th>
                        <th>Name</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sno = 1; @endphp
                    @foreach ($data as $item)
                        <tr>
                            <td>{{ $sno++ }}</td>
                            <td>{{ $item->expense_category }}</td>
                            <td>{{ $item->expense_subcategory }}</td>
                            <td>{{ $item->name }}</td>
                            <td><span class="badge bg-warning">{{ number_format($item->amount, 2) }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($item->expense_date)->format('d-m-Y') }}</td>
                            <td>
                                <button class="btn btn-outline-info rounded-pill btn-sm edit" data-id="{{ $item->id }}"
                                    data-expense_cat_id="{{ $item->expense_cat_id }}"
                                    data-expense_subcat_id="{{ $item->expense_subcat_id }}" data-name="{{ $item->name }}"
                                    data-amount="{{ $item->amount }}" data-expense_date="{{ $item->expense_date }}" data-note="{{ $item->note }}">
                                    <i class="fa fa-pencil"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>


    <form action="{{ route('customer/expenseSave') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal fade" id="modalId" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitleId">Add Expense</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="id" name="id">

                        <label>Expense Category</label>
                        <select name="expense_cat_id" id="expense_cat_id" class="form-control" required>
                            <option value="">Select Category</option>
                            @foreach ($supplierExpCat as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>

                        <label class="mt-3">Expense Sub Category</label>
                        <select name="expense_subcat_id" id="expense_subcat_id" class="form-control" required>
                            <option value="">Select Sub Category</option>
                            @foreach ($supplierExpSubCat as $subcat)
                                <option value="{{ $subcat->id }}">{{ $subcat->name }}</option>
                            @endforeach
                        </select>

                        <label class="mt-3">Name</label>
                        <input type="text" name="name" id="name" class="form-control" required>

                        <label class="mt-3">Amount</label>
                        <input type="number" name="amount" id="amount" class="form-control" required>

                        <label class="mt-3">Expense Date</label>
                        <input type="date" name="expense_date" id="expense_date" class="form-control" required>
                        <label class="mt-3">Note</label>
                        <textarea name="note" class="form-control" id="note"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        $(".add").on("click", function() {
            $("#modalTitleId").text("Add Expense");
            $("#id, #name, #amount, #expense_date").val("");
            $("#expense_cat_id, #expense_subcat_id").val("");
            $("#note").val("");
            const today = new Date().toISOString().split('T')[0];
            $("#expense_date").val(today);
            $("#modalId").modal("show");
        });

        $(".edit").on("click", function() {
            $("#modalTitleId").text("Edit Expense");
            $("#id").val($(this).data("id"));
            $("#name").val($(this).data("name"));
            $("#amount").val($(this).data("amount"));
            $("#expense_date").val($(this).data("expense_date"));
            $("#expense_cat_id").val($(this).data("expense_cat_id"));
            $("#expense_subcat_id").val($(this).data("expense_subcat_id"));
            $("#note").val($(this).data("note"));
            $("#modalId").modal("show");
        });
    </script>
@endsection
