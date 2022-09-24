import { styled } from 'components/Theme/main';

export const ProductsListStyled = styled.div`
    position: relative;
    display: grid;
    margin-left: -8px;
    margin-bottom: 20px;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
`;
