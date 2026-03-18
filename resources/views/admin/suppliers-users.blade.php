@extends('admin.layouts.main')
@section('main-section')
    @push('title')
        <title>Dashboard Suppliers Users</title>
    @endpush


    <div class="content-inner container-fluid pb-0" id="page_layout">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between">
                    <div>
                        Suppliers Users
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
                            <th>Name</th>
                            <th>Number</th>
                            <th>Email</th>
                            <th>Password</th>
                            <th>Address</th>
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
                                <td>{{ $item->email }}</td>
                                <td>{{ $item->password }}</td>
                                <td>{{ $item->address }}</td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
