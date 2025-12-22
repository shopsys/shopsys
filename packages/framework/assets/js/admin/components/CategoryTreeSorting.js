import ModalWindow from '@shopsys/administration/src/js/utils/modalWindow';
import Translator from 'bazinga-translator';
import Sortable from 'sortablejs';
import Ajax from '../../common/utils/Ajax';
import Register from '../../common/utils/Register';
import FormChangeInfo from './FormChangeInfo';

export default class CategoryTreeSorting {
    constructor($rootTree, $saveButton) {
        this.$rootTree = $rootTree;
        this.$saveButton = $saveButton;
        this.sortableInstances = [];
        this.protectRoot = this.$rootTree.hasClass('js-protect-root');

        this.initSortable(this.$rootTree[0]);
        $saveButton.click(() => this.onSaveClick());
    }

    initSortable(rootElement) {
        const nestedLists = rootElement.querySelectorAll('.js-category-tree-items');
        const allLists = [rootElement, ...nestedLists];

        allLists.forEach(list => {
            const isRootList = list === rootElement;
            const sortable = Sortable.create(list, {
                group: 'nested-categories',
                animation: 150,
                fallbackOnBody: true,
                forceFallback: true,
                fallbackTolerance: 3,
                swapThreshold: 1,
                handle: '.js-category-tree-item-handle',
                draggable: '.js-category-tree-item',
                chosenClass: 'js-category-tree-chosen',
                dragClass: 'js-category-tree-drag',
                filter: this.protectRoot && isRootList ? '.js-category-tree-item:first-child' : undefined,
                onStart: event => this.onDragStart(event),
                onEnd: event => this.onDragEnd(event),
                onMove: event => this.onMove(event),
            });

            this.sortableInstances.push(sortable);
        });
    }

    onDragStart(event) {
        const handle = event.item.querySelector('.js-category-tree-item-handle');
        if (handle) {
            handle.classList.remove('cursor-grab');
            handle.classList.add('cursor-grabbing');
        }
        document.body.classList.add('is-dragging-category');
    }

    onDragEnd(event) {
        const handle = event.item.querySelector('.js-category-tree-item-handle');
        if (handle) {
            handle.classList.remove('cursor-grabbing');
            handle.classList.add('cursor-grab');
        }
        document.body.classList.remove('is-dragging-category');

        this.clearDropIndicators();

        if (event.from !== event.to || event.oldIndex !== event.newIndex) {
            this.onChange();
        }
    }

    onMove(event) {
        this.clearDropIndicators();

        if (this.protectRoot) {
            const rootList = this.$rootTree[0];
            const targetList = event.to;
            const isMovingToRoot = targetList === rootList;

            if (isMovingToRoot) {
                return false;
            }
        }

        const related = event.related;
        if (related) {
            if (event.willInsertAfter) {
                related.classList.add('js-category-tree-drop-after');
            } else {
                related.classList.add('js-category-tree-drop-before');
            }
        }

        return true;
    }

    clearDropIndicators() {
        document.querySelectorAll('.js-category-tree-drop-before, .js-category-tree-drop-after').forEach(el => {
            el.classList.remove('js-category-tree-drop-before', 'js-category-tree-drop-after');
        });
    }

    onChange() {
        this.$saveButton.prop('disabled', false);
        FormChangeInfo.showInfo();
    }

    onSaveClick() {
        if (this.$saveButton.prop('disabled')) {
            return;
        }

        Ajax.ajax({
            loaderElement: this.$saveButton,
            url: this.$saveButton.data('category-apply-sorting-url'),
            type: 'post',
            data: {
                categoriesOrderingData: JSON.stringify(this.getNestedSetData()),
            },
            success: () => {
                this.$saveButton.prop('disabled', true);
                FormChangeInfo.removeInfo();
                // eslint-disable-next-line no-new
                new ModalWindow({
                    content: Translator.trans('Categories order saved.'),
                });
            },
            error: () => {
                // eslint-disable-next-line no-new
                new ModalWindow({
                    content: Translator.trans("There was an error while saving. The order isn't saved."),
                });
            },
        });
    }

    getNestedSetData() {
        const result = [];
        let counter = 1;

        const processNode = (element, parentId, depth) => {
            const id = this.extractIdFromElement(element);
            if (id === null) {
                return counter;
            }

            const left = counter;
            counter++;

            const childList = element.querySelector(':scope > .js-category-tree-items');
            if (childList) {
                const children = childList.querySelectorAll(':scope > .js-category-tree-item');
                children.forEach(child => {
                    counter = processNode(child, id, depth + 1);
                });
            }

            const right = counter;
            counter++;

            result.push({
                id: id,
                parent_id: parentId,
                depth: depth,
                left: left,
                right: right,
            });

            return counter;
        };

        const rootList = this.$rootTree[0];
        const rootItems = rootList.querySelectorAll(':scope > .js-category-tree-item');

        rootItems.forEach(item => {
            counter = processNode(item, null, 0);
        });

        return result;
    }

    extractIdFromElement(element) {
        const id = element.id;
        const match = id.match(/js-category-tree-(\d+)/);
        return match ? parseInt(match[1], 10) : null;
    }

    static init($container) {
        const $rootTree = $container.filterAllNodes('#js-category-tree-sorting > .js-category-tree-items');
        const $saveButton = $container.filterAllNodes('#js-category-tree-sorting-save-button');

        if ($rootTree.length > 0 && $saveButton.length > 0) {
            // eslint-disable-next-line no-new
            new CategoryTreeSorting($rootTree, $saveButton);
        }
    }
}

new Register().registerCallback(CategoryTreeSorting.init, 'CategoryTreeSorting.init');
