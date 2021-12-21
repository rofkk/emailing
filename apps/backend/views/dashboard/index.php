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
 * @since 1.0
 */

/** @var Controller $controller */
$controller = controller();

/** @var string $pageHeading */
$pageHeading = (string)$controller->getData('pageHeading');

/** @var array $glanceStats */
$glanceStats = $controller->getData('glanceStats');

/** @var array $timelineItems */
$timelineItems = $controller->getData('timelineItems');

/** @var bool $renderItems */
$renderItems = $controller->getData('renderItems');

/** @var bool $checkVersionUpdate */
$checkVersionUpdate = $controller->getData('checkVersionUpdate');

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
    ?>
    <div class="box box-primary borderless">
        <div class="box-header">
            <div class="pull-left">
                <h3 class="box-title"><?php echo IconHelper::make('info') . html_encode((string)$pageHeading); ?></h3>
            </div>
            <div class="pull-right"></div>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="box-body">

            <?php if (empty($renderItems)) {
        /**
         * This widget renders default getting started page for this particular section.
         * @since 1.3.9.3
         */
        $controller->widget('common.components.web.widgets.StartPagesWidget', [
                    'collection' => $collection = new CAttributeCollection([
                        'controller' => $controller,
                        'renderGrid' => true,
                    ]),
                    'enabled' => true,
                ]);
    } ?>
            
            <div class="row boxes-mw-wrapper">
                <?php foreach ($glanceStats as $stat) { ?>
                    <div class="col-lg-2 col-xs-6">
                        <div class="small-box">
                            <div class="inner">
                                <div class="middle">
                                    <h3><?php echo CHtml::link(html_encode((string)$stat['count']), html_encode((string)$stat['url'])); ?></h3>
                                    <p><?php echo html_encode((string)$stat['heading']); ?></p>
                                </div>
                            </div>
                            <div class="icon">
                                <?php echo html_purify((string)$stat['icon']); ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            
        </div>
    </div>

    <?php if (!empty($renderItems) && !empty($timelineItems)) { ?>
    <hr />
    <div class="box box-primary borderless">
        <div class="box-header">
            <div class="pull-left">
                <h3 class="box-title"><?php echo IconHelper::make('fa-clock-o') . t('dashboard', 'Recent activity'); ?></h3>
            </div>
            <div class="pull-right"></div>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="box-body">
            <ul class="timeline">
                <?php foreach ($timelineItems as $item) { ?>
                    <li class="time-label">
                        <span class="flat bg-red"><?php echo html_encode((string)$item['date']); ?></span>
                    </li>
                    <?php foreach ($item['items'] as $itm) { ?>
                        <li>
                            <i class="fa fa-user bg-blue"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fa fa-clock-o"></i> <?php echo html_encode((string)$itm['time']); ?></span>
                                <h3 class="timeline-header"><a href="<?php echo html_encode((string)$itm['customerUrl']); ?>"><?php echo html_encode((string)$itm['customerName']); ?></a></h3>
                                <div class="timeline-body">
                                    <?php echo html_purify((string)$itm['message']); ?>
                                </div>
                            </div>
                        </li>
                    <?php } ?>
                <?php } ?>
                <li>
                    <i class="fa fa-clock-o bg-gray"></i>
                </li>
            </ul>
        </div>
    </div>
    <?php } ?>
    
    <div class="clearfix" id="dashboard-update" data-checkupdateenabled="<?php echo (int)$checkVersionUpdate; ?>" data-checkupdateurl="<?php echo createUrl('dashboard/check_update'); ?>"><!-- --></div>
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
