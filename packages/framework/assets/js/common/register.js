export default class Register {

    constructor () {

        Register.CALL_PRIORITY_NORMAL = 500;
        Register.CALL_PRIORITY_HIGH = 300;

        if (Register.instance) {
            return Register.instance;
        }

        Register.instance = this;
        this.callbackQueue = [];

        return this;
    }

    registerCallback (callback, callPriority) {
        if (callPriority === undefined) {
            callPriority = Register.CALL_PRIORITY_NORMAL;
        }

        this.callbackQueue.push({
            callback: callback,
            callPriority: callPriority
        });
    };

    registerNewContent ($container) {
        this.callbackQueue.sort(function (a, b) {
            return a.callPriority - b.callPriority;
        });

        for (let i in this.callbackQueue) {
            this.callbackQueue[i].callback($container);
        }
    };
}
