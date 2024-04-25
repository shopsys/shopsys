import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { BreadcrumbsMetadata } from 'components/Basic/Head/BreadcrumbsMetadata';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { TIDs } from 'cypress/tids';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { Fragment } from 'react';
import { FriendlyPagesTypesKey } from 'types/friendlyUrl';
import { twMergeCustom } from 'utils/twMerge';

type BreadcrumbsProps = {
    breadcrumbs: TypeBreadcrumbFragment[];
    type?: FriendlyPagesTypesKey;
};

export const breadcrumbsTwClass =
    'flex items-center gap-3 border-b-2 border-graySlate py-3 lg:ml-4 lg:border-none lg:py-0';

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
                <ArrowIcon className="mr-3 w-3 rotate-90 text-graySlate lg:hidden" />

                <BreadcrumbsLink href="/">{t('Home page')}</BreadcrumbsLink>

                <BreadcrumbsSpan />

                {linkedBreadcrumbs.map((linkedBreadcrumb, index) => (
                    <Fragment key={index}>
                        <BreadcrumbsLink href={linkedBreadcrumb.slug} type={type}>
                            {linkedBreadcrumb.name}
                        </BreadcrumbsLink>
                        <BreadcrumbsSpan />
                    </Fragment>
                ))}

                <span className="hidden font-semibold text-[13px] lg:inline-block" tid={TIDs.breadcrumbs_tail}>
                    {lastBreadcrumb.name}
                </span>
            </div>
        </>
    );
};

export const BreadcrumbsSpan: FC = ({ tid }) => (
    <span className="hidden text-graySlate lg:flex items-center" tid={tid}>
        <ArrowIcon className="-rotate-90 w-3" />
    </span>
);

const BreadcrumbsLink: FC<{ href: string; type?: FriendlyPagesTypesKey }> = ({ href, type, children }) => (
    <ExtendedNextLink
        className="hidden text-primaryDark no-underline font-bold text-[13px] last-of-type:inline lg:inline hover:no-underline lg:text-primaryDark"
        href={href}
        type={type}
    >
        {children}
    </ExtendedNextLink>
);
