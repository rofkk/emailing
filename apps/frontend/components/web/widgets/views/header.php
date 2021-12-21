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
 * @since 1.9.15
 */

?>

<header class="navbar navbar-default">
    <div class="col-lg-10 col-lg-push-1 col-md-10 col-md-push-1 col-sm-12 col-xs-12">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only"><?php echo t('app', 'Toggle navigation'); ?></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="<?php echo app()->getHomeUrl(); ?>" title="<?php echo container()->get(OptionCommon::class)->getSiteName(); ?>">
                <span><span><?php echo container()->get(OptionCommon::class)->getSiteName(); ?></span></span>
            </a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav navbar-right">
				<?php if (container()->get(OptionCustomerRegistration::class)->getIsEnabled()) { ?>
                    <li class="hidden-xs">
                        <a href="<?php echo apps()->getAppUrl('customer', 'guest/register'); ?>" class="btn btn-default btn-flat" title="<?php echo t('app', 'Sign up'); ?>">
							<?php echo t('app', 'Sign up'); ?>
                        </a>
                    </li>
                    <li class="hidden-lg hidden-md hidden-sm">
                        <a href="<?php echo apps()->getAppUrl('customer', 'guest/register'); ?>" class="" title="<?php echo t('app', 'Sign up'); ?>">
							<?php echo t('app', 'Sign up'); ?>
                        </a>
                    </li>
				<?php } ?>
                <li class="">
                    <a href="<?php echo apps()->getAppUrl('customer', 'guest/index'); ?>" title="<?php echo t('app', 'Login'); ?>">
						<?php echo t('app', 'Login'); ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>