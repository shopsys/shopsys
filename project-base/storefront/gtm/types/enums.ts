export enum GtmPageType {
    homepage = 'homepage',
    category_detail = 'category detail',
    seo_category_detail = 'seo category detail',
    product_detail = 'product detail',
    cart = 'cart',
    transport_and_payment = 'transport and payment',
    contact_information = 'contact information',
    order_confirmation = 'order confirmation',
    search_results = 'search results',
    blog_category_detail = 'blog category detail',
    blog_article_detail = 'blog article detail',
    stores = 'stores',
    store_detail = 'store detail',
    flag_detail = 'flag detail',
    brand_detail = 'brand detail',
    article_detail = 'article detail',
    payment_fail = 'payment fail',
    payment_success = 'payment success',
    not_found = '404',
    cookie_consent = 'cookie consent',
    contact = 'contact',
    product_comparison = 'product comparison',
    other = 'other',
}

export enum GtmProductListNameType {
    blog_article_detail = 'blog article detail',
    brand_detail = 'brand detail',
    category_detail = 'category detail',
    seo_category_detail = 'seo category detail',
    flag_detail = 'flag detail',
    product_detail_accessories = 'product detail accessories',
    product_detail_variants_table = 'product detail variant table',
    search_results = 'search results',
    homepage_promo_products = 'homepage promo products',
    cart = 'cart',
    product_comparison_page = 'product comparison page',
    product_detail = 'product detail',
    autocomplete_search_results = 'autocomplete_search_results',
    wishlist = 'wishlist',
    sharedWishlist = 'sharedWishlist',
    other = 'other',
}

export enum GtmEventType {
    page_view = 'page_view',
    consent_update = 'consent.update',
    add_to_cart = 'ec.add_to_cart',
    remove_from_cart = 'ec.remove_from_cart',
    cart_view = 'ec.cart_view',
    product_list_view = 'ec.product_list_view',
    product_click = 'ec.product_click',
    product_detail_view = 'ec.product_detail_view',
    payment_and_transport_page_view = 'ec.payment_and_transport_view',
    autocomplete_results_view = 'ec.autocomplete_results_view',
    autocomplete_result_click = 'ec.autocomplete_result_click',
    transport_change = 'ec.transport_change',
    contact_information_page_view = 'ec.contact_information_view',
    payment_change = 'ec.payment_change',
    payment_fail = 'ec.payment_fail',
    create_order = 'ec.create_order',
    show_message = 'ec.show_message',
    send_form = 'send_form',
}

export enum GtmMessageOriginType {
    product_detail_page = 'product detail page',
    cart = 'cart',
    transport_and_payment_page = 'transport and payment page',
    contact_information_page = 'contact information page',
    order_confirmation_page = 'order confirmation page',
    login_popup = 'login popup',
    other = 'other',
}

export enum GtmMessageDetailType {
    flash_message = 'flash message',
}

export enum GtmSectionType {
    category = 'category',
    product = 'product',
    brand = 'brand',
    article = 'article',
}

export enum GtmUserType {
    b2b = 'B2B',
    b2c = 'B2C',
}

export enum GtmUserStatus {
    customer = 'customer',
    visitor = 'visitor',
}

export enum GtmDeviceTypes {
    desktop = 'desktop',
    tablet = 'tablet',
    mobile = 'mobile',
    unknown = 'unknown',
}

export enum GtmConsent {
    granted = 'granted',
    denied = 'denied',
}

export enum GtmFormType {
    forgotten_password = 'forgotten password',
    registration = 'registration',
}

export enum GtmMessageType {
    error = 'error',
    information = 'information',
}
