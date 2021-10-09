import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { CartInput } from 'connectors/cart/types';
import { HYDRATE } from 'next-redux-wrapper';
import { PaymentInputType } from 'connectors/payments/types';
import { TransportInputType } from 'connectors/transports/types';
import { UserDataCookieType } from 'helpers/Cookies';

type InitialState = CartInput;

export const initialState = {
    cartUuid: null,
    transport: null,
    payment: null,
    promoCode: null,
} as InitialState;

export const cookieSlice = createSlice({
    name: 'cookie',
    initialState,
    reducers: {
        setUserCookieData(state, action: PayloadAction<UserDataCookieType>) {
            state.cartUuid = action.payload.cartUuid;
            state.transport = action.payload.transport;
            state.payment = action.payload.payment;
            state.promoCode = action.payload.promoCode;
        },
        setTransport(state, action: PayloadAction<TransportInputType | null>) {
            state.transport = action.payload;
        },
        setPayment(state, action: PayloadAction<PaymentInputType | null>) {
            state.payment = action.payload;
        },
    },
    extraReducers: {
        /**
         * @see https://github.com/kirill-konshin/next-redux-wrapper#usage
         */
        [HYDRATE]: (state, action) => {
            return {
                ...state,
                ...action.payload.cookie,
            };
        },
    },
});

export const cookieActions = cookieSlice.actions;
