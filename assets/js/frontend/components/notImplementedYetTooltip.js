import Register from 'framework/common/utils/register';
import Translator from 'bazinga-translator';

export default class notImplementedYetTooltip {

    static init ($container) {
        $container.filterAllNodes('.js-not-implemented-yet')
            .attr('title', Translator.trans('Ještě nebylo implementováno.'))
            .tooltip();
    }

}

(new Register()).registerCallback(notImplementedYetTooltip.init);
