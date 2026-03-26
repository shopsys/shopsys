import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import { OrderCustomerInfo } from 'components/Blocks/OrderCustomerInfo/OrderCustomerInfo';
import { SkeletonPageConfirmation } from 'components/Blocks/Skeleton/SkeletonPageConfirmation';
import { Button } from 'components/Forms/Button/Button';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { PaymentStatus } from 'components/Pages/Order/PaymentConfirmation/PaymentStatus';
import { PaymentVerificationLoader } from 'components/Pages/Order/PaymentConfirmation/PaymentVerificationLoader';
import { useUpdatePaymentStatus } from 'components/Pages/Order/PaymentConfirmation/paymentConfirmationUtils';
import { ShowPaymentInstructionButton } from 'components/Pages/Order/PaymentConfirmation/ShowPaymentInstructionButton';
import { useGoPaySessionRecovery } from 'components/Pages/Order/PaymentConfirmation/useGoPaySessionRecovery';
import { useSanitizeOrderPaymentQuery } from 'components/Pages/Order/PaymentConfirmation/useSanitizeOrderPaymentQuery';
import { OrderConfirmationProducts } from 'components/Pages/OrderConfirmation/OrderConfirmationProducts';
import { OrderConfirmationStepper } from 'components/Pages/OrderConfirmation/OrderConfirmationStepper';
import { FlowTypesEnum } from 'components/Pages/OrderConfirmation/OrderConfirmationStepperFlows';
import { OrderConfirmationSummary } from 'components/Pages/OrderConfirmation/OrderConfirmationSummary';
import { RegistrationAfterOrder } from 'components/Pages/OrderConfirmation/RegistrationAfterOrder';
import { PaymentsInOrderSelect } from 'components/PaymentsInOrderSelect/PaymentsInOrderSelect';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useOrderDetailByHashOrUuidQuery } from 'graphql/requests/orders/queries/OrderDetailByHashOrUuidQuery.generated';
import { TypeCustomerUserRoleEnum, TypeOrderItemTypeEnum, TypePaymentContentPageStatusEnum } from 'graphql/types';
import { useRouter } from 'next/router';
import { getBasePathWithLocale } from 'utils/domain/domainUtils';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { hasOrderExternalPaymentContext } from 'utils/mappers/order';
import { getStringFromUrlQuery } from 'utils/parsing/getStringFromUrlQuery';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showInfoMessage } from 'utils/toasts/showInfoMessage';

export type OrderPaymentConfirmationUrlQuery = {
    orderIdentifier: string | undefined;
    orderEmail: string | undefined;
    orderUrlHash?: string | undefined;
    orderPaymentStatusPageValidityHash: string | undefined;
};

const OrderPaymentConfirmationPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const router = useRouter();
    const domainConfig = useDomainConfig();
    const { url: domainUrl } = domainConfig;

    const { orderIdentifier, orderEmail, orderUrlHash, orderPaymentStatusPageValidityHash } =
        router.query as OrderPaymentConfirmationUrlQuery;
    const orderUuid = getStringFromUrlQuery(orderIdentifier);
    const urlHash = getStringFromUrlQuery(orderUrlHash);
    const orderPaymentStatusPageValidityHashParam = getStringFromUrlQuery(orderPaymentStatusPageValidityHash);

    const { paymentStatusData, statusError, isCheckingStatus, recheckPaymentStatus } = useUpdatePaymentStatus(
        orderUuid,
        orderPaymentStatusPageValidityHashParam || null,
    );
    const paymentStatusOrder = paymentStatusData?.UpdatePaymentStatus;
    const paymentPageContent = paymentStatusOrder?.paymentPageContent;
    const resolvedOrderUrlHash = urlHash || paymentStatusOrder?.urlHash || '';
    const orderDetailUrlFromQuery = resolvedOrderUrlHash
        ? getInternationalizedStaticUrls([{ url: '/order-detail/:urlHash', param: resolvedOrderUrlHash }], domainUrl)[0]
        : undefined;

    const hasPaymentStatus = !!paymentStatusData || statusError;
    const hasPaymentStatusError = statusError && !paymentStatusData;

    const [{ data: orderData, fetching: isOrderFetching, error: orderDetailError }] = useOrderDetailByHashOrUuidQuery({
        variables: {
            urlHash: resolvedOrderUrlHash || undefined,
            uuid: resolvedOrderUrlHash ? undefined : orderUuid || undefined,
        },
        requestPolicy: 'network-only',
        pause: !resolvedOrderUrlHash && !orderUuid,
    });

    const order = orderData?.order;

    // Client-side fallback for PII sanitization — primary removal happens in getServerSideProps redirect,
    // but client-side navigation (e.g. SPA transitions) can bypass server-side props.
    useSanitizeOrderPaymentQuery(domainConfig, orderEmail, urlHash);
    useGoPaySessionRecovery(domainConfig, orderUuid, orderPaymentStatusPageValidityHashParam);

    const orderPaymentPageStatus = paymentPageContent?.status;
    const paymentSessionExpiredErrorMessage =
        paymentStatusData && paymentPageContent === null ? t('Order sent page is not available.') : '';
    const paymentStatusFetchErrorMessage = hasPaymentStatusError
        ? t(
              'Please try checking your payment status again. If the problem persists, check your email for order details.',
          )
        : '';

    const pageTitle = t('Order sent');

    const isPageReady = hasPaymentStatus && !isOrderFetching;
    const handleRecheckPaymentStatus = async () => {
        const recheckedPaymentStatus = await recheckPaymentStatus();

        if (recheckedPaymentStatus === 'error') {
            showErrorMessage(t('Failed to check payment status. Please try again.'));

            return;
        }

        if (recheckedPaymentStatus === TypePaymentContentPageStatusEnum.InProcess) {
            showInfoMessage(t('Payment status checked. The payment is still being processed.'));
        }
    };

    const orderPayment = order?.items.find((item) => item.type === TypeOrderItemTypeEnum.Payment);
    const orderTransport = order?.items.find((item) => item.type === TypeOrderItemTypeEnum.Transport);
    const orderRounding = order?.items.find((item) => item.type === TypeOrderItemTypeEnum.Rounding);
    const hasExternalPaymentContext = hasOrderExternalPaymentContext(order);
    const stepperFlow = order
        ? orderPaymentPageStatus === TypePaymentContentPageStatusEnum.Successful || order.isPaid
            ? FlowTypesEnum.PaymentSuccess
            : orderPaymentPageStatus === TypePaymentContentPageStatusEnum.InProcess || order.hasPaymentInProcess
              ? FlowTypesEnum.PaymentInProcess
              : FlowTypesEnum.PaymentFailed
        : undefined;

    const missingOrderFallbackContent =
        isPageReady && !paymentSessionExpiredErrorMessage && !orderDetailError
            ? t("We couldn't display the order confirmation details. Check your email for order details.")
            : undefined;

    return (
        <>
            <MetaRobots content="noindex" />

            <CommonLayout pageTypeOverride="order-confirmation" title={pageTitle}>
                <Webline>
                    {!isPageReady && (
                        <>
                            <PaymentVerificationLoader />

                            <SkeletonPageConfirmation />
                        </>
                    )}

                    {isPageReady && (paymentSessionExpiredErrorMessage || !order) && (
                        <ConfirmationPageContent
                            content={paymentSessionExpiredErrorMessage || missingOrderFallbackContent}
                            error={!paymentSessionExpiredErrorMessage ? orderDetailError : undefined}
                            heading={paymentSessionExpiredErrorMessage ? t('Your payment session expired') : pageTitle}
                            headingClassName={paymentSessionExpiredErrorMessage ? 'text-text-error' : undefined}
                            orderDetailUrl={!paymentSessionExpiredErrorMessage ? orderDetailUrlFromQuery : undefined}
                        />
                    )}

                    {isPageReady && order && hasPaymentStatusError && (
                        <ConfirmationPageContent
                            content={paymentStatusFetchErrorMessage}
                            heading={t("We couldn't verify your payment status")}
                            headingClassName="text-text-error"
                        >
                            <div className="mt-4 flex flex-wrap items-center gap-4">
                                <Button
                                    disabled={isCheckingStatus}
                                    size="small"
                                    variant="secondary"
                                    onClick={() => void handleRecheckPaymentStatus()}
                                >
                                    {isCheckingStatus ? (
                                        <>
                                            <SpinnerIcon className="size-4" />
                                            {t('Checking...')}
                                        </>
                                    ) : (
                                        t('Check payment status')
                                    )}
                                </Button>
                            </div>
                        </ConfirmationPageContent>
                    )}

                    {isPageReady && order && !paymentSessionExpiredErrorMessage && !hasPaymentStatusError && (
                        <>
                            <PaymentStatus
                                orderData={orderData}
                                paymentStatusData={paymentStatusData}
                                statusOverride={orderPaymentPageStatus}
                            />

                            {orderPaymentPageStatus === TypePaymentContentPageStatusEnum.InProcess && (
                                <div className="mt-4 flex flex-col items-start gap-2">
                                    {order.lastExternalPaymentUrl && (
                                        <p className="text-sm text-text-less">
                                            {t(
                                                'If you have already completed the payment outside the store, you can check the latest payment status.',
                                            )}
                                        </p>
                                    )}

                                    <div className="flex flex-wrap items-center gap-4">
                                        {order.lastExternalPaymentUrl && (
                                            <ShowPaymentInstructionButton
                                                href={order.lastExternalPaymentUrl}
                                                orderUrlHash={(orderUrlHash as string | undefined) || order.urlHash}
                                                orderUuid={orderUuid}
                                            />
                                        )}

                                        <Button
                                            disabled={isCheckingStatus}
                                            size="small"
                                            variant="secondary"
                                            onClick={() => void handleRecheckPaymentStatus()}
                                        >
                                            {isCheckingStatus ? (
                                                <>
                                                    <SpinnerIcon className="size-4" />
                                                    {t('Checking...')}
                                                </>
                                            ) : (
                                                t('Check payment status')
                                            )}
                                        </Button>
                                    </div>
                                </div>
                            )}
                        </>
                    )}

                    {/* Zone B: Stepper — only when page is ready + order + stepper flow exist */}
                    {isPageReady && order && !hasPaymentStatusError && stepperFlow && (
                        <OrderConfirmationStepper flow={stepperFlow} />
                    )}

                    {/* Zone C: Content grid — only when page is ready + order exists */}
                    {isPageReady && order && !hasPaymentStatusError && (
                        <div className="grid vl:grid-cols-3 gap-4 vl:gap-10">
                            <div className="vl:col-span-2 flex vl:flex-col flex-col-reverse gap-4">
                                {orderPaymentPageStatus === TypePaymentContentPageStatusEnum.Failed &&
                                    hasExternalPaymentContext && (
                                        <PaymentsInOrderSelect
                                            orderNumber={order.number}
                                            orderUrlHash={(orderUrlHash as string | undefined) || order.urlHash}
                                            orderUuid={order.uuid}
                                            paymentTransactionsCount={order.paymentTransactionsCount}
                                        />
                                    )}

                                {orderPaymentPageStatus === TypePaymentContentPageStatusEnum.InProcess && (
                                    <OrderCustomerInfo order={order} />
                                )}

                                {orderPaymentPageStatus === TypePaymentContentPageStatusEnum.Successful && (
                                    <>
                                        <OrderCustomerInfo order={order} />

                                        <RegistrationAfterOrder
                                            orderEmail={(orderEmail as string | undefined) || order.email}
                                            orderUrlHash={(orderUrlHash as string | undefined) || order.urlHash}
                                            orderUuid={order.uuid}
                                        />
                                    </>
                                )}
                            </div>

                            <div className="vl:col-span-1 flex flex-1 flex-col gap-2.5">
                                <OrderConfirmationProducts items={order.items} />

                                <OrderConfirmationSummary
                                    promoCode={order.promoCode}
                                    roundingPrice={orderRounding?.totalPrice}
                                    totalPrice={order.totalPrice}
                                    payment={{
                                        name: orderPayment?.name,
                                        price: orderPayment?.totalPrice.priceWithVat,
                                    }}
                                    transport={{
                                        name: orderTransport?.name,
                                        price: orderTransport?.totalPrice.priceWithVat,
                                    }}
                                />
                            </div>
                        </div>
                    )}
                </Webline>
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    const orderUuid = getStringFromUrlQuery(context.query.orderIdentifier);

    if (orderUuid === '') {
        return {
            redirect: {
                destination: getBasePathWithLocale('/', context),
                statusCode: 301,
            },
        };
    }

    // Remove PII (orderEmail) and empty orderUrlHash from URL server-side to avoid client-side re-render flash
    const hasEmailToRemove = !!context.query.orderEmail;
    const hasEmptyHashToRemove = context.query.orderUrlHash === '';

    if (hasEmailToRemove || hasEmptyHashToRemove) {
        const sanitizedQuery = { ...context.query };
        delete sanitizedQuery.orderEmail;

        if (hasEmptyHashToRemove) {
            delete sanitizedQuery.orderUrlHash;
        }

        const queryString = new URLSearchParams(sanitizedQuery as Record<string, string>).toString();
        const [orderPaymentConfirmationUrl] = getInternationalizedStaticUrls(
            ['/order-payment-confirmation'],
            domainConfig.url,
        );

        return {
            redirect: {
                destination: `${getBasePathWithLocale(orderPaymentConfirmationUrl, context)}${
                    queryString ? `?${queryString}` : ''
                }`,
                statusCode: 302,
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

export default OrderPaymentConfirmationPage;
