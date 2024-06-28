import NextImage from 'next/image';

type BannerImageProps = {
    mobileSrc: string;
    desktopSrc: string;
    mobileAlt: string;
    desktopAlt: string;
    isFirst: boolean;
};

export const BannerImage: FC<BannerImageProps> = ({ mobileSrc, desktopSrc, mobileAlt, desktopAlt, isFirst }) => (
    <div className="relative h-[283px] w-full">
        <NextImage
            alt={desktopAlt}
            className="hidden vl:block h-full w-full object-cover"
            layout="fill"
            loader={({ src }) => `${src}?width=936`}
            objectFit="cover"
            priority={isFirst}
            src={desktopSrc}
            onDragStart={(e) => e.preventDefault()}
        />
        <NextImage
            alt={mobileAlt}
            className="block vl:hidden h-full w-full object-cover "
            layout="fill"
            loader={({ src }) => `${src}?width=991`}
            objectFit="cover"
            priority={isFirst}
            src={mobileSrc}
            onDragStart={(e) => e.preventDefault()}
        />
    </div>
);
