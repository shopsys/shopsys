import Register from '../utils/Register';
import '../../common/bootstrap/tooltip';

export const tooltip = ($container) => $container.filterAllNodes('.js-tooltip[title]').tooltip();

(new Register()).registerCallback(tooltip);
