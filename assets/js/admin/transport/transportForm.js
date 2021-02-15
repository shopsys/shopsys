import Register from 'framework/common/utils/Register';

export default class TransportForm {

    constructor ($transportForm) {
        this.$transportForm = $transportForm;
        this.$transportPackagesContainer = $transportForm.find('#transport_form_packagesGroup').closest('.wrap-divider');
        this.$typeInputs = $transportForm.find('#transport_form_basicInformation_type input');
        this.$transportForm.on('change', '#transport_form_basicInformation_type input', () => { this.setTransportPackagesVisibility(); });

        this.setTransportPackagesVisibility();
    };

    static init ($container) {
        $container.filterAllNodes('form[name=transport_form]').each(function () {
            // eslint-disable-next-line no-new
            new TransportForm($(this));
        });
    }

    setTransportPackagesVisibility () {
        this.$transportPackagesContainer.toggle(this.isTransportTypePackage());
    }

    isTransportTypePackage () {
        return this.$typeInputs.filter(':checked').val() === 'package'; // App\Model\Transport\Transport::TYPE_PACKAGE
    }

};

(new Register()).registerCallback(TransportForm.init, 'TransportForm.init');
