@extends('admin.layouts.main')
@section('main-section')
    @push('title')
        <title> Email Template List</title>
    @endpush

    <div class="content-inner container-fluid pb-0" id="page_layout">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between">
                    <div>
                        Email Template List
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="table dataTable">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Template Name</th>
                            <th>Description</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $sno = 1; @endphp
                        @foreach ($data as $item)
                            <tr>
                                <td>{{ $sno++ }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ Str::limit($item->description, 50) }}</td>
                                <td>
                                    <a href="edit-email-temp/{{ $item->id }}"> <button class="btn btn-primary btn-sm "
                                            type="button">
                                            <i class="fa fa-pencil" aria-hidden="true"></i>
                                        </button></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
