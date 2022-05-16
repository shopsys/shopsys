import { styled } from 'components/Theme/main';
import Image from 'next/image';

export const LogoStyled = styled(Image)`
    ${({ theme }) => `
        display: flex;
        width: 100%;
        max-width: 120px;

        @media ${theme.mediaQueries.queryLg} {
            max-width: 163px;
        }
    `}
`;
