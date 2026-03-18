@extends('admin.layouts.main')
@section('main-section')
    @push('title')
        <title> Edit order delivery list</title>
    @endpush

    <div class="content-inner container-fluid pb-0" id="page_layout">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between">
                    <div>
                        Edit order delivery list
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('s1/orderDeliverySaveMain', $orderDelivery->id) }}" method="POST" class="needs-validation"
                    novalidate enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <input type="hidden" name="id" value="{{ $orderDelivery->id }}">
                        <div class="col-md-12">
                            <label for="form-control">Name</label>
                            <input type="text" name="title" class="form-control" value="{{ $orderDelivery->title }}"
                                placeholder="Enter Title" required>
                        </div>

                        <div class="col-md-12 mt-3">
                            <label for="form-control">Content</label>
                            <textarea type="text" name="content" id="div_editor1" placeholder="Enter Description" required>{{ $orderDelivery->content }}</textarea>
                        </div>

                        <div class="col-md-3 mt-4">
                            <button class="btn btn-primary" type="submit">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const badges = document.querySelectorAll('.copy-variable');
        badges.forEach(badge => {
            badge.addEventListener('click', () => {
                const value = badge.getAttribute('data-value');
                navigator.clipboard.writeText(value).then(() => {
                    toastr.success('Copied: ' + value, 'Copied to Clipboard!');
                }).catch(err => {
                    toastr.error('Failed to copy text.', 'Error');
                    console.error(err);
                });
            });
        });
    });
</script>
