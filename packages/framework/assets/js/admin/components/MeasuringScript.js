import Register from '../../common/utils/Register';

export default class MeasuringScript {

    constructor ($container) {
        const $placementSelectBox = $container.filterAllNodes('.js-measure-script-placement-choice');

        this.toggleMessageInfo($placementSelectBox.val(), $container);

        $placementSelectBox.on('change', event => {
            this.toggleMessageInfo($(event.target).val(), $container);
        });
    }

    toggleMessageInfo (value, $container) {
        $container.filterAllNodes('.js-script-placement-info').hide();
        $container.filterAllNodes('.js-script-placement-info-' + value).show();
    }

    static init ($container) {
        // eslint-disable-next-line no-new
        new MeasuringScript($container);
    }
}

(new Register()).registerCallback(MeasuringScript.init, 'MeasuringScript.init');
