import $ from 'jquery';
import '../bundles/fpjsformvalidator/js/FpJsFormValidator';
import tooltip from 'framework/common/bootstrap/tooltip';
import registerFilterAllNodes from './registerFilterAllNodes.js';
import registerFormValidator from './registerFormValidator.js';

export default function registerJquery() {
    window.jQuery = $;
    window.$ = $;

    registerFormValidator();
    registerFilterAllNodes();
    tooltip($);
}

registerJquery();
