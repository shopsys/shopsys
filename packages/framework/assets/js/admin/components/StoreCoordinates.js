import Ajax from '../../common/utils/Ajax';
import Register from '../../common/utils/Register';

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
                country: $form.find('.js-store-address-country').val(),
            },
        });
    }

    static init($container) {
        // eslint-disable-next-line no-new
        new StoreCoordinates($container);
    }
}

new Register().registerCallback(StoreCoordinates.init, 'StoreCoordinates.init');
