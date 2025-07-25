import { FooterCopyright } from './FooterCopyright';
import { FooterExtras } from './FooterExtras';
import { FooterMenu } from './FooterMenu';
import { FooterArticle } from 'types/footerArticle';

export type FooterProps =
    | {
          simpleFooter: true;
          footerArticles?: never;
      }
    | {
          simpleFooter?: never;
          footerArticles?: FooterArticle[];
      };

export const Footer: FC<FooterProps> = ({ simpleFooter, footerArticles }) => {
    return (
        <>
            {!simpleFooter && (
                <>
                    {!!footerArticles?.length && <FooterMenu footerArticles={footerArticles} />}

                    <FooterExtras />
                </>
            )}

            <FooterCopyright />
        </>
    );
};
