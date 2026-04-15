@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title>Supplier Profile</title>
    @endpush



    <div class="card">
        <div class="card-header">
            Profile
        </div>
        <div class="card-body">
            <form action="{{ route('supplier/UpdateProfile') }}" method="POST" class="needs-validation" novalidate>
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <label>Name</label>
                        <input type="text" name="name" value="{{ $data->name }}" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label>Number</label>
                        <input type="number" name="number" value="{{ $data->number }}" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ $data->email }}" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label>Address</label>
                        <input type="" name="address" value="{{ $data->address }}" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label>State</label>
                        <select name="state" id="estate" value="{{ $data->state }}" class="form-control">
                            <option value="">Select</option>
                            @foreach ($state as $item)
                                <option value="{{$item->state}}">{{$item->state}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>District</label>
                        <select name="district" id="edistrict" value="{{ $data->district }}" class="form-control">
                            <option value="">Select</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>City</label>
                        <input type="" name="city" value="{{ $data->city }}" class="form-control">
                    </div>
                    <div class="col-md-3 mt-2">
                        <label>Pincode</label>
                        <input type="" name="pincode" value="{{ $data->pincode }}" class="form-control">
                    </div>
                    <div class="col-md-3 mt-2">
                        <label>Password</label>
                        <input type="" name="password" value="{{ $data->password }}" class="form-control" required>
                    </div>
                    <div class="col-md-3 mt-2">
                        <label>Order Prefix <span class="text-danger">(Read Only)</span></label>
                        <input type="" readonly name="order_series" value="{{ $data->order_series }}" class="form-control" required>
                    </div>
                    <div class="col-md-3 mt-2">
                        <label>Order Series<span class="text-danger">(Read Only)</span></label>
                        <input type="number" readonly name="order_id" value="{{ $data->order_id}}" class="form-control" required>
                    </div>
                    <div class="col-md-3 mt-2">
                        <label>Invoice Prefix<span class="text-danger">(Read Only)</span></label>
                        <input type="" readonly name="inv_series" value="{{ $data->inv_series }}" class="form-control" required>
                    </div>
                    <div class="col-md-3 mt-2">
                        <label>Invoice Series<span class="text-danger">(Read Only)</span></label>
                        <input type="number" readonly name="inv_id" value="{{ $data->inv_id}}" class="form-control" required>
                    </div>
                    <div class="col-md-12 mt-4 text-center">
                        <button class="btn btn-primary" type="submit">Save</button>
                    </div>
                </div>
            </form>

        </div>
    </div>
    <script>
        $("#estate").on("change", function() {
   
         $.ajax({
             url: "/GetCity",
             type: "POST",
             data: {
                 state: $(this).val(),
             },
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
             },
             beforeSend: function() {
                 $("#loader").show();
             },
             success: function(result) {
                 var html = "";
                 html += '<option value="">----Select city----</option>';
                 result.forEach(element => {

                     html += '<option value="' + element.city + '" >' + element.city +
                         '</option>';
                 });
                 $("#edistrict").html(html)
             },
             complete: function() {
                 $("#loader").hide();
             },
             error: function(result) {
                 toastr.error(result.responseJSON.message);
             }
         });

     });
    </script>
@endsection
