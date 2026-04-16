@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title>Dashboard Supplier</title>
    @endpush

    <div class="d-flex justify-content-between align-items-center flex-wrap mb-5 gap-3">
        <div class="d-flex flex-column">
            <h3>Bulk Basket India</h3>
            <p class="mb-0">Dashboard</p>
        </div>
        <div class="d-flex justify-content-between align-items-center rounded flex-wrap gap-3">

            <button type="button" class="btn btn-primary">Analytics</button>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-3 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-itmes-center">
                        <div>
                            <div class="p-3 rounded bg-primary-subtle badge bg-primary" style="background-color: #84b95d !important;">
                                <svg fill="none" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M14.2124 7.76241C14.2124 10.4062 12.0489 12.5248 9.34933 12.5248C6.6507 12.5248 4.48631 10.4062 4.48631 7.76241C4.48631 5.11865 6.6507 3 9.34933 3C12.0489 3 14.2124 5.11865 14.2124 7.76241ZM2 17.9174C2 15.47 5.38553 14.8577 9.34933 14.8577C13.3347 14.8577 16.6987 15.4911 16.6987 17.9404C16.6987 20.3877 13.3131 21 9.34933 21C5.364 21 2 20.3666 2 17.9174ZM16.1734 7.84875C16.1734 9.19506 15.7605 10.4513 15.0364 11.4948C14.9611 11.6021 15.0276 11.7468 15.1587 11.7698C15.3407 11.7995 15.5276 11.8177 15.7184 11.8216C17.6167 11.8704 19.3202 10.6736 19.7908 8.87118C20.4885 6.19676 18.4415 3.79543 15.8339 3.79543C15.5511 3.79543 15.2801 3.82418 15.0159 3.87688C14.9797 3.88454 14.9405 3.90179 14.921 3.93246C14.8955 3.97174 14.9141 4.02253 14.9396 4.05607C15.7233 5.13216 16.1734 6.44206 16.1734 7.84875ZM19.3173 13.7023C20.5932 13.9466 21.4317 14.444 21.7791 15.1694C22.0736 15.7635 22.0736 16.4534 21.7791 17.0475C21.2478 18.1705 19.5335 18.5318 18.8672 18.6247C18.7292 18.6439 18.6186 18.5289 18.6333 18.3928C18.9738 15.2805 16.2664 13.8048 15.5658 13.4656C15.5364 13.4493 15.5296 13.4263 15.5325 13.411C15.5345 13.4014 15.5472 13.3861 15.5697 13.3832C17.0854 13.3545 18.7155 13.5586 19.3173 13.7023Z"
                                        fill="currentColor" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h3>{{ $totalCustomer }}</h3>
                            <p class="mb-0">Active Customer</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-itmes-center">
                        <div>
                            <div class="p-3 rounded bg-primary-subtle badge bg-primary" style="background-color: #84b95d !important;">
                                <svg fill="none" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M16.4184 6.47H16.6232C19.3152 6.47 21.5 8.72 21.5 11.48V17C21.5 19.76 19.3152 22 16.6232 22H7.3768C4.6848 22 2.5 19.76 2.5 17V11.48C2.5 8.72 4.6848 6.47 7.3768 6.47H7.58162C7.60113 5.27 8.05955 4.15 8.8886 3.31C9.72741 2.46 10.8003 2.03 12.0098 2C14.4286 2 16.3891 4 16.4184 6.47ZM9.91273 4.38C9.36653 4.94 9.06417 5.68 9.04466 6.47H14.9553C14.9261 4.83 13.6191 3.5 12.0098 3.5C11.2587 3.5 10.4784 3.81 9.91273 4.38ZM15.7064 10.32C16.116 10.32 16.4379 9.98 16.4379 9.57V8.41C16.4379 8 16.116 7.66 15.7064 7.66C15.3065 7.66 14.9748 8 14.9748 8.41V9.57C14.9748 9.98 15.3065 10.32 15.7064 10.32ZM8.93737 9.57C8.93737 9.98 8.6155 10.32 8.20585 10.32C7.80595 10.32 7.47433 9.98 7.47433 9.57V8.41C7.47433 8 7.80595 7.66 8.20585 7.66C8.6155 7.66 8.93737 8 8.93737 8.41V9.57Z"
                                        fill="currentColor" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h3>{{ $totalEstimates }}</h3>
                            <p class="mb-0">Order Challan</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-itmes-center">
                        <div>
                            <div class="p-3 rounded bg-primary-subtle badge bg-primary" style="background-color: #84b95d !important;">
                                <svg fill="none" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M14.1213 11.2331H16.8891C17.3088 11.2331 17.6386 10.8861 17.6386 10.4677C17.6386 10.0391 17.3088 9.70236 16.8891 9.70236H14.1213C13.7016 9.70236 13.3719 10.0391 13.3719 10.4677C13.3719 10.8861 13.7016 11.2331 14.1213 11.2331ZM20.1766 5.92749C20.7861 5.92749 21.1858 6.1418 21.5855 6.61123C21.9852 7.08067 22.0551 7.7542 21.9652 8.36549L21.0159 15.06C20.8361 16.3469 19.7569 17.2949 18.4879 17.2949H7.58639C6.25742 17.2949 5.15828 16.255 5.04837 14.908L4.12908 3.7834L2.62026 3.51807C2.22057 3.44664 1.94079 3.04864 2.01073 2.64043C2.08068 2.22305 2.47038 1.94649 2.88006 2.00874L5.2632 2.3751C5.60293 2.43735 5.85274 2.72207 5.88272 3.06905L6.07257 5.35499C6.10254 5.68257 6.36234 5.92749 6.68209 5.92749H20.1766ZM7.42631 18.9079C6.58697 18.9079 5.9075 19.6018 5.9075 20.459C5.9075 21.3061 6.58697 22 7.42631 22C8.25567 22 8.93514 21.3061 8.93514 20.459C8.93514 19.6018 8.25567 18.9079 7.42631 18.9079ZM18.6676 18.9079C17.8282 18.9079 17.1487 19.6018 17.1487 20.459C17.1487 21.3061 17.8282 22 18.6676 22C19.4969 22 20.1764 21.3061 20.1764 20.459C20.1764 19.6018 19.4969 18.9079 18.6676 18.9079Z"
                                        fill="currentColor" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h3>{{ $totalOrders }}</h3>
                            <p class="mb-0">Total Order</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-itmes-center">
                        <div>
                            <div class="p-3 rounded bg-primary-subtle badge bg-primary" style="background-color: #84b95d !important;">
                                <svg fill="none" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M15.164 6.08252C15.4791 6.08684 15.7949 6.09117 16.1119 6.09441C19.5172 6.09441 22 8.52216 22 11.8748V16.1811C22 19.5337 19.5172 21.9615 16.1119 21.9615C14.7478 21.9904 13.3837 22 12.0098 22C10.6359 22 9.25221 21.9904 7.88813 21.9615C4.48283 21.9615 2 19.5337 2 16.1811V11.8748C2 8.52216 4.48283 6.09441 7.89794 6.09441C9.18351 6.07514 10.4985 6.05588 11.8332 6.05588V5.8921C11.8332 5.22736 11.2738 4.68786 10.6065 4.68786H9.63494C8.52601 4.68786 7.62316 3.80154 7.62316 2.72254C7.62316 2.32755 7.95682 2 8.35918 2C8.77134 2 9.09519 2.32755 9.09519 2.72254C9.09519 3.01156 9.34053 3.24277 9.63494 3.24277H10.6065C12.0883 3.25241 13.2954 4.43738 13.3052 5.88247V6.06551C13.9239 6.06551 14.5425 6.074 15.164 6.08252ZM10.8518 14.7457H9.82139V15.7669C9.82139 16.1618 9.48773 16.4894 9.08538 16.4894C8.67321 16.4894 8.34936 16.1618 8.34936 15.7669V14.7457H7.30913C6.90677 14.7457 6.57311 14.4277 6.57311 14.0231C6.57311 13.6281 6.90677 13.3006 7.30913 13.3006H8.34936V12.289C8.34936 11.894 8.67321 11.5665 9.08538 11.5665C9.48773 11.5665 9.82139 11.894 9.82139 12.289V13.3006H10.8518C11.2542 13.3006 11.5878 13.6281 11.5878 14.0231C11.5878 14.4277 11.2542 14.7457 10.8518 14.7457ZM15.0226 13.1175H15.1207C15.5231 13.1175 15.8567 12.7996 15.8567 12.395C15.8567 12 15.5231 11.6724 15.1207 11.6724H15.0226C14.6104 11.6724 14.2866 12 14.2866 12.395C14.2866 12.7996 14.6104 13.1175 15.0226 13.1175ZM16.7007 16.4316H16.7988C17.2012 16.4316 17.5348 16.1137 17.5348 15.7091C17.5348 15.3141 17.2012 14.9865 16.7988 14.9865H16.7007C16.2875 14.9865 15.9647 15.3141 15.9647 15.7091C15.9647 16.1137 16.2875 16.4316 16.7007 16.4316Z"
                                        fill="currentColor" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h3>{{ $deliveredOrders }}</h3>
                            <p class="mb-0">Order Delivered</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card card-block card-stretch card-height">
                <div class="flex-wrap card-header d-flex justify-content-between border-0">
                    <div class="header-title">
                        <h4 class=" card-title">Recent Order Challan</h4>
                    </div>
                    <div>
                        <a href="/supplier/orders-estimate/pending"> <button class="btn btn-sm btn-primary">View
                                All</button></a>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive border rounded">
                        <table id="basic-table" class="table mb-0 table-striped" role="grid">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Customer Name</th>
                                    <th>Customer Number</th>
                                    <th>Order Id</th>
                                    <th>Payment Status</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $sno = 1; @endphp
                                @foreach ($recentEstimateOrder as $item)
                                    <tr>
                                        <td>{{ $sno++ }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->number }}</td>
                                        <td>#{{ $item->id }}</td>
                                        <td>{{ $item->payment_status }}</td>
                                        <td>{{ number_format($item->total_amount ?? 0, 2) }}</td>
                                        <td>{{ $item->order_status }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card card-block card-stretch card-height">
                <div class="flex-wrap card-header d-flex justify-content-between border-0">
                    <div class="header-title">
                        <h4 class=" card-title">Recent Orders</h4>
                    </div>
                    <div>
                        <a href="/supplier/orders"> <button class="btn btn-sm btn-primary">View All</button></a>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive border rounded">
                        <table id="basic-table" class="table mb-0 table-striped" role="grid">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Customer Name</th>
                                    <th>Customer Number</th>
                                    <th>Invocie Id</th>
                                    <th>Payment Status</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $sno = 1; @endphp
                                @foreach ($recentOrder as $item)
                                    <tr>
                                        <td>{{ $sno++ }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->number }}</td>
                                        <td>{{ $item->invoice_no ?? '--' }}</td>
                                        <td>{{ $item->payment_status }}</td>
                                        <td>{{ number_format($item->total_amount ?? 0, 2) }}</td>
                                        <td>{{ $item->order_status }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8 col-xl-8">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card card-block card-stretch card-height">
                        <div class="flex-wrap card-header d-flex justify-content-between align-items-center">
                            <div class="header-title">
                                <h4>Sales Statistics</h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="sales-chart-02" height="150"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <div class="header-title">
                        <h4 class=" card-title">Date</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="course-picker">
                        <input type="hidden" name="inline" class="d-none inline_flatpickr">
                    </div>
                </div>
            </div>
        </div>

    </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('sales-chart-02').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! $salesMonths !!},
                datasets: [{
                    label: 'Total Sales (₹)',
                    data: {!! $salesTotals !!},
                    backgroundColor: '#84b95d',
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => `₹ ${context.parsed.y.toLocaleString()}`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => `₹ ${value}`
                        }
                    }
                }
            }
        });
    </script>
@endsection
