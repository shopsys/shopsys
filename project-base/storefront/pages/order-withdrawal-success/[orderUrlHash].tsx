import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { CheckmarkIcon } from 'components/Basic/Icon/CheckmarkIcon';
import { PageGuard } from 'components/Basic/PageGuard/PageGuard';
import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import {
    OrderWithdrawalInstructionsQueryDocument,
    TypeOrderWithdrawalInstructionsQueryVariables,
    useOrderWithdrawalInstructionsQuery,
} from 'graphql/requests/orders/queries/OrderWithdrawalInstructionsQuery.generated';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import { useRouter } from 'next/router';
import { getBasePathWithLocale } from 'utils/domain/domainUtils';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getStringFromUrlQuery } from 'utils/parsing/getStringFromUrlQuery';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const OrderWithdrawalSuccessPage: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const router = useRouter();
    const orderHash = getStringFromUrlQuery(router.query.orderUrlHash);
    const { canRequestWithdrawal: userCanRequestWithdrawal } = useAuthorization();

    const [{ data: orderData, fetching: isOrderFetching }] = useOrderWithdrawalInstructionsQuery({
        variables: { urlHash: orderHash },
    });

    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.other);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);

    const [orderDetailUrl] = getInternationalizedStaticUrls([{ url: '/order-detail/:urlHash', param: orderHash }], url);
    const hasAccess = userCanRequestWithdrawal || isOrderFetching;

    return (
        <>
            <MetaRobots content="noindex" />
            <PageGuard errorRedirectUrl={orderDetailUrl} isWithAccess={hasAccess}>
                <CommonLayout pageTypeOverride="order-withdrawal-success" title={t('Withdrawal request submitted')}>
                    <Webline>
                        <ConfirmationPageContent
                            content={orderData?.order?.withdrawalInstructions}
                            heading={t('Your withdrawal request has been submitted')}
                            headingIcon={CheckmarkIcon}
                            headingVariant="success"
                        />
                    </Webline>
                </CommonLayout>
            </PageGuard>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    if (typeof context.params?.orderUrlHash !== 'string') {
        return {
            redirect: {
                destination: getBasePathWithLocale('/', context),
                statusCode: 301,
            },
        };
    }

    return initServerSideProps<TypeOrderWithdrawalInstructionsQueryVariables>({
        context,
        prefetchedQueries: [
            {
                query: OrderWithdrawalInstructionsQueryDocument,
                variables: { urlHash: context.params.orderUrlHash },
            },
        ],
        redisClient,
        domainConfig,
        t,
        authenticationConfig: {
            authorizedRoles: [TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation],
        },
    });
});

export default OrderWithdrawalSuccessPage;
