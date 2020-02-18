import SymfonyToolbarSupport from './SymfonyToolbarSupport';
import Register from '../../common/utils/Register';

export default class FixedBar {

    static onSymfonyToolbarShow () {
        $('.window-fixed-bar').addClass('window-fixed-bar--developer-mode');
    }

    static onSymfonyToolbarHide () {
        $('.window-fixed-bar').removeClass('window-fixed-bar--developer-mode');
    }

    static init () {
        SymfonyToolbarSupport.registerOnToolbarShow(FixedBar.onSymfonyToolbarShow);
        SymfonyToolbarSupport.registerOnToolbarHide(FixedBar.onSymfonyToolbarHide);

        // condition copied from: vendor/symfony/symfony/src/Symfony/Bundle/WebProfilerBundle/Resources/views/Profiler/toolbar_js.html.twig
        if (typeof Sfjs !== 'undefined' && Sfjs.getPreference('toolbar/displayState') !== 'none') {
            SymfonyToolbarSupport.notifyOnToolbarShow();
        }
    }
}

(new Register()).registerCallback(FixedBar.init);
