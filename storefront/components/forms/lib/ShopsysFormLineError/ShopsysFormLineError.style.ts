import styled, { css } from 'styled-components';

type StyledShopsysErrorIconProps = {
    htmlElement: 'textarea' | 'text-input' | 'checkbox';
};

export const StyledShopsysFormFieldError = styled.div`
    position: relative;
    margin-top: 6px;
`;

export const StyledShopsysErrorMessage = styled.span`
    ${({ theme }) => css`
        line-height: 21px;
        color: ${theme.color.red};
        font-size: ${theme.fontSize.small};
    `}
`;

export const StyledShopsysErrorIcon = styled.img<StyledShopsysErrorIconProps>`
    ${({ htmlElement }) => css`
        display: flex;
        width: 16px;
        position: absolute;

        ${htmlElement === 'textarea' &&
        `
            top: 2px;
            right: 0;
        `}

        ${htmlElement === 'text-input' &&
        `
            transform: translateY(-50%);
            top: -33px;
            right: 19px;
        `}

        ${htmlElement === 'checkbox' &&
        `
            top: 2px;
            right: -19px;
        `}
    `}
`;
