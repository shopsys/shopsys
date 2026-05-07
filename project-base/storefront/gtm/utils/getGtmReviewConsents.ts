import { GtmReviewConsentsType } from 'gtm/types/objects';

export const getGtmReviewConsents = (areReviewConsentsGranted: boolean): GtmReviewConsentsType => ({
    google: areReviewConsentsGranted,
    seznam: areReviewConsentsGranted,
    heureka: areReviewConsentsGranted,
});
