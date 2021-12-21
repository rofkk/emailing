<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * Campaigns_trackingController
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.7.3
 */

class Campaigns_trackingController extends Controller
{
    /**
     * @return array
     */
    public function accessRules()
    {
        return [
            // allow all authenticated users on all actions
            ['allow', 'users' => ['@']],
            // deny all rule.
            ['deny'],
        ];
    }

    /**
     * Handles the click tracking.
     *
     * @param string $campaign_uid
     * @param string $subscriber_uid
     * @param string $hash
     *
     * @return void
     * @throws CException
     */
    public function actionTrack_url($campaign_uid, $subscriber_uid, $hash)
    {
        /** @var Campaign $campaign */
        $campaign = $this->loadCampaignByUid($campaign_uid);

        if (empty($campaign)) {
            $this->renderJson([
                'status' => 'error',
                'error'  => t('api', 'The campaign does not exist.'),
            ], 404);
        }

        /** @var ListSubscriber $subscriber */
        $subscriber = $this->loadSubscriberByUid($subscriber_uid);

        if (empty($subscriber)) {
            $this->renderJson([
                'status' => 'error',
                'error'  => t('api', 'The subscriber does not exist.'),
            ], 404);
        }

        /** @var CampaignUrl $url */
        $url = $this->loadCampaignUrlByHash($campaign, $hash);

        if (empty($url)) {
            $this->renderJson([
                'status' => 'error',
                'error'  => t('api', 'The url hash does not exist.'),
            ], 404);
        }

        $track                = new CampaignTrackUrl();
        $track->url_id        = (int)$url->url_id;
        $track->subscriber_id = (int)$subscriber->subscriber_id;
        $track->ip_address    = (string)request()->getUserHostAddress();
        $track->user_agent    = substr((string)request()->getUserAgent(), 0, 255);
        $track->save(false);

        $url->destination = StringHelper::normalizeUrl($url->destination);
        $destination = $url->destination;
        if (preg_match('/\[(.*)?\]/', $destination)) {
            list(, , $destination) = CampaignHelper::parseContent($destination, $campaign, $subscriber);
        }

        if ($campaign->option->open_tracking == CampaignOption::TEXT_YES && !$subscriber->hasOpenedCampaign($campaign)) {
            $track                = new CampaignTrackOpen();
            $track->campaign_id   = (int)$campaign->campaign_id;
            $track->subscriber_id = (int)$subscriber->subscriber_id;
            $track->ip_address    = (string)request()->getUserHostAddress();
            $track->user_agent    = substr((string)request()->getUserAgent(), 0, 255);
            $track->save(false);
        }

        $trackUrl = container()->get(OptionUrl::class)
            ->getFrontendUrl(sprintf('campaigns/track-url/%s/%s/%s', $campaign_uid, $subscriber_uid, $hash));

        $this->renderJson([
            'status' => 'success',
            'data'   => [
                'track_url'   => $trackUrl,
                'destination' => $destination,
            ],
        ]);
    }

    /**
     * Handles the opens tracking.
     *
     * @param string $campaign_uid
     * @param string $subscriber_uid
     *
     * @return void
     * @throws CException
     */
    public function actionTrack_opening($campaign_uid, $subscriber_uid)
    {
        /** @var Campaign $campaign */
        $campaign = $this->loadCampaignByUid($campaign_uid);

        if (empty($campaign)) {
            $this->renderJson([
                'status' => 'error',
                'error'  => t('api', 'The campaign does not exist.'),
            ], 404);
        }

        /** @var ListSubscriber $subscriber */
        $subscriber = $this->loadSubscriberByUid($subscriber_uid);

        if (empty($subscriber)) {
            $this->renderJson([
                'status' => 'error',
                'error'  => t('api', 'The subscriber does not exist.'),
            ], 404);
        }

        $track = new CampaignTrackOpen();
        $track->campaign_id   = (int)$campaign->campaign_id;
        $track->subscriber_id = (int)$subscriber->subscriber_id;
        $track->ip_address    = (string)request()->getUserHostAddress();
        $track->user_agent    = substr((string)request()->getUserAgent(), 0, 255);
        $track->save(false);

        $this->renderJson([
            'status' => 'success',
            'data'   => [],
        ]);
    }

