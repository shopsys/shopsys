import { getNotificationBarsQuery } from 'app/_queries/getNotificationBarsQuery';
import { Image } from 'components/Basic/Image/Image';
import { twJoin } from 'tailwind-merge';
import { getYIQContrastTextColor } from 'utils/colors/colors';

export const UserBar = async () => {
    const { data } = await getNotificationBarsQuery();

    if (!data) {
        return null;
    }

    return data.notificationBars?.map((item, index) => (
        <div key={index} className="py-2" style={{ backgroundColor: item.rgbColor }}>
            <div
                className={twJoin(
                    'flex items-center justify-center px-5 text-center text-sm font-bold xl:mx-auto xl:w-full xl:max-w-screen-xl',
                    getYIQContrastTextColor(item.rgbColor),
                )}
            >
                {!!item.mainImage && (
                    <div className="mr-3 flex h-11 w-11 items-center justify-center">
                        <Image alt={item.mainImage.name || item.text} height={44} src={item.mainImage.url} width={44} />
                    </div>
                )}
                {typeof item.text === 'string' ? <div dangerouslySetInnerHTML={{ __html: item.text }} /> : item.text}
            </div>
        </div>
    ));
};
