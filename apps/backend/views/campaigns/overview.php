<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5.5
 */

/** @var Controller $controller */
$controller = controller();

/** @var Campaign $campaign */
$campaign = $controller->getData('campaign');

/**
 * This hook gives a chance to prepend content or to replace the default view content with a custom content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->getData()}
 * In case the content is replaced, make sure to set {@CAttributeCollection $collection->add('renderContent', false)}
 * in order to stop rendering the default content.
 * @since 1.3.3.1
 */
hooks()->doAction('before_view_file_content', $viewCollection = new CAttributeCollection([
    'controller'    => $controller,
    'renderContent' => true,
]));

// and render if allowed
if (!empty($viewCollection) && $viewCollection->itemAt('renderContent')) {
    $controller->widget('customer.components.web.widgets.campaign-tracking.CampaignOverviewWidget', [
        'campaign' => $campaign,
    ]);

    $controller->widget('customer.components.web.widgets.campaign-tracking.CampaignOverviewCounterBoxesWidget', [
        'campaign' => $campaign,
    ]);

    $controller->widget('customer.components.web.widgets.campaign-tracking.CampaignOverviewRateBoxesWidget', [
        'campaign' => $campaign,
    ]);

    $controller->widget('customer.components.web.widgets.campaign-tracking.Campaign24HoursPerformanceWidget', [
        'campaign' => $campaign,
    ]);

    $controller->widget('customer.components.web.widgets.campaign-tracking.CampaignTopDomainsOpensClicksGraphWidget', [
        'campaign' => $campaign,
    ]);

    $controller->widget('customer.components.web.widgets.campaign-tracking.CampaignGeoOpensWidget', [
        'campaign' => $campaign,
    ]);

    $controller->widget('customer.components.web.widgets.campaign-tracking.CampaignOpenUserAgentsWidget', [
        'campaign' => $campaign,
    ]);

    $controller->widget('customer.components.web.widgets.campaign-tracking.CampaignTrackingTopClickedLinksWidget', [
        'campaign'        => $campaign,
        'showDetailLinks' => false,
    ]);

    $controller->widget('customer.components.web.widgets.campaign-tracking.CampaignTrackingLatestClickedLinksWidget', [
        'campaign'        => $campaign,
        'showDetailLinks' => false,
    ]); ?>

    <div class="row">
        <div class="col-lg-6">
            <?php
            $controller->widget('customer.components.web.widgets.campaign-tracking.CampaignTrackingLatestOpensWidget', [
                'campaign' => $campaign,
                'showDetailLinks' => false,
            ]); ?>
        </div>
        <div class="col-lg-6">
            <?php
            $controller->widget('customer.components.web.widgets.campaign-tracking.CampaignTrackingSubscribersWithMostOpensWidget', [
                'campaign' => $campaign,
                'showDetailLinks' => false,
            ]); ?>
        </div>
    </div>
    
<?php
}
/**
 * This hook gives a chance to append content after the view file default content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->getData()}
 * @since 1.3.3.1
 */
hooks()->doAction('after_view_file_content', new CAttributeCollection([
    'controller'        => $controller,
    'renderedContent'   => $viewCollection->itemAt('renderContent'),
]));
