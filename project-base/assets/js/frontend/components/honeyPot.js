import Register from 'framework/common/utils/register';

(new Register()).registerCallback(($container) => $container.filterAllNodes('.js-honey').hide());
