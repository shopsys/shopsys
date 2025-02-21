import { Image } from 'components/Basic/Image/Image';

type BannerImageProps = {
    mobileSrc: string;
    desktopSrc: string;
    mobileAlt: string;
    desktopAlt: string;
    isFirst: boolean;
};

export const BannerImage: FC<BannerImageProps> = ({
    mobileSrc,
    desktopSrc,
    mobileAlt,
    desktopAlt,
    isFirst,
    children,
}) => (
    <div className="vl:h-[425px] relative h-[250px] w-full grow md:h-[345px]">
        <Image
            fill
            alt={desktopAlt}
            className="vl:block hidden h-full w-full object-cover"
            loader={({ src }) => `${src}?width=936`}
            priority={isFirst}
            sizes="(max-width: 1023px) 100vw, 1400px"
            src={desktopSrc}
            onDragStart={(e) => e.preventDefault()}
        />
        <Image
            fill
            alt={mobileAlt}
            className="vl:hidden block h-full w-full object-cover"
            loader={({ src }) => `${src}?width=991`}
            priority={isFirst}
            sizes="(max-width: 1023px) 100vw, 50vw"
            src={mobileSrc}
            onDragStart={(e) => e.preventDefault()}
        />
        {children}
    </div>
);
