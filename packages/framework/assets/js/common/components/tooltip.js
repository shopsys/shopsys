import Register from '../utils/register';
import '../../common/bootstrap/tooltip';

export const tooltip = ($container) => $container.filterAllNodes('.js-tooltip[title]').tooltip();

(new Register()).registerCallback(tooltip);
