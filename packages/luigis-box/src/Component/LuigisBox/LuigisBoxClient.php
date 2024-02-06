<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Component\LuigisBox;

use Shopsys\LuigisBoxBundle\Component\LuigisBox\Exception\LuigisBoxIndexNotRecognizedException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class LuigisBoxClient
{
    protected const WEBSERVER_NAME = 'shopsys-api';
    protected const LUIGIS_BOX_PARAMETER_PREFIX = 'luigisBox';
    protected const LUIGIS_BOX_PARAMETER_ALGORITHM_ID = 'algorithm_id';
    protected const LUIGIS_BOX_PARAMETER_OFFER_ID = 'offer_id';
    protected const LUIGIS_BOX_PARAMETER_LOCATION_ID = 'location_id';
    public const LUIGIS_BOX_INDEX_PRODUCTS = 'products';
    public const LUIGIS_BOX_INDEX_CATEGORIES = 'categories';
    public const LUIGIS_BOX_INDEX_ARTICLES = 'articles';
    public const LUIGIS_BOX_EVENT_GET_RECOMMENDATION = 'getRecommendation';
    public const LUIGIS_BOX_ACTION_SEARCH = 'search';
    public const LUIGIS_BOX_ACTION_RECOMMENDATION = 'recommendation';

    /**
     * @param string $luigisBoxApiUrl
     * @param string $luigisBoxAccountId
     * @param bool $luigisBoxIsProductionMode
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag
     */
    public function __construct(
        protected readonly string $luigisBoxApiUrl,
        protected readonly string $luigisBoxAccountId,
        protected readonly bool $luigisBoxIsProductionMode,
        protected readonly ParameterBagInterface $parameterBag,
    ) {
    }

    protected function checkNecessaryConfigurationIsSet(): void
    {
        if ($this->luigisBoxAccountId === '') {
            throw new LuigisBoxIndexNotRecognizedException('Luigi\'s Box account ID is not set.');
        }
    }

    /**
     * @param string $query
     * @param string $index
     * @param string $action
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @param string $browserId
     * @param string $requestingPage
     * @return \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult
     */
    public function getData(
        string $query,
        string $index,
        string $action,
        int $page,
        int $limit,
        array $filter = [],
        string $browserId = 'AAABQAg1a7sMJnpF6WlwIFuC',
        string $requestingPage = 'http://127.0.0.1:8000/',
    ): LuigisBoxResult {
        $this->checkNecessaryConfigurationIsSet();

        $data = json_decode(
            file_get_contents(
                $this->getLuigisBoxApiUrl(
                    $query,
                    $index,
                    $action,
                    $page,
                    $limit,
                    $filter,
                    $browserId,
                    $requestingPage,
                ),
            ),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        return new LuigisBoxResult(
            $data['data']['itemFieldValues'][$this->getIdFieldNameByIndex($index)],
            $data['data']['itemsCount'],
        );
    }

    /**
     * @param string $query
     * @param string $index
     * @param string $action
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @param string $browserId
     * @param string $requestingPage
     * @return string
     */
    protected function getLuigisBoxApiUrl(
        string $query,
        string $index,
        string $action,
        int $page,
        int $limit,
        array $filter,
        string $browserId,
        string $requestingPage,
    ): string {
        return $this->luigisBoxApiUrl .
            $this->luigisBoxAccountId . '/' .
            $this->getEnvironmentId() . '/' .
            'workflow.json?_t=' . $this->getTimestampInMilliseconds() .
            '&_a=' . urlencode($this->getWebserverName()) .
            '&_e=' . self::LUIGIS_BOX_EVENT_GET_RECOMMENDATION .
            '&_url=' . urlencode($requestingPage) .
            '&algorithmID=' . $this->getAlgorithmId($action) .
            '&offerID=' . $this->getOfferId($action) .
            '&locationID=' . $this->getLocationId($action) .
            '&query=' . urlencode('"' . $query . '"') .
            '&itemsPerPage=' . $limit .
            '&page=' . $page .
            '&index=' . urlencode($index) .
            '&_vid=' . urlencode($browserId) .
            ($filter !== [] ? '&boolQuery=' . urlencode(json_encode($filter, JSON_THROW_ON_ERROR)) : '') .
            '&idsOnly=' . $this->getOnlyIds();
    }

    /**
     * @return string
     */
    protected function getEnvironmentId(): string
    {
        return $this->luigisBoxIsProductionMode === true ? 'p' : 'test';
    }

    /**
     * @return int
     */
    protected function getTimestampInMilliseconds(): int
    {
        return (int)floor(microtime(true) * 1000);
    }

    /**
     * @return string
     */
    protected function getWebserverName(): string
    {
        return static::WEBSERVER_NAME;
    }

    /**
     * @param string $action
     * @param string $name
     * @return string
     */
    protected function getParameterByActionAndName(string $action, string $name): string
    {
        $parameterName = static::LUIGIS_BOX_PARAMETER_PREFIX . '.' . $action . '.' . $name;

        if (!$this->parameterBag->has($parameterName)) {
            throw new LuigisBoxIndexNotRecognizedException(
                sprintf('LuigisBox ENV variable for event "%s" with name "%s" or parameter "%s" is not set.', $action, $name, $parameterName),
            );
        }

        return $this->parameterBag->get($parameterName);
    }

    /**
     * @param string $action
     * @return string
     */
    protected function getAlgorithmId(string $action): string
    {
        return $this->getParameterByActionAndName($action, static::LUIGIS_BOX_PARAMETER_ALGORITHM_ID);
    }

    /**
     * @param string $action
     * @return string
     */
    protected function getOfferId(string $action): string
    {
        return $this->getParameterByActionAndName($action, static::LUIGIS_BOX_PARAMETER_OFFER_ID);
    }

    /**
     * @param string $action
     * @return string
     */
    protected function getLocationId(string $action): string
    {
        return $this->getParameterByActionAndName($action, static::LUIGIS_BOX_PARAMETER_LOCATION_ID);
    }

    /**
     * @return string
     */
    protected function getOnlyIds(): string
    {
        return 'true';
    }

    /**
     * @param string $index
     * @return string
     */
    protected function getIdFieldNameByIndex(string $index): string
    {
        if ($index === 'products') {
            return 'id';
        }

        if ($index === 'categories') {
            return 'categoryId';
        }

        throw new LuigisBoxIndexNotRecognizedException(sprintf('Luigi\'s Box index "%s" is not recognized.', $index));
    }
}
