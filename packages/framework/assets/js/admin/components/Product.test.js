/**
 * @jest-environment jsdom
 */

import $ from 'jquery';
import Product from './Product';

let resizeObserverCallback;

const captureResizeObserverCallback = callback => {
    resizeObserverCallback = callback;

    return { observe: jest.fn() };
};

const setActionBarLayout = (actionBar, { bottom, height }) => {
    actionBar.style.bottom = `${bottom}px`;
    Object.defineProperty(actionBar, 'offsetHeight', { configurable: true, value: height });
};

beforeAll(() => {
    $.fn.filterAllNodes = function (selector) {
        return this.find(selector).addBack(selector);
    };
});

beforeEach(() => {
    document.body.innerHTML = `
        <div class="js-action-bar-bottom fixed-bottom"><button type="button">Save</button></div>
        <button type="button" class="js-mobile-toc-button"></button>
    `;

    resizeObserverCallback = null;
    window.ResizeObserver = jest.fn().mockImplementation(captureResizeObserverCallback);
});

test('floating button stays above the action bar when the bar wraps into more rows', () => {
    const actionBar = document.querySelector('.js-action-bar-bottom');
    const floatingButton = document.querySelector('.js-mobile-toc-button');
    setActionBarLayout(actionBar, { bottom: 0, height: 48 });

    Product.initFloatingButtonPosition($(document.body));
    resizeObserverCallback();

    expect(floatingButton.style.bottom).toBe('64px');

    setActionBarLayout(actionBar, { bottom: 0, height: 110 });
    resizeObserverCallback();

    expect(floatingButton.style.bottom).toBe('126px');
});

test('floating button follows the action bar moved above the Symfony toolbar', () => {
    const actionBar = document.querySelector('.js-action-bar-bottom');
    const floatingButton = document.querySelector('.js-mobile-toc-button');
    setActionBarLayout(actionBar, { bottom: 0, height: 48 });

    Product.initFloatingButtonPosition($(document.body));
    resizeObserverCallback();
    setActionBarLayout(actionBar, { bottom: 36, height: 48 });

    actionBar.querySelector('button').dispatchEvent(new Event('transitionend', { bubbles: true }));

    expect(floatingButton.style.bottom).toBe('64px');

    actionBar.dispatchEvent(new Event('transitionend'));

    expect(floatingButton.style.bottom).toBe('100px');
});

test('floating button position is not observed again for content added later to the page', () => {
    Product.initFloatingButtonPosition($(document.body));
    Product.initFloatingButtonPosition($('<div class="js-video-item"></div>'));

    expect(window.ResizeObserver).toHaveBeenCalledTimes(1);
});

test('floating button keeps its default position when the page has no action bar', () => {
    document.querySelector('.js-action-bar-bottom').remove();

    Product.initFloatingButtonPosition($(document.body));
    Product.adjustFloatingButtonPosition();

    expect(window.ResizeObserver).not.toHaveBeenCalled();
    expect(document.querySelector('.js-mobile-toc-button').style.bottom).toBe('');
});
