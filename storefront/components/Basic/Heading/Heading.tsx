import { Heading1Styled, Heading2Styled, Heading3Styled, Heading4Styled } from './Heading.style';
import { FC, HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type HeadingType = 'h1' | 'h2' | 'h3' | 'h4';

type NativeProps = ExtractNativePropsFromDefault<
    HTMLAttributes<HTMLHeadingElement>,
    never,
    'style' | 'onClick' | 'className'
>;

type HeadingProps = NativeProps & {
    type: HeadingType;
};

const getTestIdentifier = (type: HeadingType) => 'basic-heading-' + type;

export const Heading: FC<HeadingProps> = ({ type, children, style, onClick, className }) => {
    const Component = renderHeading(type);

    return (
        <Component className={className} style={style} onClick={onClick} data-testid={getTestIdentifier(type)}>
            {children}
        </Component>
    );
};

const renderHeading = (type: HeadingType) => {
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
