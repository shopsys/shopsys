import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { BreadcrumbsMetadata } from 'components/Basic/Head/BreadcrumbsMetadata';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { Webline } from 'components/Layout/Webline/Webline';
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
        <Webline className="mb-4">
            <BreadcrumbsMetadata breadcrumbs={breadcrumbs} />

            <nav aria-label={t('Breadcrumb navigation')} className={twMergeCustom(breadcrumbsTwClass, className)}>
                <ArrowIcon aria-hidden="true" className="text-icon-less size-4 rotate-90 lg:hidden" />

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

                <span
                    className="font-secondary hidden text-xs font-semibold lg:inline-block"
                    data-tid={TIDs.breadcrumbs_tail}
                >
                    {lastBreadcrumb.name}
                </span>
            </nav>
        </Webline>
    );
};

export const BreadcrumbsSpan: FC = ({ tid }) => (
    <span aria-hidden="true" className="text-border-default hidden items-center lg:flex" data-tid={tid}>
        <ArrowIcon className="size-4 -rotate-90" />
    </span>
);

const BreadcrumbsLink: FC<{ href: string; type?: FriendlyPagesTypesKey; skeletonType?: PageType }> = ({
    href,
    type,
    skeletonType,
    children,
}) => (
    <ExtendedNextLink
        className="font-secondary hidden text-xs font-semibold no-underline last-of-type:inline hover:no-underline lg:inline"
        href={href}
        skeletonType={skeletonType}
        type={type}
    >
        {children}
    </ExtendedNextLink>
);
