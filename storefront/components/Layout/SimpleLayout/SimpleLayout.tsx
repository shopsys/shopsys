import { HeadingWrapperStyled, SimpleLayoutContentStyled, SimpleLayoutStyled } from './SimpleLayout.style';
import { Heading } from 'components/Basic/Heading/Heading';
import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import { FC } from 'react';
import { BreadcrumbItemType } from 'types/breadcrumb';

type SimpleLayoutProps = {
    heading: string;
    breadcrumb: BreadcrumbItemType[];
    standardWidth?: true;
};

export const SimpleLayout: FC<SimpleLayoutProps> = ({ breadcrumb, heading, children, standardWidth }) => (
    <>
        <Webline>
            <HeadingWrapperStyled>
                <Heading type="h1">{heading}</Heading>
            </HeadingWrapperStyled>
            <Breadcrumbs key="breadcrumb" breadcrumb={breadcrumb} />
        </Webline>
        <Webline>
            {standardWidth !== true && (
                <SimpleLayoutStyled>
                    <SimpleLayoutContentStyled>{children}</SimpleLayoutContentStyled>
                </SimpleLayoutStyled>
            )}
            {standardWidth === true && <>{children}</>}
        </Webline>
    </>
);
