import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { getProductReviewHtmlId } from 'components/Blocks/ProductReviews/productReviewUtils';
import { SkeletonModuleCustomerComplaints } from 'components/Blocks/Skeleton/SkeletonModuleCustomerComplaints';
import { CustomerEmptyContent } from 'components/Pages/Customer/CustomerEmptyContent';
import { MyReviewItem } from 'components/Pages/Customer/MyReviews/MyReviewItem';
import { DEFAULT_ORDERS_SIZE } from 'config/constants';
import { TypeCustomerUserProductReviewFragment } from 'graphql/requests/productReviews/fragments/CustomerUserProductReviewFragment.generated';
import { useCurrentCustomerUserProductReviewsQuery } from 'graphql/requests/productReviews/queries/CurrentCustomerUserProductReviewsQuery.generated';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { type RefObject, useEffect, useRef } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { mapConnectionEdges } from 'utils/mappers/connection';
import { useCurrentPageQuery } from 'utils/queryParams/useCurrentPageQuery';
import { useMediaMin } from 'utils/ui/useMediaMin';

type MyReviewsContentProps = {
    paginationScrollTargetRef: RefObject<HTMLDivElement | null>;
};

const PRODUCT_REVIEW_SCROLL_HISTORY_STATE_KEY = 'scrolledProductReviewUuid';

export const MyReviewsContent: FC<MyReviewsContentProps> = ({ paginationScrollTargetRef }) => {
    const { t } = useTranslation();
    const currentPage = useCurrentPageQuery();
    const scrolledReviewUuidRef = useRef<string | null>(null);
    const isDesktop = useMediaMin('vl');

    const [{ data: settingsData }] = useSettingsQuery({ requestPolicy: 'cache-only' });
    const areProductReviewsEnabled = settingsData?.settings?.productReviewsEnabled === true;

    const [{ data: myReviewsData, fetching: areMyReviewsFetching }] = useCurrentCustomerUserProductReviewsQuery({
        variables: {
            first: DEFAULT_ORDERS_SIZE,
            after: getEndCursor(currentPage, 0, DEFAULT_ORDERS_SIZE),
        },
        pause: !areProductReviewsEnabled,
        requestPolicy: 'cache-and-network',
    });

    const myReviews = mapConnectionEdges<TypeCustomerUserProductReviewFragment>(
        myReviewsData?.currentCustomerUserProductReviews.edges,
    );

    useEffect(() => {
        // CommonLayout corrects hash scrolling for the fixed header on desktop.
        if (isDesktop !== false || areMyReviewsFetching || myReviews === undefined) {
            return undefined;
        }

        const targetReview = myReviews.find(
            (productReview) => window.location.hash === `#${getProductReviewHtmlId(productReview.uuid)}`,
        );

        if (targetReview === undefined || scrolledReviewUuidRef.current === targetReview.uuid) {
            return undefined;
        }

        const targetElement = document.getElementById(getProductReviewHtmlId(targetReview.uuid));

        if (targetElement === null) {
            return undefined;
        }

        const animationFrameId = window.requestAnimationFrame(() => {
            const currentHistoryState =
                typeof window.history.state === 'object' && window.history.state !== null ? window.history.state : {};

            if (currentHistoryState[PRODUCT_REVIEW_SCROLL_HISTORY_STATE_KEY] === targetReview.uuid) {
                return;
            }

            window.history.replaceState(
                {
                    ...currentHistoryState,
                    [PRODUCT_REVIEW_SCROLL_HISTORY_STATE_KEY]: targetReview.uuid,
                },
                '',
            );
            targetElement.scrollIntoView({ block: 'start' });
            scrolledReviewUuidRef.current = targetReview.uuid;
        });

        return () => window.cancelAnimationFrame(animationFrameId);
    }, [areMyReviewsFetching, isDesktop, myReviews]);

    if (areMyReviewsFetching || myReviews === undefined) {
        return <SkeletonModuleCustomerComplaints />;
    }

    if (myReviews.length === 0) {
        return (
            <CustomerEmptyContent
                description={t('You can write a review from a product detail or from your order detail.')}
                title={t('You have not written any reviews yet')}
            />
        );
    }

    return (
        <div className="flex scroll-mt-5 flex-col gap-5" ref={paginationScrollTargetRef}>
            <ul className="m-0 flex list-none flex-col p-0">
                {myReviews.map((productReview) => (
                    <MyReviewItem key={productReview.uuid} productReview={productReview} />
                ))}
            </ul>

            <Pagination
                hasNextPage={myReviewsData?.currentCustomerUserProductReviews.pageInfo.hasNextPage}
                pageSize={DEFAULT_ORDERS_SIZE}
                totalCount={myReviewsData?.currentCustomerUserProductReviews.totalCount ?? 0}
            />
        </div>
    );
};
