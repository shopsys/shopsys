import { createSlice, PayloadAction } from '@reduxjs/toolkit';

const initialState = {
    sort: 'PRIORITY',
};

export type SortType = 'PRIORITY' | 'PRICE_ASC' | 'PRICE_DESC';

export type PayloadType = { sort: SortType };

export const sortSlice = createSlice({
    name: 'sort',
    initialState,
    reducers: {
        setSort(state, action: PayloadAction<PayloadType>) {
            state.sort = action.payload.sort;
        },
    },
});

export const sortActions = sortSlice.actions;
