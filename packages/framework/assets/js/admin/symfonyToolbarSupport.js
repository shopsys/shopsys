import Register from '../common/register';

export default class SymfonyToolbarSupport {

    static registerOnToolbarShow (callback) {
        SymfonyToolbarSupport.onToolbarShowCallbacks.push(callback);
    };

    static registerOnToolbarHide (callback) {
        SymfonyToolbarSupport.onToolbarHideCallbacks.push(callback);
    };

    static notifyOnToolbarShow () {
        for (let i in SymfonyToolbarSupport.onToolbarShowCallbacks) {
            const callback = SymfonyToolbarSupport.onToolbarShowCallbacks[i];
            callback.call();
        }
    };

    static notifyOnToolbarHide () {
        for (let i in SymfonyToolbarSupport.onToolbarHideCallbacks) {
            const callback = SymfonyToolbarSupport.onToolbarHideCallbacks[i];
            callback.call();
        }
    };

    static init () {
        $('.sf-toolbar').off('click', '[id^="sfMiniToolbar-"] > a');
        $('.sf-toolbar').off('click', '[id^="sfToolbarMainContent-"] > a.hide-button');

        $('.sf-toolbar').on('click', '[id^="sfMiniToolbar-"] > a', () => {
            SymfonyToolbarSupport.notifyOnToolbarShow();
        });

        $('.sf-toolbar').on('click', '[id^="sfToolbarMainContent-"] > a.hide-button', () => {
            SymfonyToolbarSupport.notifyOnToolbarHide();
        });

        // condition copied from: vendor/symfony/symfony/src/Symfony/Bundle/WebProfilerBundle/Resources/views/Profiler/toolbar_js.html.twig
        if (typeof Sfjs !== 'undefined' && Sfjs.getPreference('toolbar/displayState') !== 'none') {
            SymfonyToolbarSupport.notifyOnToolbarShow();
        }
    }
}

SymfonyToolbarSupport.onToolbarShowCallbacks = SymfonyToolbarSupport.onToolbarShowCallbacks || [];
SymfonyToolbarSupport.onToolbarHideCallbacks = SymfonyToolbarSupport.onToolbarHideCallbacks || [];

(new Register()).registerCallback(SymfonyToolbarSupport.init);
