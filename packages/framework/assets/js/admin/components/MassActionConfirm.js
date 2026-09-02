import $ from 'jquery';
import ConfirmWindow from '@shopsys/administration/src/js/utils/confirmWindow';
import Translator from 'bazinga-translator';
import Register from '../../common/utils/Register';

export default class MassActionConfirm {
    static init($container) {
        MassActionConfirm.isConfirmed = MassActionConfirm.isConfirmed || false;

        $container.filterAllNodes('.js-mass-action-submit').click(event => {
            const $button = $(event.currentTarget);
            if (!MassActionConfirm.isConfirmed) {
                const action = $('.js-mass-action-value option:selected').text().toLowerCase();
                const selectType = $('.js-mass-action-select-type').val();
                let count;
                switch (selectType) {
                    case 'selectTypeChecked':
                        count = $('.js-grid-mass-action-select-row:checked').length;
                        break;
                    case 'selectTypeAllResults':
                        count = $('.js-grid').data('total-count');
                        break;
                }

                ConfirmWindow.show({
                    content: Translator.trans('Do you really want to %action% %count% product?', {
                        action: action,
                        count: count,
                    }),
                    style: 'warning',
                    continueEvent: () => {
                        MassActionConfirm.isConfirmed = true;
                        $button.trigger('click');
                    },
                });

                return false;
            }
        });
    }
}

new Register().registerCallback(MassActionConfirm.init, 'MassActionConfirm.init');
