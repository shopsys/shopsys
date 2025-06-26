export default class Timeout {
    /**
     * @param {string} timeoutName
     * @param {TimerHandler} callback
     * @param {int} timeoutMilliseconds
     * @returns {void}
     */
    static setTimeoutAndClearPrevious(timeoutName, callback, timeoutMilliseconds) {
        Timeout.timeouts = Timeout.timeouts || {};

        if (typeof timeoutName !== 'string') {
            throw new Error('Timeout must have name!');
        }

        if (Object.hasOwn(Timeout.timeouts, timeoutName) === true) {
            clearTimeout(Timeout.timeouts[timeoutName]);
        }

        Timeout.timeouts[timeoutName] = setTimeout(callback, timeoutMilliseconds);
    }
}
