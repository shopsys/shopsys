import { UserText } from 'app/_components/Basic/UserText/UserText';

export type ProductDetailDescriptionTabProps = {
    description: string | null;
};

export const ProductDetailDescriptionTab: FC<ProductDetailDescriptionTabProps> = ({ description }) => {
    if (!description) {
        return null;
    }

    return <UserText htmlContent={description} />;
};
