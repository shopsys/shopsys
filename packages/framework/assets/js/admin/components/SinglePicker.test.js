/**
 * @jest-environment jsdom
 */

import ModalWindow from '@shopsys/administration/src/js/utils/modalWindow';
import $ from 'jquery';
import SinglePicker from './SinglePicker';

jest.mock('bazinga-translator', () => ({
    trans: message => message,
}));
jest.mock('./FormChangeInfo', () => ({
    showInfo: jest.fn(),
}));
jest.mock('@shopsys/administration/src/js/utils/modalWindow', () => {
    const jquery = require('jquery');

    return jest.fn().mockImplementation(() => ({
        element: jquery('<div class="modal show"></div>'),
    }));
});

beforeEach(() => {
    document.body.innerHTML = '<button data-picker-url="/picker/__js_instance_id__"></button>';
    window.SinglePickerInstances = {};
    ModalWindow.mockClear();
});

test('picker opens only one modal until the current modal is closed', () => {
    const $picker = $('button');
    const picker = new SinglePicker($picker, jest.fn());

    $picker.trigger('click');
    $picker.trigger('click');

    expect(ModalWindow).toHaveBeenCalledTimes(1);

    picker.modal.element.trigger('hidden.bs.modal');
    $picker.trigger('click');

    expect(ModalWindow).toHaveBeenCalledTimes(2);
});

test('reinitializing picker replaces the previous click handler', () => {
    const $picker = $('button');

    new SinglePicker($picker, jest.fn());
    new SinglePicker($picker, jest.fn());
    $picker.trigger('click');

    expect(ModalWindow).toHaveBeenCalledTimes(1);
});
