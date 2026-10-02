export enum OgTypeEnum {
    Website = 'website',
    Article = 'article',
}

export type MetaRobotsContent =
    | 'index'
    | 'noindex'
    | 'follow'
    | 'nofollow'
    | 'index, follow'
    | 'index, nofollow'
    | 'noindex, follow'
    | 'noindex, nofollow';
