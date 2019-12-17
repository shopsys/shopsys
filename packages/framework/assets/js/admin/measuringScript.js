import Register from '../common/register';

export default class MeasuringScript {

    constructor ($container) {
        this.$embedOnlyInOrderSentPageCheckbox = $container.filterAllNodes('input[name="script_form[placement]"]');

        if (this.$embedOnlyInOrderSentPageCheckbox.length > 0) {
            this.toggleScriptVariables();
            this.$embedOnlyInOrderSentPageCheckbox.on('change', () => this.toggleScriptVariables());
        }
    }

    toggleScriptVariables () {
        const isChecked = this.$embedOnlyInOrderSentPageCheckbox.prop('checked');
        $('#js-order-sent-page-variables').toggle(isChecked);
    }

    static init ($container) {
        // eslint-disable-next-line no-new
        new MeasuringScript($container);
    }
}

(new Register()).registerCallback(MeasuringScript.init);
