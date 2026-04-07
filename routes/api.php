<?php

use App\Http\Controllers\ApiController\ApiController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ApiController\LoginController;
use App\Http\Controllers\ApiController\mobileAppController;
use App\Http\Controllers\ApiController\WebApiController as ApiControllerWebApiController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChatGptController;
use App\Http\Controllers\ApiController\WebApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Middleware\CustomerFrontend;
use App\Http\Middleware\customerMobileMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

Route::post('/customer-login', [LoginController::class, 'customerLoginApi']);
Route::post('/send-otp', [LoginController::class, 'sendOtp']);
Route::post('/verify-otp', [LoginController::class, 'verifyOtp']);
Route::post('/forgot-password/send-otp', [LoginController::class, 'forgotSendOtp']);
Route::post('/forgot-password/verify-otp', [LoginController::class, 'forgotVerifyOtp']);
Route::post('/forgot-password/reset', [LoginController::class, 'forgotPWD']);
Route::post('/customer-signup', [LoginController::class, 'saveCustomerApi']);
Route::get('/get-category', [ApiController::class, 'getCategory'])->name('get-category');
Route::get('/get-sub-category', [ApiController::class, 'getSubCategory'])->name('get-sub-category');
Route::get('/get-sub-sub-category', [ApiController::class, 'getSubSubCategory'])->name('get-sub-sub-category');
Route::get('/get-brands', [ApiController::class, 'getBrands'])->name('get-brands');
Route::get('/get-brands/{id}/brands/{subcategoryId?}', [ApiController::class, 'getBrandSubcategory']);
Route::get('/search-products', [ApiController::class, 'getProducts'])->name('get-products');
Route::get('/get-all-products', [ApiController::class, 'getAllProducts'])->name('get-all-products');
Route::get('/get-products-deal', [ApiController::class, 'dealOnDay'])->name('get-products-deal');
Route::get('/get-slider', [ApiController::class, 'SlidersApi'])->name('get-slider');
Route::get('/get-banner', [ApiController::class, 'BannerApi'])->name('get-banner');
Route::get('/get-banner-deal-of-day', [ApiController::class, 'dealofDayApi'])->name('get-banner-deal-of-day');
Route::get('/get-footer-banner', [ApiController::class, 'FooterBannerApi'])->name('get-footer-banner');
Route::get('/get-brand-slider', [ApiController::class, 'brandSliderApi'])->name('get-brand-slider');
Route::get('/get-product-detail/{id}', [ApiController::class, 'ProductDetailsApi'])->name('get-product-detail');
Route::get('/get-faq', [ApiController::class, 'faqCategory'])->name('get-faq');
Route::get('/quality-step', [ApiController::class, 'qulityMainList'])->name('quality-step');
Route::get('/get-location', [ApiController::class, 'getLocation'])->name('get-location');
Route::get('/get-refund', [ApiController::class, 'refund'])->name('get-refund');
Route::get('/get-terms', [ApiController::class, 'terms'])->name('get-terms');
Route::get('/get-privacy', [ApiController::class, 'privacy'])->name('get-privacy');
Route::get('/get-order-delivery', [ApiController::class, 'orderDelivery'])->name('get-order-delivery');


