const constant = {
    '\\Shopsys\\FrameworkBundle\\Model\\Cookies\\CookiesFacade::EU_COOKIES_COOKIE_CONSENT_NAME': 'eu-cookies',
    '\\App\\Controller\\Front\\PromoCodeController::PROMO_CODE_PARAMETER': 'code',
    '\\App\\Controller\\Front\\CartController::RECALCULATE_ONLY_PARAMETER_NAME': 'recalculateOnly',
    '\\Shopsys\\FrameworkBundle\\Form\\ValidationGroup::VALIDATION_GROUP_DEFAULT': 'Default',
    '\\App\\Form\\Front\\Customer\\DeliveryAddressFormType::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS': 'differentDeliveryAddress',
    '\\App\\Form\\Front\\Customer\\BillingAddressFormType::VALIDATION_GROUP_COMPANY_CUSTOMER': 'companyCustomer'
};

export default (key) => constant[key];
