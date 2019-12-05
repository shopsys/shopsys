export default class Repeater {

    constructor () {
        this.timerDelay = null;
        this.timerRepeat = null;
    }

    startAutorepeat ($input, eventString) {
        $input.trigger(eventString);
        this.stopAutorepeat();
        this.timerDelay = setTimeout(function () {
            $input.trigger(eventString);
            this.timerRepeat = setInterval(function () {
                $input.trigger(eventString);
            }, 100);
        }, 500);
    }

    stopAutorepeat () {
        clearTimeout(this.timerDelay);
        clearInterval(this.timerRepeat);
    }
};
