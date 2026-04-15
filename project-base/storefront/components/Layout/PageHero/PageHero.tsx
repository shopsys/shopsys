import { LinkButton } from 'components/Forms/Button/LinkButton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { ReactNode } from 'react';
import { PageType } from 'store/slices/createPageLoadingStateSlice';

type BasePageHeroProps = {
    icon: React.ElementType;
    title: string | ReactNode;
    titleTid?: string;
    description?: string | ReactNode;
    descriptionTid?: string;
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
}) => {
    return (
        <VerticalStack gap="xs">
            <div className="mx-auto flex size-14 items-center justify-center rounded-full bg-background-most">
                <IconComponent aria-hidden="true" className="size-7" focusable="false" />
            </div>

            <h1 className="h3 text-center" data-tid={titleTid}>
                {title}
            </h1>

            {description && (
                <p
                    aria-atomic="true"
                    aria-live="polite"
                    className="mx-auto max-w-[520px] text-balance text-center"
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
