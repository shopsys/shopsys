import { PaginationActions, PaginationState } from './types';
import { createContext, Dispatch } from 'react';

export const PaginationContext = createContext<[PaginationState, Dispatch<PaginationActions>] | undefined>(undefined);
