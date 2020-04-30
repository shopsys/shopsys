<?php

declare(strict_types=1);

namespace App\Model\GoPay;

use GoPay\Http\Response;

class GoPayResponseData
{
    /**
     * @var \GoPay\Http\Response
     */
    public $response;

    /**
     * @var \App\Model\GoPay\GoPayTransaction
     */
    public $goPayTransaction;

    /**
     * @param \GoPay\Http\Response $response
     * @param \App\Model\GoPay\GoPayTransaction $goPayTransaction
     */
    public function __construct(Response $response, GoPayTransaction $goPayTransaction)
    {
        $this->response = $response;
        $this->goPayTransaction = $goPayTransaction;
    }
}
