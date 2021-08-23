import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { DomainConfigType } from '../../utils/Domain/Domain';

const initialState = {
    domain: '127.0.0.1:8000',
    publicGraphqlEndpoint: '127.0.0.1:8000/graphql/',
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
            state.publicGraphqlEndpoint = action.payload.publicGraphqlEndpoint;
        },
    },
});

export const domainActions = domainSlice.actions;
