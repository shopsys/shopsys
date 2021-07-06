import styled from 'styled-components';
export const SsfwButtonStyled = styled.button`
    width: auto;
    min-height: ${(props) => props.theme.btnHeight};
    padding: 10px 32px 10px 32px;
    vertical-align: middle;
    display: inline-block;
    transition: ${(props) => props.theme.transition} background-color, ${(props) => props.theme.transition} color;
    text-align: center;
    line-height: 27px;

    border: 0;
    border-radius: ${(props) => props.theme.radius.medium};
    color: ${(props) => props.theme.color.white};
    background-color: ${(props) => props.theme.color.orange};
    cursor: pointer;
    text-decoration: none;
    font-size: ${(props) => props.theme.fontSize.default};
    font-weight: 700;
    outline: 0;
    text-transform: uppercase;

    &:hover {
        color: ${(props) => props.theme.color.white};
        background-color: #dea700;
        text-decoration: none;
    }

    &.btn--primary {
        color: ${(props) => props.theme.color.white};
        background-color: ${(props) => props.theme.color.primary};

        &:hover {
            color: ${(props) => props.theme.color.white};
            background-color: #3b4cfc;
        }
    }

    &.btn--secondary {
        color: ${(props) => props.theme.color.black};
        background-color: ${(props) => props.theme.color.orangeLight};

        &:hover {
            color: ${(props) => props.theme.color.black};
            background-color: ${(props) => props.theme.color.white};
        }
    }
`;
