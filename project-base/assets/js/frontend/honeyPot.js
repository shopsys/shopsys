import Register from 'framework/common/register';

(new Register()).registerCallback(($container) => $container.filterAllNodes('.js-honey').hide());
