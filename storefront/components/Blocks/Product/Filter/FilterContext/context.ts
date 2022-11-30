import { FilterActions, FilterState } from './types';
import { createContext, Dispatch } from 'react';

export const FilterContext = createContext<[FilterState, Dispatch<FilterActions>] | undefined>(undefined);
