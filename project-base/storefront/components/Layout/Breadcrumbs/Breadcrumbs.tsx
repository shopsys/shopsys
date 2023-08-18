import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { BreadcrumbsMetadata } from 'components/Basic/Head/BreadcrumbsMetadata';
import { Icon } from 'components/Basic/Icon/Icon';
import { Arrow } from 'components/Basic/Icon/IconsSvg';
import { Webline } from 'components/Layout/Webline/Webline';
import { BreadcrumbFragmentApi } from 'graphql/generated';
import useTranslation from 'next-translate/useTranslation';
import { Fragment } from 'react';
import { FriendlyPagesTypesKeys } from 'types/friendlyUrl';

type BreadcrumbsProps = {
    breadcrumb: BreadcrumbFragmentApi[];
    type?: FriendlyPagesTypesKeys;
};

const TEST_IDENTIFIER = 'layout-breadcrumbs';

export const Breadcrumbs: FC<BreadcrumbsProps> = ({ breadcrumb, type }) => {
    const { t } = useTranslation();

    if (breadcrumb.length === 0) {
        return null;
    }

    return (
        <Webline>
            <BreadcrumbsMetadata breadcrumbs={breadcrumb} />
            <div
                className="-mx-5 mb-9 flex items-center border-b-2 border-greyLighter py-3 px-5 lg:mx-0 lg:border-none lg:p-0"
                data-testid={TEST_IDENTIFIER}
            >
                <Icon icon={<Arrow />} className="mr-3 w-3 rotate-90 text-greyLight lg:hidden" />
                <BreadcrumbsLink href="/" dataTestId={TEST_IDENTIFIER + '-item-root'}>
                    {t('Home page')}
                </BreadcrumbsLink>
                <BreadcrumbsSpan>/</BreadcrumbsSpan>
                {breadcrumb.slice(0, breadcrumb.length - 1).map((breadcrumb, index) => (
                    <Fragment key={index}>
                        <BreadcrumbsLink
                            href={breadcrumb.slug}
                            type={type}
                            dataTestId={TEST_IDENTIFIER + '-item-' + index}
                        >
                            {breadcrumb.name}
                        </BreadcrumbsLink>
                        <BreadcrumbsSpan>/</BreadcrumbsSpan>
                    </Fragment>
                ))}
                <BreadcrumbsSpan dataTestId={TEST_IDENTIFIER + '-item-last'}>
                    {breadcrumb[breadcrumb.length - 1].name}
                </BreadcrumbsSpan>
            </div>
        </Webline>
    );
};

const BreadcrumbsSpan: FC = ({ children, dataTestId }) => (
    <span className="mr-3 hidden text-greyLight lg:inline" data-testid={dataTestId}>
        {children}
    </span>
);

const BreadcrumbsLink: FC<{ href: string; type?: FriendlyPagesTypesKeys }> = ({ href, type, children, dataTestId }) => (
    <ExtendedNextLink
        href={href}
        type={type || 'static'}
        className="mr-3 hidden text-greyLight no-underline last-of-type:inline lg:inline lg:text-primary lg:underline"
        data-testid={dataTestId}
    >
        {children}
    </ExtendedNextLink>
);
