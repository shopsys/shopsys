import { Breadcrumbs } from './Breadcrumbs/Breadcrumbs';
import { VerticalStack } from './VerticalStack/VerticalStack';
import { UserNavigation } from 'components/Blocks/UserNavigation/UserNavigation';
import { CommonLayout, CommonLayoutProps } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { PaginationProvider } from 'components/providers/PaginationProvider';
import { useRef } from 'react';

type CustomerLayoutProps = {
    pageHeading?: string;
} & CommonLayoutProps;

export const CustomerLayout: FC<CustomerLayoutProps> = ({ pageHeading, children, breadcrumbs, ...props }) => {
    const paginationScrollTargetRef = useRef<HTMLHeadingElement>(null);

    return (
        <CommonLayout {...props}>
            <Breadcrumbs key="breadcrumb" breadcrumbs={breadcrumbs ?? []} type={props.breadcrumbsType} />

            <Webline>
                <div className="grid grid-cols-1 gap-5 lg:grid-cols-[auto_1fr] lg:gap-10">
                    <UserNavigation />

                    <VerticalStack gap="sm">
                        <PaginationProvider paginationScrollTargetRef={paginationScrollTargetRef}>
                            {pageHeading && (
                                <h1 className="scroll-mt-4" ref={paginationScrollTargetRef}>
                                    {pageHeading}
                                </h1>
                            )}
                            {children}
                        </PaginationProvider>
                    </VerticalStack>
                </div>
            </Webline>
        </CommonLayout>
    );
};
