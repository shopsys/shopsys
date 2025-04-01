export type CarouselState = {
    sliderPosition: number;
    isSliding: boolean;
    slideDirection: 'PREV' | 'NEXT';
};

type CarouselAction =
    | { type: 'PREV' | 'NEXT'; numItems: number }
    | { type: 'MOVE_TO'; slideToMoveTo: number }
    | { type: 'STOP_SLIDING' };

export const getBannerOrderCSSProperty = (index: number, pos: number, numItems: number) => {
    return (index - pos + numItems + 1) % numItems;
};

export const bannersReducer = (state: CarouselState, action: CarouselAction): CarouselState => {
    switch (action.type) {
        case 'PREV':
            return {
                ...state,
                slideDirection: 'PREV',
                isSliding: true,
                sliderPosition: state.sliderPosition === 0 ? action.numItems - 1 : state.sliderPosition - 1,
            };
        case 'NEXT':
            return {
                ...state,
                slideDirection: 'NEXT',
                isSliding: true,
                sliderPosition: state.sliderPosition === action.numItems - 1 ? 0 : state.sliderPosition + 1,
            };
        case 'MOVE_TO':
            return {
                ...state,
                isSliding: false,
                sliderPosition: action.slideToMoveTo,
            };
        case 'STOP_SLIDING':
            return { ...state, isSliding: false };
        default:
            return state;
    }
};

const getRGBFromHex = (hex: string) => {
    const color = hex.replace(/#/g, '');
    return {
        r: parseInt(color.slice(0, 2), 16),
        g: parseInt(color.slice(2, 4), 16),
        b: parseInt(color.slice(4, 6), 16),
    };
};

export const getRGBColorString = (hex: string, opacity: number) => {
    const { r, g, b } = getRGBFromHex(hex);
    return `rgb(${r} ${g} ${b} / ${opacity})`;
};

export const getYIQContrastTextColor = (hex: string) => {
    const { r, g, b } = getRGBFromHex(hex);
    const yiq = (r * 299 + g * 587 + b * 114) / 1000;

    return yiq >= 128 ? 'text-text-default' : 'text-text-inverted';
};
