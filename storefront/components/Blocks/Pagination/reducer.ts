import { PaginationActions, PaginationCallbacks, PaginationState } from './types';
import { getNewPagination } from 'helpers/pagination/getNewPagination';
import produce from 'immer';
import { Reducer } from 'react';

const setPage = (state: PaginationState, payload: PaginationCallbacks['setPage']): PaginationState => {
    return produce(state, (draft) => {
        draft.page = payload;
    });
};

const setEndCursor = (state: PaginationState, payload: PaginationCallbacks['setEndCursor']): PaginationState => {
    return produce(state, (draft) => {
        draft.endCursor = payload;
    });
};

const setPagination = (state: PaginationState, payload: PaginationCallbacks['setPagination']): PaginationState => {
    return produce(state, (draft) => {
        draft.page = payload.page;
        draft.endCursor = payload.endCursor;
    });
};

const resetPagination = (state: PaginationState): PaginationState => {
    return produce(state, (draft) => {
        const pagination = getNewPagination(1);
        draft.page = pagination.page;
        draft.endCursor = pagination.endCursor;
    });
};

export const paginationReducer: Reducer<PaginationState, PaginationActions> = (state, action): PaginationState => {
    switch (action.type) {
        case 'setPage':
            return setPage(state, action.payload);
        case 'setEndCursor':
            return setEndCursor(state, action.payload);
        case 'setPagination':
            return setPagination(state, action.payload);
        case 'resetPagination':
            return resetPagination(state);
        default:
            return state;
    }
};
