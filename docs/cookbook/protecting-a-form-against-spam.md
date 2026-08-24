# Protecting a Form Against Spam

Public form mutations in the Frontend API can be submitted by anyone, so they attract spam bots.
`Shopsys\FrontendApiBundle\Model\SpamProtection\FormSpamProtectionFacade` protects them with two layers that already
guard the contact form:

| Layer        | Triggered by                                        | Response to the client                                          |
| ------------ | --------------------------------------------------- | --------------------------------------------------------------- |
| Honey pot    | A single submission with the hidden field filled in | The **same success** as a real submission — nothing is sent     |
| Rate limiter | Too many submissions from one IP address            | `TooManyFormSubmissionsUserError`, translated by the storefront |

The honey pot answers with success on purpose. A validation error would tell the bot which field gave it away, so it
would simply stop filling it in — which is also why the honey pot must **not** be a `Blank` validation constraint.

!!! info "reCAPTCHA is not part of the platform"

    Both layers only stop bots that submit the form blindly, and neither of them affects people filling the form in.
    If they turn out not to be enough for your project, a challenge-based solution has to be integrated on top of them.

## Protecting another form

Everything below is the complete work needed for one more form. There is nothing to configure — all protected forms
share one limiter and one error code.

### 1. Name the form

The names live in `Shopsys\FrontendApiBundle\Model\SpamProtection\SpamProtectedFormEnum`, so add yours by extending
it:

```php
// app/src/Model/SpamProtection/SpamProtectedFormEnum.php
namespace App\Model\SpamProtection;

use Shopsys\FrontendApiBundle\Model\SpamProtection\SpamProtectedFormEnum as BaseSpamProtectedFormEnum;

class SpamProtectedFormEnum extends BaseSpamProtectedFormEnum
{
    public const string NEWSLETTER = 'newsletter';
}
```

```yaml
# app/config/services.yaml
Shopsys\FrontendApiBundle\Model\SpamProtection\SpamProtectedFormEnum:
    alias: App\Model\SpamProtection\SpamProtectedFormEnum
```

Without the alias the facade keeps validating against the framework enum and throws `InvalidEnumCaseException` for your
new name. The name becomes a part of the rate limiter key, so every form counts its own quota.

### 2. Add the honey pot field to the GraphQL input

The field itself is defined once in `HoneyPotInputObject`, so an input only inherits it:

```yaml
# app/config/graphql/types/ModelType/Newsletter/Input/NewsletterInput.types.yaml
NewsletterInput:
    type: input-object
    inherits:
        - 'NewsletterInputDecorator'
        - 'HoneyPotInputObject'
```

`inherits` merges the field into the input at compile time and the inherited type stays out of the public schema, so
the schema shows a plain nullable `subject: String` with a description of an ordinary field. Both properties are
load-bearing: nullable, otherwise every existing API client breaks, and plausible, because a client reading the schema
would otherwise be told where the trap is.

### 3. Call the facade first in the mutation

```php
public function __construct(
    protected readonly NewsletterFacade $newsletterFacade,
    protected readonly FormSpamProtectionFacade $formSpamProtectionFacade,
) {
}

public function newsletterMutation(Argument $argument, InputValidator $validator): bool
{
    if ($this->formSpamProtectionFacade->shouldDiscardSubmission($argument['input'], SpamProtectedFormEnum::NEWSLETTER)) {
        // the same result as for a successful submission is returned on purpose, so that a bot cannot tell it was detected
        return true;
    }

    $validator->validate();

    // ... the real work
}
```

Call it **before** `$validator->validate()`, so that malformed submissions count towards the rate limit as well.
The facade consumes the limiter even for a submission caught by the honey pot — otherwise a bot filling the honey pot in
would never use up its quota and could flood the endpoint without limit.

### 4. Render the hidden field on the storefront

```tsx
const { renderHoneyPot, getHoneyPotInput } = useHoneyPot(formProviderMethods);

const onSubmitHandler: SubmitHandler<NewsletterFormType> = async (values) => {
    await newsletter({ input: { email: values.email, ...getHoneyPotInput() } });
};

return (
    <Form renderHoneyPot={renderHoneyPot} onSubmit={formProviderMethods.handleSubmit(onSubmitHandler)}>
        {/* ... the real fields */}
    </Form>
);
```

