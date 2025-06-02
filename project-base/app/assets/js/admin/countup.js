import { CountUp } from 'countup.js';

// expose CountUp to the global object as tabler.js expects it
window.countUp = {
    CountUp: CountUp
};
