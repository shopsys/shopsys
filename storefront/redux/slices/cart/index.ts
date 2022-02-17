import { CartInput, CartResultValues, CartType } from 'types/cart';
import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { getCartInputFromCartResult } from 'utils/Cart/GetCartInputFromCartResult';
import { HYDRATE } from 'next-redux-wrapper';
import { PaymentType } from 'types/payment';
import { PickupPlaceType } from 'types/pickupPlace';
import { TransportType } from 'types/transport';

type InitialState = {
    cart: CartType | null;
    transport: TransportType | null;
    payment: PaymentType | null;
    pickupPlace: PickupPlaceType | null;
    isCartEmpty: boolean;
    cartInput: CartInput;
};

export const initialState = {
    cart: null,
    transport: null,
    payment: null,
    pickupPlace: null,
    isCartEmpty: false,
    cartInput: {
        cartUuid: null,
        transport: null,
        payment: null,
        promoCode: null,
    },
} as InitialState;

export const cartSlice = createSlice({
    name: 'cart',
    initialState,
    reducers: {
        resetCart(state) {
            state.cart = null;
            state.transport = null;
            state.pickupPlace = null;
            state.payment = null;
            state.cartInput = {
                cartUuid: null,
                transport: null,
                payment: null,
                promoCode: null,
            };
            state.isCartEmpty = true;
        },
        updateCart(state, action: PayloadAction<CartResultValues>) {
            state.cart = action.payload.cart;
            state.transport = action.payload.transport;
            state.pickupPlace = action.payload.pickupPlace;
            state.payment = action.payload.payment;
            state.cartInput = getCartInputFromCartResult(action.payload, state.payment?.goPayBankSwift ?? null);
            state.isCartEmpty = action.payload.cart?.items.length === 0;
        },
        setCart(state, action: PayloadAction<CartType | null>) {
            state.cart = action.payload;
        },
        setTransport(state, action: PayloadAction<TransportType | null>) {
            state.transport = action.payload;
        },
        setPickupPlace(state, action: PayloadAction<PickupPlaceType | null>) {
            state.pickupPlace = action.payload;
        },
        setPayment(state, action: PayloadAction<PaymentType | null>) {
            state.payment = action.payload;
        },
        setIsCartEmpty(state, action: PayloadAction<boolean>) {
            state.isCartEmpty = action.payload;
        },
        setCartInputData(state, action: PayloadAction<CartInput>) {
            state.cartInput = action.payload;
        },
        setCartUuid(state, action: PayloadAction<string | null>) {
            state.cartInput.cartUuid = action.payload;
        },
        setPromoCode(state, action: PayloadAction<string | null>) {
            state.cartInput.promoCode = action.payload;
        },
    },
    extraReducers: {
        /**
         * @see https://github.com/kirill-konshin/next-redux-wrapper#usage
         */
        [HYDRATE]: (state, action: PayloadAction<{ cart: InitialState | undefined }>) => {
            if (action.payload.cart?.cartInput === undefined) {
                return state;
            }

            return {
                ...state,
                cartInput: action.payload.cart.cartInput,
                isCartEmpty: action.payload.cart.isCartEmpty,
            };
        },
    },
});

export const cartActions = cartSlice.actions;
