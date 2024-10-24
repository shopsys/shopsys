import grapesjs from 'grapesjs';
import Translator from 'bazinga-translator';

export default grapesjs.plugins.add('table-custom', editor => {
    const addColumns = (element, numberOfColumns) => {
        const rows = element.find('tr');
        for (const row of rows) {
            for (let i = 0; i < numberOfColumns; i++) {
                const td = row.components().add({ type: 'td' });
                td.append('<div class="gjs-text-ckeditor text">Insert your text here</div>');
            }
        }
    };

    const removeColumns = (element, numberOfColumns) => {
        const rows = element.find('tr');
        for (const row of rows) {
            for (let i = 0; i < numberOfColumns; i++) {
                const cells = row.find('td');
                const lastCellIndex = cells.length - 1;
                if (lastCellIndex >= 0) {
                    cells[lastCellIndex].remove();
                } else {
                    break;
                }
            }
        }
    };

    const addRows = (element, numberOfRows) => {
        const columns = element.find('td').length / element.find('tr').length;
        const tbody = element.find('tbody')[0];

        for (let i = 0; i < numberOfRows; i++) {
            const tr = tbody.components().add({ type: 'tr' });

            for (let j = 0; j < columns; j++) {
                const td = tr.components().add({ type: 'td' });
                td.append('<div class="gjs-text-ckeditor text">Insert your text here</div>');
            }
        }
    };

    const removeRows = (element, numberOfRows) => {
        for (let i = 0; i < numberOfRows; i++) {
            const rows = element.find('tr');
            const lastRowIndex = rows.length - 1;
            if (lastRowIndex >= 0) {
                rows[lastRowIndex].remove();
            } else {
                break;
            }
        }
    };

    const changeColumns = (element) => {
        const oldColumnCount = element.find('tr')[0].find('td').length;
        const newColumnCount = element.get('attributes').columns;
        const numberOfColumnsToAddOrRemove = Math.abs(oldColumnCount - newColumnCount);

        if (oldColumnCount > newColumnCount) {
            removeColumns(element, numberOfColumnsToAddOrRemove);
        } else {
            addColumns(element, numberOfColumnsToAddOrRemove);
        }
    };

    const changeRows = (element) => {
        const oldRowCount = element.find('tr').length;
        const newRowCount = element.get('attributes').rows;
        const numberOfRowsToAddOrRemove = Math.abs(oldRowCount - newRowCount);

        if (oldRowCount > newRowCount) {
            removeRows(element, numberOfRowsToAddOrRemove);
        } else {
            addRows(element, numberOfRowsToAddOrRemove);
        }
    };

    const changeVariant = (element) => {
        const variantValue = element.get('attributes').variant;
        element.find('table')[0].setClass(variantValue);
    };

    const createInitialTable = (rows, columns, variant) => {
        const table = { type: 'table', components: [], attributes: { class: [variant] } };
        const tbody = { type: 'tbody', components: [] };

        for (let i = 0; i < rows; i++) {
            const tr = { type: 'tr', components: [] };

            for (let j = 0; j < columns; j++) {
                const td = { type: 'td', components: [] };
                const text = { type: 'text-ckeditor', content: 'Insert your text here', components: '', classes: ['gjs-text-ckeditor', 'text'] };

                td.components.push(text);
                tr.components.push(td);
            }

            tbody.components.push(tr);
        }

        table.components.push(tbody);

        return table;
    };

    editor.Blocks.add('tableCustom', {
        id: 'table-custom',
        label: Translator.trans('Table'),
        category: 'Basic',
        attributes: { class: 'fa fa-table' },
        content: {
            type: 'table-custom',
            components: [
                createInitialTable(2, 2, 'default')
            ]
        }
    });

    editor.DomComponents.addType('tr', {
        isComponent: el => el.tagName === 'TR',
        model: {
            defaults: {
                tagName: 'tr',
                draggable: ['tbody'],
                droppable: ['td']
            }
        }
    });

    editor.DomComponents.addType('td', {
        isComponent: el => el.tagName === 'TD',
        model: {
            defaults: {
                tagName: 'td',
                draggable: ['tr'],
                droppable: ['.gjs-text-ckeditor']
            }
        }
    });

    editor.DomComponents.addType('table-custom', {
        isComponent: element => element.classList && element.classList.contains('gjs-table-custom'),
        model: {
            init () {
                this.on('change:attributes:rows', changeRows);
                this.on('change:attributes:columns', changeColumns);
                this.on('change:attributes:variant', changeVariant);
            },
            defaults: {
                attributes: {
                    class: ['gjs-table-custom'],
                    rows: 2,
                    columns: 2,
                    variant: 'default'
                },
                traits: [
                    {
                        type: 'number',
                        label: 'Rows',
                        name: 'rows',
                        min: 1
                    },
                    {
                        type: 'number',
                        label: 'Columns',
                        name: 'columns',
                        min: 1
                    },
                    {
                        type: 'select',
                        label: 'Variant',
                        name: 'variant',
                        options: [
                            { id: 'default', name: 'Default' },
                            { id: 'secondary', name: 'Secondary' }
                        ]
                    }
                ]
            }
        }
    });
});
