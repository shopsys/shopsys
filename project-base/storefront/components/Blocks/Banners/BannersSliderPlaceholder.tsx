import { BannersSliderProps } from './BannersSlider';
import { TriangleIcon } from 'components/Basic/Icon/TriangleIcon';
import { Image } from 'components/Basic/Image/Image';

export const BannersSliderPlaceholder: FC<BannersSliderProps> = ({ sliderItems }) => (
    <div className="flex flex-col gap-6 vl:flex-row">
        <div className="h-[283px] rounded vl:basis-3/4 overflow-hidden">
            <a className="flex h-full w-full items-center justify-center" href={sliderItems[0].link}>
                {sliderItems[0].webMainImage && (
                    <Image
                        alt={sliderItems[0].webMainImage.name || sliderItems[0].name}
                        className="hidden lg:block h-full w-full object-cover"
                        height={300}
                        sizes="(max-width: 1024px) 100vw, 80vw"
                        src={sliderItems[0].webMainImage.url}
                        width={1025}
                    />
                )}
                {sliderItems[0].mobileMainImage && (
                    <Image
                        alt={sliderItems[0].mobileMainImage.name || sliderItems[0].name}
                        className="block lg:hidden h-full w-full object-cover"
                        height={300}
                        sizes="(max-width: 1024px) 100vw, 80vw"
                        src={sliderItems[0].mobileMainImage.url}
                        width={1025}
                    />
                )}
            </a>
        </div>
        <div className="flex flex-1 justify-center gap-1 vl:flex-col vl:justify-start vl:gap-4">
            {sliderItems.map((sliderItem, index) => (
                <button
                    key={index}
                    className="group relative block h-2 w-3 cursor-pointer rounded border-none border-blueLight bg-greyLight font-bold outline-none transition active:bg-none disabled:bg-primary vl:mx-0 vl:h-auto vl:w-full vl:border-2 vl:border-solid vl:bg-blueLight vl:py-4 vl:px-8 vl:text-left vl:hover:border-blue vl:hover:bg-blue vl:disabled:border-primary vl:disabled:bg-creamWhite"
                    disabled={index === 0 % sliderItems.length}
                >
                    <TriangleIcon className="absolute top-1/2 left-3 hidden w-2 -translate-y-1/2 text-primary vl:group-disabled:block" />
                    <span className="hidden vl:inline-block">{sliderItem.name}</span>
                </button>
            ))}
        </div>
    </div>
);
