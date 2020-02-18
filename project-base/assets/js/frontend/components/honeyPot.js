import Register from 'framework/common/utils/Register';

(new Register()).registerCallback(($container) => $container.filterAllNodes('.js-honey').hide());
