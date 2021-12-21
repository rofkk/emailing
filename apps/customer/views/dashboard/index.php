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

/** @var array $timelineItems */
$timelineItems = (array)$controller->getData('timelineItems');

/** @var array $glanceStats */
$glanceStats = (array)$controller->getData('glanceStats');

/** @var bool $renderItems */
$renderItems = (bool)$controller->getData('renderItems');

/** @var bool $showRecentCampaigns */
$showRecentCampaigns = (bool)$controller->getData('showRecentCampaigns');

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
if (!empty($viewCollection) && $viewCollection->itemAt('renderContent')) { ?>

    <div class="box box-primary borderless">
        <div class="box-header">
            <div class="pull-left">
                <h3 class="box-title"><?php echo IconHelper::make('info') . $pageHeading; ?></h3>
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
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box">
                            <div class="inner">
                                <div class="middle">
                                    <h3><?php echo CHtml::link($stat['count'], $stat['url']); ?></h3>
                                    <p><?php echo $stat['heading']; ?></p>
                                </div>
                            </div>
                            <div class="icon">
                                <?php echo $stat['icon']; ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            
        </div>
    </div>

    <?php if (!empty($showRecentCampaigns) && !empty($renderItems)) { ?>
    <div id="campaigns-overview-wrapper" data-url="<?php echo createUrl('dashboard/campaigns'); ?>">
        <!-- ajax content -->
    </div>
    <?php } ?>

    <?php if (!empty($renderItems) && !empty($timelineItems)) { ?>
    <hr />
    <div class="box box-primary borderless">
        <div class="box-header">
            <div class="pull-left">
                <h3 class="box-title"><?php echo IconHelper::make('fa-clock-o') . t('dashboard', 'Recent activity'); ?></h3>
            </div>
            <div class="pull-right">
                <?php BoxHeaderContent::make(BoxHeaderContent::RIGHT)
                    ->addIf(CHtml::link(IconHelper::make('export') . t('app', 'Export'), ['dashboard/export_recent_activity'], ['target' => '_blank', 'class' => 'btn btn-primary btn-flat', 'title' => t('app', 'Export')]), count($timelineItems))
                    ->render();
                ?>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="box-body">
            <ul class="timeline">
                <?php foreach ($timelineItems as $item) { ?>
                    <li class="time-label">
                        <span class="flat bg-red"><?php echo $item['date']; ?></span>
                    </li>
                    <?php foreach ($item['items'] as $itm) { ?>
                        <li>
                            <i class="fa fa-user bg-blue"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fa fa-clock-o"></i> <?php echo $itm['time']; ?></span>
                                <h3 class="timeline-header"><a href="<?php echo $itm['customerUrl']; ?>"><?php echo $itm['customerName']; ?></a></h3>
                                <div class="timeline-body">
                                    <?php echo $itm['message']; ?>
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
