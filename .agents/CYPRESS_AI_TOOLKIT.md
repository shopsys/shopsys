# Cypress AI Toolkit

The following official skills are vendored unchanged from
[cypress-io/ai-toolkit](https://github.com/cypress-io/ai-toolkit) at commit
[`da6ea17dfa674a2b68908d823e37f5797d5be238`](https://github.com/cypress-io/ai-toolkit/tree/da6ea17dfa674a2b68908d823e37f5797d5be238):

- `skills/cypress-docs` → `.agents/skills/cypress-docs`: official documentation research.
- `skills/cypress-explain` → `.agents/skills/cypress-explain`: explanations and read-only test audits.
- `skills/cypress-author` → `.agents/skills/cypress-author`: test authoring and repairs.

These skills supplement the Shopsys `cypress-tests` skill. Follow project conventions,
check the installed Cypress and plugin versions before applying current documentation,
and preserve the user's approval workflow. Cypress execution remains manual.

Cloud MCP, `cypress-cloud-cli`, and `cypress-tap` are not installed or configured.
Installing these instruction files does not change application dependencies or CI.

When updating, review the upstream changes, keep all referenced files together, and
update the source revision here. Keep project-specific overrides in `AGENTS.md`
rather than editing the vendored skills.

## Upstream license

MIT License

Copyright (c) 2026 Cypress.io

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
