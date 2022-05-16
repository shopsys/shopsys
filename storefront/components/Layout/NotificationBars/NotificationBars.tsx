import {
    NotificationBarsBlockStyled,
    NotificationBarsImageStyled,
    NotificationBarsStyled,
} from './NotificationBars.style';
import Image from 'components/Basic/Image/Image';
import Webline from 'components/Layout/Webline';
import { useNotificationBars } from 'connectors/notificationBars/NotificationBars';
import { FC } from 'react';

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
                                        <Image image={item.image} type="default" alt={item.text} />
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
