import { Heading1Styled, Heading2Styled, Heading3Styled, Heading4Styled } from './Heading.style';
import { FC, HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLHeadingElement>, never, 'style' | 'onClick'>;

type HeadingProps = NativeProps & {
    type: 'h1' | 'h2' | 'h3' | 'h4';
    'data-testid'?: string;
};

export const Heading: FC<HeadingProps> = (props) => {
    const testIdentifier = 'basic-heading-' + props.type;

    const Component = renderHeading(props.type);
    return (
        <Component {...props} data-testid={testIdentifier}>
            {props.children}
        </Component>
    );
};

const renderHeading = (type: 'h1' | 'h2' | 'h3' | 'h4') => {
    switch (type) {
        case 'h1':
            return Heading1Styled;
        case 'h2':
            return Heading2Styled;
        case 'h3':
            return Heading3Styled;
        case 'h4':
            return Heading4Styled;
    }

    throw new Error('Wrong type provided for Heading.');
};
