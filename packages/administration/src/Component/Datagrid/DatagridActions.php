<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid;

use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @phpstan-type ActionOptions array{
 *     label?: string,
 *     icon?: string,
 *     routeName?: string,
 *     additionalParameters?: array,
 *     confirmMessage?: string,
 * }
 */
final class DatagridActions
{
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<string, ActionOptions>
     */
    private ArrayCollection $actions;

    public function __construct()
    {
        $this->actions = new ArrayCollection();
    }

    /**
     * @param string $name
     * @param array{
     *      label?: string,
     *      icon?: string,
     *      routeName?: string,
     *      additionalParameters?: array,
     *      confirmMessage?: string,
     *  } $options
     * @phpstan-param ActionOptions $options
     * @return self
     */
    public function add(string $name, array $options): self
    {
        if ($this->actions->containsKey($name)) {
            throw new InvalidArgumentException(sprintf('Action with name "%s" already exists.', $name));
        }

        $this->actions->set($name, $this->resolveOptions($name, $options));

        return $this;
    }

    /**
     * @param string $name
     * @param array{
     *       label?: string,
     *       icon?: string,
     *       routeName?: string,
     *       additionalParameters?: array,
     *       confirmMessage?: string,
     *   } $options
     * @phpstan-param ActionOptions $options
     * @return self
     */
    public function update(string $name, array $options): self
    {
        if (!$this->actions->containsKey($name)) {
            throw new InvalidArgumentException(sprintf('Action with name "%s" does not exist.', $name));
        }

        $action = $this->actions->get($name);
        $this->actions->set($name, [...$action, ...$options]);

        return $this;
    }

    /**
     * @param string $name
     * @return self
     */
    public function delete(string $name): self
    {
        if (!$this->actions->containsKey($name)) {
            throw new InvalidArgumentException(sprintf('Action with name "%s" does not exist.', $name));
        }

        $this->actions->remove($name);

        return $this;
    }

    /**
     * @return array<string, ActionOptions>
     */
    public function getActions(): array
    {
        return $this->actions->toArray();
    }

    /**
     * @param string $name
     * @param ActionOptions $options
     * @return ActionOptions
     */
    private function resolveOptions(string $name, array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'additionalParameters' => [],
            'confirmMessage' => null,
        ]);

        $resolver->setRequired(['label', 'icon', 'routeName']);

        $resolver->setAllowedTypes('label', 'string');
        $resolver->setAllowedTypes('icon', 'string');
        $resolver->setAllowedTypes('routeName', 'string');
        $resolver->setAllowedTypes('additionalParameters', 'array');
        $resolver->setAllowedTypes('confirmMessage', ['string', 'null']);

        $options = $resolver->resolve($options);

        if ($name === 'delete' && is_string($options['confirmMessage']) === false) {
            $options['confirmMessage'] = t('Do you really want to delete this item?');
        }

        return $options;
    }
}
