import { Heading } from 'components/Basic/Heading/Heading';
import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import { BreadcrumbFragmentApi } from 'graphql/generated';

type SimpleLayoutProps = {
    heading: string;
    breadcrumb: BreadcrumbFragmentApi[];
    standardWidth?: true;
};

export const SimpleLayout: FC<SimpleLayoutProps> = ({ breadcrumb, heading, children, standardWidth }) => (
    <>
        <Webline>
            <div className="text-center">
                <Heading type="h1">{heading}</Heading>
            </div>
            <Breadcrumbs key="breadcrumb" breadcrumb={breadcrumb} />
        </Webline>
        <Webline>
            {standardWidth !== true && (
                <div className="mr-24 flex w-full justify-center">
                    <div className="mt-7 w-full rounded-2xl border-2 border-greyLighter px-2 pt-5 pb-4 lg:w-[690px] lg:px-14 lg:pt-10 lg:pb-8">
                        {children}
                    </div>
                </div>
            )}
            {standardWidth === true && <>{children}</>}
        </Webline>
    </>
);
