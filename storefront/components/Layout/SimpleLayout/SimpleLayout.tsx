import { HeadingWrapperStyled, SimpleLayoutContentStyled, SimpleLayoutStyled } from './SimpleLayout.style';
import { BreadcrumbItemType } from 'types/breadcrumb';
import Breadcrumbs from 'components/Layout/Breadcrumbs';
import { FC } from 'react';
import Heading from 'components/Basic/Heading';
import Webline from 'components/Layout/Webline';

type SimpleLayoutProps = {
    heading: string;
    breadcrumb: BreadcrumbItemType[];
    standardWidth?: true;
};

const SimpleLayout: FC<SimpleLayoutProps> = (props) => {
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

export default SimpleLayout;
