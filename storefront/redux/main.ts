import { contactInformationSlice } from './slices/contactInformation';
import { domainSlice } from './slices/domain';
import { userSlice } from './slices/user';
import { configureStore } from '@reduxjs/toolkit';
import { nextReduxCookieMiddleware, wrapMakeStore } from 'next-redux-cookie-wrapper';
import { createWrapper } from 'next-redux-wrapper';
import { TypedUseSelectorHook, useDispatch, useSelector } from 'react-redux';

const makeStore = wrapMakeStore(() =>
    configureStore({
        reducer: {
            domain: domainSlice.reducer,
            user: userSlice.reducer,
            contactInformation: contactInformationSlice.reducer,
        },
        middleware: (getDefaultMiddleware) =>
            getDefaultMiddleware().prepend(
                nextReduxCookieMiddleware({
                    compress: process.env.NODE_ENV !== 'development',
                    subtrees: ['cart.isCartEmpty', 'user', 'domain', 'contactInformation'],
                }),
            ),
    }),
);

/**
 * In the next couple of lines, we take the default useDispatch and useSelector hooks and type them
 * to correctly mirror our store and actions. This way we get better IDE completion and type checking.
 */
export type AppStore = ReturnType<typeof makeStore>;
export type AppState = ReturnType<AppStore['getState']>;

export const useShopsysSelector: TypedUseSelectorHook<AppState> = useSelector;
export const useShopsysDispatch = (): AppStore['dispatch'] => useDispatch<AppStore['dispatch']>();

export const nextReduxWrapper = createWrapper<AppStore>(makeStore, {
    serializeState: (state) => JSON.stringify(state),
    deserializeState: (state) => JSON.parse(state),
});
