import { addRelNoopenerWhenTargetIsBlank } from 'utils/links/addRelNoopenerWhenTargetIsBlank';
import { describe, expect, test } from 'vitest';

describe('addRelNoopenerWhenTargetIsBlank', () => {
    test.each([
        [undefined, undefined, undefined],
        ['nofollow', undefined, 'nofollow'],
        [undefined, '', undefined],
        ['nofollow', '_self', 'nofollow'],
        ['nofollow', '_top', 'nofollow'],
        ['nofollow', 'content-frame', 'nofollow'],
        ['nofollow', '_blankish', 'nofollow'],
    ])('rel="%s" is kept as is when the target is "%s"', (rel, target, expectedRel) => {
        expect(addRelNoopenerWhenTargetIsBlank(rel, target)).toBe(expectedRel);
    });

    test.each([
        [undefined, 'noopener'],
        ['', 'noopener'],
        ['nofollow', 'nofollow noopener'],
        ['nofollow  noreferrer', 'nofollow noreferrer noopener'],
        ['noopener', 'noopener'],
        ['noreferrer noopener', 'noreferrer noopener'],
        ['NOOPENER', 'NOOPENER'],
    ])('rel="%s" becomes "%s" when the target is _blank', (rel, expectedRel) => {
        expect(addRelNoopenerWhenTargetIsBlank(rel, '_blank')).toBe(expectedRel);
    });

    test('the target keyword is compared case-insensitively', () => {
        expect(addRelNoopenerWhenTargetIsBlank(undefined, '_BLANK')).toBe('noopener');
    });
});
