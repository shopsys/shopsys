import Register from 'framework/common/utils/Register';
import 'jquery-ui/draggable';

export default class MobileSlidingMenu {
    static init ($container) {

        const $draggableItem = $container.filterAllNodes('#js-mobile-sliding-menu__draggable');
        const baseContainerWidth = $container.filterAllNodes('#js-mobile-sliding-menu').innerWidth();

        const draggableFinalWidth = $draggableItem.outerWidth();
        const minimalLeftPosition = 1000 + baseContainerWidth - draggableFinalWidth;

        $draggableItem.draggable({
            cursor: 'move',
            axis: 'x',
            containment: 'parent',
            drag: function () {
                if ($draggableItem.position().left > 1001) {
                    $draggableItem.css('left', '1000px');
                    return false;
                }
                if ($draggableItem.position().left < minimalLeftPosition - 1) {
                    $draggableItem.css('left', minimalLeftPosition + 'px');
                    return false;
                }
            }
        });
    }
}

(new Register()).registerCallback(MobileSlidingMenu.init, 'MobileSlidingMenu.init');
