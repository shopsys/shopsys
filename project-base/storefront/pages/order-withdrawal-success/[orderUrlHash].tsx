import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import {
    OrderWithdrawalInstructionsQueryDocument,
    TypeOrderWithdrawalInstructionsQueryVariables,
    useOrderWithdrawalInstructionsQuery,
} from 'graphql/requests/orders/queries/OrderWithdrawalInstructionsQuery.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import { useRouter } from 'next/router';
import { getBasePathWithLocale } from 'utils/domain/domainUtils';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getStringFromUrlQuery } from 'utils/parsing/getStringFromUrlQuery';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';

const OrderWithdrawalSuccessPage: FC = () => {
    const { t } = useTranslation();
    const router = useRouter();
    const orderHash = getStringFromUrlQuery(router.query.orderUrlHash);

    const [{ data: orderData }] = useOrderWithdrawalInstructionsQuery({
        variables: { urlHash: orderHash },
    });

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <CommonLayout pageTypeOverride="order-withdrawal-success" title={t('Withdrawal request submitted')}>
                <Webline>
                    <ConfirmationPageContent
                        content={orderData?.order?.withdrawalInstructions}
                        heading={t('Your withdrawal request has been submitted')}
                    />
                </Webline>
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) => {
            if (typeof context.params?.orderUrlHash !== 'string') {
                return {
                    redirect: {
                        destination: getBasePathWithLocale('/', context.locale),
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
            });
        },
);

export default OrderWithdrawalSuccessPage;
