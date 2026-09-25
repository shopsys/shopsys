import { TypeSliderItemFragment } from 'graphql/requests/sliderItems/fragments/SliderItemFragment.generated';
import { mapGtmBannerPromotion } from 'gtm/mappers/mapGtmBannerPromotion';
import { describe, expect, test } from 'vitest';

const banner = {
    __typename: 'SliderItem',
    id: 42,
    uuid: '8e6a3287-e691-457a-baf4-6ca6a67e10ac',
    gtmCreative: '',
    name: 'Christmas sale',
    link: '/christmas-sale/',
    routeName: null,
    description: null,
    rgbBackgroundColor: '#ffffff',
    opacity: 1,
    webMainImage: { __typename: 'Image', name: 'Desktop banner', url: '/desktop.jpg' },
    mobileMainImage: { __typename: 'Image', name: 'Mobile banner', url: '/mobile.jpg' },
} satisfies TypeSliderItemFragment;

describe('mapGtmBannerPromotion', () => {
    test('uses the banner name when GTM creative is empty', () => {
        expect(mapGtmBannerPromotion(banner)).toMatchObject({ creativeName: 'Christmas sale' });
    });
});
