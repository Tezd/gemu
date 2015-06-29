<?php

namespace Gemu\Core\Gateway\EndPoint;

use Symfony\Component\HttpFoundation\ParameterBag;

class Request extends ParameterBag
{
    /**
     * @type string
     */
    protected $transaction_id;

    /**
     * @param string $transactionId
     * @param array $transactionData
     */
    public function __construct($transactionId, array $transactionData)
    {
        $this->transaction_id = $transactionId;
        parent::__construct($transactionData);
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transaction_id;
    }

    /**
     * @param string $path
     *
     * @return mixed
     */
    public function getDeep($path)
    {
        return $this->get($path, null, true);
    }
}
