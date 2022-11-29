import { PaginationContext } from './context';
import { PaginationActions, PaginationState } from './types';
import { Dispatch, useContext } from 'react';

export const usePaginationContext = (): [PaginationState, Dispatch<PaginationActions>] =>
    useContext(PaginationContext)!;
