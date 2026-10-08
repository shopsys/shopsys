import { fireEvent, render, screen } from '@testing-library/react';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { useGtmPromotionListViewEvent } from 'gtm/utils/pageReadyEvents/useGtmPromotionListViewEvent';
import { ReactNode } from 'react';
import { describe, expect, test, vi } from 'vitest';

const { onGtmPromotionClickEventHandlerMock } = vi.hoisted(() => ({
    onGtmPromotionClickEventHandlerMock: vi.fn(),
}));

vi.mock('components/Layout/Webline/Webline', () => ({
    Webline: ({ children }: { children: ReactNode }) => <div>{children}</div>,
}));

vi.mock('graphql/requests/adverts/queries/AdvertsQuery.generated', () => ({
    useAdvertsQuery: () => [
        {
            data: {
                adverts: [
                    {
                        __typename: 'AdvertCode',
                        uuid: '8e6a3287-e691-457a-baf4-6ca6a67e10ac',
                        name: 'Summer promotion',
                        positionName: 'footer',
                        type: 'code',
                        categories: [],
                        code: '<a href="/summer-sale/">Summer sale</a>',
                    },
                ],
            },
        },
    ],
}));

vi.mock('gtm/handlers/onGtmPromotionClickEventHandler', () => ({
    onGtmPromotionClickEventHandler: onGtmPromotionClickEventHandlerMock,
}));

vi.mock('gtm/utils/pageReadyEvents/useGtmPromotionListViewEvent', () => ({
    useGtmPromotionListViewEvent: vi.fn(),
}));

describe('Adverts', () => {
    test('identifies the advert by UUID in impressions and clicks on a link rendered by AdvertCode', () => {
        render(<Adverts positionName="footer" />);

        const link = screen.getByRole('link', { name: 'Summer sale' });
        link.addEventListener('click', (event) => event.preventDefault());
        fireEvent.click(link);

        expect(useGtmPromotionListViewEvent).toHaveBeenCalledWith([
            {
                promotionId: '8e6a3287-e691-457a-baf4-6ca6a67e10ac',
                promotionName: 'footer',
                creativeName: 'Summer promotion',
                creativeSlot: undefined,
            },
        ]);
        expect(onGtmPromotionClickEventHandlerMock).toHaveBeenCalledWith(
            {
                promotionId: '8e6a3287-e691-457a-baf4-6ca6a67e10ac',
                promotionName: 'footer',
                creativeName: 'Summer promotion',
                creativeSlot: undefined,
            },
            '/summer-sale/',
        );
    });
});
