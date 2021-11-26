import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { CartInput } from 'connectors/cart/types';
import { HYDRATE } from 'next-redux-wrapper';

export const initialState = {
    cartUuid: null,
    isCartEmpty: true,
    transport: null,
    payment: null,
    promoCode: null,
} as CartInput;

export const cartInputSlice = createSlice({
    name: 'cartInput',
    initialState,
    reducers: {
        setCartInputData(state, action: PayloadAction<CartInput>) {
            state.cartUuid = action.payload.cartUuid;
            state.isCartEmpty = action.payload.isCartEmpty;
            state.transport = action.payload.transport;
            state.payment = action.payload.payment;
            state.promoCode = action.payload.promoCode;
        },
        setPromoCode(state, action: PayloadAction<string | null>) {
            state.promoCode = action.payload;
        },
    },
    extraReducers: {
        /**
         * @see https://github.com/kirill-konshin/next-redux-wrapper#usage
         */
        [HYDRATE]: (state, action) => {
            return {
                ...state,
                ...action.payload.cartInput,
            };
        },
    },
});

export const cartInputActions = cartInputSlice.actions;
