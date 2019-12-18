import constant from './constant';
import Window from './window';
import Register from '../common/register';
import Translator from 'bazinga-translator';

export default class MassActionConfirm {

    static init ($container) {

        MassActionConfirm.isConfirmed = MassActionConfirm.isConfirmed || false;

        $container.filterAllNodes('.js-mass-action-submit').click((event) => {
            const $button = $(event.currentTarget);
            if (!MassActionConfirm.isConfirmed) {
                const action = $('.js-mass-action-value option:selected').text().toLowerCase();
                const selectType = $('.js-mass-action-select-type').val();
                let count;
                switch (selectType) {
                    case constant('\\Shopsys\\FrameworkBundle\\Model\\Product\\MassAction\\ProductMassActionData::SELECT_TYPE_CHECKED'):
                        count = $('.js-grid-mass-action-select-row:checked').length;
                        break;
                    case constant('\\Shopsys\\FrameworkBundle\\Model\\Product\\MassAction\\ProductMassActionData::SELECT_TYPE_ALL_RESULTS'):
                        count = $('.js-grid').data('total-count');
                        break;
                }

                // eslint-disable-next-line no-new
                new Window({
                    content: Translator.trans('Do you really want to %action% %count% product?', { 'action': action, 'count': count }),
                    buttonCancel: true,
                    buttonContinue: true,
                    eventContinue: () => {
                        MassActionConfirm.isConfirmed = true;
                        $button.trigger('click');
                    }
                });

                return false;
            }
        });

    }

}

(new Register()).registerCallback(MassActionConfirm.init);
