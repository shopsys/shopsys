import Register from '../register';

export const tooltip = ($container) => $container.filterAllNodes('.js-tooltip[title]').tooltip();

(new Register()).registerCallback(tooltip);
