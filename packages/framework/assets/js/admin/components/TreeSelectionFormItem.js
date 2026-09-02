import $ from 'jquery';
import IconMinus from 'icons/tabler/circle-minus.svg?raw';
import IconPlus from 'icons/tabler/circle-plus.svg?raw';
import Ajax from '../../common/utils/Ajax';

export default class TreeSelectionFormItem {
    constructor($item, parent) {
        this.$item = $item;
        this.status = null;
        this.loaded = null;
        this.$statusIcon = $item.find('.js-tree-selection-form-item-icon:first');
        this.$checkbox = $item.find('.js-tree-selection-form-item-checkbox:first');
        this.parent = parent;
        this.children = [];

        this.$childrenContainer = $item.find('.js-tree-selection-form-children-container:first');

        this.initChildren();
        this.initStatus();

        this.$statusIcon.click(() => this.statusToggle());
    }

    initChildren() {
        const _this = this;
        this.$childrenContainer.find('> .js-tree-selection-form-item').each(function () {
            const childItem = new TreeSelectionFormItem($(this), _this);
            _this.children.push(childItem);
        });
    }

    initStatus() {
        // status could be set to "opened" by children
        if (this.status === null) {
            if (this.$item.data('expandable')) {
                this.close(false);
            } else {
                this.setStatus(TreeSelectionFormItem.STATUS_NONE);
            }
            if (this.$checkbox.is(':checked')) {
                if (this.parent instanceof TreeSelectionFormItem) {
                    this.parent.open(false);
                }
            }
        }
        if (this.loaded === null) {
            this.loaded = this.children.length > 0;
        }
    }

    close(animate) {
        if (!this.$childrenContainer.is(':animated')) {
            this.$childrenContainer.slideUp(animate === true ? 'normal' : 0);
            this.setStatus(TreeSelectionFormItem.STATUS_CLOSED);
        }
    }

    setStatus(newStatus) {
        this.status = newStatus;
        this.updateStatusIcon();
    }

    updateStatusIcon() {
        switch (this.status) {
            case TreeSelectionFormItem.STATUS_OPENED:
            case TreeSelectionFormItem.STATUS_LOADING:
                this.$statusIcon.html(IconMinus);
                this.$statusIcon.addClass('cursor-pointer');
                break;
            case TreeSelectionFormItem.STATUS_CLOSED:
                this.$statusIcon.html(IconPlus);
                this.$statusIcon.addClass('cursor-pointer');
                break;
            case TreeSelectionFormItem.STATUS_NONE:
                this.$statusIcon.addClass('d-none');
                break;
        }
    }

    statusToggle() {
        if (this.status === TreeSelectionFormItem.STATUS_CLOSED) {
            this.open(true);
        } else if (this.status === TreeSelectionFormItem.STATUS_OPENED) {
            this.close(true);
        }
    }

    open(animate) {
        if (this.loaded === false) {
            this.loadChildren();
        } else if (!this.$childrenContainer.is(':animated')) {
            this.$childrenContainer.slideDown(animate === true ? 'normal' : 0);
            this.setStatus(TreeSelectionFormItem.STATUS_OPENED);
            if (this.parent instanceof TreeSelectionFormItem) {
                this.parent.open(animate);
            }
        }
    }

    loadChildren() {
        this.setStatus(TreeSelectionFormItem.STATUS_LOADING);
        const _this = this;
        Ajax.ajax({
            loaderElement: this.$item,
            url: this.$item.data('load-url'),
            dataType: 'json',
            success: data => {
                _this.loaded = true;

                $.each(data, function () {
                    const $newItem = _this.createItem(this);
                    _this.$childrenContainer.append($newItem);
                });
                _this.initChildren();

                _this.open(true);
            },
            complete: () => {
                if (_this.status === TreeSelectionFormItem.STATUS_LOADING) {
                    _this.setStatus(TreeSelectionFormItem.STATUS_CLOSED);
                }
            },
        });
    }

    createItem(itemData) {
        const $form = this.$item.closest('.js-tree-selection-form');
        const checkboxName = $form.data('checkbox-name');
        const checkboxIdPrefix = $form.data('checkbox-id-prefix');
        const checkboxId = `${checkboxIdPrefix}_${itemData.id}`;
        let newItemHtml = $form.find('.js-tree-selection-form-item-template:first').html().trim();

        newItemHtml = newItemHtml
            .replace('__load_children_url__', () => itemData.loadUrl)
            .replace('__expandable__', () => (itemData.isExpandable ? 'true' : 'false'))
            .replace('__checkbox_id__', () => checkboxId)
            .replace('__checkbox_name__', () => checkboxName)
            .replace('__value__', () => itemData.id)
            .replace('__label__', () => itemData.label);

        const $newItem = $(newItemHtml);

        $newItem.data('load-url', itemData.loadUrl);
        $newItem.data('expandable', itemData.isExpandable);
        if (!itemData.isExpandable) {
            $newItem.find('.js-tree-selection-form-children-container:first').remove();
        }
        if (itemData.isVisible === false) {
            $newItem.addClass($form.data('hidden-item-class'));
        }

        return $newItem;
    }
}

TreeSelectionFormItem.STATUS_OPENED = 'opened';
TreeSelectionFormItem.STATUS_CLOSED = 'closed';
TreeSelectionFormItem.STATUS_LOADING = 'loading';
TreeSelectionFormItem.STATUS_NONE = 'none';