// Route::post('/chat-gpt', [ChatGptController::class, 'chatGpt'])->name('chatGpt');
Route::middleware([CustomerFrontend::class])->group(function () {
    Route::post('add-to-cart', [ApiController::class, 'shopAddToCart'])->name('add-to-Cart');
    Route::post('remove-cart', [ApiController::class, 'removeItem'])->name('remove-cart');
    Route::post('add-to-wishlist', [ApiController::class, 'shopAddToWhishlist'])->name('add-to-wishlist');
    Route::post('remove-wishlist', [ApiController::class, 'removewishlist'])->name('remove-wishlist');
    Route::get('cart-view', [ApiController::class, 'cartApi'])->name('cart-view');
    Route::get('whishlist-view', [ApiController::class, 'whishList'])->name('whishlist-view');
    Route::get('get-cart', [ApiController::class, 'getCartByCustomer'])->name('get-cart');
    Route::get('order-estimate/{id}', [ApiController::class, 'orderEstimate'])->name('order-estimate');
    Route::get('order/{id}', [ApiController::class, 'orderApp'])->name('order-app');
    Route::get('wallet/{customerId}', [ApiController::class, 'walletLedger'])->name('walletLedger');
    Route::get('order-estimate-customer/{customerId}', [ApiController::class, 'orderEstimateApp'])->name('order-estimate-customer');
    Route::get('get-product', [ApiController::class, 'getproduct'])->name('get-product');
    Route::get('checkout', [ApiController::class, 'Checkout'])->name('checkout');
    Route::get('invoice/{invoiceNo}', [ApiController::class, 'getInvoiceData'])->name('invoice');
    Route::get('invoice-bill/{id}', [ApiController::class, 'getInvoiceBill'])->name('invoicebill');
    Route::post('place-order', [ApiController::class, 'SaveOrder'])->name('place-order');
    Route::get('customer-detail', [ApiController::class, 'customerProfileApi'])->name('customer-detail');
    Route::get('product-for-request-list', [ApiController::class, 'requestListProduct'])->name('product-for-request-list');
    Route::post('request-for-product', [ApiController::class, 'requestProduct'])->name('request-for-product');
    Route::post('customer-logout', [ApiController::class, 'apiLogout'])->name('customer-logout');
    Route::post('hdfc/create-order', [ApiController::class, 'createHdfcOrder'])->name('createHdfcOrder');
    Route::get('/payment-processing/{invoice_no}', [ApiController::class, 'checkStatus']);
    Route::post('/payment/hdfc/webhook', [ApiController::class, 'webhook'])
        ->name('hdfc.payment.webhook');
    Route::post('save-request-for-price', [ApiController::class, 'requestForPrice'])->name('requestForPrice');

    // Route::get('/chat/{customerId}', [ApiController::class, 'getMessages']);
    // Route::post('/chat/send', [ApiController::class, 'sendMessage']);
});


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('/users', function (Request $request) {
    // ...
});

Route::get('/subCategories', function () {
    $category_id = request('category_id');
    return DB::table('product_sub_category')
        ->where('category_id', $category_id)
        ->get();
});


//mobile app api routes 
Route::post('mobile/send-otp', [mobileAppController::class, 'sendOTP'])->name('mobile/send-otp');
Route::post('mobile/verify-otp', [mobileAppController::class, 'verifyOTP'])->name('mobile/verify-otp');
Route::get('mobile/get-products-deal', [mobileAppController::class, 'mobDealOnDay'])->name('mobDealOnDay');
Route::middleware([customerMobileMiddleware::class])->group(function () {
    Route::get('mobile/home-page', [mobileAppController::class, 'homePage'])->name('mobile/home-page');
    Route::post('mobile/check-gst', [mobileAppController::class, 'checkGST'])->name('mobile/check-gst');
    Route::post('mobile/update-profile', [mobileAppController::class, 'updateProfile'])->name('mobile/update-profile');
    Route::get('mobile/get-profile', [mobileAppController::class, 'getProfile'])->name('mobile/get-profile');
    Route::get('mobile/get-company', [mobileAppController::class, 'getCompany'])->name('mobile/get-company');
    Route::post('mobile/update-company', [mobileAppController::class, 'updateCompany'])->name('mobile/update-company');
    Route::get('mobile/get-products/{category_id}/{sub_category_id?}/{ss_category_id?}', [mobileAppController::class, 'getProducts'])->name('mobile/get-products');
    Route::get('mobile/get-category', [mobileAppController::class, 'getCategory'])->name('mobile/get-category');
    Route::post('mobile/add-to-cart', [mobileAppController::class, 'addToCart'])->name('mobile/add-to-cart');
    Route::get('mobile/get-cart', [mobileAppController::class, 'getCart'])->name('mobile/get-cart');
    Route::post('mobile/remove-cart-item', [mobileAppController::class, 'removeCartItem'])->name('mobile/remove-cart-item');
    Route::post('mobile/update-cart-qty', [mobileAppController::class, 'updateCartQty'])->name('mobile/update-cart-qty');
    Route::post('mobile/add-to-wishlist', [mobileAppController::class, 'addToWishList'])->name('mobile/add-to-wishlist');
    Route::post('mobile/update-wishlist-qty', [mobileAppController::class, 'updateWishListQty'])->name('mobile/update-wishlist-qty');
    Route::get('mobile/get-wishlist', [mobileAppController::class, 'getWishList'])->name('mobile/get-wishlist');
    Route::post('mobile/save-address', [mobileAppController::class, 'saveAddress'])->name('mobile/save-address');
    Route::get('mobile/get-address', [mobileAppController::class, 'getAddress'])->name('mobile/get-address');
    Route::post('mobile/update-default-address', [mobileAppController::class, 'updateDefaultAddress'])->name('mobile/update-default-address');
    Route::get('mobile/get-states', [mobileAppController::class, 'getStates'])->name('mobile/get-states');
    Route::get('mobile/get-district/{state}', [mobileAppController::class, 'getDistrict'])->name('mobile/get-district');
    Route::post('mobile/delete-address', [mobileAppController::class, 'deleteAddress'])->name('mobile/delete-address');
    Route::get('mobile/get-product-details/{id}', [mobileAppController::class, 'j'])->name('mobile/get-product-details');
    Route::get('mobile/get-product-by-brand/{brand_id}/{category_id?}/{sub_category_id?}/{ss_category_id?}', [mobileAppController::class, 'getProductByBrand'])->name('mobile/get-product-by-brand');
    Route::get('mobile/get-brands', [mobileAppController::class, 'getBrands'])->name('mobile/get-brands');
    Route::post('mobile/save-order', [mobileAppController::class, 'saveOrder'])->name('mobile/save-order');
    Route::get('mobile/get-estimate', [mobileAppController::class, 'getEstimate'])->name('mobile/get-estimate');
    Route::get('mobile/get-estimate-details/{id}', [mobileAppController::class, 'getEstimateDetails'])->name('mobile/get-estimate-details');
    Route::get('mobile/get-order', [mobileAppController::class, 'getOrder'])->name('mobile/get-order');
    Route::get('mobile/get-order-details/{id}', [mobileAppController::class, 'getOrderDetails'])->name('mobile/get-order-details');
    Route::get('mobile/get-wallet-ledger', [mobileAppController::class, 'getWalletLedger'])->name('mobile/get-wallet-ledger');
    Route::get('mobile/search-products/{query}', [mobileAppController::class, 'searchProducts'])->name('mobile/search-products');
});


