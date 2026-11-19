/**
 * @jest-environment jsdom
 */

import $ from 'jquery';
import ActionBar from './ActionBar';

let resizeObserverCallback;

const captureResizeObserverCallback = callback => {
    resizeObserverCallback = callback;

    return { observe: jest.fn() };
};

const setActionBarHeight = height => {
    Object.defineProperty(document.querySelector('.js-action-bar-bottom'), 'offsetHeight', {
        configurable: true,
        value: height,
    });
};

beforeAll(() => {
    $.fn.filterAllNodes = function (selector) {
        return this.find(selector).addBack(selector);
    };
});

beforeEach(() => {
    document.body.innerHTML = '<div class="js-action-bar-bottom"><button type="button">Save</button></div>';
    document.documentElement.style.removeProperty('--admin-action-bar-height');

    resizeObserverCallback = null;
    window.ResizeObserver = jest.fn().mockImplementation(captureResizeObserverCallback);
});

test('page room for the action bar follows the height of the bar', () => {
    ActionBar.init($(document.body));

    setActionBarHeight(146);
    resizeObserverCallback();

    expect(document.documentElement.style.getPropertyValue('--admin-action-bar-height')).toBe('146px');

    setActionBarHeight(187);
    resizeObserverCallback();

    expect(document.documentElement.style.getPropertyValue('--admin-action-bar-height')).toBe('187px');
});

test('action bar is not observed again for content added later to the page', () => {
    ActionBar.init($(document.body));
    ActionBar.init($('<div class="js-video-item"></div>'));

    expect(window.ResizeObserver).toHaveBeenCalledTimes(1);
});
