import Ajax from '../common/ajax';

export default class CategoryTreeFormItem {

    constructor ($item, parent) {
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

    initChildren () {
        const _this = this;
        this.$childrenContainer.find('> .js-category-tree-form-item').each(function () {
            const childItem = new CategoryTreeFormItem($(this), _this);
            _this.children.push(childItem);
        });
    }

    initStatus () {
        // status could be set to "opened" by children
        if (this.status === null) {
            if (this.$item.data('has-children')) {
                this.close(false);
            } else {
                this.setStatus(CategoryTreeFormItem.STATUS_NONE);
            }

            const _this = this;
            if (this.$checkbox.is(':checked')) {
                if (_this.parent instanceof CategoryTreeFormItem) {
                    _this.parent.open(false);
                }
            }
        }
        if (this.loaded === null) {
            this.loaded = this.children.length > 0;
        }
    }

    close (animate) {
        const _this = this;
        if (!this.$childrenContainer.is(':animated')) {
            _this.$childrenContainer.slideUp(animate === true ? 'normal' : 0);
            _this.setStatus(CategoryTreeFormItem.STATUS_CLOSED);
        }
    }

    setStatus (newStatus) {
        this.status = newStatus;
        this.updateStatusIcon();
    }

    updateStatusIcon () {
        this.$statusIcon.removeClass('svg svg-circle-plus svg-circle-remove sprite sprite-level cursor-pointer form-tree__item__icon--level');
        switch (this.status) {
            case CategoryTreeFormItem.STATUS_OPENED:
            case CategoryTreeFormItem.STATUS_LOADING:
                this.$statusIcon.addClass('svg svg-circle-remove cursor-pointer');
                break;
            case CategoryTreeFormItem.STATUS_CLOSED:
                this.$statusIcon.addClass('svg svg-circle-plus cursor-pointer');
                break;
            case CategoryTreeFormItem.STATUS_NONE:
                this.$statusIcon.addClass('sprite sprite-level form-tree__item__icon--level');
                break;
        }
    }

    statusToggle () {
        if (this.status === CategoryTreeFormItem.STATUS_CLOSED) {
            this.open(true);
        } else if (this.status === CategoryTreeFormItem.STATUS_OPENED) {
            this.close(true);
        }
    }

    open (animate) {
        const _this = this;
        if (this.loaded === false) {
            this.loadChildren();
        } else if (!this.$childrenContainer.is(':animated')) {
            _this.$childrenContainer.slideDown(animate === true ? 'normal' : 0);
            _this.setStatus(CategoryTreeFormItem.STATUS_OPENED);
            if (_this.parent instanceof CategoryTreeFormItem) {
                _this.parent.open(animate);
            }
        }
    }

    loadChildren () {
        this.setStatus(CategoryTreeFormItem.STATUS_LOADING);
        const _this = this;
        Ajax.ajax({
            loaderElement: this.$item,
            url: this.$item.data('load-url'),
            dataType: 'json',
            success: function (data) {
                _this.loaded = true;

                $.each(data, function () {
                    const $newItem = _this.createItem(this);
                    _this.$childrenContainer.append($newItem);
                });
                _this.initChildren();

                _this.open(true);
            },
            complete: function () {
                if (_this.status === CategoryTreeFormItem.STATUS_LOADING) {
                    _this.setStatus(CategoryTreeFormItem.STATUS_CLOSED);
                }
            }
        });
    }

    createItem (itemData) {
        let $form = this.$item.closest('.js-category-tree-form');
        let newItemHtml = $form.data('prototype');

        newItemHtml = newItemHtml.replace(/__name__/g, itemData.id);
        newItemHtml = newItemHtml.replace(/__category_name__/g, itemData.categoryName);

        const $newItem = $($.parseHTML(newItemHtml));
        $newItem.data('load-url', itemData.loadUrl);
        $newItem.data('has-children', itemData.hasChildren);
        if (itemData.isVisible === false) {
            $newItem.addClass($form.data('hidden-item-class'));
        }

        $newItem.find('.js-category-tree-form-item-checkbox').val(itemData.id);

        return $newItem;
    }
}

CategoryTreeFormItem.STATUS_OPENED = 'opened';
CategoryTreeFormItem.STATUS_CLOSED = 'closed';
CategoryTreeFormItem.STATUS_LOADING = 'loading';
CategoryTreeFormItem.STATUS_NONE = 'none';
