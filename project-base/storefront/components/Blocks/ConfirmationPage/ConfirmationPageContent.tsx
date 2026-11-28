import { Alert } from 'components/Basic/Alert/Alert';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { WarningIcon } from 'components/Basic/Icon/WarningIcon';
import { PageHero, type PageHeroDescriptionRole, type PageHeroVariant } from 'components/Layout/PageHero/PageHero';
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
    headingDescriptionRole?: PageHeroDescriptionRole;
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
    headingDescriptionRole,
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
                        descriptionRole={headingDescriptionRole}
                        icon={headingIcon}
                        title={heading}
                        variant={headingVariant}
                    />
                ) : (
                    <PageHero
                        description={headingDescription}
                        descriptionRole={headingDescriptionRole}
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
                <Alert className="mt-4" icon={InfoIcon} variant="warning">
                    <p>
                        {isLoggedIn && orderDetailUrl ? (
                            <Trans
                                i18nKey="Order content has expired. <link>View order details</link>"
                                components={{
                                    link: (
                                        <ExtendedNextLink
                                            aria-label={t('Go to order detail page', { ns: 'accessibility' })}
                                            href={orderDetailUrl}
                                            skeletonType="orderDetailByHash"
                                        />
                                    ),
                                }}
                            />
                        ) : (
                            t('Order content has expired. Check your email for order details.')
                        )}
                    </p>
                </Alert>
            )}

            {applicationError && !isContentExpiredError && (
                <Alert className="mt-4" icon={WarningIcon} variant="error">
                    {applicationError.message}
                </Alert>
            )}
        </>
    );
};
