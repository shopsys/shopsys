import { getTranslation } from 'app/_utils/translation/getTranslation';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { Fragment } from 'react';
import { twMergeCustom } from 'utils/twMerge';

type BreadcrumbsProps = {
    breadcrumbs: TypeBreadcrumbFragment[];
    className?: string;
};

export const breadcrumbsTwClass = 'flex items-center gap-3';
const breadcrumbsLinkTwClass =
    'hidden font-secondary text-sm font-semibold no-underline last-of-type:inline hover:no-underline lg:inline';

export const Breadcrumbs = async ({ breadcrumbs, className }: BreadcrumbsProps) => {
    const t = await getTranslation();

    if (!breadcrumbs.length) {
        return null;
    }

    const lastIndex = breadcrumbs.length - 1;
    const linkedBreadcrumbs = breadcrumbs.slice(0, lastIndex);
    const lastBreadcrumb = breadcrumbs[lastIndex];

    // TODO: add breadcrumbs metadata

    return (
        <Webline>
            {/* <BreadcrumbsMetadata breadcrumbs={breadcrumbs} /> */}

            <div className={twMergeCustom(breadcrumbsTwClass, className)}>
                <ArrowIcon className="text-borderAccent mr-3 size-4 rotate-90 lg:hidden" />

                <ExtendedNextLink className={breadcrumbsLinkTwClass} href="/">
                    {t('Home page')}
                </ExtendedNextLink>

                <ArrowIcon className="text-borderAccent hidden size-4 -rotate-90 lg:flex" />

                {linkedBreadcrumbs.map((linkedBreadcrumb, index) => (
                    <Fragment key={index}>
                        <ExtendedNextLink className={breadcrumbsLinkTwClass} href={linkedBreadcrumb.slug}>
                            {linkedBreadcrumb.name}
                        </ExtendedNextLink>
                        <ArrowIcon className="text-borderAccent hidden size-4 -rotate-90 lg:flex" />
                    </Fragment>
                ))}

                <span className="hidden text-sm font-semibold lg:inline-block" tid={TIDs.breadcrumbs_tail}>
                    {lastBreadcrumb.name}
                </span>
            </div>
        </Webline>
    );
};
