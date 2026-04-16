<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Authentication;
use App\Http\Controllers\BulkImport;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChatGptController;
use App\Http\Controllers\CommonAjax;
use App\Http\Controllers\Customer;
use App\Http\Controllers\CustomerFrontendController;
use App\Http\Controllers\FrontEnd;
use App\Http\Controllers\Masters;
use App\Http\Controllers\OutwardManagement;
use App\Http\Controllers\PurchaseManagement;
use App\Http\Controllers\StaffManagement;
use App\Http\Controllers\CustomerProductPrice;
use App\Http\Controllers\OutWardController;
use App\Http\Controllers\DispatchController;
use App\Http\Controllers\eInvoice\EInvoiceController;
use App\Http\Controllers\EstimateController;
use App\Http\Controllers\exports\excelExport;
use App\Http\Controllers\PoController;
use App\Http\Controllers\Supplier;
use App\Http\Controllers\uploadProductsGDrive;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WebsiteManagement;
use App\Http\Middleware\CheckAdmin;
use App\Http\Middleware\CustomerFrontend;
use App\Http\Middleware\Customers;
use App\Http\Middleware\staffAuth;
use App\Http\Middleware\Suppliers;

use Illuminate\Support\Facades\Route;



// Route::get('/home', [FrontEnd::class, 'Shop'])->name('home');
// Route::get('/', [FrontEnd::class, 'Index'])->name('/');

// Route::post('/chat-gpt', [ChatGptController::class, 'chatGpt'])->name('chatGpt');
Route::get('apiProducts', [CustomerFrontendController::class, 'apiProducts'])->name('apiProducts');

Route::get('/s1', [Authentication::class, 'SuperAdmin'])->name('s1');
Route::post('/s1', [Authentication::class, 'SuperAdminLogin'])->name('SuperAdminLogin');
Route::get('/', [Authentication::class, 'SupplierLogin'])->name('supplier');
Route::post('/SaveSupplierLogin', [Authentication::class, 'SaveSupplierLogin'])->name('SaveSupplierLogin');

Route::get('/customer', [Authentication::class, 'CustomerLogin'])->name('customer');
Route::post('/SaveCustomerLogin', [Authentication::class, 'SaveCustomerLogin'])->name('SaveCustomerLogin');

Route::post('/customerLoginWebsite', [Authentication::class, 'customerLoginWebsite'])->name('customerLoginWebsite');

Route::get('/shop', [FrontEnd::class, 'Shop'])->name('shop');
Route::get('/product-details/{id}', [FrontEnd::class, 'ProductDetails'])->name('product-details/{id}');
Route::post('/GetCity', [CommonAjax::class, 'GetCity'])->name('/GetCity');

Route::get('sign-up', [CustomerFrontendController::class, 'SignUp'])->name('sign-up');
Route::post('SaveCustomer', [CustomerFrontendController::class, 'SaveCustomer'])->name('SaveCustomer');

Route::get('supplier-staff', [Authentication::class, 'StaffLogin'])->name('supplier-staff');
Route::post('/SaveStaffLogin', [Authentication::class, 'SaveStaffLogin'])->name('SaveStaffLogin');
Route::get('apiProducts', [CustomerFrontendController::class, 'apiProducts'])->name('apiProducts');


