import 'magnific-popup';
import 'jquery-ui/sortable';
import 'jquery-ui/ui/widgets/mouse';
import 'jquery-ui-touch-punch';
import FormChangeInfo from './FormChangeInfo';
import Register from '../../common/utils/Register';

window.PickerInstances = {};

export default class MultiplePicker {

    constructor ($picker) {
        this.instanceId = Object.keys(window.PickerInstances).length;
        window.PickerInstances[this.instanceId] = this;

        this.$picker = $picker;
        this.$header = $picker.find('.js-picker-header');
        this.$addButton = $picker.find('.js-picker-button-add');
        this.$itemsContainer = $picker.find('.js-picker-items');
        this.items = [];

        const _this = this;
        this.$addButton.click(() => _this.openPickerWindow());
        this.$itemsContainer.find('.js-picker-item').each(function () {
            _this.initItem($(this));
        });
        this.$itemsContainer.sortable({
            items: '.js-picker-item',
            handle: '.js-picker-item-handle',
            update: () => this.updateOrdering()
        });
    }

    openPickerWindow () {
        const _this = this;
        $.magnificPopup.open({
            items: { src: _this.$picker.data('picker-url').replace('__js_instance_id__', _this.instanceId) },
            type: 'iframe',
            closeOnBgClick: true
        });

        return false;
    }

    initItem ($item) {
        const _this = this;

        _this.items.push($item);
        (new Register()).registerNewContent($item);
        $item.find('.js-picker-item-button-delete').click(() => {
            _this.removeItem($item);
        });
    }

    removeItem ($item) {
        const Id = $item.find('.js-picker-item-input:first').val();
        delete this.items[this.findItemIndex(Id)];
        const Item = this.findItemIndex(Id);
        const newItems = [];
        for (let key in this.items) {
            if (this.items[key] !== Item) {
                newItems.push(this.items[key]);
            }
        }
        this.items = newItems;

        $item.remove();
        this.reIndex();
        this.updateHeader();
        FormChangeInfo.showInfo();
    }

    findItemIndex (Id) {
        for (let key in this.items) {
            if (this.items[key].find('.js-picker-item-input:first').val() === Id.toString()) {
                return key;
            }
        }

        return null;
    }

    reIndex () {
        this.$itemsContainer.find('.js-picker-item-input').each((index, element) => {
            const name = $(element).attr('name');
            const newName = name.substr(0, name.lastIndexOf('[') + 1) + index + ']';
            $(element).attr('name', newName);
        });
    }

    updateHeader () {
        this.$header.toggle(this.items.length !== 0);
    }

    updateOrdering () {
        this.reIndex();
        FormChangeInfo.showInfo();
    }

    removeItemById (Id) {
        const $item = this.findItemById(Id);
        this.removeItem($item);
    }

    findItemById (Id) {
        return this.items[this.findItemIndex(Id)];
    }

    hasItem (Id) {
        return this.findItemIndex(Id) !== null;
    }

    addItem ($selectedElement) {
        const nextIndex = this.$itemsContainer.find('.js-picker-item').length;
        const itemHtml = this.$picker.data('picker-prototype').replace(/__name__/g, nextIndex);
        const $item = $($.parseHTML(itemHtml));

        $item.find('.js-picker-item-input').val($selectedElement.data('picker-id'));
        $item.find('.js-picker-item-name').text($selectedElement.data('picker-name'));

        this.$itemsContainer.append($item);
        this.initItem($item);
        this.updateHeader();
        FormChangeInfo.showInfo();
    }

    static init ($container) {
        $container.filterAllNodes('.js-picker').each(function () {
            // eslint-disable-next-line no-new
            new MultiplePicker($(this));
        });

        $('.js-picker-close').click(() => {
            window.parent.$.magnificPopup.instance.close();
        });
    }
}

(new Register().registerCallback(MultiplePicker.init, 'MultiplePicker.init'));
