import { act, render } from '@testing-library/react';
import { TabIndicator } from 'components/Basic/TabIndicator/TabIndicator';
import { useRef } from 'react';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

const TAB_WIDTH = 100;
const TAB_HEIGHT = 44;

const Tabs: FC<{ currentTab: string | null }> = ({ currentTab }) => {
    const tabsRef = useRef<HTMLDivElement>(null);

    return (
        <div ref={tabsRef} className="relative">
            {['first', 'second'].map((tab) => (
                <button key={tab} aria-current={currentTab === tab ? 'location' : undefined} type="button">
                    {tab}
                </button>
            ))}

            <TabIndicator activeKey={currentTab} containerRef={tabsRef} />
        </div>
    );
};

const getIndicator = (container: HTMLElement) => container.querySelector<HTMLElement>('span[aria-hidden="true"]');

describe('TabIndicator', () => {
    beforeEach(() => {
        // jsdom has no layout, so every tab is 100px wide and placed by its order in the row
        vi.spyOn(HTMLElement.prototype, 'offsetWidth', 'get').mockReturnValue(TAB_WIDTH);
        vi.spyOn(HTMLElement.prototype, 'offsetHeight', 'get').mockReturnValue(TAB_HEIGHT);
        vi.spyOn(HTMLElement.prototype, 'offsetTop', 'get').mockReturnValue(0);
        vi.spyOn(HTMLElement.prototype, 'offsetLeft', 'get').mockImplementation(function (this: HTMLElement) {
            return this.textContent === 'second' ? TAB_WIDTH : 0;
        });
    });

    afterEach(() => {
        vi.restoreAllMocks();
    });

    test('places the line under the current tab without a transition on the first render', () => {
        const { container } = render(<Tabs currentTab="second" />);
        const indicator = getIndicator(container);

        expect(indicator).toHaveStyle({ transform: 'translate(104px, 41px)', width: '92px' });
        expect(indicator?.className).not.toContain('transition');
    });

    test('slides the line to a newly current tab', async () => {
        const { container, rerender } = render(<Tabs currentTab="first" />);

        await act(() => new Promise((resolve) => window.requestAnimationFrame(resolve)));
        rerender(<Tabs currentTab="second" />);

        const indicator = getIndicator(container);

        expect(indicator?.className).toContain('transition-[transform,width]');
        expect(indicator).toHaveStyle({ transform: 'translate(104px, 41px)' });
    });

    test('renders nothing without a current tab', () => {
        const { container } = render(<Tabs currentTab={null} />);

        expect(getIndicator(container)).toBeNull();
    });
});
