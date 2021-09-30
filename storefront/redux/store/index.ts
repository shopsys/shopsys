import { TypedUseSelectorHook, useDispatch, useSelector } from 'react-redux';
import { configureStore } from '@reduxjs/toolkit';
import { createWrapper } from 'next-redux-wrapper';
import { domainSlice } from './DomainStore';
import { userSlice } from './UserStore';

const makeStore = () =>
    configureStore({
        reducer: {
            domain: domainSlice.reducer,
            user: userSlice.reducer,
        },
    });

/**
 * In the next 5 lines, we take the default useDispatch and useSelector hooks and type them
 * to correctly mirror our store and actions. This way we get better IDE completion and type checking.
 */
export type AppStore = ReturnType<typeof makeStore>;
export type AppState = ReturnType<AppStore['getState']>;
export const useShopsysSelector: TypedUseSelectorHook<AppState> = useSelector;
export const useShopsysDispatch = (): AppStore['dispatch'] => useDispatch<AppStore['dispatch']>();

export const nextReduxWrapper = createWrapper<AppStore>(makeStore);
