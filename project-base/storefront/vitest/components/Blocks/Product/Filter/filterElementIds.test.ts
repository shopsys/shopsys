import {
    PRODUCT_LIST_CONTROLS_ELEMENT_ID,
    scrollToProductListControls,
} from 'components/Blocks/Product/Filter/filterElementIds';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

const renderControlsAt = (top: number) => {
    const controlsElement = document.createElement('div');
    controlsElement.id = PRODUCT_LIST_CONTROLS_ELEMENT_ID;
    controlsElement.getBoundingClientRect = () => ({ top, bottom: top + 44, height: 44 }) as DOMRect;
    controlsElement.scrollIntoView = vi.fn();
    document.body.appendChild(controlsElement);

    return controlsElement;
};

describe('scrollToProductListControls', () => {
    beforeEach(() => {
        vi.spyOn(window, 'requestAnimationFrame').mockImplementation((callback) => {
            callback(0);

            return 0;
        });
        document.documentElement.style.setProperty('--fixed-header-height', '100px');
    });

    afterEach(() => {
        document.body.innerHTML = '';
        document.documentElement.style.removeProperty('--fixed-header-height');
        vi.restoreAllMocks();
    });

    test('keeps the page in place while the controls are visible below the fixed header', () => {
        const controlsElement = renderControlsAt(400);

        scrollToProductListControls();

        expect(controlsElement.scrollIntoView).not.toHaveBeenCalled();
    });

    test('scrolls to the controls above the screen', () => {
        const controlsElement = renderControlsAt(-300);

        scrollToProductListControls();

        expect(controlsElement.scrollIntoView).toHaveBeenCalledWith({ behavior: 'smooth', block: 'start' });
    });

    test('scrolls to the controls hidden under the fixed header', () => {
        const controlsElement = renderControlsAt(60);

        scrollToProductListControls();

        expect(controlsElement.scrollIntoView).toHaveBeenCalledWith({ behavior: 'smooth', block: 'start' });
    });
});
