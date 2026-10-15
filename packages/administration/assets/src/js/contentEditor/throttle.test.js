import throttle from './throttle';

describe('throttle', () => {
    beforeEach(() => jest.useFakeTimers());
    afterEach(() => jest.useRealTimers());

    test('calls at once and then only once per wait with the last call', () => {
        const callback = jest.fn();
        const throttled = throttle(callback, 300);

        throttled();
        throttled();
        throttled();
        expect(callback).toHaveBeenCalledTimes(1);

        jest.advanceTimersByTime(300);
        expect(callback).toHaveBeenCalledTimes(2);

        jest.advanceTimersByTime(300);
        expect(callback).toHaveBeenCalledTimes(2);
    });

    test('calls again at once after a quiet period', () => {
        const callback = jest.fn();
        const throttled = throttle(callback, 300);

        throttled();
        jest.advanceTimersByTime(300);
        throttled();

        expect(callback).toHaveBeenCalledTimes(2);
    });

    test('cancel drops the waiting call', () => {
        const callback = jest.fn();
        const throttled = throttle(callback, 300);

        throttled();
        throttled();
        throttled.cancel();
        jest.advanceTimersByTime(1000);

        expect(callback).toHaveBeenCalledTimes(1);
    });
});
