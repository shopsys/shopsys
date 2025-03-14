import { Breadcrumbs } from './Breadcrumbs/Breadcrumbs';
import { UserNavigation } from 'components/Blocks/UserNavigation/UserNavigation';
import { CommonLayout, CommonLayoutProps } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';

type CustomerLayoutProps = {
    pageHeading?: string;
} & CommonLayoutProps;

export const CustomerLayout: FC<CustomerLayoutProps> = ({ pageHeading, children, breadcrumbs, ...props }) => {
    const isUserLoggedIn = useIsUserLoggedIn();

    return (
        <CommonLayout {...props}>
            <Webline>
                <Breadcrumbs key="breadcrumb" breadcrumbs={breadcrumbs ?? []} type={props.breadcrumbsType} />
            </Webline>

            <Webline className="mt-5 flex flex-col gap-5 lg:flex-row lg:gap-10">
                <UserNavigation />

                <div className="w-full">
                    {pageHeading && <h1 className={isUserLoggedIn ? '' : 'vl:mt-4 mt-0'}>{pageHeading}</h1>}
                    {children}
                </div>
            </Webline>
        </CommonLayout>
    );
};
