import Register from '../utils/Register';
import JSONEditor from 'jsoneditor';

export default class JsonEditor {

    constructor ($container) {
        this.$targets = $container.filterAllNodes('.js-json-editor');
        this.$targets.each((_, el) => this.attach($(el)));
    }

    attach ($textarea) {

        // <div id="jsoneditor" style="width: 400px; height: 400px;"></div>
        // create the editor
        // const container = document.getElementById("jsoneditor")
        // const options = {}
        // const editor = new JSONEditor(container, options)
        //
        // // set json
        // const initialJson = {
        //     "Array": [1, 2, 3],
        //     "Boolean": true,
        //     "Null": null,
        //     "Number": 123,
        //     "Object": {"a": "b", "c": "d"},
        //     "String": "Hello World"
        // }
        // editor.set(initialJson)
        //
        // // get json
        // const updatedJson = editor.get()

        const initial = $textarea.val() || '{}';

        // prázdný div, do kterého editor vyrenderujeme
        const $box = $('<div>', { class: 'json-editor-box' }).css({ height: 400, widget: 400 });
        $textarea.after($box).hide();

        const editor = new JSONEditor($box[0], {
            // mode: 'tree',
            onChangeJSON: json => $textarea.val(JSON.stringify(json)) // sync zpět do pole
        });

        // const initialJson = {
        //     'Array': [1, 2, 3],
        //     'Boolean': true,
        //     'Null': null,
        //     'Number': 123,
        //     'Object': { 'a': 'b', 'c': 'd' },
        //     'String': 'Hello World'
        // };

        editor.set(JSON.parse(initial));
        // editor.set(initialJson);

        // kdyby ses k instanci potřeboval později dostat
        // $textarea.data('jsonEditor', editor);
    }

    static init ($container) {
        // eslint-disable-next-line no-new
        new JsonEditor($container);
    }
}

(new Register()).registerCallback(JsonEditor.init, 'JsonEditor.init');
