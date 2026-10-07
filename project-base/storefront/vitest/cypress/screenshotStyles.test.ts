import { afterEach, describe, expect, test, vi } from 'vitest';
import { createScreenshotBlackouts, prepareScreenshotStyles } from '../../cypress/support/screenshotStyles';

const options = { preserveFixedSelectors: [], disablePointerEventsSelectors: ['button'] };

describe('temporary screenshot DOM changes', () => {
    afterEach(() => {
        document.body.replaceChildren();
    });

    test('restores original inline values and priorities without undoing unrelated changes', () => {
        document.body.innerHTML =
            '<header style="position: sticky !important"></header><aside style="position: fixed; display: flex !important"></aside>';
        const header = document.querySelector('header')!;
        const aside = document.querySelector('aside')!;
        const originalStyles = document.head.querySelectorAll('style').length;
        const restore = prepareScreenshotStyles(document, options);
        expect(header.style.position).toBe('static');
        expect(aside.style.display).toBe('none');
        header.style.color = 'red';
        expect(document.head.querySelectorAll('style')).toHaveLength(originalStyles + 1);

        restore();
        restore();

        expect(header.style.position).toBe('sticky');
        expect(header.style.getPropertyPriority('position')).toBe('important');
        expect(header.style.color).toBe('red');
        expect(aside.style.display).toBe('flex');
        expect(aside.style.getPropertyPriority('display')).toBe('important');
        expect(document.head.querySelectorAll('style')).toHaveLength(originalStyles);
    });

    test('preserves a fixed capture ancestor and explicitly preserved overlays', () => {
        document.body.innerHTML =
            '<main><section style="position: fixed"><div id="capture"></div></section></main><aside><div id="popup" style="position: fixed"></div></aside><footer style="position: fixed"></footer>';
        const restore = prepareScreenshotStyles(document, {
            ...options,
            captureSelector: '#capture',
            preserveFixedSelectors: ['#popup'],
        });
        expect(document.querySelector('section')!.style.display).toBe('');
        expect(document.querySelector<HTMLElement>('#popup')!.style.display).toBe('');
        expect(document.querySelector('footer')!.style.display).toBe('none');
        restore();
        expect(document.querySelector('footer')!.style.display).toBe('');
    });

    test('measures masks in the captured document and removes only its own nodes', () => {
        document.body.innerHTML = '<div id="image-wrapper"></div><div class="blackout">application content</div>';
        const wrapper = document.querySelector('#image-wrapper')!;
        vi.spyOn(wrapper, 'getBoundingClientRect').mockReturnValue({
            x: 10,
            y: 20,
            top: 20,
            left: 10,
            right: 90,
            bottom: 100,
            width: 80,
            height: 80,
            toJSON: () => ({}),
        });
        const remove = createScreenshotBlackouts(document, [
            { selector: '#missing' },
            { selector: '#image-wrapper', zIndex: 20000 },
        ]);
        const cover = document.body.lastElementChild as HTMLElement;
        expect(cover.ownerDocument).toBe(document);
        expect(cover.style.width).toBe('80px');
        expect(cover.style.top).toBe('20px');
        expect(cover.style.left).toBe('10px');
        expect(cover.style.zIndex).toBe('20000');
        remove();
        expect(document.body.children).toHaveLength(2);
        expect(document.querySelector('.blackout')).toHaveTextContent('application content');
    });
});
