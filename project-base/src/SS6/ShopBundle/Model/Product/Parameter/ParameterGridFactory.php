<?php

namespace SS6\ShopBundle\Model\Product\Parameter;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use SS6\ShopBundle\Model\Grid\ActionColumn;
use SS6\ShopBundle\Model\Grid\GridFactory;
use SS6\ShopBundle\Model\Grid\GridFactoryInterface;
use SS6\ShopBundle\Model\Grid\QueryBuilderDataSource;
use SS6\ShopBundle\Model\Localization\Localization;
use Symfony\Component\Translation\TranslatorInterface;

class ParameterGridFactory implements GridFactoryInterface {

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \SS6\ShopBundle\Model\Grid\GridFactory
	 */
	private $gridFactory;

	/**
	 * @var \SS6\ShopBundle\Model\Localization\Localization
	 */
	private $localization;

	/**
	 * @var \Symfony\Component\Translation\TranslatorInterface
	 */
	private $translator;

	public function __construct(
		EntityManager $em,
		GridFactory $gridFactory,
		Localization $localization,
		TranslatorInterface $translator
	) {
		$this->em = $em;
		$this->gridFactory = $gridFactory;
		$this->localization = $localization;
		$this->translator = $translator;
	}

	/**
	 * @return \SS6\ShopBundle\Model\Grid\Grid
	 */
	public function create() {
		$locales = $this->localization->getAllLocales();
		$defaultLocale = $this->localization->getDefaultLocale();
		$grid = $this->gridFactory->create('parameterList', $this->getParametersDataSource());
		$grid->setDefaultOrder('pt.name');

		$grid->addColumn(
			'name',
			'pt.name',
			$this->translator->trans('Název %locale%', array('%locale%' => $this->localization->getLanguageName($defaultLocale))),
			true
		);
		foreach ($locales as $locale) {
			if ($locale !== $defaultLocale) {
				$grid->addColumn(
					'name_' . $locale,
					'pt_' . $locale . '.name',
					$this->translator->trans('Název %locale%', array('%locale%' => $this->localization->getLanguageName($locale))),
					true
				);
			}
		}

		$grid->setActionColumnClassAttribute('table-col table-col-10');
		$grid->addActionColumn(
				ActionColumn::TYPE_DELETE,
				$this->translator->trans('Smazat'),
				'admin_parameter_delete',
				array('id' => 'p.id')
			)
			->setConfirmMessage($this->translator->trans('Opravdu chcete odstranit tento parametr? '
				. 'Smazáním parametru dojde k odstranění tohoto parametru u zboží, kde je parametr přiřazen. '
				. 'Tento krok je nevratný!'));

		$grid->setTheme('@SS6Shop/Admin/Content/Parameter/listGrid.html.twig');

		return $grid;
	}

	/**
	 * @param array $locales
	 * @return QueryBuilderDataSource
	 */
	private function getParametersDataSource() {
		$locales = $this->localization->getAllLocales();
		$queryBuilder = $this->em->createQueryBuilder();
		$queryBuilder
			->select('p, pt')
			->from(Parameter::class, 'p')
			->join('p.translations', 'pt', Join::WITH, 'pt.locale = :locale')
			->setParameter('locale', $this->localization->getDefaultLocale());

		foreach ($locales as $locale) {
			if ($locale !== $this->localization->getDefaultLocale()) {
				$queryBuilder
					->addSelect('pt_' . $locale)
					->leftJoin('p.translations', 'pt_' . $locale, Join::WITH, 'pt_' . $locale . '.locale = :locale_' . $locale)
					->setParameter('locale_' . $locale, $locale);
			}
		}

		return new QueryBuilderDataSource($queryBuilder, 'p.id');
	}
}
