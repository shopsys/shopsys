import Register from 'framework/assets/js/common/register';

(new Register()).registerCallback(($container) => $container.filterAllNodes('.js-honey').hide());
