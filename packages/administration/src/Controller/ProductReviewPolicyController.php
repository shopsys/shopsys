<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\FrameworkBundle\Form\Admin\ProductReviewPolicy\ProductReviewPolicySettingFormType;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewEnabledChecker;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewPolicyFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_PRODUCT_REVIEW_POLICY)]
class ProductReviewPolicyController extends AdminBaseController
{
    public function __construct(
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly ProductReviewPolicyFacade $productReviewPolicyFacade,
        protected readonly ProductReviewEnabledChecker $productReviewEnabledChecker,
    ) {
    }

    #[Route(path: '/product-review-policy/setting/')]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function settingAction(Request $request): Response
    {
        if (!$this->productReviewEnabledChecker->isEnabledOnAnyDomain()) {
            throw new NotFoundHttpException('Product reviews are not enabled on any domain.');
        }

        $selectedDomainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        if (!$this->productReviewEnabledChecker->isEnabledForDomain($selectedDomainId)) {
            return $this->render('@ShopsysAdministration/content/productReviewPolicy/disabled.html.twig');
        }

        $productReviewPolicyArticle = $this->productReviewPolicyFacade->findProductReviewPolicyArticleByDomainId($selectedDomainId);

        $form = $this->createForm(
            ProductReviewPolicySettingFormType::class,
            [ProductReviewPolicySettingFormType::PRODUCT_REVIEW_POLICY_ARTICLE_FIELD_NAME => $productReviewPolicyArticle],
            ['domain_id' => $selectedDomainId],
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productReviewPolicyArticle = $form->getData()[ProductReviewPolicySettingFormType::PRODUCT_REVIEW_POLICY_ARTICLE_FIELD_NAME];

            $this->productReviewPolicyFacade->setProductReviewPolicyArticleOnDomain(
                $productReviewPolicyArticle,
                $selectedDomainId,
            );

            $this->addSuccessFlashTwig(t('Product review policy settings modified.'));

            return $this->redirectToRoute('shopsys_administration_productreviewpolicy_setting');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/productReviewPolicy/setting.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
