# Forms

## Double submit protection
- see https://shopsys.atlassian.net/browse/FWCC-529
- see https://shopsys.atlassian.net/browse/FWCC-555

If you want your form to be protected against double submit, there are three easy steps to follow:
1. Use [`Form`](components/Forms/Form/Form.tsx) component for your form definition
    - this ensures the form can not be submitted using "Enter" key when it is already being submitted
2. Use [`Button`](components/Forms/Button/Button.tsx) component as the submit button in the form
   - this ensures the form can not be submitted by repeated clicking on the submit button when it is already being submitted - the button gets disabled during the submitting
3. Use `await` in your form submit handler
    - this ensures the handler waits for the defined promise
