import { styled } from 'components/Theme/main';

export const RadiobuttonStyled = styled.input`
    position: absolute;
    height: 1px;
    width: 1px;
    margin: -1px;
    padding: 0;
    z-index: -1000;
    overflow: hidden;

    clip: rect(0 0 0 0);
    border: 0;
`;

export const LabelImageWrapper = styled.div`
    display: flex;
    justify-content: center;
    align-items: center;
    margin-right: 10px;
    width: 45px;
    height: 25px;
`;
