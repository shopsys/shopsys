import Register from '../../common/utils/Register';

export default class MassAction {
    static init ($container) {
        $container.filterAllNodes('#js-mass-action-button').click(() => $('#js-mass-action').toggleClass('active'));
    }
}

(new Register()).registerCallback(MassAction.init);
