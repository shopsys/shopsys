import SymfonyToolbarSupport from './symfonyToolbarSupport';
import Register from '../common/register';

export default class FixedBar {

    static onSymfonyToolbarShow () {
        $('.window-fixed-bar').addClass('window-fixed-bar--developer-mode');
    };

    static onSymfonyToolbarHide () {
        $('.window-fixed-bar').removeClass('window-fixed-bar--developer-mode');
    };

    static init () {
        SymfonyToolbarSupport.registerOnToolbarShow(FixedBar.onSymfonyToolbarShow);
        SymfonyToolbarSupport.registerOnToolbarHide(FixedBar.onSymfonyToolbarHide);
    }
}

(new Register()).registerCallback(FixedBar.init);
