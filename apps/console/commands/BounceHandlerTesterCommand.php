<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * BounceHandlerTesterCommand
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.6.6
 */

class BounceHandlerTesterCommand extends ConsoleCommand
{
    /**
     * @return void
     * @throws CException
     */
    public function init()
    {
        parent::init();

        Yii::import('common.vendors.BounceHandler.*');
    }

    /**
     * @return int
     * @throws ReflectionException
     */
    public function actionIndex()
    {
        $this->stdout('Starting...');

        $headerPrefix   = app_param('email.custom.header.prefix', '');
        $headerPrefixUp = strtoupper((string)$headerPrefix);

        $bounceHandler = new BounceHandlerTester('__TEST__', '__TEST__', '__TEST__', [
            'deleteMessages'                => false,
            'deleteAllMessages'             => false,
            'processLimit'                  => 1000,
            'searchCharset'                 => app()->charset,
            'imapOpenParams'                => [],
            'processDaysBack'               => 10,
            'processOnlyFeedbackReports'    => false,
            'requiredHeaders'               => [
                $headerPrefix . 'Campaign-Uid',
                $headerPrefix . 'Subscriber-Uid',
            ],
            'logger' => [$this, 'stdout'],
        ]);

        $this->stdout('Fetching the results...');

        // fetch the results
        $results = $bounceHandler->getResults();

        $this->stdout(sprintf('Found %d results.', count($results)));

        // done
        if (empty($results)) {
            $this->stdout('No results!');
            return 0;
        }

        $hard = $soft = $internal = $fbl = 0;

        foreach ($results as $result) {
            foreach ($result['originalEmailHeadersArray'] as $key => $value) {
                unset($result['originalEmailHeadersArray'][$key]);
                $result['originalEmailHeadersArray'][strtoupper((string)$key)] = $value;
            }

            if (!isset($result['originalEmailHeadersArray'][$headerPrefixUp . 'CAMPAIGN-UID'], $result['originalEmailHeadersArray'][$headerPrefixUp . 'SUBSCRIBER-UID'])) {
                continue;
            }

            $campaignUid   = trim($result['originalEmailHeadersArray'][$headerPrefixUp . 'CAMPAIGN-UID']);
            $subscriberUid = trim($result['originalEmailHeadersArray'][$headerPrefixUp . 'SUBSCRIBER-UID']);

            $this->stdout(sprintf('Processing campaign uid: %s and subscriber uid %s.', $campaignUid, $subscriberUid));

            if (in_array($result['bounceType'], [BounceHandler::BOUNCE_SOFT, BounceHandler::BOUNCE_HARD])) {
                if ($result['bounceType'] == BounceHandler::BOUNCE_SOFT) {
                    $soft++;
                } else {
                    $hard++;
                }

                $this->stdout(sprintf('Subscriber uid: %s is %s bounced with the message: %s.', $subscriberUid, $result['bounceType'], $result['email']));
            } elseif ($result['bounceType'] == BounceHandler::FEEDBACK_LOOP_REPORT) {
                $fbl++;
                $_message = 'DELETED / UNSUB';

                $this->stdout(sprintf('Subscriber uid: %s is %s bounced with the message: %s.', $subscriberUid, (string)$result['bounceType'], (string)$_message));
            } elseif ($result['bounceType'] == BounceHandler::BOUNCE_INTERNAL) {
                $internal++;
                $this->stdout(sprintf('Subscriber uid: %s is %s bounced with the message: %s.', $subscriberUid, $result['bounceType'], $result['email']));
            }
        }

        $this->stdout(sprintf('Overall: %d hard / %d soft / %d internal / %d fbl', $hard, $soft, $internal, $fbl));


        return 0;
    }
}
