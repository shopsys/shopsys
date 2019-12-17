import $ from 'jquery';
import 'waypoints/lib/jquery.waypoints';
import 'jquery.counterup/jquery.counterup';
import Register from '../../common/register';

export default class CounterUp {

    static init ($container) {
        window.jQuery = window.$ = $;
        $container.filterAllNodes("[data-counter='counterup']").counterUp({
            delay: 10,
            time: 1000
        });
    }

}

(new Register().registerCallback(CounterUp.init));
