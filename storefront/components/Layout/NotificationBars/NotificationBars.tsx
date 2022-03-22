import {
    NotificationBarsBlockStyled,
    NotificationBarsImageStyled,
    NotificationBarsStyled,
} from './NotificationBars.style';
import { FC } from 'react';
import Image from 'components/Basic/Image/Image';
import { useNotificationBars } from 'connectors/notificationBars/NotificationBars';
import Webline from 'components/Layout/Webline';

const NotificationBars: FC = () => {
    const items = useNotificationBars();

    return (
        <>
            {items.map((item, index) => {
                return (
                    <NotificationBarsStyled key={index} backgroundColor={item.rgbColor}>
                        <Webline>
                            <NotificationBarsBlockStyled backgroundColor={item.rgbColor}>
                                {item.image !== null && (
                                    <NotificationBarsImageStyled>
                                        <Image image={item.image} alt={item.text} />
                                    </NotificationBarsImageStyled>
                                )}
                                <div dangerouslySetInnerHTML={{ __html: item.text }} />
                            </NotificationBarsBlockStyled>
                        </Webline>
                    </NotificationBarsStyled>
                );
            })}
        </>
    );
};

export default NotificationBars;
