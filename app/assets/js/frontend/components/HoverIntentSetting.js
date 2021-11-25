export default class HoverIntentSetting {

    constructor ($hoverIntentParent) {
        this.interval = 200;
        this.timeout = 200;
        this.classForOpen = 'open';
        this.forceClick = false;
        this.forceClickElement = '';
        this.linkOnMobile = false;
        this.$selector = $hoverIntentParent;

        if ($hoverIntentParent.data('hover-intent-interval')) {
            this.interval = parseInt($hoverIntentParent.data('hover-intent-interval'));
        }

        if ($hoverIntentParent.data('hover-intent-timeout')) {
            this.timeout = parseInt($hoverIntentParent.data('hover-intent-timeout'));
        }

        if ($hoverIntentParent.data('hover-intent-class-for-open')) {
            this.classForOpen = $hoverIntentParent.data('hover-intent-class-for-open');
        }

        if ($hoverIntentParent.data('hover-intent-force-click')) {
            this.forceClick = $hoverIntentParent.data('hover-intent-force-click');
        }

        if ($hoverIntentParent.data('hover-intent-force-click-element')) {
            this.forceClickElement = $hoverIntentParent.data('hover-intent-force-click-element');
        }

        if ($hoverIntentParent.data('hover-intent-link-on-mobile')) {
            this.linkOnMobile = $hoverIntentParent.data('hover-intent-link-on-mobile');
        }
    }

    getInterval () {
        return this.interval;
    }

    getTimeout () {
        return this.timeout;
    }

    getClassForOpen () {
        return this.classForOpen;
    }

    getSelector () {
        return this.$selector;
    }

    getForceClick () {
        return this.forceClick;
    }

    getForceClickElement () {
        return this.forceClickElement;
    }

    getLinkOnMobile () {
        return this.linkOnMobile;
    }
}
