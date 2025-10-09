import Register from '../../common/utils/Register';

export default class SelectToggle {
    constructor($container) {
        const $selects = $container.filterAllNodes('[data-js-toggle-opt-group-control]');

        if ($selects.length > 0) {
            $selects.each((_index, select) => {
                const $select = $(select);
                const $control = $($select.data('js-toggle-opt-group-control'));

                if ($control.length > 0) {
                    this.toggleOptgroupOnControlChange($select, $control);
                }
            });
        }
    }

    toggleOptgroupOnControlChange($select, $control) {
        $control.on('change', event => {
            this.showOnlyOptionsFromDomain($select, event.target.value);
        });

        this.showOnlyOptionsFromDomain($select, $control.val());
    }

    showOnlyOptionsFromDomain($select, domainId) {
        $select.find('option[data-js-toggle-option]').prop('disabled', true);
        $select.find(`option[data-js-toggle-option=${domainId}]`).prop('disabled', false);

        const $firstEnabled = $select.find(`option[data-js-toggle-option=${domainId}]:not(:disabled)`).first();

        if ($firstEnabled.length > 0 && $firstEnabled.val() !== '') {
            $select.val($firstEnabled.val()).trigger('change');
        }

        const tomselectInstance = $select[0].tomselect;

        if (tomselectInstance) {
            tomselectInstance.destroy();

            new Register().registerNewContent($select);
        }
    }

    static init($container) {
        // eslint-disable-next-line no-new
        new SelectToggle($container);
    }
}

new Register().registerCallback(SelectToggle.init, 'SelectToggle.init');
