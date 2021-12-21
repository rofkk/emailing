<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * CampaignOverviewCounterBoxesWidget
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.7.3
 */

class CampaignOverviewCounterBoxesWidget extends CWidget
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

        $canExportStats   = false;
        $opensLink        = 'javascript:;';
        $clicksLink       = 'javascript:;';
        $unsubscribesLink = 'javascript:;';
        $complaintsLink   = 'javascript:;';
        $bouncesLink      = 'javascript:;';

        if (isset($this->getController()->campaignReportsController)) {
            $canExportStats   = ($campaign->customer->getGroupOption('campaigns.can_export_stats', 'yes') == 'yes');
            $opensLink        = createUrl($this->getController()->campaignReportsController . '/open_unique', ['campaign_uid' => $campaign->campaign_uid]);
            $clicksLink       = createUrl($this->getController()->campaignReportsController . '/click', ['campaign_uid' => $campaign->campaign_uid]);
            $unsubscribesLink = createUrl($this->getController()->campaignReportsController . '/unsubscribe', ['campaign_uid' => $campaign->campaign_uid]);
            $complaintsLink   = createUrl($this->getController()->campaignReportsController . '/complain', ['campaign_uid' => $campaign->campaign_uid]);
            $bouncesLink      = createUrl($this->getController()->campaignReportsController . '/bounce', ['campaign_uid' => $campaign->campaign_uid]);
        }

        $this->render('overview-counter-boxes', compact('campaign', 'canExportStats', 'opensLink', 'clicksLink', 'unsubscribesLink', 'complaintsLink', 'bouncesLink'));
    }
}
