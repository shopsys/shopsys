import 'codemirror';
/**
 * Script used for the Styleguide structure only
 */

// eslint-disable-next-line no-global-assign
if (typeof window !== 'undefined') global = window;

var StyleguideIndex = {
    init: function () {
        this.$body = $('html, body');
        this.$breakpointsLinks = $('.styleguide-header__breakpoints__item__link');
        this.$sidebarContent = $('.styleguide-sidebar__content');
        this.$sidebarLinks = $('.styleguide-sidebar__nav__item__link');
        this.$sidebarToggle = $('[class*="styleguide-sidebar__toggle"]');
        this.$iframe = $('.styleguide-iframe-content');
        this.$iframeContent = $('.styleguide-iframe-wrapper').contents();

        this.sidebarOpenedClass = 'opened';
        this.sidebarActiveLinkClass = 'active';
        this.sidebarLinkWasClickedClass = 'side-menu-clicked';

        this.sidebarSetup();
        this.checkHashOnLoad();
        this.events();
    },

    /**
   * All events of the structure
   * should be registered here
   */
    events: function () {
        var _this = this;

        this.$breakpointsLinks.click(function () {
            _this.resizeContent($(this));
        });

        this.$sidebarLinks.click(function (e) {
            e.preventDefault();
            _this.setActiveSidebarLinkOnClick($(this));
            _this.navigateToAnchor($(this));
        });

        this.$sidebarToggle.click(function (e) {
            e.preventDefault();
            _this.$body.toggleClass(_this.sidebarOpenedClass);
        });

        $(window).resize(function () {
            _this.sidebarResizeHandler();
        });
    },

    /**
   * Resize iframe based on
   * the breakpoint choice
   */
    resizeContent: function ($elem) {
        var sizeLabel = $elem.data('size-label');
        var size = sizeLabel === 'full' ? $elem.data('size') : parseInt($elem.data('size').replace('px', ''), 10);

        this.$iframe.width(size);
    },

    /**
   * Check URL hash and navigate
   * to the respective module
   */
    checkHashOnLoad: function () {

        // Shutdown this feature in Chrome.
        // Chrome have a know issue with file protocol and iframe comunication.
        // It is not supported so we should not raise errors.
        if (window.location.hash === '#' || window.location.hash === '') return false;

        var top = this.$iframeContent.find('section' + window.location.hash.replace('!', '')).offset().top;
        if (this.$iframeContent.find('section' + window.location.hash.replace('!', '')).index() === 0) {
            $(window).on('load', function () {
                this.$body.animate({ scrollTop: 0 }, 500);
            });
        } else {
            this.$body.animate({ scrollTop: top - 50 }, 0);
        }
    },

    /**
   * Navigate to module on
   * sidebar links click
   */
    navigateToAnchor: function ($elem) {

        // Shutdown this feature in Chrome.
        // Chrome have a know issue with file protocol and iframe comunication.
        // It is not supported so we should not raise errors.
        var top = this.$iframeContent.find('section' + $elem.attr('href')).offset().top - 50;

        // Use ! to prevent de default browser behavior of anchor navigation
        window.location.hash = '!' + $elem.attr('href').replace('#', '');

        this.$body.animate({ scrollTop: top }, 800);
    },
    sidebarOpen: function () {
        this.$body.addClass('opened');
    },
    sidebarClose: function () {
        this.$body.removeClass('opened');
    },

    /**
   * Leave or close the sidebar
   * if the window is small
   */
    sidebarResizeHandler: function () {
        if ($(window).width() <= 1220) {
            this.sidebarClose();
        } else {
            this.sidebarOpen();
        }
    },
    sidebarSetup: function () {

        // Shutdown this feature in Chrome.
        // Chrome have a know issue with file protocol and iframe comunication.
        // It is not supported so we should not raise errors.

        if ($(window).width() >= 1220) {
            this.sidebarOpen();
        }
    },
    setActiveSidebarLinkOnClick: function ($elem) {
        this.$body.addClass(this.sidebarLinkWasClickedClass);
        this.$sidebarLinks.removeClass(this.sidebarActiveLinkClass);
        $elem.addClass(this.sidebarActiveLinkClass);
    }
};

$(window).on('load', function () {
    StyleguideIndex.init();

    var textareas = document.querySelectorAll('.codemirror-html');
    for (var i = 0; i < textareas.length; i++) {
        CodeMirror.fromTextArea(textareas[i], {
            lineNumbers: true,
            theme: 'hopscotch',
            mode: 'htmlmixed',
            scrollbarStyle: 'simple',
            extraKeys: { 'Ctrl-Space': 'autocomplete' }
        });
    }

    // editor css
    var textareas = document.querySelectorAll('.codemirror-css');
    for (var i = 0; i < textareas.length; i++) {
        CodeMirror.fromTextArea(textareas[i], {
            lineNumbers: true,
            theme: 'hopscotch',
            mode: 'css',
            scrollbarStyle: 'simple',
            extraKeys: { 'Ctrl-Space': 'autocomplete' }
        });
    }

    // editor js
    var textareas = document.querySelectorAll('.codemirror-js');
    for (var i = 0; i < textareas.length; i++) {
        CodeMirror.fromTextArea(textareas[i], {
            lineNumbers: true,
            theme: 'hopscotch',
            mode: 'javascript',
            scrollbarStyle: 'simple',
            extraKeys: { 'Ctrl-Space': 'autocomplete' }
        });
    }
});
