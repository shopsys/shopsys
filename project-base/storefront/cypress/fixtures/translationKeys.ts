export const translationKeys = {
    placeholder: {
        password: 'Password',
        passwordAgain: 'Password again',
        email: 'Your email',
        phone: 'Phone',
        firstName: 'First name',
        lastName: 'Last name',
        street: 'Street and house no.',
        city: 'City',
        postCode: 'Postcode',
        coupone: 'Coupon',
        note: 'Note',
        company: 'Company',
        companyName: 'Company name',
        companyNumber: 'Company number',
        companyTaxNumber: 'Tax number',
    },
    payment: {
        creditCard: 'Credit card',
        cash: 'Cash',
        onDelivery: 'Cash on delivery',
        payLater: 'Pay later',
    },
    transport: {
        personalCollection: 'Personal collection',
        czechPost: 'Czech post',
        ppl: 'PPL',
        droneDelivery: 'Drone delivery',
        personalPickup: 'Personal pickup',
    },
    transportGroup: {
        deliveryToAddress: 'Delivery to address',
        pickupPoint: 'Pickup point',
    },
    order: {
        created:
            'Order number {{ orderNumber }} has been sent, thank you for your purchase. We will contact you about next order status.',
        confirmation: {
            personalCollection: 'We are looking forward to your visit.',
            czechPost:
                'the Czech Post will try to deliver your parcel on time, but it will not succeed and despite the constant presence of your person at home, it will not catch you and you will have to pick up the parcel personally at the counter. Here, however, you have to endure an endlessly long line and an eternally grumpy lady postman.',
            packeta: 'Probably best value for your money',
            drone: 'Expect delivery by the end of next month',
            goPay: 'You have chosen GoPay Payment, you will be shown a payment gateway.',
            card: 'You have chosen payment by credit card. Please finish it in two business days.',
        },
        promoCode: 'Promo code',
    },
    link: {
        orderDetail: 'Track',
    },
    filter: {
        price: 'Price',
        from: 'from',
        to: 'to',
        clearAllActiveFilters: 'Clear all active filters',
    },
    toast: {
        success: {
            accountCreated: 'Your account has been created and you are logged in now',
            loggedIn: 'Successfully logged in',
            loggedOut: 'Successfully logged out',
            codeAdded: 'Code was added to the order.',
            codeRemoved: 'Code was removed from the order.',
            giftVoucherAdded: 'Gift voucher was added to the order.',
            giftVoucherRemoved: 'Gift voucher was removed from the order.',
            deliveryAddressCreated: 'Your delivery address has been created',
            userDeleted: 'User has been deleted',
            userSaved: 'User profile has been changed successfully',
            userAdded: 'User has been added successfully',
        },
        error: {
            emailAlreadyRegistered: 'This email is already registered',
        },
        info: {
            promoCodeNotApplicable: 'The promo code {{ promoCode }} is no longer applicable.',
            cartModified: 'Your cart has been modified. Please check the changes.',
        },
    },
};
