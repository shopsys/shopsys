import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { HYDRATE } from 'next-redux-wrapper';
import { FilterOptionsParameterStateType, FilterOptionsStateType } from 'types/productFilter';

export const initialState = {
    brands: [],
    flags: [],
    parameters: [],
    onlyInStock: false,
    minimalPrice: null,
    maximalPrice: null,
} as FilterOptionsStateType;

export const optionsFilterSlice = createSlice({
    name: 'optionsFilter',
    initialState,
    reducers: {
        setOptionsFilter(state, action: PayloadAction<FilterOptionsStateType>) {
            state.brands = action.payload.brands;
            state.flags = action.payload.flags;
            state.parameters = action.payload.parameters;
            state.onlyInStock = action.payload.onlyInStock;
            state.minimalPrice = action.payload.minimalPrice;
            state.maximalPrice = action.payload.maximalPrice;
        },
        setBrandsFilter(state, action: PayloadAction<string[]>) {
            state.brands = action.payload;
        },
        setFlagsFilter(state, action: PayloadAction<string[]>) {
            state.flags = action.payload;
        },
        setParametersFilter(state, action: PayloadAction<FilterOptionsParameterStateType[]>) {
            state.parameters = action.payload;
        },
        setOnlyInStockFilter(state, action: PayloadAction<boolean>) {
            state.onlyInStock = action.payload;
        },
        setMinimalPriceFilter(state, action: PayloadAction<number | null>) {
            state.minimalPrice = action.payload;
        },
        setMaximalPriceFilter(state, action: PayloadAction<number | null>) {
            state.maximalPrice = action.payload;
        },
    },
    extraReducers: {
        /**
         * @see https://github.com/kirill-konshin/next-redux-wrapper#usage
         */
        [HYDRATE]: (state, action) => {
            return {
                ...state,
                ...action.payload.optionsFilter,
            };
        },
    },
});

export const optionsFilterActions = optionsFilterSlice.actions;
