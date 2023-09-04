import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { BreadcrumbsMetadata } from 'components/Basic/Head/BreadcrumbsMetadata';
import { ArrowIcon } from 'components/Basic/Icon/IconsSvg';
import { BreadcrumbFragmentApi } from 'graphql/generated';
import { twMergeCustom } from 'helpers/twMerge';
import useTranslation from 'next-translate/useTranslation';
import { Fragment } from 'react';
import { FriendlyPagesTypesKeys } from 'types/friendlyUrl';

type BreadcrumbsProps = {
    breadcrumbs: BreadcrumbFragmentApi[];
    type?: FriendlyPagesTypesKeys;
};

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
        <div
            className={twMergeCustom(
                'flex items-center gap-2 border-b-2 border-greyLighter py-3 lg:ml-4 lg:border-none lg:py-0',
                className,
            )}
            data-testid={TEST_IDENTIFIER}
        >
            <BreadcrumbsMetadata breadcrumbs={breadcrumbs} />

            <ArrowIcon className="mr-3 w-3 rotate-90 text-greyLight lg:hidden" />

            <BreadcrumbsLink href="/" dataTestId={TEST_IDENTIFIER + '-item-root'}>
                {t('Home page')}
            </BreadcrumbsLink>

            <BreadcrumbsSpan>/</BreadcrumbsSpan>

            {linkedBreadcrumbs.map((linkedBreadcrumb, index) => (
                <Fragment key={index}>
                    <BreadcrumbsLink
                        href={linkedBreadcrumb.slug}
                        type={type}
                        dataTestId={TEST_IDENTIFIER + '-item-' + index}
                    >
                        {linkedBreadcrumb.name}
                    </BreadcrumbsLink>
                    <BreadcrumbsSpan>/</BreadcrumbsSpan>
                </Fragment>
            ))}

            <BreadcrumbsSpan dataTestId={TEST_IDENTIFIER + '-item-last'}>{lastBreadcrumb.name}</BreadcrumbsSpan>
        </div>
    );
};

const BreadcrumbsSpan: FC = ({ children, dataTestId }) => (
    <span className="hidden text-greyLight lg:inline-block" data-testid={dataTestId}>
        {children}
    </span>
);

const BreadcrumbsLink: FC<{ href: string; type?: FriendlyPagesTypesKeys }> = ({ href, type, children, dataTestId }) => (
    <ExtendedNextLink
        href={href}
        type={type || 'static'}
        className="hidden text-greyLight no-underline last-of-type:inline lg:inline lg:text-primary lg:underline"
        data-testid={dataTestId}
    >
        {children}
    </ExtendedNextLink>
);