`useHoneyPot()` hands out both halves of the trap, because using only one of them is the mistake nothing else catches:
`renderHoneyPot` alone renders the field without ever sending its value, so the form looks protected and is not.
Leaving either half unused fails the lint instead. Spread `getHoneyPotInput()` wherever the mutation carries the honey
pot — inside `input` for an input-object mutation, among the top level variables for a mutation with flat ones — and
call it inside the submit handler, because filling a registered input in does not re-render the form.

`renderHoneyPot` is opt-in on purpose: a form that sends its whole form model as the mutation input would otherwise
start sending a field its GraphQL input does not know.

The value is deliberately kept **out of the typed form model** — it is a trap, not a form field, so it does not belong
in the `*FormType`, the Yup schema or the `*FormMeta` fields. It does reach the submit handler at runtime, because
`yupResolver` runs with `{ raw: true }`; the helper exists because `*FormType` does not declare the field and
`values.subject` would not compile.

`HoneyPotInput` hides the field with `sr-only` plus `aria-hidden`, `tabIndex={-1}` and `autoComplete="off"`, and
carries no `data-tid`. All of it is load-bearing: `display: none` is skipped by some bots, `sr-only` alone would expose
the field to screen readers, a reachable field could be filled in by a person by accident, autofill would block a real
submission, and a test identifier is exactly the kind of stable attribute a bot would learn to recognise the trap by.

The honey pot needs no error handling — the mutation answers with success, which the form already treats as sent.

### 5. Regenerate the generated files

```sh
make generate-schema
```

## Testing

The rate limiter is **not** disabled in the test environment. `ApplicationTestCase` gives every test method its own
client IP, so test methods do not share a quota and no extra setup is needed in the test of a protected mutation.

Assert both layers, following `ContactFormMutationTest`: a filled honey pot returns success without any effect, and the
submission after the last allowed one fails with the `too-many-form-submissions` user error.

## One-time configuration

Already in place, listed here because a downstream project has to have it as well. One limiter and one Redis-backed
pool serve every protected form:

```yaml
# app/config/packages/rate_limiter.yaml
framework:
    rate_limiter:
        frontend_api_form_spam_protection:
            policy: fixed_window
            limit: 3
            interval: '5 minutes'
            cache_pool: form_spam_protection_rate_limiter_cache
```

Three submissions per five minutes is a default meant for visitors on their own connections. The quota is counted per
IP address and shared across domains, so a shop whose visitors come through one corporate or school connection should
raise it.

The pool is declared in `cache.yaml` over an `snc_redis` client, and overridden to a filesystem adapter in
`test/cache.yaml`.

!!! warning "Always configure the storage explicitly"

    A limiter declared without `cache_pool` falls back to `cache.app`, which is not configured in this project and
    therefore ends up on the local filesystem of a single application container. Every instance then counts on its own,
    so the effective limit is multiplied by the number of running instances and reset on every deploy — the limiter
    appears to do nothing.

    The build version is deliberately left out of the Redis prefix, so that a deploy does not grant spammers a fresh
    quota. In the test environment the pool must not be an `array` adapter either, because that one is thrown away on
    every kernel reboot and a functional test would never reach the limit.

To give one form a limiter of its own, declare a second limiter and override
`FormSpamProtectionFacade::getRateLimiterFactory()`.

## Prerequisite in production: trusted proxies

The limiter depends on `Request::getClientIp()`, which is only as reliable as `framework.trusted_proxies` (the
`TRUSTED_PROXIES` environment variable, `127.0.0.1` by default).

Behind a CDN or a load balancer it must be set to the address or range of that proxy. Otherwise either every visitor
collapses into a single IP bucket and legitimate people are blocked, or `X-Forwarded-For` is accepted from anyone and
the limit can be bypassed by spoofing it.

## Classic Symfony forms in the administration

The steps above apply to the headless Frontend API. A server-rendered Symfony form — in the administration, for
example — has its own building blocks in `packages/framework`, which work through the Form component and the session
and therefore cannot be reused here:

- `Shopsys\FrameworkBundle\Form\HoneyPotType` — an unmapped text field with a `Blank` constraint,
- `Shopsys\FrameworkBundle\Component\Form\TimedFormTypeExtension` — a minimum fill-in time check.

Note that `HoneyPotType` rejects the submission with a validation error, the opposite strategy to the silent success
described above. Pick the one that fits the form stack you are working with.
