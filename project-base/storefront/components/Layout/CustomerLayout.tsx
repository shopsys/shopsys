import { UserNavigation } from 'components/Blocks/UserNavigation/UserNavigation';
import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { CommonLayout, CommonLayoutProps } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import useTranslation from 'next-translate/useTranslation';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

type CustomerLayoutProps = {
    pageHeading?: string;
} & CommonLayoutProps;

export const CustomerLayout: FC<CustomerLayoutProps> = ({ pageHeading, children, breadcrumbs, ...props }) => {
    const isUserLoggedIn = useIsUserLoggedIn();
    const { url } = useDomainConfig();
    const { t } = useTranslation();
    const [customerUrl] = getInternationalizedStaticUrls(['/customer'], url);

    return (
        <CommonLayout {...props}>
            <Webline>
                <Breadcrumbs
                    key="breadcrumb"
                    type={props.breadcrumbsType}
                    breadcrumbs={[
                        { __typename: 'Link', name: t('Customer'), slug: customerUrl },
                        ...(breadcrumbs ?? []),
                    ]}
                />
            </Webline>
            <Webline className="mt-8 flex flex-col gap-6 lg:mt-4 lg:flex-row vl:mt-12">
                <UserNavigation />
                <div className="w-full">
                    {pageHeading && <h1 className={isUserLoggedIn ? '' : 'mt-0 vl:mt-4'}>{pageHeading}</h1>}
                    {children}
                </div>
            </Webline>
        </CommonLayout>
    );
};
