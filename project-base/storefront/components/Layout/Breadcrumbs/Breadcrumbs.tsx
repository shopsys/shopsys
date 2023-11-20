import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { BreadcrumbsMetadata } from 'components/Basic/Head/BreadcrumbsMetadata';
import { ArrowIcon } from 'components/Basic/Icon/IconsSvg';
import { BreadcrumbFragmentApi } from 'graphql/generated';
import { twMergeCustom } from 'helpers/twMerge';
import useTranslation from 'next-translate/useTranslation';
import { Fragment } from 'react';
import { FriendlyPagesTypesKey } from 'types/friendlyUrl';

type BreadcrumbsProps = {
    breadcrumbs: BreadcrumbFragmentApi[];
    type?: FriendlyPagesTypesKey;
};

export const breadcrumbsTwClass =
    'flex items-center gap-2 border-b-2 border-greyLighter py-3 lg:ml-4 lg:border-none lg:py-0';

const TEST_IDENTIFIER = 'layout-breadcrumbs';

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

            <div className={twMergeCustom(breadcrumbsTwClass, className)} data-testid={TEST_IDENTIFIER}>
                <ArrowIcon className="mr-3 w-3 rotate-90 text-greyLight lg:hidden" />

                <BreadcrumbsLink dataTestId={TEST_IDENTIFIER + '-item-root'} href="/">
                    {t('Home page')}
                </BreadcrumbsLink>

                <BreadcrumbsSpan>/</BreadcrumbsSpan>

                {linkedBreadcrumbs.map((linkedBreadcrumb, index) => (
                    <Fragment key={index}>
                        <BreadcrumbsLink
                            dataTestId={TEST_IDENTIFIER + '-item-' + index}
                            href={linkedBreadcrumb.slug}
                            type={type}
                        >
                            {linkedBreadcrumb.name}
                        </BreadcrumbsLink>
                        <BreadcrumbsSpan>/</BreadcrumbsSpan>
                    </Fragment>
                ))}

                <BreadcrumbsSpan dataTestId={TEST_IDENTIFIER + '-item-last'}>{lastBreadcrumb.name}</BreadcrumbsSpan>
            </div>
        </>
    );
};

export const BreadcrumbsSpan: FC = ({ children, dataTestId }) => (
    <span className="hidden text-greyLight lg:inline-block" data-testid={dataTestId}>
        {children}
    </span>
);

const BreadcrumbsLink: FC<{ href: string; type?: FriendlyPagesTypesKey }> = ({ href, type, children, dataTestId }) => (
    <ExtendedNextLink
        className="hidden text-greyLight no-underline last-of-type:inline lg:inline lg:text-primary lg:underline"
        data-testid={dataTestId}
        href={href}
        type={type}
    >
        {children}
    </ExtendedNextLink>
);
