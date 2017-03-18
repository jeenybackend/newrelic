<?php

namespace EasyTaxi\NewRelic\Config;

class TransactionConfig
{
    public $transactionName = 'index.php';
    public $applicationName = 'PHP Application';
    public $monitoredMethodName;
}
