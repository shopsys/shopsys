import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { DomainConfigType } from 'utils/Domain/Domain';

const initialState = {
    url: 'http://127.0.0.1:8000/',
    publicGraphqlEndpoint: 'http://127.0.0.1:8000/graphql/',
    defaultLocale: 'cs',
    currencyCode: 'CZK',
};

export const domainSlice = createSlice({
    name: 'domain',
    initialState,
    reducers: {
        setDomain(state, action: PayloadAction<DomainConfigType>) {
            state.currencyCode = action.payload.currencyCode;
            state.url = action.payload.url;
            state.defaultLocale = action.payload.defaultLocale;
            state.publicGraphqlEndpoint = action.payload.publicGraphqlEndpoint;
        },
    },
});

export const domainActions = domainSlice.actions;
