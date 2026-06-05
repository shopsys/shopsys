import { LinkButton } from 'components/Forms/Button/LinkButton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { ReactNode } from 'react';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { twJoin } from 'tailwind-merge';

export type PageHeroVariant = 'default' | 'error' | 'info' | 'success';

type BasePageHeroProps = {
    icon: React.ElementType;
    title: string | ReactNode;
    titleTid?: string;
    description?: string | ReactNode;
    descriptionTid?: string;
    variant?: PageHeroVariant;
};

type PageHeroProps = BasePageHeroProps &
    (
        | {
              actionHref: string;
              actionTitle: string;
              actionSkeletonType: PageType;
          }
        | {
              actionHref?: never;
              actionTitle?: never;
              actionSkeletonType?: never;
          }
    );

export const PageHero: FC<PageHeroProps> = ({
    icon: IconComponent,
    title,
    titleTid,
    description,
    descriptionTid,
    actionHref,
    actionTitle,
    actionSkeletonType,
    variant = 'default',
}) => {
    const variantClasses: Record<PageHeroVariant, string> = {
        default: 'bg-background-most text-icon-default',
        error: 'bg-toast-bg-error text-icon-error',
        info: 'bg-background-accent-less text-icon-accent',
        success: 'bg-toast-bg-success text-icon-success',
    };

    return (
        <VerticalStack gap="xs">
            <div
                className={twJoin(
                    'mx-auto flex size-14 items-center justify-center rounded-full',
                    variantClasses[variant],
                )}
            >
                <IconComponent aria-hidden="true" className="size-7" focusable="false" />
            </div>

            <h1 className="h3 text-center" data-tid={titleTid}>
                {title}
            </h1>

            {description && (
                <p
                    aria-atomic="true"
                    aria-live="polite"
                    className="mx-auto max-w-130 text-balance text-center"
                    data-tid={descriptionTid}
                    role="alert"
                >
                    {description}
                </p>
            )}

            {actionHref && actionTitle && (
                <LinkButton className="self-center" href={actionHref} skeletonType={actionSkeletonType}>
                    {actionTitle}
                </LinkButton>
            )}
        </VerticalStack>
    );
};
