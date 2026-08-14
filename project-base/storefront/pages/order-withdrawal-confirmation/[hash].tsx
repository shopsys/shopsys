import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { WarningIcon } from 'components/Basic/Icon/WarningIcon';
import { WithdrawalIcon } from 'components/Basic/Icon/WithdrawalIcon';
import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import { SkeletonPageOrderWithdrawalConfirmation } from 'components/Blocks/Skeleton/SkeletonPageOrderWithdrawalConfirmation';
import { Button } from 'components/Forms/Button/Button';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { useConfirmOrderWithdrawalRequestMutation } from 'graphql/requests/orders/mutations/ConfirmOrderWithdrawalRequestMutation.generated';
import { OrderWithdrawalDataQueryDocument } from 'graphql/requests/orders/queries/OrderWithdrawalDataQuery.generated';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { onGtmWithdrawalEventHandler } from 'gtm/handlers/onGtmWithdrawalEventHandler';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import { useRouter } from 'next/router';
import { useState } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { useClient } from 'urql';
import { getBasePathWithLocale } from 'utils/domain/domainUtils';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getStringFromUrlQuery } from 'utils/parsing/getStringFromUrlQuery';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const OrderWithdrawalConfirmationPage: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const router = useRouter();
    const confirmationHash = getStringFromUrlQuery(router.query.hash);
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const [{ error: confirmationError }, confirmOrderWithdrawalRequest] = useConfirmOrderWithdrawalRequestMutation();
    const client = useClient();
    const [isConfirmationInProgress, setIsConfirmationInProgress] = useState(false);

    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.other);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);

    const onConfirmWithdrawalRequestHandler = async () => {
        if (!confirmationHash || isConfirmationInProgress) {
            return;
        }

        setIsConfirmationInProgress(true);

        const confirmationResult = await confirmOrderWithdrawalRequest({ confirmationHash });
        const orderUrlHash = confirmationResult.data?.ConfirmOrderWithdrawalRequest;

        if (!orderUrlHash) {
            return;
        }

        const orderResult = await client.query(OrderWithdrawalDataQueryDocument, { urlHash: orderUrlHash }).toPromise();
        const orderNumber = orderResult.data?.order?.number;

        if (orderNumber) {
            onGtmWithdrawalEventHandler(orderNumber);
        }

        const [orderWithdrawalSuccessUrl] = getInternationalizedStaticUrls(
            [{ url: '/order-withdrawal-success/:orderUrlHash', param: orderUrlHash }],
            url,
        );
        updatePageLoadingState({ isPageLoading: true, redirectPageType: 'order-withdrawal-success' });
        router.replace(orderWithdrawalSuccessUrl);
    };

    return (
        <>
            <MetaRobots content="noindex" />
            <CommonLayout pageTypeOverride="order-withdrawal-confirmation" title={t('Withdrawal request confirmation')}>
                {confirmationError ? (
                    <Webline>
                        <ConfirmationPageContent
                            heading={t('The confirmation link is invalid or expired')}
                            headingIcon={WarningIcon}
                            headingVariant="error"
                            headingDescription={t(
                                'Submit the withdrawal request again from your order detail to receive a new confirmation email.',
                            )}
                        />
                    </Webline>
                ) : isConfirmationInProgress ? (
                    <SkeletonPageOrderWithdrawalConfirmation />
                ) : (
                    <Webline>
                        <div className="mb-4 lg:mt-6">
                            <PageHero
                                description={t(
                                    'Click the button below to confirm the withdrawal request for your order.',
                                )}
                                icon={WithdrawalIcon}
                                title={t('Withdrawal request confirmation')}
                            />
                        </div>

                        <div className="flex justify-center">
                            <Button
                                tid={TIDs.order_withdrawal_confirmation_button}
                                onClick={onConfirmWithdrawalRequestHandler}
                            >
                                {t('Confirm withdrawal request')}
                            </Button>
                        </div>
                    </Webline>
                )}
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    if (typeof context.params?.hash !== 'string') {
        return {
            redirect: {
                destination: getBasePathWithLocale('/', context),
                statusCode: 301,
            },
        };
    }

    return initServerSideProps({
        context,
        redisClient,
        domainConfig,
        t,
        authenticationConfig: {
            authorizedRoles: [TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation],
        },
    });
});

export default OrderWithdrawalConfirmationPage;
