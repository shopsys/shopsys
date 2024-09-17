import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { BreadcrumbsMetadata } from 'components/Basic/Head/BreadcrumbsMetadata';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { TIDs } from 'cypress/tids';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { Fragment } from 'react';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { FriendlyPagesTypesKey } from 'types/friendlyUrl';
import { twMergeCustom } from 'utils/twMerge';

type BreadcrumbsProps = {
    breadcrumbs: TypeBreadcrumbFragment[];
    type?: FriendlyPagesTypesKey;
};

export const breadcrumbsTwClass = 'flex items-center gap-3';

export const Breadcrumbs: FC<BreadcrumbsProps> = ({ breadcrumbs, type, className }) => {
    const { t } = useTranslation();

    if (!breadcrumbs.length) {
        return null;
    }

    const lastIndex = breadcrumbs.length - 1;
    const linkedBreadcrumbs = breadcrumbs.slice(0, lastIndex);
    const lastBreadcrumb = breadcrumbs[lastIndex];

    return (
        <>
            <BreadcrumbsMetadata breadcrumbs={breadcrumbs} />

            <div className={twMergeCustom(breadcrumbsTwClass, className)}>
                <ArrowIcon className="mr-3 w-2.5 rotate-90 text-borderAccent lg:hidden" />

                <BreadcrumbsLink href="/" skeletonType="homepage">
                    {t('Home page')}
                </BreadcrumbsLink>

                <BreadcrumbsSpan />

                {linkedBreadcrumbs.map((linkedBreadcrumb, index) => (
                    <Fragment key={index}>
                        <BreadcrumbsLink href={linkedBreadcrumb.slug} type={type}>
                            {linkedBreadcrumb.name}
                        </BreadcrumbsLink>
                        <BreadcrumbsSpan />
                    </Fragment>
                ))}

                <span className="hidden text-[13px] font-semibold lg:inline-block" tid={TIDs.breadcrumbs_tail}>
                    {lastBreadcrumb.name}
                </span>
            </div>
        </>
    );
};

export const BreadcrumbsSpan: FC = ({ tid }) => (
    <span className="hidden items-center text-borderAccent lg:flex" tid={tid}>
        <ArrowIcon className="w-2.5 -rotate-90" />
    </span>
);

const BreadcrumbsLink: FC<{ href: string; type?: FriendlyPagesTypesKey; skeletonType?: PageType }> = ({
    href,
    type,
    skeletonType,
    children,
}) => (
    <ExtendedNextLink
        className="hidden font-secondary text-[13px] font-semibold no-underline last-of-type:inline hover:no-underline lg:inline"
        href={href}
        skeletonType={skeletonType}
        type={type}
    >
        {children}
    </ExtendedNextLink>
);