//Web Api Route
Route::get('/web-test', function () {
    return response()->json(['message' => 'Web API is working']);
});
 
// Route::post('web/send-otp', [mobileAppController::class, 'sendOTP'])->name('web/send-otp');
// Route::post('web/verify-otp', [mobileAppController::class, 'verifyOTP'])->name('web/verify-otp');
Route::post('web/customer-login', [LoginController::class, 'customerLoginApi']);
Route::post('web/send-otp', [LoginController::class, 'sendOtp']);
Route::post('web/verify-otp', [LoginController::class, 'verifyOtp']);
Route::post('web/forgot-password/send-otp', [LoginController::class, 'forgotSendOtp']);
Route::post('web/forgot-password/verify-otp', [LoginController::class, 'forgotVerifyOtp']);
Route::post('web/forgot-password/reset', [LoginController::class, 'forgotPWD']);
Route::post('web/customer-save', [LoginController::class, 'saveCustomerApi']);
Route::get('web/get-category', [WebApiController::class, 'getCategory'])->name('web/get-category');
Route::get('web/get-sub-category', [WebApiController::class, 'getSubCategory'])->name('web/get-sub-category');
Route::get('web/get-sub-sub-category', [WebApiController::class, 'getSubSubCategory'])->name('web/get-sub-sub-category');
Route::get('web/get-brands', [WebApiController::class, 'getBrands'])->name('web/get-brands');
Route::get('web/get-brands/{id}/brands/{subcategoryId?}', [WebApiController::class, 'getBrandSubcategory']);
Route::get('web/search-products', [WebApiController::class, 'getProducts'])->name('web/get-products');
Route::get('web/get-all-products', [WebApiController::class, 'getAllProducts'])->name('web/get-all-products');
Route::get('web/get-products-deal', [WebApiController::class, 'dealOnDay'])->name('web/get-products-deal');
Route::get('web/get-slider', [WebApiController::class, 'SlidersApi'])->name('web/get-slider');
Route::get('web/get-banner-deal-of-day', [WebApiController::class, 'dealofDayApi'])->name('web/get-banner-deal-of-day');
Route::get('web/get-footer-banner', [WebApiController::class, 'FooterBannerApi'])->name('web/get-footer-banner');
Route::get('web/get-brand-slider', [WebApiController::class, 'brandSliderApi'])->name('web/get-brand-slider');
Route::get('web/get-product-details/{id}', [WebApiController::class, 'ProductDetailsApi'])->name('web/get-product-details');
Route::get('web/get-faq', [WebApiController::class, 'faqCategory'])->name('web/get-faq');
Route::get('web/quality-step', [WebApiController::class, 'qulityMainList'])->name('web/quality-step');
Route::get('web/get-location', [WebApiController::class, 'getLocation'])->name('web/get-location');
Route::get('web/get-refund', [WebApiController::class, 'refund'])->name('web/get-refund');
Route::get('web/get-terms', [WebApiController::class, 'terms'])->name('web/get-terms');
Route::get('web/get-privacy', [WebApiController::class, 'privacy'])->name('web/get-privacy');
Route::get('web/get-order-delivery', [WebApiController::class, 'orderDelivery'])->name('web/get-order-delivery');
Route::get('web/promotional-banner', [WebApiController::class, 'BannerApi'])->name('web/promotional-banner');
Route::post('web/check-gst', [WebApiController::class, 'checkGSTApi'])->name('web/check-gst');
Route::get('web/get-featured-product', [WebApiController::class, 'homeCategory'])->name('web/get-featured-product');
Route::get('web/get-customers', [WebApiController::class, 'getCustomers'])->name('web/get-customers');

