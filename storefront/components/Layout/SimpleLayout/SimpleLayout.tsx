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

export const SimpleLayout: FC<SimpleLayoutProps> = (props) => {
    return (
        <>
            <Webline>
                <HeadingWrapperStyled>
                    <Heading type="h1">{props.heading}</Heading>
                </HeadingWrapperStyled>
                <Breadcrumbs key="breadcrumb" breadcrumb={props.breadcrumb} />
            </Webline>
            <Webline>
                {props.standardWidth !== true && (
                    <SimpleLayoutStyled>
                        <SimpleLayoutContentStyled>{props.children}</SimpleLayoutContentStyled>
                    </SimpleLayoutStyled>
                )}
                {props.standardWidth === true && <>{props.children}</>}
            </Webline>
        </>
    );
};
