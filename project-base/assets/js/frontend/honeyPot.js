import Register from '../copyFromFw/register';

(new Register()).registerCallback(($container) => $container.filterAllNodes('.js-honey').hide());
