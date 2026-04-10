import { Tab } from '@tabler/core';
import Register from '../../common/utils/Register';

export default class OrderDetailTabs {
    static init($container) {
        $container.filterAllNodes('.nav-tabs .nav-link[data-bs-toggle="tab"]').on('shown.bs.tab', e => {
            history.replaceState(null, '', e.target.getAttribute('href'));
        });

        const hash = window.location.hash;

        if (hash) {
            const tabEl = document.querySelector(`.nav-tabs .nav-link[href="${hash}"]`);

            if (tabEl) {
                new Tab(tabEl).show();
            }
        }
    }
}

new Register().registerCallback(OrderDetailTabs.init, 'OrderDetailTabs.init');
