import { Infobox } from 'components/Basic/Infobox/Infobox';
import { StyleguidePreview } from './StyleguideElements';

export const StyleguideInfobox: FC = () => {
    return (
        <StyleguidePreview>
            <Infobox message="Example message" />
        </StyleguidePreview>
    );
};
