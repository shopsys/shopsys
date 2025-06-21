import tooltip from 'framework/common/bootstrap/tooltip';
import $ from 'jquery';
import registerFilterAllNodes from './registerFilterAllNodes.js';

export default function registerJquery() {
    window.jQuery = $;
    window.$ = $;

    registerFilterAllNodes();
    tooltip($);
}

registerJquery();
