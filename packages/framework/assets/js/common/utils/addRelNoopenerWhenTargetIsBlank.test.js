import { addRelNoopenerWhenTargetIsBlank } from './addRelNoopenerWhenTargetIsBlank';

test.each([
    // only _blank opens a new tab, the other values navigate an existing browsing context
    [undefined, undefined, undefined],
    ['nofollow', undefined, 'nofollow'],
    ['nofollow', '', 'nofollow'],
    ['nofollow', '_self', 'nofollow'],
    ['nofollow', '_top', 'nofollow'],
    ['nofollow', 'content-frame', 'nofollow'],
    ['nofollow', '_blankish', 'nofollow'],

    // target is _blank, noopener is added or merged into the existing rel
    [undefined, '_blank', 'noopener'],
    ['', '_blank', 'noopener'],
    ['nofollow', '_blank', 'nofollow noopener'],
    ['nofollow  noreferrer', '_blank', 'nofollow noreferrer noopener'],

    // the keywords are compared case-insensitively
    [undefined, '_BLANK', 'noopener'],
    ['noopener', '_blank', 'noopener'],
    ['NOOPENER', '_blank', 'NOOPENER'],
    ['noreferrer noopener', '_blank', 'noreferrer noopener'],
])('rel %j with target %j becomes %j', (rel, target, expectedRel) => {
    expect(addRelNoopenerWhenTargetIsBlank(rel, target)).toBe(expectedRel);
});
