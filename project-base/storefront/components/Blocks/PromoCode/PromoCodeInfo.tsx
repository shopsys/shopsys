import { Icon } from 'components/Basic/Icon/Icon';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';

type PromoCodeInfoProps = {
    promoCode: string;
    onRemovePromoCodeCallback: (promoCode: string) => void;
};

const TEST_IDENTIFIER = 'blocks-promocode-promocodeinfo';

export const PromoCodeInfo: FC<PromoCodeInfoProps> = ({ onRemovePromoCodeCallback, promoCode }) => {
    const t = useTypedTranslationFunction();

    const onRemovePromoCodeHandler = () => {
        onRemovePromoCodeCallback(promoCode);
    };

    return (
        <div data-testid={TEST_IDENTIFIER}>
            <div className="text-primary" data-testid={TEST_IDENTIFIER + '-title'}>
                {t('Your discount with the code has been applied.')}
            </div>
            <div className="flex items-center font-bold" data-testid={TEST_IDENTIFIER + '-code'}>
                {promoCode}
                <Icon
                    iconType="icon"
                    icon="Cross"
                    onClick={onRemovePromoCodeHandler}
                    className="mr-1 w-4 cursor-pointer text-greyDark hover:text-primary"
                />
            </div>
            <p data-testid={TEST_IDENTIFIER + '-description'}>
                {t(
                    'The discount was applied to all non-discounted items to which the promotion applies according to the rules.',
                )}
            </p>
        </div>
    );
};
