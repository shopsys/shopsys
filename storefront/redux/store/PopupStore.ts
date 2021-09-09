import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { PopupContentType } from 'components/Layout/Popup/Popup';

interface IinitialState {
    isPopupShown: boolean;
    popupContent: undefined | PopupContentType;
}

const initialState = {
    isPopupShown: false,
    popupContent: undefined,
} as IinitialState;

export const popupSlice = createSlice({
    name: 'popup',
    initialState,
    reducers: {
        showPopup(state, action: PayloadAction<PopupContentType>) {
            state.popupContent = action.payload;
            state.isPopupShown = true;
        },
        hidePopup(state) {
            state.isPopupShown = false;
            state.popupContent = undefined;
        },
    },
});

export const popupActions = popupSlice.actions;
