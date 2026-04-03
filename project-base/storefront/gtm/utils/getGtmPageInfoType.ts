import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { GtmPageInfoInterface } from 'gtm/types/objects';
import { getRandomPageId } from './getRandomPageId';

export const getGtmPageInfoType = (
    pageType: GtmPageType,
    breadcrumbs?: TypeBreadcrumbFragment[],
): GtmPageInfoInterface => ({
    type: pageType,
    pageId: getRandomPageId(),
    breadcrumbs: breadcrumbs ?? [],
});
