import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { CartInput } from 'connectors/cart/types';
import { HYDRATE } from 'next-redux-wrapper';

export const initialState = {
    cartUuid: null,
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
            state.transport = action.payload.transport;
            state.payment = action.payload.payment;
            state.promoCode = action.payload.promoCode;
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
