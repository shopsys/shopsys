import { UserNavigation } from 'components/Blocks/UserNavigation/UserNavigation';
import { CommonLayout, CommonLayoutProps } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';

type CustomerLayoutProps = {
    pageHeading?: string;
} & CommonLayoutProps;

export const CustomerLayout: FC<CustomerLayoutProps> = ({ pageHeading, children, breadcrumbs, ...props }) => {
    return (
        <CommonLayout {...props} breadcrumbs={breadcrumbs} breadcrumbsType={props.breadcrumbsType}>
            <Webline className="flex flex-col gap-4 lg:flex-row">
                <UserNavigation />

                <div className="flex w-full flex-col gap-4">
                    {pageHeading && <h1>{pageHeading}</h1>}

                    {children}
                </div>
            </Webline>
        </CommonLayout>
    );
};