Route::middleware([CheckAdmin::class])->group(function () {
    Route::get('s1/logout', [Authentication::class, 'logout'])->name('s1/Logout');
    //admin routes
    Route::get('s1/dashboard', [Admin::class, 'Dashboard'])->name('s1/dashboard');
    Route::get('s1/customers', [Masters::class, 'Customers'])->name('s1/customer');
    Route::get('s1/suppliers', [Masters::class, 'Suppliers'])->name('s1/suppliers');
    Route::get('s1/supplier-users/{id}', [Masters::class, 'SupplierUsers'])->name('s1/supplier-users');
    Route::post('s1/SaveSuppliers', [Masters::class, 'SaveSuppliers'])->name('s1/SaveSuppliers');

    // website management

    Route::get('s1/sliders', [WebsiteManagement::class, 'Sliders'])->name('s1/sliders');
    Route::post('s1/SaveSlider', [WebsiteManagement::class, 'SaveSlider'])->name('s1/SaveSlider');

    //masters 
    Route::get('s1/documents', [Masters::class, 'Documents'])->name('s1/documents');
    Route::post('s1/SaveDocuments', [Masters::class, 'SaveDocuments'])->name('s1/SaveDocuments');

    Route::get('s1/sliders1', [WebsiteManagement::class, 'Sliders1'])->name('s1/sliders1');
    Route::post('s1/SaveSlider1', [WebsiteManagement::class, 'SaveSlider1'])->name('s1/SaveSlider1');

    Route::get('s1/app-slider', [WebsiteManagement::class, 'AppSlider'])->name('s1/AppSlider');
    Route::post('s1/SaveAppSlider', [WebsiteManagement::class, 'SaveAppSlider'])->name('s1/SaveAppSlider');

    Route::get('s1/app-hero-slider', [WebsiteManagement::class, 'AppHeroSlider'])->name('s1/AppHeroSlider');
    Route::post('s1/SaveHeroSlider', [WebsiteManagement::class, 'SaveHeroSlider'])->name('s1/SaveHeroSlider');


    Route::get('s1/sliders2', [WebsiteManagement::class, 'Sliders2'])->name('s1/sliders2');
    Route::post('s1/SaveSlider2', [WebsiteManagement::class, 'SaveSlider2'])->name('s1/SaveSlider2');

    Route::get('s1/email-temp-list', [WebsiteManagement::class, 'emailTempList'])->name('s1/email-temp-list');
    Route::get('s1/edit-email-temp/{id}', [WebsiteManagement::class, 'editEmailTemp'])->name('s1/editEmailTemp');
    Route::post('s1/SaveEmailTemplate/{id}', [WebsiteManagement::class, 'SaveEmailTemplate'])->name('s1/SaveEmailTemplate');


    Route::get('s1/sliders3', [WebsiteManagement::class, 'Sliders3'])->name('s1/sliders3');
    Route::post('s1/SaveSlider3', [WebsiteManagement::class, 'SaveSlider3'])->name('s1/SaveSlider3');
    Route::get('s1/sliders4', [WebsiteManagement::class, 'Sliders4'])->name('s1/sliders4');
    Route::post('s1/SaveSlider4', [WebsiteManagement::class, 'SaveSlider4'])->name('s1/SaveSlider4');

    Route::get('s1/faq-category-list', [WebsiteManagement::class, 'faqCategory'])->name('s1/faqCategory');
    Route::post('s1/faqSaveCategory', [WebsiteManagement::class, 'faqSaveCategory'])->name('s1/faqSaveCategory');

    Route::get('s1/faq-main-list', [WebsiteManagement::class, 'faqMainList'])->name('s1/faqMainList');
    Route::post('s1/faqSaveMain', [WebsiteManagement::class, 'faqSaveMain'])->name('s1/faqSaveMain');
    Route::post('s1/faqDeleteMain', [WebsiteManagement::class, 'faqDeleteMain'])->name('s1/faqDeleteMain');

    Route::get('s1/quality-list', [WebsiteManagement::class, 'qulityMainList'])->name('s1/qulityMainList');
    Route::post('s1/qulitySaveMain', [WebsiteManagement::class, 'qulitySaveMain'])->name('s1/qulitySaveMain');

    Route::get('s1/refund-list', [WebsiteManagement::class, 'refundList'])->name('s1/refundList');
    Route::post('s1/refundSaveMain/{id}', [WebsiteManagement::class, 'refundSaveMain'])
        ->name('s1/refundSaveMain');

    Route::get('s1/term-list', [WebsiteManagement::class, 'termList'])->name('s1/termList');
    Route::post('s1/termSaveMain/{id}', [WebsiteManagement::class, 'termSaveMain'])
        ->name('s1/termSaveMain');

    Route::get('s1/privacy-list', [WebsiteManagement::class, 'privacyList'])->name('s1/privacyList');
    Route::post('s1/privacySaveMain/{id}', [WebsiteManagement::class, 'privacySaveMain'])
        ->name('s1/privacySaveMain');

    Route::get('s1/order-cancellation', [WebsiteManagement::class, 'orderCancellation'])->name('s1/orderCancellation');
    Route::post('s1/orderCancellationSaveMain/{id}', [WebsiteManagement::class, 'orderCancellationSaveMain'])
        ->name('s1/orderCancellationSaveMain');

    Route::get('s1/order-delivery-list', [WebsiteManagement::class, 'orderDelivery'])->name('s1/orderDelivery');
    Route::post('s1/orderDeliverySaveMain/{id}', [WebsiteManagement::class, 'orderDeliverySaveMain'])
        ->name('s1/orderDeliverySaveMain');

    Route::get('s1/order-return', [WebsiteManagement::class, 'orderReturn'])->name('s1/orderReturn');
    Route::post('s1/orderReturnSaveMain/{id}', [WebsiteManagement::class, 'orderReturnSaveMain'])
        ->name('s1/orderReturnSaveMain');
});