    /**
     * Handles unsubscription of an existing subscriber.
     *
     * @param string $campaign_uid
     * @param string $subscriber_uid
     *
     * @return void
     * @throws CException
     */
    public function actionTrack_unsubscribe($campaign_uid, $subscriber_uid)
    {
        if (!request()->getIsPostRequest()) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'Only POST requests allowed for this endpoint.'),
            ], 400);
        }

        /** @var Customer $customer */
        $customer = user()->getModel();

        /** @var Campaign $campaign */
        $campaign = $this->loadCampaignByUid($campaign_uid);

        if (empty($campaign)) {
            $this->renderJson([
                'status' => 'error',
                'error'  => t('api', 'The campaign does not exist.'),
            ], 404);
        }

        /** @var ListSubscriber $subscriber */
        $subscriber = $this->loadSubscriberByUid($subscriber_uid);

        if (empty($subscriber)) {
            $this->renderJson([
                'status' => 'error',
                'error'  => t('api', 'The subscriber does not exist.'),
            ], 404);
        }

        if (!$subscriber->getIsConfirmed()) {
            $this->renderJson([
                'status' => 'success',
            ]);
        }

        if (!$subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED)) {
            $this->renderJson([
                'status' => 'success',
            ]);
        }

        $track = CampaignTrackUnsubscribe::model()->findByAttributes([
            'campaign_id'   => (int)$campaign->campaign_id,
            'subscriber_id' => (int)$subscriber->subscriber_id,
        ]);

        if (!empty($track)) {
            $this->renderJson([
                'status' => 'success',
            ]);
        }

        $track = new CampaignTrackUnsubscribe();
        $track->campaign_id   = (int)$campaign->campaign_id;
        $track->subscriber_id = (int)$subscriber->subscriber_id;
        $track->ip_address    = substr((string)request()->getPost('ip_address', (string)request()->getUserHostAddress()), 0, 45);
        $track->user_agent    = substr((string)request()->getPost('user_agent', (string)request()->getUserAgent()), 0, 255);
        $track->reason        = substr((string)request()->getPost('reason', 'Unsubscribed via API!'), 0, 255);
        $track->save();

        $subscriber->takeListSubscriberAction(ListSubscriberAction::ACTION_UNSUBSCRIBE);

        /** @var CustomerActionLogBehavior $logAction */
        $logAction = $customer->getLogAction();
        $logAction->subscriberUnsubscribed($subscriber);

        $this->renderJson([
            'status' => 'success',
        ]);
    }

    /**
     * @param string $campaign_uid
     *
     * @return Campaign|null
     */
    public function loadCampaignByUid(string $campaign_uid): ?Campaign
    {
        $criteria = new CDbCriteria();
        $criteria->compare('campaign_uid', $campaign_uid);

        /** @var Campaign $model */
        $model = Campaign::model()->find($criteria);

        return $model;
    }

    /**
     * @param string $subscriber_uid
     *
     * @return ListSubscriber|null
     */
    public function loadSubscriberByUid(string $subscriber_uid): ?ListSubscriber
    {
        $criteria = new CDbCriteria();
        $criteria->compare('subscriber_uid', $subscriber_uid);
        return ListSubscriber::model()->find($criteria);
    }

    /**
     * @param Campaign $campaign
     * @param string $hash
     *
     * @return CampaignUrl|null
     */
    public function loadCampaignUrlByHash(Campaign $campaign, string $hash): ?CampaignUrl
    {
        // try with a real hash
        $url = CampaignUrl::model()->findByAttributes([
            'campaign_id'   => (int)$campaign->campaign_id,
            'hash'          => $hash,
        ]);

        // maybe a url destination
        if (empty($url) && stripos($hash, 'http') === 0) {
            $url = CampaignUrl::model()->findByAttributes([
                'campaign_id'   => $campaign->campaign_id,
                'destination'   => $hash,
            ]);
        }

        return $url;
    }
}
