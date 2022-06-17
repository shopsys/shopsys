import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const ToggleSwitchWrapper = styled.div`
    position: relative;
`;

export const ToggleSwitchLabel = styled.label(
    ({ theme }) => css`
        position: absolute;
        top: 0;
        left: 0;
        width: 42px;
        height: 26px;

        border-radius: 20px;
        background: ${theme.color.greyLight};
        cursor: pointer;

        &::after {
            content: '';
            display: block;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            margin: 4px;

            background: #ffffff;
            box-shadow: 1px 3px 3px 1px rgba(0, 0, 0, 0.2);
            transition: 0.2s;
        }
    `,
);

export const ToggleSwitchStyled = styled.input(
    ({ theme }) => css`
        width: 42px;
        height: 26px;
        opacity: 0;
        z-index: 1;

        border-radius: ${theme.radius.biggest};

        &:checked + ${ToggleSwitchLabel} {
            background: ${theme.color.primary};

            &::after {
                content: '';
                display: block;
                width: 18px;
                height: 18px;
                border-radius: 50%;
                margin-left: 22px;

                transition: 0.2s;
            }
        }
    `,
);
