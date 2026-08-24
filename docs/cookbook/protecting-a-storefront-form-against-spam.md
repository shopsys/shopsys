# Protecting a Storefront Form Against Spam

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

Everything below is the complete work needed for one more form. All protected forms share one rate limiter
configuration and one error code, while the quota is counted separately for every form and IP address.

### 1. Name the form and its honey pot field

Both live in `Shopsys\FrontendApiBundle\Model\SpamProtection\SpamProtectedFormEnum`, so add yours by extending it:

```php
// app/src/Model/SpamProtection/SpamProtectedFormEnum.php
namespace App\Model\SpamProtection;

use Override;
use Shopsys\FrontendApiBundle\Model\SpamProtection\SpamProtectedFormEnum as BaseSpamProtectedFormEnum;

class SpamProtectedFormEnum extends BaseSpamProtectedFormEnum
{
    public const string NEWSLETTER = 'newsletter';

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getHoneyPotFieldNameIndexedByFormName(): array
    {
        return array_merge(parent::getHoneyPotFieldNameIndexedByFormName(), [
            static::NEWSLETTER => 'nickname',
        ]);
    }
}
```

```yaml
# app/config/services.yaml
Shopsys\FrontendApiBundle\Model\SpamProtection\SpamProtectedFormEnum:
    alias: App\Model\SpamProtection\SpamProtectedFormEnum
```

Choose a field name that fits the form and reads like an ordinary optional field of it — the bot must not be able to
tell it apart from the real ones. The map is the only thing that decides whether a form is protected — a form name
missing from it is refused with `HoneyPotFieldNameNotConfiguredException`, whether or not the enum declares a case for
it.

The platform ships `contact-form` mapped to `subject` only as the default that matches the `project-base` skeleton. The
field itself is declared in your own input type, so renaming it is entirely up to your project.

### 2. Add the honey pot field to the GraphQL input

Declare it as an ordinary field of the form's input:

```yaml
# app/config/graphql/types/ModelType/Newsletter/Input/NewsletterInput.types.yaml
NewsletterInput:
    type: input-object
    inherits:
        - 'NewsletterInputDecorator'
    config:
        fields:
            # honey pot of SpamProtectedFormEnum::getHoneyPotFieldNameIndexedByFormName(),
            # nullable and unvalidated on purpose and the mutation never reads it
            nickname:
                type: 'String'
                description: 'Nickname of the subscriber'
```

All three properties are load-bearing: nullable, otherwise every existing API client breaks; without a validation
constraint, otherwise the error tells the bot where the trap is; and with a plausible name and description, because a
client reading the schema would otherwise be told the same.

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
const honeyPot = useHoneyPot(formProviderMethods, 'nickname');

const onSubmitHandler: SubmitHandler<NewsletterFormType> = async (values) => {
    await newsletter({ input: { email: values.email, [honeyPot.fieldName]: honeyPot.value } });
};