Route::middleware([Suppliers::class])->group(function () {
    Route::get('supplier/logout', [Supplier::class, 'logout'])->name('supplier/Logout');
    Route::get('supplier/profile', [Supplier::class, 'Profile'])->name('supplier/profile');
    Route::post('supplier/UpdateProfile', [Supplier::class, 'UpdateProfile'])->name('supplier/UpdateProfile');
    //admin routes
    Route::get('supplier/dashboard', [Supplier::class, 'Dashboard'])->name('supplier/dashboard');
    Route::get('supplier/customers/{id}', [Supplier::class, 'Customers'])->name('supplier/customer');
    Route::post('Supplier/SaveCustomer', [Supplier::class, 'SaveCustomer'])->name('Supplier/SaveCustomer');

    // master routes
    Route::get('supplier/product-category', [Masters::class, 'ProductCategory'])->name('supplier/product-category');
    Route::post('supplier/SaveProductCategory', [Masters::class, 'SaveProductCategory'])->name('supplier/SaveProductCategory');

    // master supplier Expense
    Route::get('supplier/expense-category', [Masters::class, 'expenseCategory'])->name('supplier/expense-category');
    Route::post('supplier/expenseSaveCategory', [Masters::class, 'expenseSaveCategory'])->name('supplier/expenseSaveCategory');

    Route::get('supplier/expense-list', [Masters::class, 'expenseList'])->name('supplier/expense-list');
    Route::post('supplier/expenseSave', [Masters::class, 'expenseSave'])->name('supplier/expenseSave');


    Route::get('supplier/expense-subcategory', [Masters::class, 'expenseSubCategory'])->name('supplier/product-expenseSubCategory');
    Route::post('supplier/expenseSaveSubCategory', [Masters::class, 'expenseSaveSubCategory'])->name('supplier/expenseSaveSubCategory');

    Route::get('supplier/product-sub-category', [Masters::class, 'ProductSubCategory'])->name('supplier/product-sub-category');
    Route::post('supplier/SaveProductSubCategory', [Masters::class, 'SaveProductSubCategory'])->name('supplier/SaveProductSubCategory');

    Route::get('supplier/product-sub-sub-category', [Masters::class, 'ProductSubSubCategory'])->name('supplier/product-sub-sub-category');
    Route::post('supplier/SaveProductSubSubCategory', [Masters::class, 'SaveProductSubSubCategory'])->name('supplier/SaveProductSubSubCategory');

    Route::get('supplier/product-brand', [Masters::class, 'ProductBrand'])->name('supplier/product-brand');
    Route::post('supplier/SaveProductBrand', [Masters::class, 'SaveProductBrand'])->name('supplier/SaveProductBrand');
    Route::post('supplier/deleteProductBrand', [Masters::class, 'deleteProductBrand'])->name('supplier/deleteProductBrand');
    Route::post('supplier/GetProductCategory', [Masters::class, 'GetProductCategory'])->name('supplier/GetProductCategory');
    Route::post('supplier/GetProductSubCategory', [Masters::class, 'GetProductSubCategory'])->name('supplier/GetProductSubCategory');
    Route::post('supplier/GetProductSubSubCategory', [Masters::class, 'GetProductSubSubCategory'])->name('supplier/GetProductSubSubCategory');
    Route::get('supplier/product-type', [Masters::class, 'ProductType'])->name('supplier/product-type');
    Route::post('supplier/SaveProductType', [Masters::class, 'SaveProductType'])->name('supplier/SaveProductType');

    Route::get('supplier/product-uom', [Masters::class, 'ProductUOM'])->name('supplier/product-uom');
    Route::post('supplier/SaveProductUOM', [Masters::class, 'SaveProductUOM'])->name('supplier/SaveProductUOM');

    Route::get('supplier/product-gst', [Masters::class, 'ProductGST'])->name('supplier/product-gst');
    Route::post('supplier/SaveProductGST', [Masters::class, 'SaveProductGST'])->name('supplier/SaveProductGST');

    Route::get('supplier/products', [Masters::class, 'Products'])->name('supplier/products');
    Route::post('supplier/SaveProducts', [Masters::class, 'SaveProducts'])->name('supplier/SaveProducts');
    Route::post('supplier/uploadMultipleImages', [Masters::class, 'uploadMultipleImages'])->name('supplier/uploadMultipleImages');
    Route::get('supplier/add-tags', [Masters::class, 'generateMissingTagsBatch'])->name('supplier/add-tags');
    Route::post('/supplier/update-base-price', [Masters::class, 'UpdateBasePrice']);
    Route::get('/supplier/search-product', [Masters::class, 'searchProduct'])->name('supplier.searchProduct');

    // Product Master Ajax 
    Route::post('/supplier/save-product-category-ajax', [Masters::class, 'SaveProductCategoryAjax'])->name('supplier.SaveProductCategory');
    Route::get('/supplier/product-category-ajax', [Masters::class, 'getCategories'])->name('supplier.getCategories');
    Route::post('/supplier/SaveProductBrand-ajax', [Masters::class, 'SaveProductBrandAjax'])->name('supplier/SaveProductBrand-ajax');
    Route::get('/supplier/product-brand-ajax', [Masters::class, 'getBrandsAjax'])->name('supplier.getBrands-ajax');
    Route::post('/supplier/SaveProductSubCategory-ajax', [Masters::class, 'SaveProductSubCategoryAjax'])->name('supplier/SaveProductSubCategoryAjax');
    Route::get('/supplier/getSubCategoriesAjax', [Masters::class, 'getSubCategoriesAjax'])->name('supplier.getSubCategoriesAjax');

    Route::post('/supplier/SaveProductSubSubCategoryAjax', [Masters::class, 'SaveProductSubSubCategoryAjax'])->name('supplier/SaveProductSubSubCategoryAjax');
    Route::get('/supplier/getSubCategoriesByCategoryAjax', [Masters::class, 'getSubCategoriesByCategoryAjax'])->name('supplier/getSubCategoriesByCategoryAjax');
    Route::get('/supplier/getSubSubCategoriesAjax', [Masters::class, 'getSubSubCategoriesAjax'])->name('supplier/getSubSubCategoriesAjax');


    Route::get('supplier/customer-profile/{id}', [Supplier::class, 'CustomerProfile'])->name('supplier/customer-profile');
    Route::post('supplier/UpdateCompanyDetails', [Supplier::class, 'UpdateCompanyDetails'])->name('supplier/UpdateCompanyDetails');
    Route::post('supplier/UpdatePersonalDetails', [Supplier::class, 'UpdatePersonalDetails'])->name('supplier/UpdatePersonalDetails');
    Route::post('supplier/UploadDocument', [Supplier::class, 'UploadDocument'])->name('supplier/UploadDocument');
    Route::post('supplier/UploadAgreement', [Supplier::class, 'UploadAgreement'])->name('supplier/UploadAgreement');
    Route::post('supplier/UploadWallet', [Supplier::class, 'UploadWallet'])->name('supplier/UploadWallet');
    Route::get('supplier/get-wallet-history/{id}', [Supplier::class, 'getWalletHistory']);
    Route::post('supplier/GetProductPrices', [Supplier::class, 'GetProductPrices'])->name('supplier/GetProductPrices');
    Route::post('supplier/DeleteProductPrice', [Supplier::class, 'DeleteProductPrice'])->name('supplier/DeleteProductPrice');
    Route::post('supplier/AddProductPrice', [Supplier::class, 'AddProductPrice'])->name('supplier/AddProductPrice');
    Route::post('supplier/getMultipleImages', [Supplier::class, 'getMultipleImages'])->name('supplier/getMultipleImages');
    Route::post('supplier/deleteImage', [Supplier::class, 'deleteImage'])->name('supplier/deleteImage');

    // Order Estimate
    Route::get('supplier/create-estimate', [EstimateController::class, 'createEstimate'])->name('supplier/create-estimate');
    Route::get('customer-address/{id}', [EstimateController::class, 'getCustomerAddress']);
    Route::post('supplier/saveEstimate', [EstimateController::class, 'saveEstimate'])->name('supplier/saveEstimate');



    Route::get('supplier/orders-estimate/{status}', [Supplier::class, 'OrdersEstimate'])->name('supplier/orders-estimate');
    Route::get('supplier/order-estimate-details/{id}', [Supplier::class, 'OrderDetailsEstimate'])->name('supplier/order-estimate-details');
    Route::get('supplier/order-estimate-request-price/{id}', [Supplier::class, 'OrderEstimateRequestPrice'])->name('supplier/order-estimate-request-price');
    Route::get('supplier/order-estimate-edit/{id}', [Supplier::class, 'OrderEstimateEdit'])->name('supplier/order-estimate-edit');
    Route::get('supplier/get-products', [Supplier::class, 'getProducts'])->name('supplier/get-products');
    Route::get('supplier/get-product-details/{id}', [Supplier::class, 'getProductDetails'])->name('supplier/get-product-details');
    Route::post('supplier/orders-save/', [Supplier::class, 'OrdersSave'])->name('supplier/ordersSave');
    Route::post('supplier/EditEstimateOrder', [Supplier::class, 'EditEstimateOrder'])->name('supplier/EditEstimateOrder');



    // order management
    Route::get('supplier/orders/{status}', [Supplier::class, 'Orders'])->name('supplier/orderss');
    Route::get('supplier/order-details/{id}', [Supplier::class, 'OrderDetails'])->name('supplier/order-details');
    Route::get('supplier/orders', [Supplier::class, 'OrdersManagement'])->name('supplier/orders');




    //bulk import
    Route::post('supplier/ImportProducts', [BulkImport::class, 'ImportProducts'])->name('supplier/ImportProducts');
    Route::post('supplier/UpdateOrderStatus', [Supplier::class, 'UpdateOrderStatus'])->name('supplier/UpdateOrderStatus');
    Route::post('supplier/AddWalletLedger', [Supplier::class, 'AddWalletLedger'])->name('supplier/AddWalletLedger');
    Route::post('supplier/AddExtraCharge', [Supplier::class, 'AddExtraCharge'])->name('supplier/AddExtraCharge');
    Route::get('supplier/wallet-management', [Supplier::class, 'WalletManagement'])->name('supplier/wallet-management');
    Route::post('supplier/importGDriveProducts', [uploadProductsGDrive::class, 'importGDriveProducts'])->name('supplier/importGDriveProducts');

    //user management
    Route::get("supplier/user-role", [Supplier::class, "UserRole"])->name("supplier/user-role");
    Route::post("supplier/saveUserRole", [Supplier::class, "saveUserRole"])->name("supplier/saveUserRole");



    Route::get("supplier/users", [Supplier::class, "users"])->name("supplier/users");
    Route::post("supplier/updateSupplierUser", [Supplier::class, "updateSupplierUser"])->name("supplier/updateSupplierUser");

    //ajax
    Route::get('supplier/getProduct', [Supplier::class, 'getProduct'])->name('supplier.getProduct');
    Route::get('/supplier/request-list', [Supplier::class, 'requestListProduct'])->name('/supplier/request-list');
    Route::post('supplier/UpdateProductRequestStatus', [Supplier::class, 'UpdateProductRequestStatus'])
        ->name('supplier/UpdateProductRequestStatus');

    Route::post('supplier/UpdateProductStatus', [Masters::class, 'UpdateProductStatus'])->name('supplier/UpdateProductStatus');
    Route::post('supplier/UpdateProductIsdeal', [Masters::class, 'UpdateProductIsdeal'])->name('supplier/UpdateProductIsdeal');
    Route::post('supplier/UpdateProductIsHome', [Masters::class, 'UpdateProductIsHome'])->name('supplier/UpdateProductIsHome');
    Route::post('supplier/UpdateProductDiscount', [Masters::class, 'UpdateProductDiscount'])->name('supplier/UpdateProductDiscount');

    Route::get('supplier/help-support', [Supplier::class, 'helpSupport'])->name('supplier/help-support');
    Route::post('supplier/chat.markSeen', [Supplier::class, 'markAsSeen'])->name('supplier/chat.markSeen');

    Route::get('/chat/{customerId}', [ChatController::class, 'getMessages']);
    Route::post('/chat/send', [ChatController::class, 'sendMessage']);


    // WareHouse ManageMent System

    Route::get('supplier/wareHouseZone', [WarehouseController::class, 'wareHouseZone'])->name('supplier/wareHouseZone');
    Route::post('supplier/updateWareHouseZone', [WarehouseController::class, 'updateWareHouseZone'])->name('supplier/updateWareHouseZone');

    Route::get('supplier/warehouse', [WarehouseController::class, 'warehouseList'])->name('supplier/warehouseList');
    Route::post('supplier/saveWareHouse', [WarehouseController::class, 'saveWareHouse'])->name('supplier/saveWareHouse');
    Route::get('supplier/warehouse-location/{id}', [WarehouseController::class, 'warehouseLocation'])->name('supplier/warehouseLocation');
    Route::post('supplier/saveWareHouseLocation', [WarehouseController::class, 'saveWareHouseLocation'])->name('supplier/saveWareHouseLocation');
    Route::post('supplier/importWareHouseLocation', [WarehouseController::class, 'importWareHouseLocation'])->name('supplier/importWareHouseLocation');
    Route::post('supplier/wareHouseProductLocation', [WarehouseController::class, 'wareHouseProductLocation'])->name('supplier/wareHouseProductLocation');
    Route::get('supplier/get-allocated-products', [WarehouseController::class, 'getAllocatedProducts']);
    Route::get('supplier/warehouse-product-pending/{id}', [WarehouseController::class, 'warehouseLocationPending'])->name('supplier/warehouseLocationPending');
    Route::post('/get-zones', [Supplier::class, 'getZonesByWarehouse']);
    Route::post('/get-locations', [Supplier::class, 'getLocationByZone']);
    Route::post('/save-allocation', [Supplier::class, 'saveProductWarehouseAllocation']);
    Route::get('/get-product-allocation', [Masters::class, 'getProductAllocation']);
    Route::post('/remove-allocation', [Supplier::class, 'removeAllocation']);
    Route::get('supplier/import-progress', function () {
        return response()->json([
            'percent' => session('import_progress', 0)
        ]);
    })->name('supplier/importProgress');

    // Customer Product Price
    Route::get('product-search', [CustomerProductPrice::class, 'customerProductSearch']);
    Route::get('supplier/customer-product-price/{id}', [CustomerProductPrice::class, 'customerProductAdd'])->name('supplier/customer-product-price');
    Route::post('supplier/customer-product-save',  [CustomerProductPrice::class, 'store'])->name('customer-product-save');
    Route::get('supplier/customer-product-list/{id}', [CustomerProductPrice::class, 'customerProductList'])->name('supplier/customer-product-list');
    Route::post('supplier/GetCustomerProductPrices', [CustomerProductPrice::class, 'GetCustomerProductPrices'])->name('supplier/GetCustomerProductPrices');
    Route::post('supplier/DeleteCustomerProductPrice', [CustomerProductPrice::class, 'DeleteCustomerProductPrice'])->name('supplier/DeleteCustomerProductPrice');
    Route::post('supplier/AddCustomerProductPrice', [CustomerProductPrice::class, 'AddCustomerProductPrice'])->name('supplier/AddCustomerProductPrice');

    // Route::get('supplier/create-orders-estimate', [Supplier::class, 'createOrderEstimate'])->name('supplier/create-orders-estimate');
    // Route::post('supplier/create-orders-estimate', [Supplier::class, 'getCustomerProducts'])->name('supplier/create-orders-estimate');
    Route::post('supplier/getOrderProducts', [CustomerProductPrice::class, 'getOrderProducts'])->name('supplier/getOrderProducts');
    Route::post('supplier/getProductPrice', [CustomerProductPrice::class, 'getProductPrice'])->name('supplier/getProductPrice');
    Route::post('supplier/getProductQtyWisePrice', [CustomerProductPrice::class, 'getProductQtyWisePrice'])->name('supplier/getProductQtyWisePrice');



    // Vendor Management
    Route::get("supplier/vendor", [Supplier::class, "vendorList"])->name("supplier/vendorList");
    Route::post("supplier/saveVendor", [Supplier::class, "saveVendor"])->name("supplier/saveVendor");
    Route::post("supplier/saveVendorAjax", [Supplier::class, "saveVendorAjax"])->name("supplier/saveVendorAjax");
    Route::get("supplier/vendor-ajax", [Supplier::class, "getVendorsAjax"])->name("supplier/vendor-ajax");
    Route::get("supplier/vendor-product-list/{id}", [Supplier::class, "vendorProductList"])->name("supplier/vendorProductList");
    Route::post("supplier/AllocateProduct", [Supplier::class, "AllocateProduct"])->name("supplier/AllocateProduct");

    Route::get("supplier/get-vendor-product-allocation", [Supplier::class, "GetVendorAllocation"]);
    Route::post("supplier/save-vendor-allocation", [Supplier::class, "SaveVendorAllocation"]);
    Route::post("supplier/remove-vendor-allocation", [Supplier::class, "RemoveVendorAllocation"]);

    // PO Management
    Route::get("supplier/generate-po", [PoController::class, "GeneratePo"])->name("supplier/GeneratePo");
    Route::post("supplier/savePo", [PoController::class, "savePo"])->name("supplier/savePo");
    Route::post('GetVendorProducts', [PoController::class, 'GetVendorProducts'])->name('GetVendorProducts');
    Route::get('supplier/purchase-order-view/{id}', [PoController::class, 'purchaseOrderView'])->name('purchase-order-view');
    Route::get("supplier/purchase-order/{status}", [PoController::class, "purchaseOrder"])->name("supplier/purchaseOrder");
    Route::post('supplier/saveGeneratePO', [PoController::class, 'saveGeneratePO'])->name('supplier/saveGeneratePO');
    Route::post('supplier/deletePO', [PoController::class, 'deletePO'])->name('supplier/deletePO');
    Route::post('UploadPORequirementList', [PoController::class, 'UploadPORequirementList'])->name('UploadPORequirementList');
    Route::post('UploadNewPORequirementList', [PoController::class, 'UploadNewPORequirementList'])->name('UploadNewPORequirementList');
    Route::get('supplier/inward-stock/{id?}', [PoController::class, 'InwardStock'])->name('supplier/inward-stock');
    Route::post('supplier/SaveInwardStock', [PoController::class, 'SaveInwardStock'])->name('supplier/SaveInwardStock');
    Route::post('supplier/approveStockInward', [PoController::class, 'approveStockInward'])->name('supplier/approveStockInward');
    Route::get('supplier/inward-report', [PoController::class, 'inWardReport'])->name('supplier/inward-report');
    Route::get('supplier/inward-report-view/{id}', [PoController::class, 'InwardReportView'])->name('supplier/inward-report-view');
    Route::get('supplier/inward-report-slip/{id}', [PoController::class, 'InwardReportSlip'])->name('supplier/inward-report-slip');
    Route::post('supplier/deleteStockInward', [PoController::class, 'deleteStockInward'])->name('supplier/deleteStockInward');
    Route::get('supplier/purchase-return', [PoController::class, 'PurchaseReturnList'])->name('supplier/purchase-return');
    Route::post('supplier/GetInwardChallan', [PoController::class, 'GetInwardChallan'])->name('supplier/GetInwardChallan');
    Route::post('supplier/GetInwardChallanProducts', [PoController::class, 'GetInwardChallanProducts'])->name('supplier/GetInwardChallanProducts');
    Route::post('supplier/SavePurchaseReturn', [PoController::class, 'SavePurchaseReturn'])->name('supplier/SavePurchaseReturn');
    Route::get('purchase-return-challan-view/{id}', [PoController::class, 'PurchaseReturnChallanView'])->name('purchase-return-challan-view');
    Route::get('supplier/inward-product-wise', [PoController::class, 'inwardProductWise'])->name('supplier/inward-product-wise');
    Route::get('supplier/current-stock', [PoController::class, 'CurrentStock'])->name('supplier/current-stock');
    //  Route::get('supplier/current-stock-history/{id}', [PoController::class, 'CurrentStockHistory'])->name('supplier/current-stock-history');
    Route::post('getLocation', [PoController::class, 'getLocation'])->name('getLocation');
    Route::get('getLocationPurchase', [PoController::class, 'getLocationPurchase'])->name('getLocationPurchase');
    Route::get('getPOProducts', [PoController::class, 'getPOProducts'])->name('getPOProducts');


    Route::post('GetPODet', [PoController::class, 'GetPODet'])->name('GetPODet');
    Route::post('GetProducts1', [PoController::class, 'GetProducts1'])->name('GetProducts1');
    Route::post('/GetWarehouseLocations', [PoController::class, 'GetWarehouseLocations']);
    Route::post('GetPO', [PoController::class, 'GetPO'])->name('GetPO');
    Route::post('/GetLastVendorPrice', [PoController::class, 'GetLastVendorPrice']);
    Route::post('/checkInvoiceNo', [PoController::class, 'checkInvoiceNo'])->name("checkInvoiceNo");



    // Out ward Management
    Route::get('supplier/outward-stock', [OutWardController::class, 'OutwardStock'])->name('supplier/outward-stock');
    Route::post('GetCustomerOrder', [OutWardController::class, 'GetCustomerOrder'])->name('GetCustomerOrder');
    Route::post('GetOrderDet', [OutWardController::class, 'GetOrderDet'])->name('GetOrderDet');
    Route::post('supplier/SaveOutwardStock', [OutWardController::class, 'SaveOutwardStock'])->name('supplier/SaveOutwardStock');
    Route::get('supplier/outward-order-list', [OutWardController::class, 'OutwardOrderList'])->name('supplier/outward-order-list');
    Route::get('supplier/outward-challan-view/{id}', [OutWardController::class, 'OutwardChallanView'])->name('supplier/outward-challan-view');
    Route::get('supplier/invoice-view/{id}', [OutWardController::class, 'invoiceView'])->name('supplier/invoice-view');
    Route::post('supplier/convertToInvoice', [OutWardController::class, 'convertToInvoice'])->name('supplier/convertToInvoice');
    Route::get('supplier/invoices', [OutWardController::class, 'invoices'])->name('supplier/invoices');
    Route::post('supplier/cancelOutwardChallan', [OutWardController::class, 'cancelOutwardChallan'])->name('supplier/cancelOutwardChallan');
    Route::post('supplier/DispatchChallan', [OutWardController::class, 'DispatchChallan'])->name('supplier/DispatchChallan');

    Route::post('/supplier/generateEInvoice', [EInvoiceController::class, 'generateEInvoice'])->name('/supplier/generateEInvoice');
    Route::post('/supplier/generateEwayBill', [EInvoiceController::class, 'generateEwayBill'])
        ->name('/supplier/generateEwayBill');

    //Dispatch  Plan
    Route::get('supplier/mode-of-transport', [DispatchController::class, 'ModeOfTransport'])->name('supplier/mode-of-transport');
    Route::post('supplier/SaveModeOfTransport', [DispatchController::class, 'SaveModeOfTransport'])->name('supplier/SaveModeOfTransport');
    Route::get('supplier/dispatch-plan/{status}', [DispatchController::class, 'dispatchPlan'])->name('supplier/dispatch-plan');
    Route::get('supplier/outwards/{status}', [DispatchController::class, 'orderDelivered'])->name('supplier/outwards-delivered');
    Route::post('supplier/DispatchTransport', [DispatchController::class, 'DispatchTransport'])->name('supplier/DispatchTransport');
    Route::post('supplier/DispatchOrderStatus', [DispatchController::class, 'DispatchOrderStatus'])->name('supplier/DispatchOrderStatus');

    //export
    Route::get('supplier/export-products', [excelExport::class, 'export']);
});

