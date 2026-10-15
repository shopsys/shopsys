import { BlockProps } from 'components/Blocks/ContentEditor/ContentRenderer';
import { getButtonClassName } from 'components/Forms/Button/Button';
import { TIDs } from 'cypress/tids';
import { ButtonLinkBlockData } from 'types/contentEditor';
import { twMergeCustom } from 'utils/twMerge';

const positionClassNames = { left: 'justify-start', center: 'justify-center', right: 'justify-end' };

// a plain anchor styled as the storefront button, the URL is entered by the administrator and may lead anywhere
export const ButtonLinkBlock: FC<BlockProps<ButtonLinkBlockData>> = ({ block }) => {
    const { text, url, openInNewTab, position, variant } = block.data;

    return (
        <div className={`my-3 flex ${positionClassNames[position]}`}>
            <a
                className={twMergeCustom(
                    getButtonClassName(variant, 'medium', false, false),
                    'no-underline hover:no-underline',
                )}
                data-tid={TIDs.content_block_button_link}
                href={url}
                {...(openInNewTab && { target: '_blank', rel: 'noopener' })}
            >
                {text}
            </a>
        </div>
    );
};
