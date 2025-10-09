import autosize from 'autosize';
import { CountUp } from 'countup.js';

// expose modules to the global Window object as tabler.js expects them to be available globally
window.countUp = {
    CountUp: CountUp,
};

window.autosize = autosize;
