import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { WarningIcon } from 'components/Basic/Icon/WarningIcon';
import { PageHero, PageHeroVariant } from 'components/Layout/PageHero/PageHero';
import { TIDs } from 'cypress/tids';
import Trans from 'next-translate/Trans';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { CombinedError } from 'urql';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type ConfirmationPageContentProps = {
    content?: string;
    error?: CombinedError;
    heading: string;
    headingDescription?: string;
    headingIcon: React.ElementType;
    headingVariant?: PageHeroVariant;
    orderDetailUrl?: string;
} & (
    | {
          actionHref: string;
          actionSkeletonType: PageType;
          actionTitle: string;
      }
    | {
          actionHref?: never;
          actionSkeletonType?: never;
          actionTitle?: never;
      }
);

export const ConfirmationPageContent: FC<ConfirmationPageContentProps> = ({
    actionHref,
    actionSkeletonType,
    actionTitle,
    heading,
    headingDescription,
    headingIcon,
    headingVariant,
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
            <div className="mb-4 lg:mt-6">
                {actionHref ? (
                    <PageHero
                        actionHref={actionHref}
                        actionSkeletonType={actionSkeletonType}
                        actionTitle={actionTitle}
                        description={headingDescription}
                        icon={headingIcon}
                        title={heading}
                        variant={headingVariant}
                    />
                ) : (
                    <PageHero
                        description={headingDescription}
                        icon={headingIcon}
                        title={heading}
                        variant={headingVariant}
                    />
                )}
            </div>

            {!!content && (
                <>
                    <div
                        dangerouslySetInnerHTML={{ __html: content }}
                        data-tid={TIDs.order_confirmation_page_text_wrapper}
                        className="text-center"
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
                <div className="mt-4 flex items-center gap-2 rounded-xl border-1 border-toast-border-error bg-toast-bg-error p-5">
                    <WarningIcon className="size-5 text-icon-error" />

                    <p className="text-sm">{applicationError.message}</p>
                </div>
            )}
        </>
    );
};
