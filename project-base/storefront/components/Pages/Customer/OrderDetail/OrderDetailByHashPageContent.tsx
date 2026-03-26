import { DocumentIcon } from 'components/Basic/Icon/DocumentIcon';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { TypeOrderDetailByHashQuery } from 'graphql/requests/orders/queries/OrderDetailByHashQuery.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { OrderDetailContent } from './OrderDetailContent';

type OrderDetailByHashPageContentProps = {
    breadcrumbs: TypeBreadcrumbFragment[];
    isOrderDataFetching: boolean;
    order: NonNullable<TypeOrderDetailByHashQuery['order']> | undefined;
};

export const OrderDetailByHashPageContent: FC<OrderDetailByHashPageContentProps> = ({
    breadcrumbs,
    isOrderDataFetching,
    order,
}) => {
    const { t } = useTranslation();

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    const pageTitle = order?.number ? `${t('Order number')} ${order.number}` : t('Order details');

    return (
        <CommonLayout
            breadcrumbs={breadcrumbs}
            isFetchingData={isOrderDataFetching}
            pageTypeOverride="order-detail-public"
            title={pageTitle}
        >
            {!!order && (
                <Webline width="lg">
                    <VerticalStack gap="sm">
                        <PageHero
                            icon={DocumentIcon}
                            title={`${t('Your order')} ${order.number}`}
                            titleTid={TIDs.order_detail_number_heading}
                        />

                        <OrderDetailContent order={order} />
                    </VerticalStack>
                </Webline>
            )}
        </CommonLayout>
    );
};
