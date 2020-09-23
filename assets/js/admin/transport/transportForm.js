import Register from 'framework/common/utils/Register';

export default class TransportForm {

    constructor ($transportForm) {
        this.$transportForm = $transportForm;
        this.$transportPackagesContainer = $transportForm.find('#transport_form_packagesGroup').closest('.wrap-divider');
        this.$transportPalletPricesContainer = $transportForm.find('#transport_form_palletPricesGroup').closest('.wrap-divider');
        this.$transportPalletPrices = $transportForm.find('.js-transport-pallet-prices');
        this.$typeInputs = $transportForm.find('#transport_form_basicInformation_type input');
        this.$transportForm.on('change', '#transport_form_basicInformation_type input', () => {
            this.setTransportPackagesVisibility();
            this.setPalletPricesVisibility();
        });

        this.setTransportPackagesVisibility();
        this.setPalletPricesVisibility();
    };

    static init ($container) {
        $container.filterAllNodes('form[name=transport_form]').each(function () {
            // eslint-disable-next-line no-new
            new TransportForm($(this));
        });
    }

    setPalletPricesVisibility () {
        if (this.$typeInputs.filter(':checked').val() === 'pallet') { // App\Model\Transport\Transport::TYPE_PALLET
            this.$transportPalletPricesContainer.show();
            this.$transportPalletPrices.trigger('reinit');
        } else {
            this.$transportPalletPricesContainer.hide();
            this.$transportPalletPrices.trigger('removeAllItems');
        }
    }

    setTransportPackagesVisibility () {
        this.$transportPackagesContainer.toggle(this.isTransportTypePackage());
    }

    isTransportTypePackage () {
        return this.$typeInputs.filter(':checked').val() === 'package'; // App\Model\Transport\Transport::TYPE_PACKAGE
    }

};

(new Register()).registerCallback(TransportForm.init, 'TransportForm.init', 100); // Priority has to be lower then priority of transportPalletPrices.js