return (
    <Form formName={formMeta.formName} honeyPot={honeyPot} onSubmit={formProviderMethods.handleSubmit(onSubmitHandler)}>
        {/* ... the real fields */}
    </Form>
);
```

The name is written in three places — the enum map, the input yaml and this call — and all three belong to your project,
so a rename changes all of them at once. A storefront left behind then sends a field the input does not declare and the
mutation fails, instead of quietly sending a name nothing reads.

`useHoneyPot()` hands out both halves of the trap: `Form` renders the hidden input from the object, and `value` carries
what was typed into it. Passing the object to `Form` and forgetting to put `value` into the mutation is the mistake
nothing else catches — the form then renders the field without ever sending its value, so it looks protected and is not.
Nothing detects that at build time, so the hook reports the reverse case through `logException` after the first render,
when the field it was given was never rendered. Put the pair wherever the mutation carries the honey pot — inside
`input` for an input-object mutation, among the top level variables for a mutation with flat ones.

`honeyPot` is opt-in on purpose: a form that sends its whole form model as the mutation input would otherwise start
sending a field its GraphQL input does not know.

The value is deliberately kept **out of the typed form model** — it is a trap, not a form field, so it does not belong
in the form's TypeScript type (`NewsletterFormType` here), the Yup schema or the `*FormMeta` fields. It does reach the
submit handler at runtime, because `yupResolver` runs with `{ raw: true }`; the hook exists because the form type does
not declare the field and `values.nickname` would not compile.

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

Assert both layers on the backend, following `ContactFormMutationTest`: a filled honey pot returns success without any
effect, and the submission after the last allowed one fails with the `too-many-form-submissions` user error.

Assert on the storefront as well, following `ContactContentHoneyPot.test.tsx`, that a value filled into the hidden
field reaches the variables of the mutation. That is the half of the trap nothing else checks.

## One-time configuration

Already in place, listed here because a downstream project has to have it as well — see
`app/config/packages/rate_limiter.yaml` for the limits themselves. The limiter stores its counters in a Redis-backed
pool declared in `cache.yaml` and overridden to a filesystem adapter in `test/cache.yaml`.

All protected forms share that one configuration — the policy, the limit, the interval and the storage — but the quota
is counted separately for every form and IP address, because the form name is a part of the limiter key.

The default is meant for visitors on their own connections. The quota is shared across domains, so a shop whose
visitors come through one corporate or school connection should raise it.

!!! warning "Always configure the storage explicitly"

    A limiter declared without `cache_pool` falls back to `cache.app`, which is not configured in this project and
    therefore ends up on the local filesystem of a single application container. Every instance then counts on its own,
    so the effective limit is multiplied by the number of running instances and reset on every deploy — the limiter
    appears to do nothing.

    The build version is deliberately left out of the Redis prefix, so that a deploy does not grant spammers a fresh
    quota. In the test environment the pool must not be an `array` adapter either, because that one is thrown away on
    every kernel reboot and a functional test would never reach the limit.

## Giving one form its own limits

Only needed when the shared configuration does not fit one of the forms.

### 1. Declare a second limiter

Reuse the same `cache_pool`, so that all protected forms keep counting in one storage:

```yaml
# app/config/packages/rate_limiter.yaml
framework:
    rate_limiter:
        frontend_api_newsletter_spam_protection:
            policy: fixed_window
            limit: 10
            interval: '1 hour'
            cache_pool: form_spam_protection_rate_limiter_cache
```

### 2. Inject it into the facade and branch on the form name

```php
// app/src/Model/SpamProtection/FormSpamProtectionFacade.php
class FormSpamProtectionFacade extends BaseFormSpamProtectionFacade
{
    public function __construct(
        ClientIpProvider $clientIpProvider,
        LoggerInterface $logger,
        RateLimiterFactoryInterface $formSpamProtectionRateLimiter,
        SpamProtectedFormEnum $spamProtectedFormEnum,
        protected readonly RateLimiterFactoryInterface $newsletterSpamProtectionRateLimiter,
    ) {
        parent::__construct($clientIpProvider, $logger, $formSpamProtectionRateLimiter, $spamProtectedFormEnum);
    }

    #[Override]
    protected function getRateLimiterFactory(string $formName): RateLimiterFactoryInterface
    {
        if ($formName === SpamProtectedFormEnum::NEWSLETTER) {
            return $this->newsletterSpamProtectionRateLimiter;
        }

        return parent::getRateLimiterFactory($formName);
    }
}
```

### 3. Wire both limiters and alias the facade

The mutations ask for the framework class, so the alias is what makes them use yours:

```yaml
# app/config/services.yaml
App\Model\SpamProtection\FormSpamProtectionFacade:
    arguments:
        $formSpamProtectionRateLimiter: '@limiter.frontend_api_form_spam_protection'
        $newsletterSpamProtectionRateLimiter: '@limiter.frontend_api_newsletter_spam_protection'

Shopsys\FrontendApiBundle\Model\SpamProtection\FormSpamProtectionFacade:
    alias: App\Model\SpamProtection\FormSpamProtectionFacade
```

## Classic Symfony forms in the administration

The steps above apply to the headless Frontend API. A server-rendered Symfony form — in the administration, for
example — has its own building blocks in `packages/framework`, which work through the Form component and the session
and therefore cannot be reused here:

- `Shopsys\FrameworkBundle\Form\HoneyPotType` — an unmapped text field with a `Blank` constraint,
- `Shopsys\FrameworkBundle\Component\Form\TimedFormTypeExtension` — a minimum fill-in time check.

Note that `HoneyPotType` rejects the submission with a validation error, the opposite strategy to the silent success
described above. Pick the one that fits the form stack you are working with.
