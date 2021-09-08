import { TypedUseSelectorHook, useDispatch, useSelector } from 'react-redux';
import { configureStore } from '@reduxjs/toolkit';
import { domainSlice } from './DomainStore';
import { popupSlice } from './PopupStore';
import { userSlice } from './UserStore';

const store = configureStore({
    reducer: {
        popup: popupSlice.reducer,
        domain: domainSlice.reducer,
        user: userSlice.reducer,
    },
});

/**
 * In the next 5 lines, we take the default useDispatch and useSelector hooks and type them
 * to correctly mirror our store and actions. This way we get better IDE completion and type checking.
 */
export type AppDispatch = typeof store.dispatch;
export type RootState = ReturnType<typeof store.getState>;

export const useShopsysDispatch = () => useDispatch<AppDispatch>();
export const useShopsysSelector: TypedUseSelectorHook<RootState> = useSelector;

export default store;
