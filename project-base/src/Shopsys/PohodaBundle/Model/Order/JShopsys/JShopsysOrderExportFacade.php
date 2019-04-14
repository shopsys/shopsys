<?php

namespace Shopsys\PohodaBundle\Model\Order\JShopsys;

use Shopsys\PohodaBundle\Component\JShopsys\JShopsysActionCallableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JShopsysOrderExportFacade implements JShopsysActionCallableInterface
{
    private const ORDERS_EXPORT_XML_FILE_NAME_PREDIX = 'objednavky_imp_';

    /**
     * @var string
     */
    private $ordersExportDirectory;

    /**
     * @param string $ordersExportDirectory
     */
    public function __construct(string $ordersExportDirectory)
    {
        $this->ordersExportDirectory = $ordersExportDirectory;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function call(Request $request, Response $response): Response
    {
        /**
         * This "odkdy" parameter is sent automatically as part of the request from jShopsys.
         * In jShopsys this value is displayed in the section "Menu >> Zobrazit nastavení >> Export objednávek".
         * This value is changed automatically after the order export action in jShopsys is started.
         */
        $ordersFromTimestampActionParameter = $request->request->has('odkdy') ? (int)$request->request->get('odkdy') : null;

        if ($ordersFromTimestampActionParameter === null) {
            return $response->setContent('500-INFO-Není uveden parametr odkdy.');
        }

        /**
         * Here will be implemented export of selected orders into XML file
         */
        $exportedOrders = [1];
        if (count($exportedOrders) > 0) {
            /**
             * Generate xml file into $this->ordersExportDirectory (web/db/pohoda/data/export_xml)
             */
            $ordersExportFileName = self::ORDERS_EXPORT_XML_FILE_NAME_PREDIX . time() . '.xml';

            /**
             * Implement xml export itself ...
             */
            fopen($this->ordersExportDirectory . '/' . $ordersExportFileName, 'w+');

            return $response->setContent('201-NAME_FILE-' . $ordersExportFileName);
        }

        return $response->setContent('202-INFO-Na serveru jiz neni co generovat.');
    }
}
