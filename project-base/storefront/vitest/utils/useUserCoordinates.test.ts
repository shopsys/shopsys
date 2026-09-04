import { renderHook } from '@testing-library/react';
import { useUserCoordinates } from 'utils/useUserCoordinates';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

const testState = vi.hoisted(() => ({
    coordinates: null as { latitude: number; longitude: number } | null,
    updateCoordinates: vi.fn(),
}));

vi.mock('store/useSessionStore', () => ({
    useSessionStore: (selector: (state: unknown) => unknown) =>
        selector({
            coordinates: testState.coordinates,
            updateCoordinates: testState.updateCoordinates,
        }),
}));

const originalGeolocation = navigator.geolocation;
const getCurrentPosition = vi.fn((success: PositionCallback) => {
    success({ coords: { latitude: 50.087, longitude: 14.421 } } as GeolocationPosition);
});

describe('useUserCoordinates', () => {
    beforeEach(() => {
        testState.coordinates = null;
        testState.updateCoordinates.mockClear();
        getCurrentPosition.mockClear();

        Object.defineProperty(navigator, 'geolocation', {
            configurable: true,
            value: { getCurrentPosition },
        });
    });

    afterEach(() => {
        Object.defineProperty(navigator, 'geolocation', {
            configurable: true,
            value: originalGeolocation,
        });
    });

    test('asks the browser once and stores the coordinates in the session when they are unknown', () => {
        const { result } = renderHook(() => useUserCoordinates());

        expect(result.current).toBeNull();
        expect(getCurrentPosition).toHaveBeenCalledTimes(1);
        expect(getCurrentPosition).toHaveBeenCalledWith(expect.any(Function), undefined, {
            maximumAge: 300000,
            timeout: 10000,
        });
        expect(testState.updateCoordinates).toHaveBeenCalledWith({ latitude: 50.087, longitude: 14.421 });
    });

    test('returns the session coordinates without asking the browser again', () => {
        testState.coordinates = { latitude: 49.2, longitude: 16.6 };

        const { result } = renderHook(() => useUserCoordinates());

        expect(result.current).toEqual({ latitude: 49.2, longitude: 16.6 });
        expect(getCurrentPosition).not.toHaveBeenCalled();
    });

    test('stays unknown when the browser has no geolocation', () => {
        Object.defineProperty(navigator, 'geolocation', { configurable: true, value: undefined });

        const { result } = renderHook(() => useUserCoordinates());

        expect(result.current).toBeNull();
        expect(testState.updateCoordinates).not.toHaveBeenCalled();
    });
});
