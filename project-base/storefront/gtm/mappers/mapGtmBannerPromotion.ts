import { TypeSliderItemFragment } from 'graphql/requests/sliderItems/fragments/SliderItemFragment.generated';
import { GtmPromotionType } from 'gtm/types/events';

export const mapGtmBannerPromotion = (banner: TypeSliderItemFragment): GtmPromotionType => ({
    promotionId: 'homepage_banners_slider',
    promotionName: 'Homepage - carousel',
    creativeName: banner.gtmCreative || banner.name,
    creativeSlot: banner.id,
});
