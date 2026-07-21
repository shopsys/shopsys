import ModalWindow from '@shopsys/administration/src/js/utils/modalWindow';
import Translator from 'bazinga-translator';
import Check from 'icons/tabler/check.svg';
import Ajax from '../../common/utils/Ajax';
import Register from '../../common/utils/Register';
import FormChangeInfo from './FormChangeInfo';

export default class StoreCoordinates {
    constructor($container) {
        this.$loadCoordinatesButtons = $container.filterAllNodes('.js-load-store-coordinates');
        this.$loadCoordinatesButtons
            .off('click.storeCoordinates')
            .on('click.storeCoordinates', this.handleLoadCoordinatesClick.bind(this));
    }

    handleLoadCoordinatesClick(event) {
        event.preventDefault();

        const $button = $(event.currentTarget);
        const $form = $button.closest('form');

        Ajax.ajax({
            url: $button.data('load-coordinates-url'),
            method: 'POST',
            loaderElement: $button,
            data: {
                street: $form.find('.js-store-address-street').val(),
                city: $form.find('.js-store-address-city').val(),
                postcode: $form.find('.js-store-address-postcode').val(),
                countryCode: $form.find('.js-store-address-country option:selected').data('country-code'),
            },
            success: data => {
                if (data.latitude === undefined || data.longitude === undefined) {
                    new ModalWindow({
                        content: Translator.trans('Coordinates could not be loaded for the entered address.'),
                        style: 'danger',
                    });
                    return;
                }

                $form.find('.js-store-coordinate-latitude').val(data.latitude);
                $form.find('.js-store-coordinate-longitude').val(data.longitude);
                FormChangeInfo.showInfo();
                this.showSuccessState($button);
            },
        });
    }

    showSuccessState($button) {
        const originalHtml = $button.html();
        $button
            .removeClass('btn-primary')
            .addClass('btn-success')
            .html(`<span class="icon-wrapper">${Check}</span> ${Translator.trans('Coordinates loaded')}`);

        setTimeout(() => {
            $button.removeClass('btn-success').addClass('btn-primary').html(originalHtml);
        }, 3000);
    }

    static init($container) {
        // eslint-disable-next-line no-new
        new StoreCoordinates($container);
    }
}

new Register().registerCallback(StoreCoordinates.init, 'StoreCoordinates.init');
