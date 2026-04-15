import { UserNavigation } from 'components/Blocks/UserNavigation/UserNavigation';
import { CommonLayout, CommonLayoutProps } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { PaginationProvider } from 'components/providers/PaginationProvider';
import { useRef } from 'react';
import { Breadcrumbs } from './Breadcrumbs/Breadcrumbs';
import { VerticalStack } from './VerticalStack/VerticalStack';

export const CustomerLayout: FC<CommonLayoutProps> = ({ children, breadcrumbs, ...props }) => {
    const paginationScrollTargetRef = useRef<HTMLHeadingElement>(null);

    return (
        <CommonLayout {...props}>
            <Breadcrumbs key="breadcrumb" breadcrumbs={breadcrumbs ?? []} type={props.breadcrumbsType} />

            <Webline>
                <div className="grid grid-cols-1 gap-5 lg:grid-cols-[auto_1fr] lg:gap-10">
                    <UserNavigation />

                    <VerticalStack gap="sm">
                        <PaginationProvider paginationScrollTargetRef={paginationScrollTargetRef}>
                            {children}
                        </PaginationProvider>
                    </VerticalStack>
                </div>
            </Webline>
        </CommonLayout>
    );
};
