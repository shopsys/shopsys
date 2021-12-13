import Icon from 'components/Basic/Icon';
import { styled } from 'components/Theme/main';

export const FrontendSwitcherWrapperStyled = styled.div`
    position: fixed;
    right: 0;
    top: 15vh;
    box-shadow: -4px 0 14px 0 rgba(0, 0, 0, 0.73);
    padding: 5px;
    border-radius: 8px 0 0 8px;
    z-index: 999;
    background-color: greenyellow;
`;

export const FrontendSwitcherIconStyled = styled(Icon)`
    width: 20px;
    height: 20px;
`;
