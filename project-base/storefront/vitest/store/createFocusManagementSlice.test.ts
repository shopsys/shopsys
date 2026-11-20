import { createFocusManagementSlice } from 'store/slices/createFocusManagementSlice';
import { afterEach, describe, expect, test } from 'vitest';
import { create } from 'zustand';

afterEach(() => {
    document.body.replaceChildren();
});

describe('popup return focus', () => {
    test('keeps the original trigger when it can still receive focus', () => {
        const store = create(createFocusManagementSlice);
        const original = document.createElement('button');
        const alternative = document.createElement('button');
        alternative.name = 'account-login';
        document.body.append(original, alternative);
        original.focus();
        store.getState().storeCurrentFocus('button[name="account-login"]');
        original.blur();

        store.getState().restoreStoredFocus();

        expect(original).toHaveFocus();
    });

    test.each(['removed', 'disabled'])('uses an available account trigger when the original is %s', (state) => {
        const store = create(createFocusManagementSlice);
        const original = document.createElement('button');
        const unavailable = document.createElement('button');
        const alternative = document.createElement('button');
        unavailable.name = alternative.name = 'account-login';
        unavailable.disabled = true;
        document.body.append(original, unavailable, alternative);
        original.focus();
        store.getState().storeCurrentFocus('button[name="account-login"]');
        original.blur();
        if (state === 'removed') {
            original.remove();
        } else {
            original.disabled = true;
        }

        store.getState().restoreStoredFocus();

        expect(alternative).toHaveFocus();
    });

    test('clears the fallback after restoring focus or cancelling restoration', () => {
        const store = create(createFocusManagementSlice);
        const original = document.createElement('button');
        const alternative = document.createElement('button');
        alternative.name = 'account-login';
        document.body.append(original, alternative);
        original.focus();
        store.getState().storeCurrentFocus('button[name="account-login"]');
        original.remove();
        store.getState().restoreStoredFocus();
        alternative.blur();

        store.getState().restoreStoredFocus();

        expect(alternative).not.toHaveFocus();

        alternative.focus();
        store.getState().storeCurrentFocus('button[name="account-login"]');
        store.getState().clearStoredFocus();
        alternative.blur();

        store.getState().restoreStoredFocus();

        expect(alternative).not.toHaveFocus();
    });
});
