import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const FreeTransportStyled = styled.div`
    ${({ theme }) => css`
        display: block;
        padding: 5px 10px;
        margin: 6px 0;
        min-height: 26px;

        font-size: 12px;
        background-color: ${theme.color.greenVeryLight};
        border-radius: ${theme.radius.medium};

        strong {
            color: ${theme.color.greenDark};
            font-weight: 700;
        }
    `}
`;
