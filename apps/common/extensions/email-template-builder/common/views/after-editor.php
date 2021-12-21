<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/** @var Controller $controller */
$controller = controller();

/** @var string $builderId */
$builderId = (string)$controller->getData('builderId');

/** @var string $modelName */
$modelName = (string)$controller->getData('modelName');

/** @var array $json */
$json = (array)$controller->getData('json');

/** @var array $options */
$options = (array)$controller->getData('options');

?>
<div id="builder_<?php echo $builderId; ?>"></div>
<textarea name="<?php echo $modelName; ?>[content_json]" id="<?php echo $builderId; ?>_json" style="display: none"></textarea>
<script>
    jQuery(document).ready(function($){
        (function(){
            var params = {
                builderId : '<?php echo $builderId; ?>',
                options   :  <?php echo json_encode($options); ?>,
                json      :  <?php echo json_encode($json); ?>
            };
            var builderHandler = new TemplateBuilderHandler(params);

            $(document).on('click', '#btn_' + params.builderId, function(){
                builderHandler.toggle();
                return false;
            });

            if (builderHandler.shouldOpen()) {
                setTimeout(function(){
                    builderHandler.open();
                }, 1000);
            }

            $('#btn_' + params.builderId).closest('form').on('submit', function(){
                if (builderHandler.isEnabled()) {
                    $('#<?php echo $builderId; ?>_json').val(builderHandler.getInstance().getJson());
                    CKEDITOR.instances['<?php echo $builderId; ?>'].setData(builderHandler.getInstance().getHtml());
                }
            });

            $('#builder_<?php echo $builderId; ?>').on('templateBuilderHandler.afterClose', function(e, data){
                $('#<?php echo $builderId; ?>_json').val(data.json);
            });
        })()
    });
</script>
