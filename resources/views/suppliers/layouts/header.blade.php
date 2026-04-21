 <!DOCTYPE html>
 <html lang="en">

 <head>
     <meta charset="utf-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
     @stack('title')

     <meta name="csrf-token" content="{{ csrf_token() }}">


     <!-- Favicon -->
     <link rel="shortcut icon" href="/backend/images/favicon.ico">

     <!-- Library / Plugin Css Build -->
     <link rel="stylesheet" href="/backend/css/libs.min.css">

     <link rel="stylesheet" href="/backend/css/sheperd.css">

     <!-- Flatpickr css -->
     <link rel="stylesheet" href="/backend/css/flatpickr.min.css">


     <link rel="stylesheet" href="/backend/css/qompac-ui.min.css">

     <!-- Custom Css -->
     <link rel="stylesheet" href="/backend/css/custom.min.css">
     <!-- Dark Css -->
     <link rel="stylesheet" href="/backend/css/dark.min.css">

     <!-- Customizer Css -->
     <link rel="stylesheet" href="/backend/css/customizer.min.css">

     <!-- RTL Css -->
     <link rel="stylesheet" href="/backend/css/rtl.min.css">

     <link rel="stylesheet" href="/backend/css/swiper-bundle.min.css">
     <link rel="stylesheet" href="/dataTables/datatables.min.css">


     <!-- Google Font -->
     <link rel="preconnect" href="https://fonts.googleapis.com">
     <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
     <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@100;200;300;400;500;600;700;800;900&display=swap"
         rel="stylesheet">




     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
         integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
         crossorigin="anonymous" referrerpolicy="no-referrer" />
     <script src="https://code.jquery.com/jquery-2.2.4.js" integrity="sha256-iT6Q9iMJYuQiMWNd9lDyBUStIq/8PuOW33aOqmvFpqI="
         crossorigin="anonymous"></script>

     <link rel="stylesheet" href="/richtexteditor/rte_theme_default.css" />
     <script type="text/javascript" src="/richtexteditor/rte.js"></script>
     <script type="text/javascript" src='/richtexteditor/plugins/all_plugins.js'></script>


 </head>

 <body class="">
     <!-- loader Start -->
     {{-- <div id="loading">
         <div class="loader simple-loader">
             <div class="loader-body ">
                 <img src="/backend/images/loader.webp" alt="loader" class="image-loader img-fluid ">
             </div>
         </div>
     </div> --}}
     <aside class="sidebar sidebar-base sidebar-white sidebar-default navs-rounded-all " id="first-tour"
         data-toggle="main-sidebar" data-sidebar="responsive">
         <div class="sidebar-header d-flex align-items-center justify-content-start">
             <a href="/supplier/dashboard" class="navbar-brand">

                 <!--Logo start-->
                 <div class="logo-main">
                     <div class="logo-normal">
                         <img src="/logo/{{ $setting->img }}" width="180px">
                     </div>
                     <div class="logo-mini">
                         <img src="/logo/{{ $setting->img }}" width="180px">
                     </div>
                 </div>
                 {{-- <!--logo End-->
                 <h4 class="logo-title">{{ $setting->company_name }}</h4> --}}
             </a>
             <div class="sidebar-toggle" data-toggle="sidebar" data-active="true">
                 <i class="icon">

                     <svg class="icon-10" width="10" height="10" viewBox="0 0 8 8" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                         <path
                             d="M7.29853 8C7.11974 8 6.94002 7.93083 6.80335 7.79248L3.53927 4.50446C3.40728 4.37085 3.33333 4.18987 3.33333 4.00036C3.33333 3.81179 3.40728 3.63081 3.53927 3.4972L6.80335 0.207279C7.07762 -0.069408 7.52132 -0.069408 7.79558 0.209174C8.06892 0.487756 8.06798 0.937847 7.79371 1.21453L5.02949 4.00036L7.79371 6.78618C8.06798 7.06286 8.06892 7.51201 7.79558 7.79059C7.65892 7.93083 7.47826 8 7.29853 8Z"
                             fill="white"></path>
                         <path
                             d="M3.96552 8C3.78673 8 3.60701 7.93083 3.47034 7.79248L0.206261 4.50446C0.0742745 4.37085 0.000325203 4.18987 0.000325203 4.00036C0.000325203 3.81179 0.0742745 3.63081 0.206261 3.4972L3.47034 0.207279C3.74461 -0.069408 4.18831 -0.069408 4.46258 0.209174C4.73591 0.487756 4.73497 0.937847 4.4607 1.21453L1.69649 4.00036L4.4607 6.78618C4.73497 7.06286 4.73591 7.51201 4.46258 7.79059C4.32591 7.93083 4.14525 8 3.96552 8Z"
                             fill="white"></path>
                     </svg>
                 </i>
             </div>
         </div>
         <div class="sidebar-body pt-0 data-scrollbar">
             <div class="sidebar-list">
                 <!-- Sidebar Menu Start -->
                 <ul class="navbar-nav iq-main-menu" id="sidebar-menu">
                     <li class="nav-item static-item">
                         <a class="nav-link static-item disabled text-start" href="#" tabindex="-1">
                             <span class="default-icon">Home</span>
                             <span class="mini-icon" data-bs-toggle="tooltip" title="Home"
                                 data-bs-placement="right">-</span>
                         </a>
                     </li>
                     <li class="nav-item">
                         <a class="nav-link {{ request()->is('supplier/dashboard') ? 'active' : '' }}"
                             aria-current="page" href="/supplier/dashboard">
                             <i class="icon" data-bs-toggle="tooltip" title="Dashboard" data-bs-placement="right">
                                 <svg width="20" class="icon-20" viewBox="0 0 24 24" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                     <path opacity="0.4"
                                         d="M16.0756 2H19.4616C20.8639 2 22.0001 3.14585 22.0001 4.55996V7.97452C22.0001 9.38864 20.8639 10.5345 19.4616 10.5345H16.0756C14.6734 10.5345 13.5371 9.38864 13.5371 7.97452V4.55996C13.5371 3.14585 14.6734 2 16.0756 2Z"
                                         fill="currentColor"></path>
                                     <path fill-rule="evenodd" clip-rule="evenodd"
                                         d="M4.53852 2H7.92449C9.32676 2 10.463 3.14585 10.463 4.55996V7.97452C10.463 9.38864 9.32676 10.5345 7.92449 10.5345H4.53852C3.13626 10.5345 2 9.38864 2 7.97452V4.55996C2 3.14585 3.13626 2 4.53852 2ZM4.53852 13.4655H7.92449C9.32676 13.4655 10.463 14.6114 10.463 16.0255V19.44C10.463 20.8532 9.32676 22 7.92449 22H4.53852C3.13626 22 2 20.8532 2 19.44V16.0255C2 14.6114 3.13626 13.4655 4.53852 13.4655ZM19.4615 13.4655H16.0755C14.6732 13.4655 13.537 14.6114 13.537 16.0255V19.44C13.537 20.8532 14.6732 22 16.0755 22H19.4615C20.8637 22 22 20.8532 22 19.44V16.0255C22 14.6114 20.8637 13.4655 19.4615 13.4655Z"
                                         fill="currentColor"></path>
                                 </svg>
                             </i>
                             <span class="item-name">Dashboard</span>
                         </a>
                     </li>

                     <li class="nav-item">
                         @php
                             $active = ['supplier/customers/2', 'supplier/customers/1', 'supplier/customers/0'];
                         @endphp
                         <a class="nav-link  {{ in_array(request()->path(), $active) ? 'active' : '' }} "
                             data-bs-toggle="collapse" href="#customers-mgmt" role="button" aria-expanded="false"
                             aria-controls="horizontal-menu">
                             <i class="fa fa-users" aria-hidden="true"></i>
                             <span class="item-name">Customers</span>
                             <i class="right-icon">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="18" class="icon-18"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                         d="M9 5l7 7-7 7"></path>
                                 </svg>
                             </i>
                         </a>
                         <ul class="sub-nav collapse" id="customers-mgmt" data-bs-parent="#sidebar-menu">
                             <li class="nav-item">
                                 <a class="nav-link {{ request()->is('supplier/customers/2') ? 'active' : '' }}"
                                     href="/supplier/customers/2">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="Pending Customers"
                                         data-bs-placement="right"> Pending Customers </i>
                                     <span class="item-name"> Pending Customer </span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link  {{ request()->is('supplier/customers/1') ? 'active' : '' }} "
                                     href="/supplier/customers/1">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="Active Customers"
                                         data-bs-placement="right"> Active Customers </i>
                                     <span class="item-name"> Active Customer </span>
                                 </a>
                             </li>

                             <li class="nav-item">
                                 <a class="nav-link  {{ request()->is('supplier/customers/0') ? 'active' : '' }}"
                                     href="/supplier/customers/0">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="InActive Customers"
                                         data-bs-placement="right"> InActive Customers </i>
                                     <span class="item-name"> InActive Customer </span>
                                 </a>
                             </li>

                         </ul>
                     </li>

                     @php
                         $active = [
                             'supplier/generate-po',
                             'supplier/purchase-order/pending',
                             'supplier/purchase-order/generated',
                             'supplier/inward-stock',
                             'supplier/purchase-order/partial',
                             'supplier/purchase-order/complete',
                             'supplier/purchase-return',
                             'supplier/inward-product-wise',
                         ];
                     @endphp
                     <li class="nav-item">
                         <a class="nav-link {{ in_array(request()->path(), $active) ? 'active' : '' }}"
                             data-bs-toggle="collapse" href="#purchase-order" role="button" aria-expanded="false"
                             aria-controls="horizontal-menu">
                             <i class="fa fa-shopping-bag" aria-hidden="true"></i>
                             <span class="item-name">Purchase Management</span>
                             <i class="right-icon">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="18" class="icon-18"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                         d="M9 5l7 7-7 7"></path>
                                 </svg>
                             </i>
                         </a>
                         <ul class="sub-nav collapse" id="purchase-order" data-bs-parent="#sidebar-menu">
                             <li class="nav-item">
                                 <a class="nav-link   {{ request()->is('supplier/generate-po') ? 'active' : '' }}"
                                     href="/supplier/generate-po">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="User Role"
                                         data-bs-placement="right">Generate PO</i>
                                     <span class="item-name">Generate PO </span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link {{ request()->is('supplier/purchase-order/pending') ? 'active' : '' }} "
                                     href="/supplier/purchase-order/pending">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="User Role"
                                         data-bs-placement="right">Generating PO</i>
                                     <span class="item-name">Generating PO </span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link {{ request()->is('supplier/purchase-order/generated') ? 'active' : '' }} "
                                     href="/supplier/purchase-order/generated">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="User Role"
                                         data-bs-placement="right">Generated PO</i>
                                     <span class="item-name">Generated PO</span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link {{ request()->is('supplier/inward-stock') ? 'active' : '' }} "
                                     href="/supplier/inward-stock">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="User Role"
                                         data-bs-placement="right">Purchases</i>
                                     <span class="item-name">Purchases</span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link {{ request()->is('supplier/inward-report') ? 'active' : '' }}"
                                     href="/supplier/inward-report">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="User Role"
                                         data-bs-placement="right">MRN</i>
                                     <span class="item-name">MRN</span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link {{ request()->is('supplier/purchase-order/partial') ? 'active' : '' }} "
                                     href="/supplier/purchase-order/partial">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="User Role"
                                         data-bs-placement="right">Partial Approved</i>
                                     <span class="item-name">Partial Approved</span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link {{ request()->is('supplier/purchase-order/complete') ? 'active' : '' }} "
                                     href="/supplier/purchase-order/complete">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="User Role"
                                         data-bs-placement="right">Full Approved</i>
                                     <span class="item-name">Full Approved</span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link  {{ request()->is('supplier/purchase-return') ? 'active' : '' }}"
                                     href="/supplier/purchase-return">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="User Role"
                                         data-bs-placement="right">Purchase Return</i>
                                     <span class="item-name">Purchase Return</span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link {{ request()->is('supplier/inward-product-wise') ? 'active' : '' }}"
                                     href="/supplier/inward-product-wise">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="User Role"
                                         data-bs-placement="right">MRN Product Wise</i>
                                     <span class="item-name">MRN Product Wise</span>
                                 </a>
                             </li>
                         </ul>
                     </li>

                     <li class="nav-item">
                         <a class="nav-link " aria-current="page" href="/supplier/request-list">
                             <i class="fa fa-cubes" aria-hidden="true"></i>
                             <span class="item-name">Product For Request</span>
                         </a>
                     </li>

                     {{-- <li class="nav-item">
                         <a class="nav-link" data-bs-toggle="collapse" href="#order-estimate" role="button"
                             aria-expanded="false" aria-controls="horizontal-menu">
                             <i class="fa fa-file" aria-hidden="true"></i>
                             <span class="item-name">Order Challan </span>
                             <i class="right-icon">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="18" class="icon-18"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                         d="M9 5l7 7-7 7"></path>
                                 </svg>
                             </i>
                         </a>
                         <ul class="sub-nav collapse" id="order-estimate" data-bs-parent="#sidebar-menu">
                             <li class="nav-item">
                                 <a class="nav-link " href="/supplier/create-estimate">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="Pending Order"
                                         data-bs-placement="right"> PO </i>
                                     <span class="item-name">Create Challan</span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link " href="/supplier/orders-estimate/pending">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="Pending Order"
                                         data-bs-placement="right"> PO </i>
                                     <span class="item-name"> New Challan</span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link " href="/supplier/orders-estimate/processing">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="Pending Order"
                                         data-bs-placement="right"> PO </i>
                                     <span class="item-name"> Processing Challan</span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link " href="/supplier/orders-estimate/complete">
                                     <i class="icon svg-icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="complete Order"
                                         data-bs-placement="right"> CO </i>
                                     <span class="item-name">Complete Challan</span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link " href="/supplier/orders-estimate/cancel">
                                     <i class="icon svg-icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="complete Order"
                                         data-bs-placement="right"> CO </i>
                                     <span class="item-name">Cancel Challan</span>
                                 </a>
                             </li>
                         </ul>
                     </li>

                     <li class="nav-item">
                         <a class="nav-link" data-bs-toggle="collapse" href="#order-mgmt" role="button"
                             aria-expanded="false" aria-controls="horizontal-menu">
                             <i class="fa fa-clipboard" aria-hidden="true"></i>
                             <span class="item-name">Order Management </span>
                             <i class="right-icon">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="18" class="icon-18"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                         d="M9 5l7 7-7 7"></path>
                                 </svg>
                             </i>
                         </a>
                         <ul class="sub-nav collapse" id="order-mgmt" data-bs-parent="#sidebar-menu">
                             <li class="nav-item">
                                 <a class="nav-link " href="/supplier/orders?status=pending">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="Pending Order"
                                         data-bs-placement="right"> Order Management </i>
                                     <span class="item-name">New Order</span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link " href="/supplier/orders?status=processing">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="Pending Order"
                                         data-bs-placement="right"> Order Management </i>
                                     <span class="item-name"> Pending Order </span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link " href="/supplier/orders?status=complete">
                                     <i class="icon svg-icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="complete Order"
                                         data-bs-placement="right"> Order Management </i>
                                     <span class="item-name">Complete Order </span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link " href="/supplier/outward-order-list?status=pending">
                                     <i class="icon svg-icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="complete Order"
                                         data-bs-placement="right"> Order Management </i>
                                     <span class="item-name">View Pick Ticket </span>
                                 </a>
                             </li>
                         </ul>
                     </li> --}}

                     <li class="nav-item">
                         <a class="nav-link" data-bs-toggle="collapse" href="#order-mgmte" role="button"
                             aria-expanded="false" aria-controls="horizontal-menu">
                             <i class="fa fa-clipboard" aria-hidden="true"></i>
                             <span class="item-name">Order Management </span>
                             <i class="right-icon">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="18" class="icon-18"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                         d="M9 5l7 7-7 7"></path>
                                 </svg>
                             </i>
                         </a>
                         <ul class="sub-nav collapse" id="order-mgmte" data-bs-parent="#sidebar-menu">
                            <li class="nav-item">
                                 <a class="nav-link  {{ request()->is('supplier/create-estimate') ? 'active' : '' }}" href="/supplier/create-estimate">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="Create Challan"
                                         data-bs-placement="right"> Order Management </i>
                                     <span class="item-name">Create Challan</span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link  {{ request()->is('supplier/orders-estimate/pending') ? 'active' : '' }} " href="/supplier/orders-estimate/pending">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="New Challan"
                                         data-bs-placement="right"> Order Management </i>
                                     <span class="item-name">New Challan</span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link {{ request()->is('supplier/orders') && request('status') == 'processing' ? 'active' : ''}}" href="/supplier/orders?status=processing">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="Processing Order"
                                         data-bs-placement="right"> Order Management </i>
                                     <span class="item-name"> Processing Order </span>
                                 </a>
                             </li>
                              <li class="nav-item">
                                 <a class="nav-link {{ request()->is('supplier/orders') && request('status') == 'pending' ? 'active' : '' }}" href="/supplier/orders?status=pending">
                                     <i class="icon svg-icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="complete Order"
                                         data-bs-placement="right"> Order Management </i>
                                     <span class="item-name">Pending Order </span>
                                 </a>
                             </li>
                              
                             <li class="nav-item">
                                 <a class="nav-link {{ request()->is('supplier/orders') && request('status') == 'complete' ? 'active' : '' }}" href="/supplier/orders?status=complete">
                                     <i class="icon svg-icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="complete Order"
                                         data-bs-placement="right"> Order Management </i>
                                     <span class="item-name">Complete Order </span>
                                 </a>
                             </li>
                            
                             
                         </ul>
                     </li>

                     <li class="nav-item">
                         <a class="nav-link" data-bs-toggle="collapse" href="#order-mgmtes" role="button"
                             aria-expanded="false" aria-controls="horizontal-menu">
                             <i class="fa fa-inr" aria-hidden="true"></i>
                             <span class="item-name">Invoice Management </span>
                             <i class="right-icon">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="18" class="icon-18"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                         d="M9 5l7 7-7 7"></path>
                                 </svg>
                             </i>
                         </a>
                         <ul class="sub-nav collapse" id="order-mgmtes" data-bs-parent="#sidebar-menu">
                            <li class="nav-item">
                                    <a class="nav-link {{ request()->is('supplier/outward-order-list') && request('status') == 'pending' ? 'active' : '' }}" href="/supplier/outward-order-list?status=pending">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="Create Challan"
                                         data-bs-placement="right"> Order Management </i>
                                     <span class="item-name">View Pick Ticket </span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link  {{ request()->is('supplier/invoices') ? 'active' : '' }} " href="/supplier/invoices">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="New Challan"
                                         data-bs-placement="right"> Order Management </i>
                                     <span class="item-name">Invoices</span>
                                 </a>
                             </li>
                             
                             
                         </ul>
                     </li>
                     
                     
                     {{-- <li class="nav-item">
                         <a class="nav-link " aria-current="page" href="/supplier/invoices">
                             <i class="fa fa-inr" aria-hidden="true"></i>
                             <span class="item-name">Invoices</span>
                         </a>
                     </li> --}}

                     <li class="nav-item">
                         <a class="nav-link" data-bs-toggle="collapse" href="#dispatch-mgmt" role="button"
                             aria-expanded="false" aria-controls="horizontal-menu">
                             <i class="fa fa-truck" aria-hidden="true"></i>
                             <span class="item-name">Dispatch Management</span>
                             <i class="right-icon">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="18" class="icon-18"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                         d="M9 5l7 7-7 7"></path>
                                 </svg>
                             </i>
                         </a>
                         <ul class="sub-nav collapse" id="dispatch-mgmt" data-bs-parent="#sidebar-menu">
                             <li class="nav-item">
                                 <a class="nav-link {{ request()->is('supplier/dispatch-plan/processing') ? 'active' : '' }}" href="/supplier/dispatch-plan/processing">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="Pending Order"
                                         data-bs-placement="right">Dispatch Management</i>
                                     <span class="item-name">Dispatch Plan</span>
                                 </a>
                             </li>
                             {{-- <li class="nav-item">
                                 <a class="nav-link " href="#">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="Pending Order"
                                         data-bs-placement="right"> Dispatch Management </i>
                                     <span class="item-name">Ready to Deliver </span>
                                 </a>
                             </li> --}}
                             <li class="nav-item">
                                 <a class="nav-link {{ request()->is('supplier/outwards/delivered') ? 'active' : '' }}" href="/supplier/outwards/delivered">
                                     <i class="icon svg-icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="complete Order"
                                         data-bs-placement="right">Dispatch Management</i>
                                     <span class="item-name">Delivered </span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link {{ request()->is('supplier/outwards/cancel') ? 'active' : '' }}" href="/supplier/outwards/cancel">
                                     <i class="icon svg-icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="complete Order"
                                         data-bs-placement="right">Dispatch Management</i>
                                     <span class="item-name">Cancel </span>
                                 </a>
                             </li>
                         </ul>
                     </li>

                     <li class="nav-item">
                         <a class="nav-link " aria-current="page" href="#">
                             <i class="fa fa-reply" aria-hidden="true"></i>
                             <span class="item-name">Sale Return</span>
                         </a>
                     </li>

                     {{-- <li class="nav-item">
                         <a class="nav-link " aria-current="page" href="/supplier/wallet-management">
                             <i class="fa fa-wallet" aria-hidden="true"></i>
                             <span class="item-name">Wallet Management</span>
                         </a>
                     </li> --}}

                     <li class="nav-item">
                         <a class="nav-link" data-bs-toggle="collapse" href="#report-stock" role="button"
                             aria-expanded="false" aria-controls="horizontal-menu">
                             <i class="fa fa-line-chart" aria-hidden="true"></i>
                             <span class="item-name">Stock Report</span>
                             <i class="right-icon">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="18" class="icon-18"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                         d="M9 5l7 7-7 7"></path>
                                 </svg>
                             </i>
                         </a>
                         <ul class="sub-nav collapse" id="report-stock" data-bs-parent="#sidebar-menu">
                             <li class="nav-item">
                                 <a class="nav-link {{ request()->is('supplier/current-stock') ? 'active' : '' }}" href="/supplier/current-stock">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="Current Stock"
                                         data-bs-placement="right"> SR </i>
                                     <span class="item-name">Current Stock </span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link " href="#">
                                     <i class="icon svg-icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="Near By Stock"
                                         data-bs-placement="right"> CR </i>
                                     <span class="item-name">Near By Stock</span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link " href="#">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="Defective stock"
                                         data-bs-placement="right">Defective stock </i>
                                     <span class="item-name">Defective stock</span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link " href="#">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="Scrap | Exp Stock"
                                         data-bs-placement="right"> Scrap | Esp Stock
                                     </i>
                                     <span class="item-name">Scrap | Esp Stock</span>
                                 </a>
                             </li>
                         </ul>
                     </li>

                     <li class="nav-item">
                         <a class="nav-link" data-bs-toggle="collapse" href="#reports" role="button"
                             aria-expanded="false" aria-controls="horizontal-menu">
                             <i class="fa fa-bar-chart" aria-hidden="true"></i>
                             <span class="item-name">Reports</span>
                             <i class="right-icon">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="18" class="icon-18"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                         d="M9 5l7 7-7 7"></path>
                                 </svg>
                             </i>
                         </a>
                         <ul class="sub-nav collapse" id="reports" data-bs-parent="#sidebar-menu">
                             <li class="nav-item">
                                 <a class="nav-link " href="#">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="Sale Report"
                                         data-bs-placement="right"> SR </i>
                                     <span class="item-name"> Sale Report </span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link " href="#">
                                     <i class="icon svg-icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="Customer Report"
                                         data-bs-placement="right"> CR </i>
                                     <span class="item-name">Customer Report</span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link " href="#">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="P and L"
                                         data-bs-placement="right"> P&L </i>
                                     <span class="item-name">P&L Report</span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link " href="#">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip"
                                         title="Category Wise Report" data-bs-placement="right"> DO </i>
                                     <span class="item-name">Category Wise Report</span>
                                 </a>
                             </li>
                         </ul>
                     </li>

                     <li class="nav-item">
                         <a class="nav-link " aria-current="page" href="#">
                             <i class="fa fa-search" aria-hidden="true"></i>
                             <span class="item-name">Audit Stock</span>
                         </a>
                     </li>

                     <li class="nav-item">
                         <a class="nav-link" data-bs-toggle="collapse" href="#expense" role="button"
                             aria-expanded="false" aria-controls="horizontal-menu">
                             <i class="fa fa-credit-card" aria-hidden="true"></i>
                             <span class="item-name">Supplier Expense</span>
                             <i class="right-icon">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="18" class="icon-18"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                         d="M9 5l7 7-7 7"></path>
                                 </svg>
                             </i>
                         </a>
                         <ul class="sub-nav collapse" id="expense" data-bs-parent="#sidebar-menu">
                             <li class="nav-item">
                                 <a class="nav-link {{ request()->is('supplier/expense-category') ? 'active' : '' }}" href="/supplier/expense-category">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="Sale Report"
                                         data-bs-placement="right"> </i>
                                     <span class="item-name"> Category</span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link {{ request()->is('supplier/expense-subcategory') ? 'active' : '' }}" href="/supplier/expense-subcategory">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="Sale Report"
                                         data-bs-placement="right"> </i>
                                     <span class="item-name">Sub Category</span>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link {{ request()->is('supplier/expense-list') ? 'active' : '' }}" href="/supplier/expense-list">
                                     <i class="icon">
                                         <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="currentColor"
                                             xmlns="http://www.w3.org/2000/svg">
                                             <g>
                                                 <circle cx="12" cy="12" r="8" fill="currentColor">
                                                 </circle>
                                             </g>
                                         </svg>
                                     </i>
                                     <i class="sidenav-mini-icon" data-bs-toggle="tooltip" title="Sale Report"
                                         data-bs-placement="right"> </i>
                                     <span class="item-name">Expense List</span>
                                 </a>
                             </li>
                         </ul>
                     </li>

                     <li class="nav-item">
                         <a class="nav-link " aria-current="page" href="/supplier/help-support">
                             <i class="fa fa-life-ring" aria-hidden="true"></i>
                             <span class="item-name">Help Support</span>
                         </a>
                     </li>

                     <li class="nav-item">
                         <a class="nav-link " aria-current="page" href="/supplier/logout">
                             <i class="fa-solid fa-right-from-bracket"></i>
                             <span class="item-name">Logout</span>
                         </a>
                     </li>

                 </ul>

                 <!-- Sidebar Menu End -->
             </div>
         </div>
         <div class="sidebar-footer"></div>
     </aside>
     <main class="main-content">
         <div class="position-relative ">
             <!--Nav Start-->
             <nav class="nav navbar navbar-expand-xl navbar-light iq-navbar">
                 <div class="container-fluid navbar-inner">
                     <a href="/supplier/dashboard" class="navbar-brand">

                         <!--Logo start-->
                         <div class="logo-main">
                             <div class="logo-normal">
                                 <svg class="text-primary icon-30" viewBox="0 0 32 32" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                     <path fill-rule="evenodd" clip-rule="evenodd"
                                         d="M7.25333 2H22.0444L29.7244 15.2103L22.0444 28.1333H7.25333L0 15.2103L7.25333 2ZM11.2356 9.32316H18.0622L21.3334 15.2103L18.0622 20.9539H11.2356L8.10669 15.2103L11.2356 9.32316Z"
                                         fill="currentColor"></path>
                                     <path d="M23.751 30L13.2266 15.2103H21.4755L31.9999 30H23.751Z" fill="#3FF0B9">
                                     </path>
                                 </svg>
                             </div>
                             <div class="logo-mini">
                                 <svg class="text-primary icon-30" viewBox="0 0 32 32" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                     <path fill-rule="evenodd" clip-rule="evenodd"
                                         d="M7.25333 2H22.0444L29.7244 15.2103L22.0444 28.1333H7.25333L0 15.2103L7.25333 2ZM11.2356 9.32316H18.0622L21.3334 15.2103L18.0622 20.9539H11.2356L8.10669 15.2103L11.2356 9.32316Z"
                                         fill="currentColor"></path>
                                     <path d="M23.751 30L13.2266 15.2103H21.4755L31.9999 30H23.751Z" fill="#3FF0B9">
                                     </path>
                                 </svg>
                             </div>
                         </div>
                         <!--logo End-->
                         <h4 class="logo-title d-block d-xl-none" data-setting="app_name">Qompac UI</h4>
                     </a>
                     <div class="sidebar-toggle" data-toggle="sidebar" data-active="true">
                         <i class="icon d-flex">
                             <svg class="icon-20" width="20" viewBox="0 0 24 24">
                                 <path fill="currentColor"
                                     d="M4,11V13H16L10.5,18.5L11.92,19.92L19.84,12L11.92,4.08L10.5,5.5L16,11H4Z"></path>
                             </svg>
                         </i>
                     </div>

                     <div class="d-flex align-items-center">
                         <button id="navbar-toggle" class="navbar-toggler" type="button" data-bs-toggle="collapse"
                             data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                             aria-expanded="false" aria-label="Toggle navigation">
                             <span class="navbar-toggler-icon">
                                 <span class="navbar-toggler-bar bar1 mt-1"></span>
                                 <span class="navbar-toggler-bar bar2"></span>
                                 <span class="navbar-toggler-bar bar3"></span>
                             </span>
                         </button>
                     </div>
                     <div class="collapse navbar-collapse" id="navbarSupportedContent">
                         <ul class="mb-2 navbar-nav ms-auto align-items-center navbar-list mb-lg-0 ">
                             <li class="nav-item dropdown">
                                 <a class="py-0 nav-link d-flex align-items-center ps-3" href="#"
                                     id="profile-setting" role="button" data-bs-toggle="dropdown"
                                     aria-expanded="false">
                                     <div class="caption ms-3 d-none d-md-block ">
                                         <h6 class="mb-0 caption-title">User Management</h6>
                                     </div>
                                 </a>
                                 <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profile-setting">
                                     <li><a class="dropdown-item" href="/supplier/user-role">User Role</a></li>

                                     <li>
                                         <hr class="dropdown-divider">
                                     </li>
                                     <li><a class="dropdown-item" href="/supplier/users">Users</a>
                                     </li>

                                 </ul>
                             </li>

                             <li class="nav-item dropdown">
                                 <a class="py-0 nav-link d-flex align-items-center ps-3" href="#"
                                     id="profile-setting" role="button" data-bs-toggle="dropdown"
                                     aria-expanded="false">
                                     <div class="caption ms-3 d-none d-md-block ">
                                         <h6 class="mb-0 caption-title">Product Master</h6>
                                     </div>
                                 </a>
                                 <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profile-setting">
                                     <li><a class="dropdown-item" href="/supplier/product-gst">GST</a></li>

                                     <li>
                                         <hr class="dropdown-divider">
                                     </li>
                                     <li><a class="dropdown-item" href="/supplier/product-uom"> Unit Type</a>
                                     </li>
                                     <li>
                                         <hr class="dropdown-divider">
                                     </li>
                                     <li><a class="dropdown-item" href="/supplier/product-brand"> Brand</a>
                                     </li>
                                     <li>
                                         <hr class="dropdown-divider">
                                     </li>
                                     <li><a class="dropdown-item" href="/supplier/product-category"> Category</a>
                                     </li>
                                     <li>
                                         <hr class="dropdown-divider">
                                     </li>
                                     <li><a class="dropdown-item" href="/supplier/product-sub-category"> Sub
                                             Category</a>
                                     </li>
                                     <li>
                                         <hr class="dropdown-divider">
                                     </li>
                                     <li><a class="dropdown-item" href="/supplier/product-sub-sub-category"> Sub Sub
                                             Category</a>
                                     </li>
                                     <li>
                                         <hr class="dropdown-divider">
                                     </li>
                                     <li><a class="dropdown-item" href="/supplier/product-type"> Product Type</a>
                                     </li>
                                     <li>
                                         <hr class="dropdown-divider">
                                     </li>
                                     <li><a class="dropdown-item" href="/supplier/products"> Products</a>
                                     </li>
                                 </ul>
                             </li>

                             <li class="nav-item dropdown">
                                 <a class="py-0 nav-link d-flex align-items-center ps-3" href="#"
                                     id="profile-setting" role="button" data-bs-toggle="dropdown"
                                     aria-expanded="false">
                                     <div class="caption ms-3 d-none d-md-block ">
                                         <h6 class="mb-0 caption-title">Ware House</h6>
                                     </div>
                                 </a>
                                 <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profile-setting">
                                     <li><a class="dropdown-item" href="/supplier/wareHouseZone"> Ware House
                                             Zone</a>
                                     </li>

                                     <li>
                                         <hr class="dropdown-divider">
                                     </li>
                                     <li><a class="dropdown-item" href="/supplier/warehouse">Ware House List</a>
                                     </li>
                                 </ul>
                             </li>

                             <li class="nav-item dropdown">
                                 <a class="py-0 nav-link d-flex align-items-center ps-3" href="#"
                                     id="profile-setting" role="button" data-bs-toggle="dropdown"
                                     aria-expanded="false">
                                     <div class="caption ms-3 d-none d-md-block ">
                                         <h6 class="mb-0 caption-title">Masters</h6>
                                     </div>
                                 </a>
                                 <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profile-setting">
                                     <li><a class="dropdown-item" href="/supplier/vendor">Vendor Users</a></li>
                                     <li>
                                         <hr class="dropdown-divider">
                                     </li>
                                     <li><a class="dropdown-item" href="/supplier/mode-of-transport">Mode Of
                                             Transport</a></li>
                                     <li>
                                         <hr class="dropdown-divider">
                                     </li>
                                 </ul>
                             </li>

                             <li class="nav-item theme-scheme-dropdown">
                                 <a href="#" class="nav-link" id="mode-drop">
                                     <svg class="icon-24" width="24" viewBox="0 0 24 24" fill="none"
                                         xmlns="http://www.w3.org/2000/svg">
                                         <path
                                             d="M11.9905 5.62598C10.7293 5.62574 9.49646 5.9995 8.44775 6.69997C7.39903 7.40045 6.58159 8.39619 6.09881 9.56126C5.61603 10.7263 5.48958 12.0084 5.73547 13.2453C5.98135 14.4823 6.58852 15.6185 7.48019 16.5104C8.37186 17.4022 9.50798 18.0096 10.7449 18.2557C11.9818 18.5019 13.2639 18.3757 14.429 17.8931C15.5942 17.4106 16.5901 16.5933 17.2908 15.5448C17.9915 14.4962 18.3655 13.2634 18.3655 12.0023C18.3637 10.3119 17.6916 8.69129 16.4964 7.49593C15.3013 6.30056 13.6808 5.62806 11.9905 5.62598Z"
                                             fill="currentColor"></path>
                                         <path
                                             d="M22.1258 10.8771H20.627C20.3286 10.8771 20.0424 10.9956 19.8314 11.2066C19.6204 11.4176 19.5018 11.7038 19.5018 12.0023C19.5018 12.3007 19.6204 12.5869 19.8314 12.7979C20.0424 13.0089 20.3286 13.1274 20.627 13.1274H22.1258C22.4242 13.1274 22.7104 13.0089 22.9214 12.7979C23.1324 12.5869 23.2509 12.3007 23.2509 12.0023C23.2509 11.7038 23.1324 11.4176 22.9214 11.2066C22.7104 10.9956 22.4242 10.8771 22.1258 10.8771Z"
                                             fill="currentColor"></path>
                                         <path
                                             d="M11.9905 19.4995C11.6923 19.5 11.4064 19.6187 11.1956 19.8296C10.9848 20.0405 10.8663 20.3265 10.866 20.6247V22.1249C10.866 22.4231 10.9845 22.7091 11.1953 22.9199C11.4062 23.1308 11.6922 23.2492 11.9904 23.2492C12.2886 23.2492 12.5746 23.1308 12.7854 22.9199C12.9963 22.7091 13.1147 22.4231 13.1147 22.1249V20.6247C13.1145 20.3265 12.996 20.0406 12.7853 19.8296C12.5745 19.6187 12.2887 19.5 11.9905 19.4995Z"
                                             fill="currentColor"></path>
                                         <path
                                             d="M4.49743 12.0023C4.49718 11.704 4.37865 11.4181 4.16785 11.2072C3.95705 10.9962 3.67119 10.8775 3.37298 10.8771H1.87445C1.57603 10.8771 1.28984 10.9956 1.07883 11.2066C0.867812 11.4176 0.749266 11.7038 0.749266 12.0023C0.749266 12.3007 0.867812 12.5869 1.07883 12.7979C1.28984 13.0089 1.57603 13.1274 1.87445 13.1274H3.37299C3.6712 13.127 3.95706 13.0083 4.16785 12.7973C4.37865 12.5864 4.49718 12.3005 4.49743 12.0023Z"
                                             fill="currentColor"></path>
                                         <path
                                             d="M11.9905 4.50058C12.2887 4.50012 12.5745 4.38141 12.7853 4.17048C12.9961 3.95954 13.1147 3.67361 13.1149 3.3754V1.87521C13.1149 1.57701 12.9965 1.29103 12.7856 1.08017C12.5748 0.869313 12.2888 0.750854 11.9906 0.750854C11.6924 0.750854 11.4064 0.869313 11.1955 1.08017C10.9847 1.29103 10.8662 1.57701 10.8662 1.87521V3.3754C10.8664 3.67359 10.9849 3.95952 11.1957 4.17046C11.4065 4.3814 11.6923 4.50012 11.9905 4.50058Z"
                                             fill="currentColor"></path>
                                         <path
                                             d="M18.8857 6.6972L19.9465 5.63642C20.0512 5.53209 20.1343 5.40813 20.1911 5.27163C20.2479 5.13513 20.2772 4.98877 20.2774 4.84093C20.2775 4.69309 20.2485 4.54667 20.192 4.41006C20.1355 4.27344 20.0526 4.14932 19.948 4.04478C19.8435 3.94024 19.7194 3.85734 19.5828 3.80083C19.4462 3.74432 19.2997 3.71531 19.1519 3.71545C19.0041 3.7156 18.8577 3.7449 18.7212 3.80167C18.5847 3.85845 18.4607 3.94159 18.3564 4.04633L17.2956 5.10714C17.1909 5.21147 17.1077 5.33543 17.0509 5.47194C16.9942 5.60844 16.9649 5.7548 16.9647 5.90264C16.9646 6.05048 16.9936 6.19689 17.0501 6.33351C17.1066 6.47012 17.1895 6.59425 17.294 6.69878C17.3986 6.80332 17.5227 6.88621 17.6593 6.94272C17.7959 6.99923 17.9424 7.02824 18.0902 7.02809C18.238 7.02795 18.3844 6.99865 18.5209 6.94187C18.6574 6.88509 18.7814 6.80195 18.8857 6.6972Z"
                                             fill="currentColor"></path>
                                         <path
                                             d="M18.8855 17.3073C18.7812 17.2026 18.6572 17.1195 18.5207 17.0627C18.3843 17.006 18.2379 16.9767 18.0901 16.9766C17.9423 16.9764 17.7959 17.0055 17.6593 17.062C17.5227 17.1185 17.3986 17.2014 17.2941 17.3059C17.1895 17.4104 17.1067 17.5345 17.0501 17.6711C16.9936 17.8077 16.9646 17.9541 16.9648 18.1019C16.9649 18.2497 16.9942 18.3961 17.0509 18.5326C17.1077 18.6691 17.1908 18.793 17.2955 18.8974L18.3563 19.9582C18.4606 20.0629 18.5846 20.146 18.721 20.2027C18.8575 20.2595 19.0039 20.2887 19.1517 20.2889C19.2995 20.289 19.4459 20.26 19.5825 20.2035C19.7191 20.147 19.8432 20.0641 19.9477 19.9595C20.0523 19.855 20.1351 19.7309 20.1916 19.5943C20.2482 19.4577 20.2772 19.3113 20.277 19.1635C20.2769 19.0157 20.2476 18.8694 20.1909 18.7329C20.1341 18.5964 20.051 18.4724 19.9463 18.3681L18.8855 17.3073Z"
                                             fill="currentColor"></path>
                                         <path
                                             d="M5.09528 17.3072L4.0345 18.368C3.92972 18.4723 3.84655 18.5963 3.78974 18.7328C3.73294 18.8693 3.70362 19.0156 3.70346 19.1635C3.7033 19.3114 3.7323 19.4578 3.78881 19.5944C3.84532 19.7311 3.92822 19.8552 4.03277 19.9598C4.13732 20.0643 4.26147 20.1472 4.3981 20.2037C4.53473 20.2602 4.68117 20.2892 4.82902 20.2891C4.97688 20.2889 5.12325 20.2596 5.25976 20.2028C5.39627 20.146 5.52024 20.0628 5.62456 19.958L6.68536 18.8973C6.79007 18.7929 6.87318 18.6689 6.92993 18.5325C6.98667 18.396 7.01595 18.2496 7.01608 18.1018C7.01621 17.954 6.98719 17.8076 6.93068 17.671C6.87417 17.5344 6.79129 17.4103 6.68676 17.3058C6.58224 17.2012 6.45813 17.1183 6.32153 17.0618C6.18494 17.0053 6.03855 16.9763 5.89073 16.9764C5.74291 16.9766 5.59657 17.0058 5.46007 17.0626C5.32358 17.1193 5.19962 17.2024 5.09528 17.3072Z"
                                             fill="currentColor"></path>
                                         <path
                                             d="M5.09541 6.69715C5.19979 6.8017 5.32374 6.88466 5.4602 6.94128C5.59665 6.9979 5.74292 7.02708 5.89065 7.02714C6.03839 7.0272 6.18469 6.99815 6.32119 6.94164C6.45769 6.88514 6.58171 6.80228 6.68618 6.69782C6.79064 6.59336 6.87349 6.46933 6.93 6.33283C6.9865 6.19633 7.01556 6.05003 7.01549 5.9023C7.01543 5.75457 6.98625 5.60829 6.92963 5.47184C6.87301 5.33539 6.79005 5.21143 6.6855 5.10706L5.6247 4.04626C5.5204 3.94137 5.39643 3.8581 5.25989 3.80121C5.12335 3.74432 4.97692 3.71493 4.82901 3.71472C4.68109 3.71452 4.53458 3.7435 4.39789 3.80001C4.26119 3.85652 4.13699 3.93945 4.03239 4.04404C3.9278 4.14864 3.84487 4.27284 3.78836 4.40954C3.73185 4.54624 3.70287 4.69274 3.70308 4.84066C3.70329 4.98858 3.73268 5.135 3.78957 5.27154C3.84646 5.40808 3.92974 5.53205 4.03462 5.63635L5.09541 6.69715Z"
                                             fill="currentColor"></path>
                                     </svg>
                                 </a>
                                 <ul class="list-unstyled dropdown-menu dropdown-content">
                                     <li data-setting="radio">
                                         <div class="dropdown-item d-flex align-items-center">
                                             <input type="radio" value="light" class="btn-check"
                                                 name="theme_scheme" id="color-mode-light">
                                             <label class="d-block" for="color-mode-light">
                                                 <svg class="icon-24" width="24" height="24"
                                                     viewBox="0 0 24 24" fill="none"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                     <path
                                                         d="M11.9905 5.62598C10.7293 5.62574 9.49646 5.9995 8.44775 6.69997C7.39903 7.40045 6.58159 8.39619 6.09881 9.56126C5.61603 10.7263 5.48958 12.0084 5.73547 13.2453C5.98135 14.4823 6.58852 15.6185 7.48019 16.5104C8.37186 17.4022 9.50798 18.0096 10.7449 18.2557C11.9818 18.5019 13.2639 18.3757 14.429 17.8931C15.5942 17.4106 16.5901 16.5933 17.2908 15.5448C17.9915 14.4962 18.3655 13.2634 18.3655 12.0023C18.3637 10.3119 17.6916 8.69129 16.4964 7.49593C15.3013 6.30056 13.6808 5.62806 11.9905 5.62598Z"
                                                         fill="currentColor"></path>
                                                     <path
                                                         d="M22.1258 10.8771H20.627C20.3286 10.8771 20.0424 10.9956 19.8314 11.2066C19.6204 11.4176 19.5018 11.7038 19.5018 12.0023C19.5018 12.3007 19.6204 12.5869 19.8314 12.7979C20.0424 13.0089 20.3286 13.1274 20.627 13.1274H22.1258C22.4242 13.1274 22.7104 13.0089 22.9214 12.7979C23.1324 12.5869 23.2509 12.3007 23.2509 12.0023C23.2509 11.7038 23.1324 11.4176 22.9214 11.2066C22.7104 10.9956 22.4242 10.8771 22.1258 10.8771Z"
                                                         fill="currentColor"></path>
                                                     <path
                                                         d="M11.9905 19.4995C11.6923 19.5 11.4064 19.6187 11.1956 19.8296C10.9848 20.0405 10.8663 20.3265 10.866 20.6247V22.1249C10.866 22.4231 10.9845 22.7091 11.1953 22.9199C11.4062 23.1308 11.6922 23.2492 11.9904 23.2492C12.2886 23.2492 12.5746 23.1308 12.7854 22.9199C12.9963 22.7091 13.1147 22.4231 13.1147 22.1249V20.6247C13.1145 20.3265 12.996 20.0406 12.7853 19.8296C12.5745 19.6187 12.2887 19.5 11.9905 19.4995Z"
                                                         fill="currentColor"></path>
                                                     <path
                                                         d="M4.49743 12.0023C4.49718 11.704 4.37865 11.4181 4.16785 11.2072C3.95705 10.9962 3.67119 10.8775 3.37298 10.8771H1.87445C1.57603 10.8771 1.28984 10.9956 1.07883 11.2066C0.867812 11.4176 0.749266 11.7038 0.749266 12.0023C0.749266 12.3007 0.867812 12.5869 1.07883 12.7979C1.28984 13.0089 1.57603 13.1274 1.87445 13.1274H3.37299C3.6712 13.127 3.95706 13.0083 4.16785 12.7973C4.37865 12.5864 4.49718 12.3005 4.49743 12.0023Z"
                                                         fill="currentColor"></path>
                                                     <path
                                                         d="M11.9905 4.50058C12.2887 4.50012 12.5745 4.38141 12.7853 4.17048C12.9961 3.95954 13.1147 3.67361 13.1149 3.3754V1.87521C13.1149 1.57701 12.9965 1.29103 12.7856 1.08017C12.5748 0.869313 12.2888 0.750854 11.9906 0.750854C11.6924 0.750854 11.4064 0.869313 11.1955 1.08017C10.9847 1.29103 10.8662 1.57701 10.8662 1.87521V3.3754C10.8664 3.67359 10.9849 3.95952 11.1957 4.17046C11.4065 4.3814 11.6923 4.50012 11.9905 4.50058Z"
                                                         fill="currentColor"></path>
                                                     <path
                                                         d="M18.8857 6.6972L19.9465 5.63642C20.0512 5.53209 20.1343 5.40813 20.1911 5.27163C20.2479 5.13513 20.2772 4.98877 20.2774 4.84093C20.2775 4.69309 20.2485 4.54667 20.192 4.41006C20.1355 4.27344 20.0526 4.14932 19.948 4.04478C19.8435 3.94024 19.7194 3.85734 19.5828 3.80083C19.4462 3.74432 19.2997 3.71531 19.1519 3.71545C19.0041 3.7156 18.8577 3.7449 18.7212 3.80167C18.5847 3.85845 18.4607 3.94159 18.3564 4.04633L17.2956 5.10714C17.1909 5.21147 17.1077 5.33543 17.0509 5.47194C16.9942 5.60844 16.9649 5.7548 16.9647 5.90264C16.9646 6.05048 16.9936 6.19689 17.0501 6.33351C17.1066 6.47012 17.1895 6.59425 17.294 6.69878C17.3986 6.80332 17.5227 6.88621 17.6593 6.94272C17.7959 6.99923 17.9424 7.02824 18.0902 7.02809C18.238 7.02795 18.3844 6.99865 18.5209 6.94187C18.6574 6.88509 18.7814 6.80195 18.8857 6.6972Z"
                                                         fill="currentColor"></path>
                                                     <path
                                                         d="M18.8855 17.3073C18.7812 17.2026 18.6572 17.1195 18.5207 17.0627C18.3843 17.006 18.2379 16.9767 18.0901 16.9766C17.9423 16.9764 17.7959 17.0055 17.6593 17.062C17.5227 17.1185 17.3986 17.2014 17.2941 17.3059C17.1895 17.4104 17.1067 17.5345 17.0501 17.6711C16.9936 17.8077 16.9646 17.9541 16.9648 18.1019C16.9649 18.2497 16.9942 18.3961 17.0509 18.5326C17.1077 18.6691 17.1908 18.793 17.2955 18.8974L18.3563 19.9582C18.4606 20.0629 18.5846 20.146 18.721 20.2027C18.8575 20.2595 19.0039 20.2887 19.1517 20.2889C19.2995 20.289 19.4459 20.26 19.5825 20.2035C19.7191 20.147 19.8432 20.0641 19.9477 19.9595C20.0523 19.855 20.1351 19.7309 20.1916 19.5943C20.2482 19.4577 20.2772 19.3113 20.277 19.1635C20.2769 19.0157 20.2476 18.8694 20.1909 18.7329C20.1341 18.5964 20.051 18.4724 19.9463 18.3681L18.8855 17.3073Z"
                                                         fill="currentColor"></path>
                                                     <path
                                                         d="M5.09528 17.3072L4.0345 18.368C3.92972 18.4723 3.84655 18.5963 3.78974 18.7328C3.73294 18.8693 3.70362 19.0156 3.70346 19.1635C3.7033 19.3114 3.7323 19.4578 3.78881 19.5944C3.84532 19.7311 3.92822 19.8552 4.03277 19.9598C4.13732 20.0643 4.26147 20.1472 4.3981 20.2037C4.53473 20.2602 4.68117 20.2892 4.82902 20.2891C4.97688 20.2889 5.12325 20.2596 5.25976 20.2028C5.39627 20.146 5.52024 20.0628 5.62456 19.958L6.68536 18.8973C6.79007 18.7929 6.87318 18.6689 6.92993 18.5325C6.98667 18.396 7.01595 18.2496 7.01608 18.1018C7.01621 17.954 6.98719 17.8076 6.93068 17.671C6.87417 17.5344 6.79129 17.4103 6.68676 17.3058C6.58224 17.2012 6.45813 17.1183 6.32153 17.0618C6.18494 17.0053 6.03855 16.9763 5.89073 16.9764C5.74291 16.9766 5.59657 17.0058 5.46007 17.0626C5.32358 17.1193 5.19962 17.2024 5.09528 17.3072Z"
                                                         fill="currentColor"></path>
                                                     <path
                                                         d="M5.09541 6.69715C5.19979 6.8017 5.32374 6.88466 5.4602 6.94128C5.59665 6.9979 5.74292 7.02708 5.89065 7.02714C6.03839 7.0272 6.18469 6.99815 6.32119 6.94164C6.45769 6.88514 6.58171 6.80228 6.68618 6.69782C6.79064 6.59336 6.87349 6.46933 6.93 6.33283C6.9865 6.19633 7.01556 6.05003 7.01549 5.9023C7.01543 5.75457 6.98625 5.60829 6.92963 5.47184C6.87301 5.33539 6.79005 5.21143 6.6855 5.10706L5.6247 4.04626C5.5204 3.94137 5.39643 3.8581 5.25989 3.80121C5.12335 3.74432 4.97692 3.71493 4.82901 3.71472C4.68109 3.71452 4.53458 3.7435 4.39789 3.80001C4.26119 3.85652 4.13699 3.93945 4.03239 4.04404C3.9278 4.14864 3.84487 4.27284 3.78836 4.40954C3.73185 4.54624 3.70287 4.69274 3.70308 4.84066C3.70329 4.98858 3.73268 5.135 3.78957 5.27154C3.84646 5.40808 3.92974 5.53205 4.03462 5.63635L5.09541 6.69715Z"
                                                         fill="currentColor"></path>
                                                 </svg>
                                                 <span class="ms-3 mb-0">Light</span>
                                             </label>
                                         </div>
                                     </li>
                                     <li data-setting="radio">
                                         <div class="dropdown-item d-flex align-items-center">
                                             <input type="radio" value="dark" class="btn-check"
                                                 name="theme_scheme" id="color-mode-dark">
                                             <label class="d-block" for="color-mode-dark">
                                                 <svg class="icon-24" width="24" height="24"
                                                     viewBox="0 0 24 24" fill="none"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                     <path
                                                         d="M19.0647 5.43757C19.3421 5.43757 19.567 5.21271 19.567 4.93534C19.567 4.65796 19.3421 4.43311 19.0647 4.43311C18.7874 4.43311 18.5625 4.65796 18.5625 4.93534C18.5625 5.21271 18.7874 5.43757 19.0647 5.43757Z"
                                                         fill="currentColor"></path>
                                                     <path
                                                         d="M20.0692 9.48884C20.3466 9.48884 20.5714 9.26398 20.5714 8.98661C20.5714 8.70923 20.3466 8.48438 20.0692 8.48438C19.7918 8.48438 19.567 8.70923 19.567 8.98661C19.567 9.26398 19.7918 9.48884 20.0692 9.48884Z"
                                                         fill="currentColor"></path>
                                                     <path
                                                         d="M12.0335 20.5714C15.6943 20.5714 18.9426 18.2053 20.1168 14.7338C20.1884 14.5225 20.1114 14.289 19.9284 14.161C19.746 14.034 19.5003 14.0418 19.3257 14.1821C18.2432 15.0546 16.9371 15.5156 15.5491 15.5156C12.2257 15.5156 9.48884 12.8122 9.48884 9.48886C9.48884 7.41079 10.5773 5.47137 12.3449 4.35752C12.5342 4.23832 12.6 4.00733 12.5377 3.79251C12.4759 3.57768 12.2571 3.42859 12.0335 3.42859C7.32556 3.42859 3.42857 7.29209 3.42857 12C3.42857 16.7079 7.32556 20.5714 12.0335 20.5714Z"
                                                         fill="currentColor"></path>
                                                     <path
                                                         d="M13.0379 7.47998C13.8688 7.47998 14.5446 8.15585 14.5446 8.98668C14.5446 9.26428 14.7693 9.48891 15.0469 9.48891C15.3245 9.48891 15.5491 9.26428 15.5491 8.98668C15.5491 8.15585 16.225 7.47998 17.0558 7.47998C17.3334 7.47998 17.558 7.25535 17.558 6.97775C17.558 6.70015 17.3334 6.47552 17.0558 6.47552C16.225 6.47552 15.5491 5.76616 15.5491 4.93534C15.5491 4.65774 15.3245 4.43311 15.0469 4.43311C14.7693 4.43311 14.5446 4.65774 14.5446 4.93534C14.5446 5.76616 13.8688 6.47552 13.0379 6.47552C12.7603 6.47552 12.5357 6.70015 12.5357 6.97775C12.5357 7.25535 12.7603 7.47998 13.0379 7.47998Z"
                                                         fill="currentColor"></path>
                                                 </svg>
                                                 <span class="ms-3 mb-0">Dark</span>
                                             </label>
                                         </div>
                                     </li>
                                     <li data-setting="radio">
                                         <div class="dropdown-item d-flex align-items-center">
                                             <input type="radio" value="auto" class="btn-check"
                                                 name="theme_scheme" id="color-mode-auto" checked="">
                                             <label class="d-block" for="color-mode-auto">
                                                 <svg class="icon-24" width="24" height="24"
                                                     viewBox="0 0 24 24" fill="none"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                     <path fill-rule="evenodd" clip-rule="evenodd"
                                                         d="M1.34375 3.9463V15.2178C1.34375 16.119 2.08105 16.8563 2.98219 16.8563H8.65093V19.4594H6.15702C5.38853 19.4594 4.75981 19.9617 4.75981 20.5757V21.6921H19.2403V20.5757C19.2403 19.9617 18.6116 19.4594 17.8431 19.4594H15.3492V16.8563H21.0179C21.919 16.8563 22.6562 16.119 22.6562 15.2178V3.9463C22.6562 3.04516 21.9189 2.30786 21.0179 2.30786H2.98219C2.08105 2.30786 1.34375 3.04516 1.34375 3.9463ZM12.9034 9.9016C13.241 9.98792 13.5597 10.1216 13.852 10.2949L15.0393 9.4353L15.9893 10.3853L15.1297 11.5727C15.303 11.865 15.4366 12.1837 15.523 12.5212L16.97 12.7528V13.4089H13.9851C13.9766 12.3198 13.0912 11.4394 12 11.4394C10.9089 11.4394 10.0235 12.3198 10.015 13.4089H7.03006V12.7528L8.47712 12.5211C8.56345 12.1836 8.69703 11.8649 8.87037 11.5727L8.0107 10.3853L8.96078 9.4353L10.148 10.2949C10.4404 10.1215 10.759 9.98788 11.0966 9.9016L11.3282 8.45467H12.6718L12.9034 9.9016ZM16.1353 7.93758C15.6779 7.93758 15.3071 7.56681 15.3071 7.1094C15.3071 6.652 15.6779 6.28122 16.1353 6.28122C16.5926 6.28122 16.9634 6.652 16.9634 7.1094C16.9634 7.56681 16.5926 7.93758 16.1353 7.93758ZM2.71385 14.0964V3.90518C2.71385 3.78023 2.81612 3.67796 2.94107 3.67796H21.0589C21.1839 3.67796 21.2861 3.78023 21.2861 3.90518V14.0964C15.0954 14.0964 8.90462 14.0964 2.71385 14.0964Z"
                                                         fill="currentColor"></path>
                                                 </svg>
                                                 <span class="ms-3 mb-0">Auto</span>
                                             </label>
                                         </div>
                                     </li>
                                 </ul>
                             </li>
                             <li class="nav-item dropdown">
                                 <a class="py-0 nav-link d-flex align-items-center ps-3" href="#"
                                     id="profile-setting" role="button" data-bs-toggle="dropdown"
                                     aria-expanded="false">
                                     <img src="/backend/images/01.png" alt="User-Profile"
                                         class="theme-color-default-img img-fluid avatar avatar-50 avatar-rounded"
                                         loading="lazy">
                                     <img src="/backend/images/avtar_1.png" alt="User-Profile"
                                         class="theme-color-purple-img img-fluid avatar avatar-50 avatar-rounded"
                                         loading="lazy">
                                     <img src="/backend/images/avtar_2.png" alt="User-Profile"
                                         class="theme-color-blue-img img-fluid avatar avatar-50 avatar-rounded"
                                         loading="lazy">
                                     <img src="/backend/images/avtar_3.png" alt="User-Profile"
                                         class="theme-color-green-img img-fluid avatar avatar-50 avatar-rounded"
                                         loading="lazy">
                                     <img src="/backend/images/avtar_4.png" alt="User-Profile"
                                         class="theme-color-yellow-img img-fluid avatar avatar-50 avatar-rounded"
                                         loading="lazy">
                                     <img src="/backend/images/avtar_5.png" alt="User-Profile"
                                         class="theme-color-pink-img img-fluid avatar avatar-50 avatar-rounded"
                                         loading="lazy">
                                     <div class="caption ms-3 d-none d-md-block ">
                                         <h6 class="mb-0 caption-title">{{ session('supplier')->name }}</h6>
                                         <p class="mb-0 caption-sub-title"{{ session('supplier')->number }}></p>
                                     </div>
                                 </a>
                                 <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profile-setting">
                                     <li><a class="dropdown-item" href="/supplier/profile">Profile</a></li>

                                     <li>
                                         <hr class="dropdown-divider">
                                     </li>
                                     <li><a class="dropdown-item" href="/supplier/logout">Logout</a>
                                     </li>
                                 </ul>
                             </li>
                         </ul>
                     </div>
                 </div>
             </nav>
             <!--Nav End-->
         </div>
         <div class="content-inner container-fluid pb-0" id="page_layout">
