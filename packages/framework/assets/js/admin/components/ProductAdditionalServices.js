import Translator from 'bazinga-translator';
import Register from '../../common/utils/Register';

const PRIORITY_AFTER_INIT_COMPONENTS = 600;
const CUSTOM_LABEL_MAX_LENGTH = 100;
const CUSTOM_LABEL_SEPARATOR_LENGTH = 1;

function initProductAdditionalServices($container) {
    $container.filterAllNodes('select[data-js-additional-services-feed-name-lengths]').each(function () {
        const $select = $(this);
        const feedNameLengthsByServiceId = $select.data('js-additional-services-feed-name-lengths');
        const $warning = $('<p class="form-text mb-0 text-danger"></p>')
            .text(
                Translator.trans(
                    'Only 100 characters will be exported to the Google feed custom label, so not all chosen services may appear in it.',
                ),
            )
            .hide();

        const $tomSelectWrapper = $select.next('.ts-wrapper');
        ($tomSelectWrapper.length ? $tomSelectWrapper : $select).after($warning);

        const updateWarning = selectedServiceIds => {
            let feedCustomLabelLength = 0;

            for (const serviceId of selectedServiceIds) {
                const feedNameLength = feedNameLengthsByServiceId[serviceId];

                if (typeof feedNameLength !== 'number') {
                    continue;
                }

                feedCustomLabelLength +=
                    feedNameLength + (feedCustomLabelLength > 0 ? CUSTOM_LABEL_SEPARATOR_LENGTH : 0);
            }

            $warning.toggle(feedCustomLabelLength > CUSTOM_LABEL_MAX_LENGTH);
        };

        if (this.tomselect) {
            this.tomselect.on('change', () => updateWarning(this.tomselect.getValue()));
            updateWarning(this.tomselect.getValue());
        } else {
            const readSelectedServiceIds = () => updateWarning($select.val() || []);
            $select.on('change', readSelectedServiceIds);
            readSelectedServiceIds();
        }
    });
}

new Register().registerCallback(
    initProductAdditionalServices,
    'initProductAdditionalServices',
    PRIORITY_AFTER_INIT_COMPONENTS,
);
