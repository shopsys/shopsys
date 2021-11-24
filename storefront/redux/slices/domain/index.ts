import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { DomainConfigType } from 'utils/Domain/Domain';
import { HYDRATE } from 'next-redux-wrapper';

const initialState = {
    url: process.env.DOMAIN_HOSTNAME_1,
    publicGraphqlEndpoint: process.env.PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_1,
    defaultLocale: 'cs',
    currencyCode: 'CZK',
    domainId: 1,
    mapSetting: {
        latitude: 49.8175,
        longitude: 15.473,
        zoom: 7,
    },
} as DomainConfigType;

export const domainSlice = createSlice({
    name: 'domain',
    initialState,
    reducers: {
        setDomain(state, action: PayloadAction<DomainConfigType>) {
            state.currencyCode = action.payload.currencyCode;
            state.url = action.payload.url;
            state.defaultLocale = action.payload.defaultLocale;
            state.publicGraphqlEndpoint = action.payload.publicGraphqlEndpoint;
            state.domainId = action.payload.domainId;
            state.mapSetting = action.payload.mapSetting;
        },
    },
    extraReducers: {
        /**
         * @see https://github.com/kirill-konshin/next-redux-wrapper#usage
         */
        [HYDRATE]: (state, action) => {
            return {
                ...state,
                ...action.payload.domain,
            };
        },
    },
});

export const domainActions = domainSlice.actions;
