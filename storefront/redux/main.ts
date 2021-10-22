import { TypedUseSelectorHook, useDispatch, useSelector } from 'react-redux';
import { cartInputSlice } from './slices/cartInput';
import { configureStore } from '@reduxjs/toolkit';
import { contactInformationSlice } from './slices/contactInformation';
import { createWrapper } from 'next-redux-wrapper';
import { domainSlice } from './slices/domain';
import { userSlice } from './slices/user';

const makeStore = () =>
    configureStore({
        reducer: {
            domain: domainSlice.reducer,
            user: userSlice.reducer,
            cartInput: cartInputSlice.reducer,
            contactInformation: contactInformationSlice.reducer,
        },
    });

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
