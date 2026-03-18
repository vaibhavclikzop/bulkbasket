@extends('suppliers.layouts.main')
@section('main-section')
    @push('title')
        <title>Dashboard Supplier</title>
    @endpush

        <div class="d-flex justify-content-between align-items-center flex-wrap mb-5 gap-3">
            <div class="d-flex flex-column">
                <h3>Quick Insights</h3>
                <p class="mb-0">Financial Dashboard</p>
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
                                <div class="p-3 rounded bg-primary-subtle badge bg-primary">
                                    <svg class="icon-30" width="30" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M11.997 15.1746C7.684 15.1746 4 15.8546 4 18.5746C4 21.2956 7.661 21.9996 11.997 21.9996C16.31 21.9996 19.994 21.3206 19.994 18.5996C19.994 15.8786 16.334 15.1746 11.997 15.1746Z"
                                            fill="currentColor"></path>
                                        <path opacity="0.4"
                                            d="M11.9971 12.5838C14.9351 12.5838 17.2891 10.2288 17.2891 7.29176C17.2891 4.35476 14.9351 1.99976 11.9971 1.99976C9.06008 1.99976 6.70508 4.35476 6.70508 7.29176C6.70508 10.2288 9.06008 12.5838 11.9971 12.5838Z"
                                            fill="currentColor"></path>
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
                                <div class="p-3 rounded bg-primary-subtle badge bg-primary">
                                    <svg class="icon-30" width="30" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M11.997 15.1746C7.684 15.1746 4 15.8546 4 18.5746C4 21.2956 7.661 21.9996 11.997 21.9996C16.31 21.9996 19.994 21.3206 19.994 18.5996C19.994 15.8786 16.334 15.1746 11.997 15.1746Z"
                                            fill="currentColor"></path>
                                        <path opacity="0.4"
                                            d="M11.9971 12.5838C14.9351 12.5838 17.2891 10.2288 17.2891 7.29176C17.2891 4.35476 14.9351 1.99976 11.9971 1.99976C9.06008 1.99976 6.70508 4.35476 6.70508 7.29176C6.70508 10.2288 9.06008 12.5838 11.9971 12.5838Z"
                                            fill="currentColor"></path>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h3>{{ $totalEstimates }}</h3>
                                <p class="mb-0">Order Estimate</p>
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
                                <div class="p-3 rounded bg-primary-subtle badge bg-primary">
                                    <svg class="icon-30" width="30" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M11.997 15.1746C7.684 15.1746 4 15.8546 4 18.5746C4 21.2956 7.661 21.9996 11.997 21.9996C16.31 21.9996 19.994 21.3206 19.994 18.5996C19.994 15.8786 16.334 15.1746 11.997 15.1746Z"
                                            fill="currentColor"></path>
                                        <path opacity="0.4"
                                            d="M11.9971 12.5838C14.9351 12.5838 17.2891 10.2288 17.2891 7.29176C17.2891 4.35476 14.9351 1.99976 11.9971 1.99976C9.06008 1.99976 6.70508 4.35476 6.70508 7.29176C6.70508 10.2288 9.06008 12.5838 11.9971 12.5838Z"
                                            fill="currentColor"></path>
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
                                <div class="p-3 rounded bg-primary-subtle badge bg-primary">
                                    <svg class="icon-30" width="30" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M11.997 15.1746C7.684 15.1746 4 15.8546 4 18.5746C4 21.2956 7.661 21.9996 11.997 21.9996C16.31 21.9996 19.994 21.3206 19.994 18.5996C19.994 15.8786 16.334 15.1746 11.997 15.1746Z"
                                            fill="currentColor"></path>
                                        <path opacity="0.4"
                                            d="M11.9971 12.5838C14.9351 12.5838 17.2891 10.2288 17.2891 7.29176C17.2891 4.35476 14.9351 1.99976 11.9971 1.99976C9.06008 1.99976 6.70508 4.35476 6.70508 7.29176C6.70508 10.2288 9.06008 12.5838 11.9971 12.5838Z"
                                            fill="currentColor"></path>
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
                            <h4 class=" card-title">Recent Order Estimate</h4>
                        </div>
                        <div>
                            <a href="/supplier/orders-estimate/pending"> <button class="btn btn-sm btn-primary">View All</button></a>
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
                    backgroundColor: '#437a3a',
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
