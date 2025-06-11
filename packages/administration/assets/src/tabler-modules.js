import { CountUp } from 'countup.js';
import autosize from 'autosize';

// expose modules to the global Window object as tabler.js expects them to be available globally
window.countUp = {
    CountUp: CountUp
};

window.autosize = autosize;
