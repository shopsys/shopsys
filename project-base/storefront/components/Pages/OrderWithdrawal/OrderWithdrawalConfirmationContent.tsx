import { WarningIcon } from 'components/Basic/Icon/WarningIcon';
import { WithdrawalIcon } from 'components/Basic/Icon/WithdrawalIcon';
import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import { SkeletonPageOrderWithdrawalConfirmation } from 'components/Blocks/Skeleton/SkeletonPageOrderWithdrawalConfirmation';
import { Button } from 'components/Forms/Button/Button';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { useConfirmOrderWithdrawalRequestMutation } from 'graphql/requests/orders/mutations/ConfirmOrderWithdrawalRequestMutation.generated';
import { OrderWithdrawalDataQueryDocument } from 'graphql/requests/orders/queries/OrderWithdrawalDataQuery.generated';
import { onGtmWithdrawalEventHandler } from 'gtm/handlers/onGtmWithdrawalEventHandler';
import { useRouter } from 'next/router';
import { useState } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { useClient } from 'urql';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getStringFromUrlQuery } from 'utils/parsing/getStringFromUrlQuery';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export const OrderWithdrawalConfirmationContent: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const router = useRouter();
    const confirmationHash = getStringFromUrlQuery(router.query.hash);
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const [{ error: confirmationError }, confirmOrderWithdrawalRequest] = useConfirmOrderWithdrawalRequestMutation();
    const client = useClient();
    const [isConfirmationInProgress, setIsConfirmationInProgress] = useState(false);

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

    return confirmationError ? (
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
                    description={t('Click the button below to confirm the withdrawal request for your order.')}
                    icon={WithdrawalIcon}
                    title={t('Withdrawal request confirmation')}
                />
            </div>

            <div className="flex justify-center">
                <Button tid={TIDs.order_withdrawal_confirmation_button} onClick={onConfirmWithdrawalRequestHandler}>
                    {t('Confirm withdrawal request')}
                </Button>
            </div>
        </Webline>
    );
};
