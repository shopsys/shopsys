import Register from 'framework/common/utils/Register';
import Overlay from '../utils/overlay';

export default class MobileDropdown {
    constructor ($dropdownButton, overlay) {
        this.$dropdownButton = $dropdownButton;
        this.$dropdownWrapper = $dropdownButton.closest('.js-mobile-dropdown');
        this.$dropdownContainer = this.$dropdownWrapper.find('.js-mobile-dropdown-container');
        this.overlay = overlay;
        this.isToggleFinished = true;

        this.$dropdownButton.on('click', (event) => this.toggleDropdown(event));

        $(document).click(() => this.close());

        $(this.$dropdownContainer).click(function (event) {
            event.stopPropagation();
        });
    }

    toggleDropdown (event) {
        if (this.isToggleFinished) {
            this.isToggleFinished = false;
            event.stopPropagation();

            if (this.isDropdownOpen()) {
                this.overlay.hideOverlay();
            } else {
                this.overlay.showOverlay();
            }

            this.$dropdownContainer.slideToggle(() => this.afterToggle());
        }
    }

    afterToggle () {
        this.isToggleFinished = true;
    }

    isDropdownOpen () {
        return this.$dropdownContainer.is(':visible');
    }

    close () {
        if (this.isDropdownOpen()) {
            this.$dropdownContainer.slideUp();
            this.overlay.hideOverlay();
        }
    }

    static init ($container) {
        const overlay = new Overlay();

        $container.filterAllNodes('.js-mobile-dropdown-button').each(function () {
            // eslint-disable-next-line no-new
            new MobileDropdown($(this), overlay);
        });
    }
}

(new Register()).registerCallback(MobileDropdown.init, 'MobileDropdown.init');
