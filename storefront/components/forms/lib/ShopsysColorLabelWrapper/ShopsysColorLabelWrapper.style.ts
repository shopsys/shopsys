import { styled } from 'theme/main';

const localVariables = {
    labelColorSize: '25px',
} as const;

export const StyledShopsysColorLabelWrapper = styled.div<StyledShopsysLabelWrapperProps>`
    position: relative;
    width: 100%;

    input {
        & ~ label {
            position: relative;
            display: block;
            height: ${localVariables.labelColorSize};
            width: ${localVariables.labelColorSize};

            font-size: 0;
            border: 1px solid hsla(0,0%,5%,.08);
            border-radius: 100%;
            cursor: pointer;
        }
    }
`;
