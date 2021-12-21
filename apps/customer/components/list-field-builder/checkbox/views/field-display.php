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
 * @since 1.5.5
 */

?>

<div class="form-group field-<?php echo $field->type->identifier; ?> wrap-<?php echo strtolower((string)$field->tag); ?>" style="display: <?php echo !empty($visible) ? 'block' : 'none'; ?>">
    <div>
        <?php echo CHtml::checkBox($field->tag, strlen(trim((string)$model->value)), $model->getHtmlOptions('value', [
            'value'        => $field->checkValue,
            'uncheckValue' => '',
        ])); ?>
        <?php echo CHtml::activeLabelEx($model, 'value', ['for' => $field->tag]); ?>
    </div>
    <?php echo CHtml::error($model, 'value'); ?>
    <?php if (!empty($field->description)) { ?>
        <div class="field-description">
            <?php echo $field->description; ?>
        </div>
    <?php } ?>
</div>