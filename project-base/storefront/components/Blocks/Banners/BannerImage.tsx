import NextImage from 'next/image';

type BannerImageProps = {
    mobileSrc: string;
    desktopSrc: string;
    mobileAlt: string;
    desktopAlt: string;
    isFirst: boolean;
};

export const BannerImage: FC<BannerImageProps> = ({ mobileSrc, desktopSrc, mobileAlt, desktopAlt, isFirst }) => (
    <div className="relative h-[250px] w-full md:h-[400px] vl:h-[630px]">
        <NextImage
            alt={desktopAlt}
            className="hidden h-full w-full object-cover vl:block"
            layout="fill"
            loader={({ src }) => `${src}?width=936`}
            objectFit="cover"
            priority={isFirst}
            src={desktopSrc}
            onDragStart={(e) => e.preventDefault()}
        />
        <NextImage
            alt={mobileAlt}
            className="block h-full w-full object-cover vl:hidden "
            layout="fill"
            loader={({ src }) => `${src}?width=991`}
            objectFit="cover"
            priority={isFirst}
            src={mobileSrc}
            onDragStart={(e) => e.preventDefault()}
        />
    </div>
);
