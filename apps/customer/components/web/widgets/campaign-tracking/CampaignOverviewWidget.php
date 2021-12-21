<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * CampaignOverviewWidget
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.7.3
 */

class CampaignOverviewWidget extends CWidget
{
    /**
     * @var Campaign
     */
    public $campaign;

    /**
     * @return void
     * @throws CException
     */
    public function run()
    {
        $campaign = $this->campaign;

        if ($campaign->status == Campaign::STATUS_DRAFT) {
            return;
        }

        /** @var OptionUrl $optionUrl */
        $optionUrl = container()->get(OptionUrl::class);

        $webVersionUrl  = $optionUrl->getFrontendUrl();
        $webVersionUrl .= 'campaigns/' . $campaign->campaign_uid;
        $forwardsUrl    = 'javascript:;';
        $abusesUrl      = 'javascript:;';
        $recipientsUrl  = 'javascript:;';
        $shareReports   = null;

        if (apps()->isAppName('customer')) {
            $shareReports   = $campaign->shareReports;
            $forwardsUrl    = ['campaign_reports/forward_friend', 'campaign_uid' => $campaign->campaign_uid];
            $abusesUrl      = ['campaign_reports/abuse_reports', 'campaign_uid' => $campaign->campaign_uid];
            $recipientsUrl  = ['campaign_reports/delivery', 'campaign_uid' => $campaign->campaign_uid];
        } elseif (apps()->isAppName('frontend')) {
            $forwardsUrl    = ['campaigns_reports/forward_friend', 'campaign_uid' => $campaign->campaign_uid];
            $abusesUrl      = ['campaigns_reports/abuse_reports', 'campaign_uid' => $campaign->campaign_uid];
            $recipientsUrl  = ['campaigns_reports/delivery', 'campaign_uid' => $campaign->campaign_uid];
        }

        $recurringInfo = null;
        if ($campaign->getIsRecurring()) {
            $cron = new JQCron($campaign->getRecurringCronjob());
            $recurringInfo = $cron->getText(LanguageHelper::getAppLanguageCode());
        }

        $this->render('overview', compact('campaign', 'webVersionUrl', 'recurringInfo', 'shareReports', 'forwardsUrl', 'abusesUrl', 'recipientsUrl'));
    }
}
