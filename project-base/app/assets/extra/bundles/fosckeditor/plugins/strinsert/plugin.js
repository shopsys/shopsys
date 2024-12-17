/**
 * @license Copyright © 2013 Stuart Sillitoe <stuart@vericode.co.uk>
 * This is open source, can modify it as you wish.
 *
 * Stuart Sillitoe
 * stuartsillitoe.co.uk
 *
 */

/**
 * List of dicts which define strings to choose from to insert into the editor.
 *
 * Each insertable string dict is defined by three possible keys:
 *    'value': The value to insert.
 *    'name': The name for the string to use in the dropdown.
 *    'label': The voice label (also used as the tooltip title) for the string.
 *
 * Only the value to insert is required to define an insertable string, the
 * value will be used as the name (and the name as the label) if other keys are
 * not provided.
 *
 * If the value key is *not* defined and the name key is, then a group header
 * with the given name will be provided in the dropdown box.  This heading is
 * not clickable and does not insert, it is for organizational purposes only.
 */
CKEDITOR.config.strinsert_strings = [
    { 'name': 'Name', 'value': '*|VALUE|*' },
    { 'name': 'Group 1' },
    { 'name': 'Another name', 'value': 'totally_different', 'label': 'Good looking' }
];

/**
 * String to use as the button label.
 */
CKEDITOR.config.strinsert_button_label = 'Insert';

/**
 * String to use as the button title.
 */
CKEDITOR.config.strinsert_button_title = 'Insert content';

/**
 * String to use as the button voice label.
 */
CKEDITOR.config.strinsert_button_voice = 'Insert content';

CKEDITOR.plugins.add('strinsert',
    {
        requires: ['richcombo'],
        init: function (editor) {
            var config = editor.config;

            // Gets the list of insertable strings from the settings.
            var strings = config.strinsert_strings;

            // add the menu to the editor
            editor.ui.addRichCombo('strinsert',
                {
                    label: config.strinsert_button_label,
                    title: config.strinsert_button_title,
                    voiceLabel: config.strinsert_button_voice,
                    toolbar: 'insert',
                    className: 'cke_format',
                    multiSelect: false,
                    panel:
                        {
                            css: [CKEDITOR.skin.getPath('editor')].concat(config.contentsCss),
                            voiceLabel: editor.lang.panelVoiceLabel
                        },

                    init: function () {
                        // var lastgroup = '';
                        for (var i = 0, len = strings.length; i < len; i++) {
                            string = strings[i];
                            // If there is no value, make a group header using the name.
                            if (!string.value) {
                                this.startGroup(string.name);
                                // eslint-disable-next-line brace-style
                            }
                            // If we have a value, we have a string insert row.
                            else {
                                // If no name provided, use the value for the name.
                                if (!string.name) {
                                    string.name = string.value;
                                }
                                // If no label provided, use the name for the label.
                                if (!string.label) {
                                    string.label = string.name;
                                }
                                this.add(string.value, string.name, string.label);
                            }
                        }
                    },

                    onClick: function (value) {
                        editor.focus();
                        editor.fire('saveSnapshot');
                        editor.insertHtml(value);
                        editor.fire('saveSnapshot');
                    }

                });
        }
    });
