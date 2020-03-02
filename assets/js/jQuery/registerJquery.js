import $ from 'jquery';
import '../bundles/fpjsformvalidator/js/FpJsFormValidator';
import registerFormValidator from './registerFormValidator.js';
import registerFilterAllNodes from './registerFilterAllNodes.js';
import tooltip from 'framework/common/bootstrap/tooltip';

export default function registerJquery () {

    window.jQuery = $;
    window.$ = $;

    registerFormValidator();
    registerFilterAllNodes();
    tooltip($);
}

registerJquery();
