<?php

namespace Shopsys\Plugin;

interface PluginCrudExtensionInterface
{
    /**
     * Returns a class name of a form type to be used
     *
     * Should return FQCN of a implementation of \Symfony\Component\Form\FormTypeInterface to be used as a sub-form
     */
    public function getFormTypeClass(): string;

    /**
     * Returns a human readable label of the sub-form
     */
    public function getFormLabel(): string;

    /**
     * Returns the data of an entity with provided id to be fed into the sub-form
     *
     * @return mixed
     */
    public function getData(int $id);

    /**
     * Saves the data of an entity with provided id after submitting of the sub-form
     *
     * @param mixed[] $data
     */
    public function saveData(int $id, array $data): void;

    /**
     * Removes all saved data of an entity with provided id after deleting the entity
     */
    public function removeData(int $id): void;
}
