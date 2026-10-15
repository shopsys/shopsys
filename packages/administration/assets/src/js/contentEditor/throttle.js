/**
 * Calls the callback at once and then at most once per wait, with the latest call that came in the meantime,
 * so the last change is never lost.
 *
 * @param {() => void} callback
 * @param {number} wait milliseconds
 * @returns {{ (): void, cancel: () => void }}
 */
export default function throttle(callback, wait) {
    let timer = null;
    let isCallWaiting = false;

    const run = () => {
        callback();
        timer = setTimeout(() => {
            timer = null;

            if (isCallWaiting) {
                isCallWaiting = false;
                run();
            }
        }, wait);
    };

    const throttled = () => {
        if (timer === null) {
            run();
        } else {
            isCallWaiting = true;
        }
    };

    throttled.cancel = () => {
        clearTimeout(timer);
        timer = null;
        isCallWaiting = false;
    };

    return throttled;
}
