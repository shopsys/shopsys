import { Link } from 'components/Basic/Link/Link';
import {
    useCreateProductReviewForm,
    useCreateProductReviewFormMeta,
} from 'components/Blocks/ProductReviews/createProductReviewFormMeta';
import { CreateProductReviewPopupProps } from 'components/Blocks/ProductReviews/productReviewTypes';
import { StarRatingPicker } from 'components/Blocks/ProductReviews/StarRatingPicker';
import { useProductReviewPolicyArticleUrl } from 'components/Blocks/ProductReviews/useProductReviewPolicyArticleUrl';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { DropzoneControlled } from 'components/Forms/Dropzone/DropzoneControlled';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { Select } from 'components/Forms/Select/Select';
import { TextareaControlled } from 'components/Forms/Textarea/TextareaControlled';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { VALIDATION_CONSTANTS } from 'components/Forms/validationConstants';
import { Popup } from 'components/Layout/Popup/Popup';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { useCreateProductReviewMutation } from 'graphql/requests/productReviews/mutations/CreateProductReviewMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { Controller, FormProvider, SubmitHandler } from 'react-hook-form';
import { useSessionStore } from 'store/useSessionStore';
import { ProductReviewFormType } from 'types/form';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useErrorHandler } from 'utils/errors/useErrorHandler';
import { formatBytes } from 'utils/formaters/formatBytes';
import { blurInput } from 'utils/forms/blurInput';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

