import { Image } from 'components/Basic/Image/Image';

type BannerImageProps = {
    mobileSrc: string;
    desktopSrc: string;
    mobileAlt: string;
    desktopAlt: string;
    isFirst: boolean;
};

export const BannerImage: FC<BannerImageProps> = ({ mobileSrc, desktopSrc, mobileAlt, desktopAlt, isFirst }) => (
    <div className="relative h-[250px] w-full md:h-[345px] vl:h-[425px]">
        <Image
            fill
            alt={desktopAlt}
            className="hidden h-full w-full object-cover vl:block"
            loader={({ src }) => `${src}?width=936`}
            priority={isFirst}
            sizes="(max-width: 1023px) 100vw, 1400px"
            src={desktopSrc}
            onDragStart={(e) => e.preventDefault()}
        />
        <Image
            fill
            alt={mobileAlt}
            className="block h-full w-full object-cover vl:hidden"
            loader={({ src }) => `${src}?width=991`}
            priority={isFirst}
            sizes="(max-width: 1023px) 100vw, 50vw"
            src={mobileSrc}
            onDragStart={(e) => e.preventDefault()}
        />
    </div>
);
