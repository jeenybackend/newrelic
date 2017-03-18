<?php

namespace EasyTaxi\NewRelic;

use EasyTaxi\NewRelic\Config\TransactionConfig;
use EasyTaxi\NewRelic\Exception\InvalidCallerInstanceException;
use EasyTaxi\NewRelic\Exception\NotLoadedNewRelicExtensionException;
use EasyTaxi\NewRelic\Formatter\ArgumentsFormatter;
use EasyTaxi\NewRelic\Formatter\FormatterInterface;

class Transaction
{
    private $instance;
    private $config;
    private $formatter;

    public function __construct($instance, TransactionConfig $config)
    {
        if (!is_object($instance)) {
            throw new InvalidCallerInstanceException();
        }

        $this->instance = $instance;
        $this->config = $config;
        $this->formatter = new ArgumentsFormatter();

        if (!extension_loaded('newrelic')) {
            throw new NotLoadedNewRelicExtensionException();
        }
    }

    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    public function __call($name, $arguments)
    {
        $this->transactionStart($name, $arguments);

        try {
            return call_user_func_array([$this->instance, $name], $arguments);
        } catch (\Exception $genericException) {
            $this->transactionFail($name, $genericException);
            throw $genericException;
        } finally {
            $this->transactionEnd($name);
        }
    }

    private function addNewRelicParameter($customParameters)
    {
        foreach ($customParameters as $key => $value) {
            if (null === $value || is_scalar($value)) {
                newrelic_add_custom_parameter($key, $value);
            } else {
                newrelic_add_custom_parameter($key, @json_encode($value));
            }
        }
    }

    private function transactionStart($name, $arguments)
    {
        if (!$this->shouldBeMonitored($name)) {
            return;
        }

        newrelic_set_appname($this->config->applicationName);
        newrelic_start_transaction($this->config->applicationName);
        newrelic_name_transaction($this->config->transactionName);
        $customParameters = $this->formatter->format($arguments);
        $this->addNewRelicParameter($customParameters);
    }

    private function shouldBeMonitored($name)
    {
        return !$this->config->monitoredMethodName || $name == $this->config->monitoredMethodName;
    }

    private function transactionEnd($name)
    {
        if (!$this->shouldBeMonitored($name)) {
            return;
        }

        newrelic_end_transaction();
    }

    private function transactionFail($name, \Exception $genericException)
    {
        if (!$this->shouldBeMonitored($name)) {
            return;
        }

        newrelic_notice_error($genericException->getMessage(), $genericException);
    }
}