Route::middleware([CustomerFrontend::class])->group(function () {
    Route::post('web/add-to-cart', [WebApiController::class, 'addToCart'])->name('web/add-to-cart');
    Route::get('web/get-cart', [WebApiController::class, 'getCart'])->name('web/get-cart');
    Route::post('web/remove-cart-item', [WebApiController::class, 'removeCartItem'])->name('web/remove-cart-item');
    Route::post('web/update-cart-qty', [WebApiController::class, 'updateCartQty'])->name('web/update-cart-qty');
    Route::post('web/add-to-wishlist', [WebApiController::class, 'addToWishList'])->name('web/add-to-wishlist');
    Route::post('web/update-wishlist-qty', [WebApiController::class, 'updateWishListQty'])->name('web/update-wishlist-qty');
    Route::get('web/get-wishlist', [WebApiController::class, 'getWishList'])->name('web/get-wishlist');
    Route::post('web/update-profile', [WebApiController::class, 'updateProfile'])->name('web/update-profile');
    Route::get('web/get-profile', [WebApiController::class, 'getProfile'])->name('web/get-profile');
    Route::get('web/get-company', [WebApiController::class, 'getCompany'])->name('web/get-company');
    Route::post('web/update-company', [WebApiController::class, 'updateCompany'])->name('web/update-company');
    Route::post('web/save-address', [WebApiController::class, 'saveAddress'])->name('web/save-address');
    Route::get('web/get-address', [WebApiController::class, 'getAddress'])->name('web/get-address');
    Route::post('web/update-default-address', [WebApiController::class, 'updateDefaultAddress'])->name('web/update-default-address');
    Route::get('web/get-states', [WebApiController::class, 'getStates'])->name('web/get-states');
    Route::get('web/get-district/{state}', [WebApiController::class, 'getDistrict'])->name('web/get-district');
    Route::post('web/delete-address', [WebApiController::class, 'deleteAddress'])->name('web/delete-address');
     Route::get('web/checkout', [WebApiController::class, 'Checkout'])->name('web/checkout');
    Route::post('web/place-order', [WebApiController::class, 'placeOrder'])->name('web/place-order');
    Route::post('web/save-order', [WebApiController::class, 'SaveOrder'])->name('web/save-order');
    Route::get('web/get-estimate', [WebApiController::class, 'getEstimate'])->name('web/get-estimate');
    Route::get('web/get-estimate-details/{id}', [WebApiController::class, 'getEstimateDetails'])->name('web/get-estimate-details');
    Route::get('web/get-order', [WebApiController::class, 'getOrder'])->name('web/get-order');
    Route::get('web/get-order-details/{id}', [WebApiController::class, 'getOrderDetails'])->name('web/get-order-details');
    Route::post('web/add-to-cart-bulk', [WebApiController::class, 'addWishlistToCartBulk'])->name('web/add-to-cart-bulk');
    Route::post('web/customer-logout', [LoginController::class, 'apiLogout'])->name('web/customer-logout');
});
