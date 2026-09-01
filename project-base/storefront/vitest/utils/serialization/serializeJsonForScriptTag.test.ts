import { serializeJsonForScriptTag } from 'utils/serialization/serializeJsonForScriptTag';
import { describe, expect, it } from 'vitest';

describe('serializeJsonForScriptTag', () => {
    it('escapes script-tag-significant characters while preserving the serialized data', () => {
        const data = {
            reviewBody: '</script><script>alert("xss")</script>&>',
            lineSeparators: '\u2028\u2029',
        };

        const serializedData = serializeJsonForScriptTag(data);

        expect(serializedData).not.toMatch(/[&><\u2028\u2029]/);
        expect(serializedData).toContain('\\u003c/script\\u003e');
        expect(serializedData).toContain('\\u0026');
        expect(JSON.parse(serializedData)).toEqual(data);
    });

    it('throws when the value cannot be serialized to JSON', () => {
        expect(() => serializeJsonForScriptTag(undefined)).toThrow(TypeError);
    });
});
