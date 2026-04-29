import IconMinus from 'icons/tabler/circle-minus.svg';
import IconPlus from 'icons/tabler/circle-plus.svg';
import Ajax from '../../common/utils/Ajax';

export default class CategoryTreeFormItem {
    constructor($item, parent) {
        this.$item = $item;
        this.status = null;
        this.loaded = null;
        this.$statusIcon = $item.find('.js-category-tree-form-item-icon:first');
        this.$checkbox = $item.find('.js-category-tree-form-item-checkbox:first');
        this.parent = parent;
        this.children = [];

        this.$childrenContainer = $item.find('.js-category-tree-form-children-container:first');

        this.initChildren();
        this.initStatus();

        this.$statusIcon.click(() => this.statusToggle());
    }

    initChildren() {
        const _this = this;
        this.$childrenContainer.find('> .js-category-tree-form-item').each(function () {
            const childItem = new CategoryTreeFormItem($(this), _this);
            _this.children.push(childItem);
        });
    }

    initStatus() {
        // status could be set to "opened" by children
        if (this.status === null) {
            if (this.$item.data('has-children')) {
                this.close(false);
            } else {
                this.setStatus(CategoryTreeFormItem.STATUS_NONE);
            }
            if (this.$checkbox.is(':checked')) {
                if (this.parent instanceof CategoryTreeFormItem) {
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
            this.setStatus(CategoryTreeFormItem.STATUS_CLOSED);
        }
    }

    setStatus(newStatus) {
        this.status = newStatus;
        this.updateStatusIcon();
    }

    updateStatusIcon() {
        switch (this.status) {
            case CategoryTreeFormItem.STATUS_OPENED:
            case CategoryTreeFormItem.STATUS_LOADING:
                this.$statusIcon.html(IconMinus);
                this.$statusIcon.addClass('cursor-pointer');
                break;
            case CategoryTreeFormItem.STATUS_CLOSED:
                this.$statusIcon.html(IconPlus);
                this.$statusIcon.addClass('cursor-pointer');
                break;
            case CategoryTreeFormItem.STATUS_NONE:
                this.$statusIcon.addClass('d-none');
                break;
        }
    }

    statusToggle() {
        if (this.status === CategoryTreeFormItem.STATUS_CLOSED) {
            this.open(true);
        } else if (this.status === CategoryTreeFormItem.STATUS_OPENED) {
            this.close(true);
        }
    }

    open(animate) {
        if (this.loaded === false) {
            this.loadChildren();
        } else if (!this.$childrenContainer.is(':animated')) {
            this.$childrenContainer.slideDown(animate === true ? 'normal' : 0);
            this.setStatus(CategoryTreeFormItem.STATUS_OPENED);
            if (this.parent instanceof CategoryTreeFormItem) {
                this.parent.open(animate);
            }
        }
    }

    loadChildren() {
        this.setStatus(CategoryTreeFormItem.STATUS_LOADING);
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
                if (_this.status === CategoryTreeFormItem.STATUS_LOADING) {
                    _this.setStatus(CategoryTreeFormItem.STATUS_CLOSED);
                }
            },
        });
    }

    createItem(itemData) {
        const $form = this.$item.closest('.js-category-tree-form');
        const checkboxName = $form.data('checkbox-name');
        const checkboxIdPrefix = $form.data('checkbox-id-prefix');
        const checkboxId = `${checkboxIdPrefix}_${itemData.id}`;
        let newItemHtml = $form.find('.js-category-tree-form-item-template:first').html().trim();

        newItemHtml = newItemHtml
            .replace('__load_children_url__', () => itemData.loadUrl)
            .replace('__has_children__', () => (itemData.hasChildren ? 'true' : 'false'))
            .replace('__checkbox_id__', () => checkboxId)
            .replace('__checkbox_name__', () => checkboxName)
            .replace('__value__', () => itemData.id)
            .replace('__label__', () => itemData.categoryName);

        const $newItem = $(newItemHtml);

        $newItem.data('load-url', itemData.loadUrl);
        $newItem.data('has-children', itemData.hasChildren);
        if (itemData.isVisible === false) {
            $newItem.addClass($form.data('hidden-item-class'));
        }

        return $newItem;
    }
}

CategoryTreeFormItem.STATUS_OPENED = 'opened';
CategoryTreeFormItem.STATUS_CLOSED = 'closed';
CategoryTreeFormItem.STATUS_LOADING = 'loading';
CategoryTreeFormItem.STATUS_NONE = 'none';
