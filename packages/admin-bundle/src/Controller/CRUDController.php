<?php

declare(strict_types=1);

namespace Shopsys\AdminBundle\Controller;

use Sonata\AdminBundle\Exception\LockException;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Exception\ModelManagerThrowable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CRUDController extends \Sonata\AdminBundle\Controller\CRUDController
{
    /**
     * @throws NotFoundHttpException If the object does not exist
     * @throws AccessDeniedException If access is not granted
     */
    public function editAction(Request $request): Response
    {
        // the key used to lookup the template
        $templateKey = 'edit';

        $existingObject = $this->assertObjectExists($request, true);
        \assert(null !== $existingObject);

        $this->checkParentChildAssociation($request, $existingObject);

        $this->admin->checkAccess('edit', $existingObject);

        $preResponse = $this->preEdit($request, $existingObject);
        if (null !== $preResponse) {
            return $preResponse;
        }
        $this->admin->setSubject($existingObject);
        $objectId = $this->admin->getNormalizedIdentifier($existingObject);
        \assert(null !== $objectId);

        $form = $this->admin->getForm();

        // TODO: custom one line
        $existingObject = $this->admin->generateDataObject($existingObject);

        $form->setData($existingObject);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode($request) || $this->isPreviewApproved($request))) {
                /** @phpstan-var T $submittedObject */
                $submittedObject = $form->getData();

                // TODO: custom one line
                //$this->admin->setSubject($submittedObject);

                try {
                    $existingObject = $this->admin->update($submittedObject);

                    if ($this->isXmlHttpRequest($request)) {
                        return $this->handleXmlHttpRequestSuccessResponse($request, $existingObject);
                    }

                    $this->addFlash(
                        'sonata_flash_success',
                        $this->trans(
                            'flash_edit_success',
                            ['%name%' => $this->escapeHtml($this->admin->toString($existingObject))],
                            'SonataAdminBundle'
                        )
                    );

                    // redirect to edit mode
                    return $this->redirectTo($request, $existingObject);
                } catch (ModelManagerException $e) {
                    // NEXT_MAJOR: Remove this catch.
                    $errorMessage = $this->handleModelManagerException($e);

                    $isFormValid = false;
                } catch (ModelManagerThrowable $e) {
                    $errorMessage = $this->handleModelManagerThrowable($e);

                    $isFormValid = false;
                } catch (LockException) {
                    $this->addFlash('sonata_flash_error', $this->trans('flash_lock_error', [
                        '%name%' => $this->escapeHtml($this->admin->toString($existingObject)),
                        '%link_start%' => sprintf('<a href="%s">', $this->admin->generateObjectUrl('edit', $existingObject)),
                        '%link_end%' => '</a>',
                    ], 'SonataAdminBundle'));
                }
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if ($this->isXmlHttpRequest($request) && null !== ($response = $this->handleXmlHttpRequestErrorResponse($request, $form))) {
                    return $response;
                }

                $this->addFlash(
                    'sonata_flash_error',
                    $errorMessage ?? $this->trans(
                    'flash_edit_error',
                    ['%name%' => $this->escapeHtml($this->admin->toString($existingObject))],
                    'SonataAdminBundle'
                )
                );
            } elseif ($this->isPreviewRequested($request)) {
                // enable the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }

        $formView = $form->createView();
        // set the theme for the current Admin Form
        $this->setFormTheme($formView, $this->admin->getFormTheme());

        $template = $this->admin->getTemplateRegistry()->getTemplate($templateKey);

        /**
         * @psalm-suppress DeprecatedMethod
         */
        return $this->renderWithExtraParams($template, [
            'action' => 'edit',
            'form' => $formView,
            'object' => $existingObject,
            'objectId' => $objectId,
        ]);
    }
}