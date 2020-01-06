import counterUp from 'counterup2';
import Register from '../../common/utils/register';

export default class CounterUp {

    static init () {
        document.querySelectorAll('.js-counter').forEach(counterItem => {
            counterUp(counterItem, {
                duration: 1000,
                delay: 10
            });
        });
    }

}

(new Register().registerCallback(CounterUp.init));
