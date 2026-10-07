type ScreenshotStyleOptions = {
    captureSelector?: string;
    preserveFixedSelectors: string[];
    disablePointerEventsSelectors: string[];
};

export const prepareScreenshotStyles = (doc: Document, options: ScreenshotStyleOptions): (() => void) => {
    const restorers: (() => void)[] = [];
    const overrideStyle = (element: HTMLElement, property: string, value: string) => {
        const original = element.style.getPropertyValue(property);
        const priority = element.style.getPropertyPriority(property);
        element.style.setProperty(property, value, 'important');
        restorers.push(() => {
            if (original) {
                element.style.setProperty(property, original, priority);
            } else {
                element.style.removeProperty(property);
            }
        });
    };
    const style = doc.createElement('style');
    style.textContent = `
        ::-webkit-scrollbar { display: none; }
        * { scrollbar-width: none !important; }
        *, *::before, *::after {
            transition: none !important;
            animation: none !important;
            caret-color: transparent !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
        }
        ${options.disablePointerEventsSelectors.length ? `${options.disablePointerEventsSelectors.join(', ')} { pointer-events: none !important; }` : ''}
    `;
    doc.head.appendChild(style);
    restorers.push(() => style.remove());

    const captureElement = options.captureSelector ? doc.querySelector(options.captureSelector) : null;
    const preserved = options.preserveFixedSelectors.flatMap((selector) => Array.from(doc.querySelectorAll(selector)));
    doc.querySelectorAll<HTMLElement>('*').forEach((element) => {
        const position = doc.defaultView!.getComputedStyle(element).position;
        if (position === 'sticky') {
            overrideStyle(element, 'position', 'static');
        } else if (position === 'fixed') {
            const containsCapture = captureElement && (element === captureElement || element.contains(captureElement));
            const isPreserved = preserved.some(
                (target) =>
                    element === target ||
                    element.contains(target) ||
                    (target.parentElement !== null && target.parentElement === element.parentElement),
            );
            if (!containsCapture && !isPreserved) {
                overrideStyle(element, 'display', 'none');
            }
        }
    });

    return () => {
        restorers.reverse().forEach((restore) => restore());
        restorers.length = 0;
    };
};

export const createScreenshotBlackouts = (
    doc: Document,
    masks: { selector: string; zIndex?: number }[],
): (() => void) => {
    const covers: HTMLElement[] = [];
    masks.forEach(({ selector, zIndex }) => {
        doc.querySelectorAll(selector).forEach((element) => {
            const rect = element.getBoundingClientRect();
            const cover = doc.createElement('div');
            Object.assign(cover.style, {
                position: 'absolute',
                width: `${rect.width}px`,
                height: `${rect.height}px`,
                top: `${rect.top + doc.defaultView!.scrollY}px`,
                left: `${rect.left + doc.defaultView!.scrollX}px`,
                backgroundColor: 'black',
                zIndex: String(zIndex ?? 10000),
                pointerEvents: 'none',
            });
            doc.body.appendChild(cover);
            covers.push(cover);
        });
    });
    return () => covers.forEach((cover) => cover.remove());
};