Route::middleware([Customers::class])->group(function () {

    //ajax
    Route::post('customer/GetCategory', [Customer::class, 'GetCategory'])->name('customer/GetCategory');
    Route::post('customer/GetSubCategory', [Customer::class, 'GetSubCategory'])->name('customer/GetSubCategory');
    Route::post('customer/GetProducts', [Customer::class, 'GetProducts'])->name('customer/GetProducts');
    Route::post('customer/GetFinishProduct', [Customer::class, 'GetFinishProduct'])->name('customer/GetFinishProduct');

    Route::post('customer/GetGatheringDet', [Customer::class, 'GetGatheringDet'])->name('customer/GetGatheringDet');


    Route::get('customer/logout', [Authentication::class, 'CustomerLogout'])->name('customer/Logout');
    Route::get('customer/profile', [Supplier::class, 'Profile'])->name('customer/profile');
    Route::post('customer/UpdateProfile', [Supplier::class, 'UpdateProfile'])->name('customer/UpdateProfile');
    //masters routes
    Route::get('customer/dashboard', [Customer::class, 'Dashboard'])->name('customer/dashboard');
    Route::get('customer/brand', [Customer::class, 'Brand'])->name('customer/brand');
    Route::post('customer/SaveBrand', [Customer::class, 'SaveBrand'])->name('customer/SaveBrand');

    Route::get('customer/category', [Customer::class, 'Category'])->name('customer/category');
    Route::post('customer/SaveCategory', [Customer::class, 'SaveCategory'])->name('customer/SaveCategory');

    Route::get('customer/sub-category', [Customer::class, 'SubCategory'])->name('customer/sub-category');
    Route::post('customer/SaveSubCategory', [Customer::class, 'SaveSubCategory'])->name('customer/SaveSubCategory');

    Route::get('customer/products', [Customer::class, 'Product'])->name('customer/products');
    Route::post('customer/SaveProduct', [Customer::class, 'SaveProduct'])->name('customer/SaveProduct');
    Route::post('customer/ImportProducts', [Customer::class, 'ImportProducts'])->name('customer/ImportProducts');

    Route::get('customer/finish-product-category', [Customer::class, 'FinishProductCategory'])->name('customer/finish-product-category');
    Route::post('customer/SaveFinishCategory', [Customer::class, 'SaveFinishCategory'])->name('customer/SaveFinishCategory');


    Route::get('customer/finish-product', [Customer::class, 'FinishProduct'])->name('customer/finish-product');
    Route::post('customer/SaveFinishProduct', [Customer::class, 'SaveFinishProduct'])->name('customer/SaveFinishProduct');
    Route::post('customer/UpdateFinishProduct', [Customer::class, 'UpdateFinishProduct'])->name('customer/UpdateFinishProduct');



    Route::get('customer/customer-raw-material-product/{id}', [Customer::class, 'RawMaterialProduct'])->name('customer/customer-raw-material-product/{id}');
    Route::post('customer/SaveRawProduct', [Customer::class, 'SaveRawProduct'])->name('customer/SaveRawProduct');
    Route::post('customer/DeleteProduct', [Customer::class, 'DeleteProduct'])->name('customer/DeleteProduct');

    Route::get('customer/gathering-list', [Customer::class, 'GatheringList'])->name('customer/gathering-list');
    Route::get('customer/add-gathering', [Customer::class, 'AddGathering'])->name('customer/add-gathering');
    Route::post('customer/SaveGathering', [Customer::class, 'SaveGathering'])->name('customer/SaveGathering');
    Route::post('customer/UpdateGathering', [Customer::class, 'UpdateGathering'])->name('customer/UpdateGathering');
    Route::get('customer/gathering-menu/{id}', [Customer::class, 'GatheringMenu'])->name('customer/gathering-menu');

    Route::post('customer/AddGatheringMenu', [Customer::class, 'AddGatheringMenu'])->name('customer/AddGatheringMenu');
    Route::post('customer/DeleteGatheringMenuItem', [Customer::class, 'DeleteGatheringMenuItem'])->name('customer/DeleteGatheringMenuItem');
    Route::get('customer/customer', [Customer::class, 'Customer'])->name('customer/customer');
    Route::post('customer/SaveCustomer', [Customer::class, 'SaveCustomer'])->name('customer/SaveCustomer');

    Route::get('customer/customer-gathering', [Customer::class, 'CustomerGathering'])->name('customer/customer-gathering');
    Route::get('customer/add-customer-gathering', [Customer::class, 'AddCustomerGathering'])->name('customer/add-customer-gathering');
    Route::post('customer/SaveCustomerGathering', [Customer::class, 'SaveCustomerGathering'])->name('customer/SaveCustomerGathering');

    Route::get('customer/customer-gathering-menu/{id}', [Customer::class, 'CustomerGatheringMenu'])->name('customer/customer-gathering-menu');
    Route::get('customer/customer-gathering-menu-raw-material/{id}', [Customer::class, 'CustomerGatheringMenuRawMaterial'])->name('customer/customer-gathering-menu-raw-material');


    Route::get("customer/vendor", [Customer::class, "vendor"])->name("customer/vendor");
    Route::post("customer/saveVendor", [Customer::class, "saveVendor"])->name("customer/saveVendor");

    Route::get("customer/gst", [Customer::class, "gst"])->name("customer/gst");
    Route::post("customer/saveGST", [Customer::class, "saveGST"])->name("customer/saveGST");



    Route::get("customer/unit-type", [Customer::class, "unitType"])->name("customer/unit-type");
    Route::post("customer/saveUnitType", [Customer::class, "saveUnitType"])->name("customer/saveUnitType");

    Route::get("customer/department", [Customer::class, "department"])->name("customer/department");
    Route::post("customer/saveDepartment", [Customer::class, "saveDepartment"])->name("customer/saveDepartment");

    //purchase management
    Route::get("customer/vendor-product/{id}", [PurchaseManagement::class, "vendorProduct"])->name("customer/vendor-product");
    Route::post("customer/saveVendorProduct", [PurchaseManagement::class, "saveVendorProduct"])->name("customer/saveVendorProduct");
    Route::post("customer/GetVendorProducts", [PurchaseManagement::class, "GetVendorProducts"])->name("customer/GetVendorProducts");



    Route::get("customer/generate-po", [PurchaseManagement::class, "generatePO"])->name("customer/generate-po");
    Route::post("customer/SavePO", [PurchaseManagement::class, "SavePO"])->name("customer/SavePO");

    Route::get("customer/po/{status}", [PurchaseManagement::class, "po"])->name("customer/po");
    Route::get("customer/purchase-view/{id}", [PurchaseManagement::class, "PurchaseView"])->name("customer/purchase-view");
    Route::post("customer/UpdateCharges", [PurchaseManagement::class, "UpdateCharges"])->name("customer/UpdateCharges");
    Route::post("customer/DeletePOProduct", [PurchaseManagement::class, "DeletePOProduct"])->name("customer/DeletePOProduct");
    Route::post("customer/SavePOProduct", [PurchaseManagement::class, "SavePOProduct"])->name("customer/SavePOProduct");

    Route::get("customer/inward-stock", [PurchaseManagement::class, "InwardStock"])->name("customer/inward-stock");
    Route::post("customer/SaveInwardStock", [PurchaseManagement::class, "SaveInwardStock"])->name("customer/SaveInwardStock");

    Route::post("customer/GetPO", [PurchaseManagement::class, "GetPO"])->name("customer/GetPO");
    Route::post("customer/GetPODet", [PurchaseManagement::class, "GetPODet"])->name("customer/GetPODet");

    Route::get("customer/inward-report", [PurchaseManagement::class, "InwardReport"])->name("customer/inward-report");
    Route::get("customer/inward-report-view/{id}", [PurchaseManagement::class, "InwardReportView"])->name("customer/inward-report-view");


    //outward management
    Route::get("customer/outward-stock", [OutwardManagement::class, "outwardStock"])->name("customer/outward-stock");
    Route::post("customer/SaveOutward", [OutwardManagement::class, "SaveOutward"])->name("customer/SaveOutward");

    Route::get("customer/outward-report", [OutwardManagement::class, "outwardReport"])->name("customer/outward-report");

    Route::post("customer/DispatchChallan", [OutwardManagement::class, "DispatchChallan"])->name("customer/DispatchChallan");
    Route::post("customer/DeliveredChallan", [OutwardManagement::class, "DeliveredChallan"])->name("customer/DeliveredChallan");
    Route::get("customer/outward-challan-view/{id}", [OutwardManagement::class, "OutwardChallanView"])->name("customer/outward-challan-view");

    // master customer Expense
    Route::get('customer/expenses-category', [Customer::class, 'customerExpenseCategory'])->name('customer/expenses-category');
    Route::post('customer/expenseSaveCategory', [Customer::class, 'expenseSaveCategory'])->name('customer/expenseSaveCategory');

    Route::get('customer/expense-list', [Customer::class, 'expenseList'])->name('customer/expense-list');
    Route::post('customer/expenseSave', [Customer::class, 'expenseSave'])->name('customer/expenseSave');

    Route::get('customer/expense-subcategory', [Customer::class, 'expenseSubCategory'])->name('customer/product-expenseSubCategory');
    Route::post('customer/expenseSaveSubCategory', [Customer::class, 'expenseSaveSubCategory'])->name('customer/expenseSaveSubCategory');
});


