import { getRandomPageId } from './getRandomPageId';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { GtmPageInfoInterface } from 'gtm/types/objects';

export const getGtmPageInfoType = (
    pageType: GtmPageType,
    breadcrumbs?: TypeBreadcrumbFragment[],
): GtmPageInfoInterface => ({
    type: pageType,
    pageId: getRandomPageId(),
    breadcrumbs: breadcrumbs ?? [],
});
