import Register from 'framework/common/utils/Register';

export default class MobileDropdown {
    constructor () {
        this.dropdownWrapper = '.js-mobile-dropdown';
        this.dropdownButton = '.js-mobile-dropdown-button';
        this.dropdownContainer = '.js-mobile-dropdown-container';
        this.overlay = '.web__overlay';
        this.isToggleFinished = true;
    }

    static toggleDropdown (event, mobileDropdown) {
        if (mobileDropdown.isToggleFinished) {
            const currentContainer = $(event.currentTarget).closest(mobileDropdown.dropdownWrapper).find(mobileDropdown.dropdownContainer);
            mobileDropdown.isToggleFinished = false;
            
            event.stopPropagation(); 
            $(mobileDropdown.overlay).fadeToggle();
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
                $(mobileDropdown.overlay).fadeOut();
            }
        })

        $(mobileDropdown.dropdownContainer).click(function(e){
            e.stopPropagation(); 
        });
    }
}

(new Register()).registerCallback(MobileDropdown.init, 'MobileDropdown.init');