export const CreateProductReviewPopup: FC<CreateProductReviewPopupProps> = ({
    productUuid,
    productName,
    variants,
    orderUrlHash,
    guestPrefill,
}) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const isUserLoggedIn = useIsUserLoggedIn();
    const user = useCurrentCustomerData();
    const productReviewPolicyArticleUrl = useProductReviewPolicyArticleUrl();
    const [, createProductReview] = useCreateProductReviewMutation();

    const [formProviderMethods] = useCreateProductReviewForm(
        {
            productUuid: productUuid ?? '',
            rating: 0,
            text: '',
            firstName: user?.firstName ?? guestPrefill?.firstName ?? '',
            lastName: user?.lastName ?? guestPrefill?.lastName ?? '',
            email: user?.email ?? guestPrefill?.email ?? '',
            isAnonymous: false,
            images: [],
        },
        isUserLoggedIn,
    );
    const formMeta = useCreateProductReviewFormMeta();
    const isSubmitting = formProviderMethods.formState.isSubmitting;
    // no customMessage on purpose — specific review error codes (e.g. duplicate review) map to their own messages
    const handleError = useErrorHandler({
        form: formProviderMethods,
        gtmOrigin: GtmMessageOriginType.other,
    });

    const variantsAsOptions = variants?.map((variant) => ({ value: variant.uuid, label: variant.fullName })) ?? [];
    const isVariantSelectShown = variantsAsOptions.length > 0;

    const createProductReviewHandler: SubmitHandler<ProductReviewFormType> = async (productReviewFormData) => {
        blurInput();

        const createProductReviewResult = await createProductReview({
            input: {
                productUuid: productReviewFormData.productUuid,
                rating: productReviewFormData.rating,
                text: productReviewFormData.text || null,
                firstName: productReviewFormData.firstName,
                lastName: productReviewFormData.lastName,
                email: isUserLoggedIn ? null : productReviewFormData.email,
                isAnonymous: productReviewFormData.isAnonymous,
                orderUrlHash: orderUrlHash ?? null,
                images: productReviewFormData.images,
            },
        });

        if (createProductReviewResult.error !== undefined) {
            handleError(createProductReviewResult.error);

            return;
        }

        updatePortalContent(null);

        showSuccessMessage(
            `${t('Thank you for your review!')} ${t('We will publish your review as soon as it is approved.')}${
                isUserLoggedIn ? ` ${t('You will find your review in the My reviews section of your account.')}` : ''
            }`,
        );
    };

    return (
        <Popup
            className="w-11/12 lg:w-1/2"
            contentClassName="overflow-y-auto"
            title={t('Write a review')}
            ariaDescription={t('Rate the product with 1 to 5 stars and optionally describe your experience.', {
                ns: 'accessibility',
            })}
        >
            <p className="mb-4 font-semibold">{productName}</p>

            <FormProvider {...formProviderMethods}>
                <Form
                    formName={formMeta.formName}
                    onSubmit={formProviderMethods.handleSubmit(createProductReviewHandler)}
                >
                    <FormContentWrapper>
                        <FormBlockWrapper>
                            {isVariantSelectShown && (
                                <Controller
                                    name={formMeta.fields.productUuid.name}
                                    render={({ fieldState: { error }, field }) => (
                                        <FormLine>
                                            <Select
                                                isRequired
                                                ariaLabel={t('Select a product variant', { ns: 'accessibility' })}
                                                isDisabled={isSubmitting}
                                                label={formMeta.fields.productUuid.label}
                                                options={variantsAsOptions}
                                                tid={`${formMeta.formName}-${formMeta.fields.productUuid.name}`}
                                                activeOption={variantsAsOptions.find(
                                                    (option) => option.value === field.value,
                                                )}
                                                onSelectOption={(option) => field.onChange(option.value)}
                                            />
                                            <FormLineError error={error} inputType="select" />
                                        </FormLine>
                                    )}
                                />
                            )}

                            <Controller
                                name={formMeta.fields.rating.name}
                                render={({ fieldState: { error }, field }) => (
                                    <FormLine>
                                        <span className="mb-1 block font-semibold text-sm">
                                            {formMeta.fields.rating.label} <span className="text-text-error">*</span>
                                        </span>

                                        <StarRatingPicker
                                            id={`${formMeta.formName}-${field.name}`}
                                            isDisabled={isSubmitting}
                                            value={field.value}
                                            onChange={field.onChange}
                                        />

                                        <FormLineError error={error} inputType="text-input" />
                                    </FormLine>
                                )}
                            />

                            <TextareaControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.text.name}
                                textareaProps={{
                                    label: formMeta.fields.text.label,
                                    rows: 4,
                                    disabled: isSubmitting,
                                }}
                            />

                            <FormColumn>
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.firstName.name}
                                    width="half"
                                    textInputProps={{
                                        label: formMeta.fields.firstName.label,
                                        required: true,
                                        type: 'text',
                                        autoComplete: 'given-name',
                                        disabled: isSubmitting,
                                    }}
                                />

                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.lastName.name}
                                    width="half"
                                    textInputProps={{
                                        label: formMeta.fields.lastName.label,
                                        required: true,
                                        type: 'text',
                                        autoComplete: 'family-name',
                                        disabled: isSubmitting,
                                    }}
                                />
                            </FormColumn>

                            {!isUserLoggedIn && (
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.email.name}
                                    textInputProps={{
                                        label: formMeta.fields.email.label,
                                        required: true,
                                        type: 'email',
                                        autoComplete: 'email',
                                        disabled: isSubmitting,
                                    }}
                                />
                            )}

                            <DropzoneControlled
                                showPreviews
                                control={formProviderMethods.control}
                                disabled={isSubmitting}
                                formName={formMeta.formName}
                                label={t('Drag & drop some files here, or click to select files')}
                                name={formMeta.fields.images.name}
                                render={(dropzone) => <FormLine>{dropzone}</FormLine>}
                                legend={t(
                                    'You can attach up to {{ maxFilesCount }} photos in JPG or PNG format, each up to {{ max }}.',
                                    {
                                        maxFilesCount: VALIDATION_CONSTANTS.reviewMaxFilesCount,
                                        max: formatBytes(VALIDATION_CONSTANTS.fileMaxSize),
                                    },
                                )}
                            />

                            <CheckboxControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.isAnonymous.name}
                                checkboxProps={{
                                    disabled: isSubmitting,
                                    labelWrapperClassName: 'items-start',
                                    label: (
                                        <span className="flex flex-col gap-0.5">
                                            {formMeta.fields.isAnonymous.label}
                                            <span className="font-normal text-sm text-text-less">
                                                {t(
                                                    'If checked, "Anonymous customer" is displayed with the review instead of your name.',
                                                )}
                                            </span>
                                        </span>
                                    ),
                                }}
                            />

                            <p className="border-border-less border-t pt-4 text-sm text-text-less">
                                {t('The review will be published once approved.')}

                                {productReviewPolicyArticleUrl && (
                                    <>
                                        {' '}
                                        <Link
                                            href={productReviewPolicyArticleUrl}
                                            size="small"
                                            target="_blank"
                                            className="text-sm"
                                        >
                                            {t('How we work with reviews')}
                                        </Link>
                                    </>
                                )}
                            </p>
                        </FormBlockWrapper>

                        <FormButtonWrapper>
                            <SubmitButton>{t('Send review')}</SubmitButton>
                        </FormButtonWrapper>
                    </FormContentWrapper>
                </Form>
            </FormProvider>
        </Popup>
    );
};
