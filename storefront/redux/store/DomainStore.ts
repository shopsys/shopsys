import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { DomainConfigType } from '../../utils/Domain/Domain';

const initialState = {
    domain: '127.0.0.1:3000',
    backendHost: '127.0.0.1',
    defaultLocale: 'cs',
    currencyCode: 'CZK',
};

export const domainSlice = createSlice({
    name: 'domain',
    initialState,
    reducers: {
        setDomain(state, action: PayloadAction<DomainConfigType>) {
            state.currencyCode = action.payload.currencyCode;
            state.domain = action.payload.domain;
            state.defaultLocale = action.payload.defaultLocale;
            state.backendHost = action.payload.backendHost;
        },
    },
});

export const domainActions = domainSlice.actions;
