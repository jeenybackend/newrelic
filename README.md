New Relic Libraries
==========================

[![Build Status](https://travis-ci.org/easytaxibr/newrelic.svg?branch=master)](https://travis-ci.org/easytaxibr/newrelic)
[![Code Coverage](https://scrutinizer-ci.com/g/easytaxibr/newrelic/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/easytaxibr/newrelic/?branch=master)

New Relic Transaction Library
==========================

Use this library to report background jobs or long running scripts to New Relic APM.

## Examples

```php
<?php

namespace Consumers;

class Email
{
    public function sendEmail($recipient, $body, $header)
    {
        //Send email
    }

    public function beforePerform()
    {
        //Before Hook
    }

    public function perform()
    {
        //Perform a job
    }

    public function afterPerform()
    {
        //After Hook
    }
}

namespace Foo\Bar;

use EasyTaxi\NewRelic;
use Consumers;

$consumer = new Consumers\Email();

while (true) {
    $transactionConfig = new NewRelic\Config\TransactionConfig();
    $transactionConfig->applicationName = 'Background Jobs';
    $transactionConfig->transactionName = 'consumer::sendEmail';
    $consumerMonitored = new NewRelic\Transaction($consumer, $transactionConfig);
    $consumerMonitored->sendEmail('Spock', 'James', 'Tiberius');

    $transactionConfig->monitoredMethodName = 'perform';
    $consumerMonitored->beforePerform();
    $consumerMonitored->perform();
    $consumerMonitored->afterPerform();
}
```

> You MUST have an agent configured and running on the server

## Configuration options

Use `TransactionConfig` class to personalize your job.

- You can use `transactionName` field to specify the name of each transactions
    > Defaults to `index.php` if not specified

- You can use `applicationName` field to specify the name your application
    > Defaults to `PHP Application` if not specified

- You can use `monitoredMethodName` field to specify only one method to be monitored
    > If not defined every call to a method will be considered one transaction

New Relic Insights Library
==========================

Use this library to easily post custom events to New Relic Insights.

```php
<?php

use EasyTaxi\NewRelic;
use GuzzleHttp\Client;

$client = new Client([
    #You need to change it to your account number
    'base_uri' => 'https://insights-collector.newrelic.com/v1/accounts/99999/'
]);
$this->newRelicInsights = new NewRelic\Insights($client, 'YOUR_KEY_HERE');

$events = new NewRelic\Entity\Insights\EventCollection();

$event = new NewRelic\Entity\Insights\Event();
$event->eventType = "Purchase";
$event->account = 3;
$event->amount = 259.54;
$events->add($event);

$event2 = new NewRelic\Entity\Insights\Event();
$event2->eventType = "Purchase";
$event2->account = 4;
$events->add($event2);

$promise = $this->newRelicInsights->sendEvent($events);
$promise->wait();
```

> You can find your key at Insights https://insights.newrelic.com/accounts/99999/manage/add_data

## Configuring

* Your `base_uri` MUST end with trailing slash `/`
* You MUST replace `99999` with your account number

## Installing

The recommended way to install is through
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version:

```bash
composer.phar require easytaxibr/newrelic
```
