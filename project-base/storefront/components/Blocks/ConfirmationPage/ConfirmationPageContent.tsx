import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { TIDs } from 'cypress/tids';
import Trans from 'next-translate/Trans';
import { CombinedError } from 'urql';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type ConfirmationPageContentProps = {
    heading: string;
    headingClassName?: string;
    content?: string;
    error?: CombinedError;
    orderDetailUrl?: string;
};

export const ConfirmationPageContent: FC<ConfirmationPageContentProps> = ({
    heading,
    headingClassName,
    content,
    children,
    error,
    orderDetailUrl,
}) => {
    const { t } = useTranslation();
    const isLoggedIn = useIsUserLoggedIn();
    const { applicationError } = error ? getUserFriendlyErrors(error, t) : { applicationError: undefined };
    const isContentExpiredError = applicationError?.type === 'order-sent-page-not-available';

    return (
        <>
            <h1 className={twMergeCustom('mt-1 mb-4 lg:mt-6', headingClassName)}>{heading}</h1>

            {!!content && (
                <>
                    <div
                        dangerouslySetInnerHTML={{ __html: content }}
                        data-tid={TIDs.order_confirmation_page_text_wrapper}
                    />
                    {children}
                </>
            )}

            {isContentExpiredError && (
                <div className="mt-4 flex items-center gap-2 rounded-xl border-1 border-toast-border-warning bg-toast-bg-warning p-5">
                    <InfoIcon className="size-5 text-icon-warning" />
                    <p className="text-sm">
                        {isLoggedIn && orderDetailUrl ? (
                            <Trans
                                i18nKey="Order content has expired. <link>View order details</link>"
                                components={{
                                    link: (
                                        <ExtendedNextLink
                                            aria-label={t('Go to order detail page', { ns: 'accessibility' })}
                                            href={orderDetailUrl}
                                            type="orderDetail"
                                        />
                                    ),
                                }}
                            />
                        ) : (
                            t('Order content has expired. Check your email for order details.')
                        )}
                    </p>
                </div>
            )}

            {applicationError && !isContentExpiredError && (
                <div className="mt-4">
                    <p className="text-text-error">{applicationError.message}</p>
                </div>
            )}
        </>
    );
};
