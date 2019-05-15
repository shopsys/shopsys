<?php

namespace Shopsys\PohodaBundle\Model\Product\JShopsys;

use Shopsys\PohodaBundle\Component\JShopsys\JShopsysActionCallableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JShopsysProductImportFacade implements JShopsysActionCallableInterface
{
    const ACTION_TYPE_DELETE = 'delete';

    /**
     * @var string
     */
    private $productsImportDirectory;

    /**
     * @param string $productsImportDirectory
     */
    public function __construct(string $productsImportDirectory)
    {
        $this->productsImportDirectory = $productsImportDirectory;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function call(Request $request, Response $response): Response
    {
        /**
         * This "action" parameter is sent automatically as part of the request from jShopsys.
         * Csv files with products are uploaded on eshop ftp one by one and they are grouped into several groups.
         * Possible groups are "all", "insert", "update", "delete".
         * Group of currently processed file is sent as "action" parameter of the request from jShopsys
         */
        $productsProcessingActionType = $request->request->has('action') ? $request->request->get('action') : null;

        if ($productsProcessingActionType === null) {
            return $response->setContent('500-INFO-Není uveden parametr action.');
        }

        if ($productsProcessingActionType === self::ACTION_TYPE_DELETE) {
            return $response->setContent('200-OK-Tento typ akce je ignorován.');
        }

        $productsImportFileName = $request->request->has('filename') ? $request->request->get('filename') : null;

        if ($productsImportFileName === null) {
            return $response->setContent('500-INFO-Není uveden parametr filename.');
        }

        $importedFilesDirectoryPathByProcessingActionType = $this->getImportedFilesDirectoryPathByProcessingActionType(
            $productsProcessingActionType
        );

        $productsImportFilePath = $importedFilesDirectoryPathByProcessingActionType . '/' . $productsImportFileName;

        if (!file_exists($productsImportFilePath)) {
            return $response->setContent(sprintf('500-INFO-Soubor %s nelze načíst.', $productsImportFileName));
        }

        try {
            $this->processProductsImportFile($productsImportFilePath);
        } catch (\Exception $exc) {
            return $response->setContent('500-INFO-Při zpracování souboru %s došlo k chybě.', $productsImportFileName);
        }

        return $response->setContent('200-OK-Vybraná akce byla dokončena.');
    }

    /**
     * @param string $productsProcessingActionType
     * @return string
     */
    private function getImportedFilesDirectoryPathByProcessingActionType(string $productsProcessingActionType)
    {
        /**
         * JShopsys uploads CSV with products into multiple directories.
         * update_in/ csv files with updated products (products exported at least once before)
         * insert_in/ csv files with new products
         * all_in/
         * delete_in/
         * This is the result of some historical reasons and there is not necessary to process trasferred products according to this structure.
         * A better way is to choose the unique attribute of the product and create/update products by this unique attribute.
         * Action "delete" is usually ignored.
         */
        return $this->productsImportDirectory . '/' . $productsProcessingActionType . '_in';
    }

    /**
     * @param string $productsImportFilePath
     */
    private function processProductsImportFile(string $productsImportFilePath)
    {
    }
}
