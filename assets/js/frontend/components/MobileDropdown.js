import Register from 'framework/common/utils/Register';
import Overlay from '../utils/overlay';

export default class MobileDropdown {
    constructor () {
        this.dropdownWrapper = '.js-mobile-dropdown';
        this.dropdownButton = '.js-mobile-dropdown-button';
        this.dropdownContainer = '.js-mobile-dropdown-container';
        this.isToggleFinished = true;
        this.isDropdownOpen = false;
    }

    static toggleDropdown (event, mobileDropdown) {
        if (mobileDropdown.isToggleFinished) {
            const currentContainer = $(event.currentTarget).closest(mobileDropdown.dropdownWrapper).find(mobileDropdown.dropdownContainer);
            mobileDropdown.isToggleFinished = false;
            
            event.stopPropagation(); 
            
            if (mobileDropdown.isDropdownOpen) {
                Overlay.hideOverlay();
                mobileDropdown.isDropdownOpen = !mobileDropdown.isDropdownOpen;
            } else {
                Overlay.showOverlay();
                mobileDropdown.isDropdownOpen = !mobileDropdown.isDropdownOpen;
            }

            currentContainer.slideToggle(function(){
                mobileDropdown.isToggleFinished = true;
            });
        }
    }

    static init ($container) {
        const mobileDropdown = new MobileDropdown($(this));

        $container.filterAllNodes(mobileDropdown.dropdownButton).on('click', (event) => MobileDropdown.toggleDropdown(event, mobileDropdown));

        $(document).click(function(){
            if ($(mobileDropdown.dropdownContainer).is(":visible")) {
                $(mobileDropdown.dropdownContainer).slideUp();
                Overlay.hideOverlay();
                mobileDropdown.isDropdownOpen = !mobileDropdown.isDropdownOpen;
            }
        })

        $(mobileDropdown.dropdownContainer).click(function(event){
            event.stopPropagation(); 
        });
    }
}

(new Register()).registerCallback(MobileDropdown.init, 'MobileDropdown.init');