Route::middleware([CustomerFrontend::class])->group(function () {

    Route::get('profile', [CustomerFrontendController::class, 'Profile'])->name('profile');
    Route::post('AddToCart', [CustomerFrontendController::class, 'AddToCart'])->name('AddToCart');
    Route::get('cart', [CustomerFrontendController::class, 'Cart'])->name('cart');
    // Route::get('checkout', [CustomerFrontendController::class, 'Checkout'])->name('checkout');
    Route::post('SaveOrder', [CustomerFrontendController::class, 'SaveOrder'])->name('SaveOrder');
    Route::get('logout', [CustomerFrontendController::class, 'Logout'])->name('Logout');
    Route::post('UpdateCompanyDetails', [CustomerFrontendController::class, 'UpdateCompanyDetails'])->name('UpdateCompanyDetails');
    Route::post('UpdateCustomerDetails', [CustomerFrontendController::class, 'UpdateCustomerDetails'])->name('UpdateCustomerDetails');
    // Route::get('invoice/{id}', [CustomerFrontendController::class, 'Invoice'])->name('invoice');
    Route::post('UploadDocument', [Supplier::class, 'UploadDocument'])->name('UploadDocument');
    Route::post('shopAddToCart', [CustomerFrontendController::class, 'shopAddToCart'])->name('shopAddToCart');
});

Route::middleware([staffAuth::class])->group(function () {
    Route::get('staff/dashboard', [StaffManagement::class, 'StaffDashboard'])->name('staff/dashboard');
    Route::get('staff/orders/{status}', [StaffManagement::class, 'Orders'])->name('staff/orders');
    Route::get('staff/order-details/{id}', [StaffManagement::class, 'OrderDetails'])->name('staff/order-details');
    Route::get('staff/orders-estimate/{status}', [StaffManagement::class, 'orderEstimate'])->name('staff/orders-estimate');

    Route::get('staff/logout', [StaffManagement::class, 'Logout'])->name('staff/logout');
});
