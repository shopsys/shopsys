import { UserNavigation } from 'components/Blocks/UserNavigation/UserNavigation';
import { CommonLayout, CommonLayoutProps } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { PaginationProvider } from 'components/providers/PaginationProvider';
import { RefObject, useRef } from 'react';
import { Breadcrumbs } from './Breadcrumbs/Breadcrumbs';
import { VerticalStack } from './VerticalStack/VerticalStack';

type CustomerLayoutProps = CommonLayoutProps & {
    paginationScrollTargetRef?: RefObject<HTMLElement | null>;
};

export const CustomerLayout: FC<CustomerLayoutProps> = ({
    children,
    breadcrumbs,
    paginationScrollTargetRef,
    ...props
}) => {
    const defaultPaginationScrollTargetRef = useRef<HTMLDivElement>(null);

    return (
        <CommonLayout {...props}>
            <Breadcrumbs key="breadcrumb" breadcrumbs={breadcrumbs ?? []} type={props.breadcrumbsType} />

            <Webline width="xl">
                <div className="grid grid-cols-1 gap-5 lg:grid-cols-[auto_1fr] lg:gap-10">
                    <UserNavigation />

                    <div className="min-w-0" ref={defaultPaginationScrollTargetRef}>
                        <VerticalStack gap="sm">
                            <PaginationProvider
                                paginationScrollTargetRef={
                                    paginationScrollTargetRef ?? defaultPaginationScrollTargetRef
                                }
                            >
                                {children}
                            </PaginationProvider>
                        </VerticalStack>
                    </div>
                </div>
            </Webline>
        </CommonLayout>
    );
};
