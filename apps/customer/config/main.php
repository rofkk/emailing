<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * Customer application main configuration file
 *
 * This file should not be altered in any way!
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

return [
    'basePath'          => Yii::getPathOfAlias('customer'),
    'defaultController' => 'dashboard',

    'preload' => [
        'customerSystemInit',
    ],

    // autoloading model and component classes
    'import' => [
        'customer.components.*',
        'customer.components.db.*',
        'customer.components.db.ar.*',
        'customer.components.db.behaviors.*',
        'customer.components.utils.*',
        'customer.components.web.*',
        'customer.components.web.auth.*',
        'customer.models.*',
    ],

    'components' => [

        'urlManager' => [
            'rules' => [
                ['guest/forgot_password', 'pattern' => 'guest/forgot-password'],
                ['guest/reset_password', 'pattern' => 'guest/reset-password/<reset_key:([a-zA-Z0-9]{40})>'],
                ['guest/confirm_registration', 'pattern' => 'guest/confirm-registration/<key:([a-zA-Z0-9]{40})>'],

                ['lists/index', 'pattern' => 'lists/index/*'],

                ['list_subscribers/index', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/subscribers'],
                ['list_subscribers/create', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/subscribers/create'],
                ['list_subscribers/bulk_action', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/subscribers/bulk-action'],
                ['list_subscribers/campaign_for_subscriber', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/subscribers/<subscriber_uid:([a-z0-9]+)>/campaign-for-subscriber'],
                ['list_subscribers/campaigns_export', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/subscribers/<subscriber_uid:([a-z0-9]+)>/campaigns/export'],
                ['list_subscribers/profile_export', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/subscribers/<subscriber_uid:([a-z0-9]+)>/profile/export'],
                ['list_subscribers/<action>', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/subscribers/<subscriber_uid:([a-z0-9]+)>/<action:(update|subscribe|unsubscribe|disable|delete|campaigns|profile)>'],
                ['list_segments/index', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/segments'],
                ['list_segments/create', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/segments/create'],
                ['list_segments/<action>', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/segments/<segment_uid:([a-z0-9]+)>/<action:(update|delete|copy|subscribers)>'],
                ['list_fields/index', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/fields'],
                ['list_page/index', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/page/<type:([a-zA-Z0-9_\-]+)>'],
                ['list_forms/index', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/forms'],

                ['list_open_graph/index', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/open-graph'],

                ['list_import/index', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/import'],
                ['list_import/<action>', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/import/<action>'],
                ['list_export/index', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/export'],
                ['list_export/<action>', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/export/<action>'],
                ['list_segments_export/index', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/segments/<segment_uid:([a-z0-9]+)>/export'],
                ['list_segments_export/<action>', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/segments/<segment_uid:([a-z0-9]+)>/export/<action>'],

                ['lists_tools/<action>', 'pattern' => 'lists/tools/<action>'],

                ['list_tools/copy_subscribers', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/tools/copy-subscribers'],
                ['list_tools/<action>', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/tools/<action>'],

                ['lists/all_subscribers', 'pattern' => 'lists/all-subscribers/*'],
                ['lists/all_subscribers', 'pattern' => 'lists/all-subscribers'],
                ['lists/toggle_archive', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/toggle-archive/*'],
                ['lists/toggle_archive', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/toggle-archive'],
                ['lists/<action>', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/<action:([a-z0-9]+)>'],
                ['templates/gallery_import', 'pattern' => 'templates/gallery-import/<template_uid:([a-z0-9]+)>'],
                ['templates/update_sort_order', 'pattern' => 'templates/update-sort-order'],
                ['templates/<action>', 'pattern' => 'templates/<template_uid:([a-z0-9]+)>/<action:(update|test|delete|preview|copy)>'],

                ['campaign_reports/open_by_subscriber', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/open-by-subscriber/<subscriber_uid:([a-z0-9]+)>'],
                ['campaign_reports/click_by_subscriber_unique', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/click-by-subscriber-unique/<subscriber_uid:([a-z0-9]+)>'],
                ['campaign_reports/click_by_subscriber', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/click-by-subscriber/<subscriber_uid:([a-z0-9]+)>'],
                ['campaign_reports/open_unique', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/open-unique'],
                ['campaign_reports/click_url', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/click-url'],
                ['campaign_reports/forward_friend', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/forward-friend'],
                ['campaign_reports/abuse_reports', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/abuse-reports'],
                ['campaign_reports/<action>', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/<action:(\w+)>/*'],
                ['campaign_reports/<action>', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/<action:(\w+)>'],

                ['campaigns_stats/<action>', 'pattern' => 'campaigns/stats/<action:(\w+)>/*'],
                ['campaigns_stats/<action>', 'pattern' => 'campaigns/stats/<action:(\w+)>'],

                ['campaigns_geo_opens/export_all', 'pattern' => 'campaigns/geo-opens/export/all'],
                ['campaigns_geo_opens/export_unique', 'pattern' => 'campaigns/geo-opens/export/unique'],
                ['campaigns_geo_opens/<action>', 'pattern' => 'campaigns/geo-opens/<action:(\w+)>/*'],
                ['campaigns_geo_opens/<action>', 'pattern' => 'campaigns/geo-opens/<action:(\w+)>'],
                ['campaigns_geo_opens/index', 'pattern' => 'campaigns/geo-opens'],

                ['campaigns_abuse_complaints/<action>', 'pattern' => 'campaigns/abuse-complaints/<action:(\w+)>/*'],
                ['campaigns_abuse_complaints/<action>', 'pattern' => 'campaigns/abuse-complaints/<action:(\w+)>'],
                ['campaigns_abuse_complaints/index', 'pattern' => 'campaigns/abuse-complaints'],

                ['campaigns_abuse_reports/<action>', 'pattern' => 'campaigns/abuse-reports/<action:(\w+)>/*'],
                ['campaigns_abuse_reports/<action>', 'pattern' => 'campaigns/abuse-reports/<action:(\w+)>'],
                ['campaigns_abuse_reports/index', 'pattern' => 'campaigns/abuse-reports'],

                ['campaign_send_groups/quick_create', 'pattern' => 'campaigns/send-groups/quick-create'],
                ['campaign_send_groups/<action>', 'pattern' => 'campaigns/send-groups/<group_uid:([a-z0-9]+)>/<action:(\w+)>'],
                ['campaign_send_groups/<action>', 'pattern' => 'campaigns/send-groups/<action:(\w+)>'],
                ['campaign_send_groups/index', 'pattern' => 'campaigns/send-groups'],

                ['campaign_groups/<action>', 'pattern' => 'campaigns/groups/<group_uid:([a-z0-9]+)>/<action:(\w+)>'],
                ['campaign_groups/<action>', 'pattern' => 'campaigns/groups/<action:(\w+)>'],
                ['campaign_groups/index', 'pattern' => 'campaigns/groups'],

                ['campaign_tags/<action>', 'pattern' => 'campaigns/tags/<tag_uid:([a-z0-9]+)>/<action:(\w+)>'],
                ['campaign_tags/<action>', 'pattern' => 'campaigns/tags/<action:(\w+)>'],
                ['campaign_tags/index', 'pattern' => 'campaigns/tags'],

                ['messages/view', 'pattern' => 'messages/<message_uid:([a-z0-9]+)>/view'],
                ['messages/delete', 'pattern' => 'messages/<message_uid:([a-z0-9]+)>/delete'],
                ['messages/mark_all_as_seen', 'pattern' => 'messages/mark-all-as-seen'],

                ['campaigns/resend_giveups', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/resend-giveups'],
                ['campaigns/pause_unpause', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/pause-unpause'],
                ['campaigns/import_from_share_code', 'pattern' => 'campaigns/import-from-share-code'],

                ['campaigns/<action>', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/<action:(\w+)>'],

                ['api_keys/<action>', 'pattern' => 'api-keys/<action>/*'],
                ['api_keys/<action>', 'pattern' => 'api-keys/<action>'],

                ['survey_fields/index', 'pattern' => 'surveys/<survey_uid:([a-z0-9]+)>/fields'],
                ['survey_responders/index', 'pattern' => 'surveys/<survey_uid:([a-z0-9]+)>/responders'],
                ['survey_responders/create', 'pattern' => 'surveys/<survey_uid:([a-z0-9]+)>/responders/create'],
                ['survey_export/<action>', 'pattern' => 'surveys/<survey_uid:([a-z0-9]+)>/responders/export/<action>'],
                ['survey_responders/<action>', 'pattern' => 'surveys/<survey_uid:([a-z0-9]+)>/responders/<responder_uid:([a-z0-9]+)>/<action:(update|delete)>'],
                ['survey_segments/index', 'pattern' => 'surveys/<survey_uid:([a-z0-9]+)>/segments'],
                ['survey_segments/create', 'pattern' => 'surveys/<survey_uid:([a-z0-9]+)>/segments/create'],
                ['survey_segments/<action>', 'pattern' => 'surveys/<survey_uid:([a-z0-9]+)>/segments/<segment_uid:([a-z0-9]+)>/<action:(update|delete|copy|responders)>'],

                ['survey_segments_export/index', 'pattern' => 'surveys/<survey_uid:([a-z0-9]+)>/segments/<segment_uid:([a-z0-9]+)>/export'],
                ['survey_segments_export/<action>', 'pattern' => 'surveys/<survey_uid:([a-z0-9]+)>/segments/<segment_uid:([a-z0-9]+)>/export/<action>'],

                ['surveys/index', 'pattern' => 'surveys/index/*'],
                ['surveys/<action>', 'pattern' => 'surveys/<survey_uid:([a-z0-9]+)>/<action:([a-z0-9]+)>'],

                ['dashboard/delete_log', 'pattern' => 'dashboard/delete-log/id/<id:(\d+)>'],
                ['dashboard/delete_logs', 'pattern' => 'dashboard/delete-logs'],
                ['dashboard/export_recent_activity', 'pattern' => 'dashboard/export-recent-activity'],

                ['campaign_reports_export/basic', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports-export/basic'],
                ['campaign_reports_export/click_url', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports-export/click-url'],
                ['campaign_reports_export/click_by_subscriber', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports-export/click-by-subscriber/<subscriber_uid:([a-z0-9]+)>'],
                ['campaign_reports_export/click_by_subscriber_unique', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports-export/click-by-subscriber-unique/<subscriber_uid:([a-z0-9]+)>'],
                ['campaign_reports_export/<action>', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports-export/<action:(\w+)>'],

                ['delivery_servers/<action>', 'pattern' => 'delivery-servers/<action:(\w+)>/*'],
                ['delivery_servers/<action>', 'pattern' => 'delivery-servers/<action:(\w+)>'],
                ['delivery_servers', 'pattern' => 'delivery-servers'],

                ['bounce_servers/<action>', 'pattern' => 'bounce-servers/<action:(\w+)>/*'],
                ['bounce_servers/<action>', 'pattern' => 'bounce-servers/<action:(\w+)>'],
                ['bounce_servers', 'pattern' => 'bounce-servers'],

                ['feedback_loop_servers/<action>', 'pattern' => 'feedback-loop-servers/<action:(\w+)>/*'],
                ['feedback_loop_servers/<action>', 'pattern' => 'feedback-loop-servers/<action:(\w+)>'],
                ['feedback_loop_servers', 'pattern' => 'feedback-loop-servers'],

                ['email_box_monitors/<action>', 'pattern' => 'email-box-monitors/<action:(\w+)>/*'],
                ['email_box_monitors/<action>', 'pattern' => 'email-box-monitors/<action:(\w+)>'],
                ['email_box_monitors', 'pattern' => 'email-box-monitors'],

                ['price_plans/orders_export', 'pattern' => 'price-plans/orders/export'],
                ['price_plans/order_detail', 'pattern' => 'price-plans/orders/<order_uid:([a-z0-9]+)>'],
                ['price_plans/order_pdf', 'pattern' => 'price-plans/orders/<order_uid:([a-z0-9]+)>/pdf'],
                ['price_plans/email_invoice', 'pattern' => 'price-plans/orders/<order_uid:([a-z0-9]+)>/email-invoice'],
                ['price_plans/<action>', 'pattern' => 'price-plans/<action:(\w+)>/*'],
                ['price_plans/<action>', 'pattern' => 'price-plans/<action>'],

                ['tracking_domains/<action>', 'pattern' => 'tracking-domains/<action:(\w+)>/*'],
                ['tracking_domains/<action>', 'pattern' => 'tracking-domains/<action:(\w+)>'],
                ['tracking_domains', 'pattern' => 'tracking-domains'],

                ['sending_domains/<action>', 'pattern' => 'sending-domains/<action:(\w+)>/*'],
                ['sending_domains/<action>', 'pattern' => 'sending-domains/<action:(\w+)>'],
                ['sending_domains', 'pattern' => 'sending-domains'],

                ['email_blacklist/delete_all', 'pattern' => 'email-blacklist/delete-all'],
                ['email_blacklist/<action>', 'pattern' => 'email-blacklist/<action:(\w+)>/<email_uid:([a-z0-9]+)>'],
                ['email_blacklist/<action>', 'pattern' => 'email-blacklist/<action:(\w+)>/*'],
                ['email_blacklist/<action>', 'pattern' => 'email-blacklist/<action:(\w+)>'],

                ['templates_categories/<action>', 'pattern' => 'templates/categories/<action:(\w+)>/*'],
                ['templates_categories/<action>', 'pattern' => 'templates/categories/<action:(\w+)>'],

                ['suppression_list_emails/<action>', 'pattern' => 'suppression-lists/<list_uid:([a-z0-9]+)>/emails/<email_id:([0-9]+)>/<action:(\w+)>'],
                ['suppression_list_emails/<action>', 'pattern' => 'suppression-lists/<list_uid:([a-z0-9]+)>/emails/<action:(\w+)>/*'],
                ['suppression_list_emails/<action>', 'pattern' => 'suppression-lists/<list_uid:([a-z0-9]+)>/emails/<action:(\w+)>'],

                ['suppression_lists/<action>', 'pattern' => 'suppression-lists/<list_uid:([a-z0-9]+)>/<action:(\w+)>'],
                ['suppression_lists/<action>', 'pattern' => 'suppression-lists/<action:(\w+)>'],

                ['download_queued/index', 'pattern' => 'download-queued/<file:([a-z0-9]{40}\.(csv|txt|zip))>'],
            ],
        ],

        'assetManager' => [
            'basePath'  => Yii::getPathOfAlias('root.customer.assets.cache'),
            'baseUrl'   => AppInitHelper::getBaseUrl('assets/cache'),
        ],

        'themeManager' => [
            'class'     => 'common.components.managers.ThemeManager',
            'basePath'  => Yii::getPathOfAlias('root.customer.themes'),
            'baseUrl'   => AppInitHelper::getBaseUrl('themes'),
        ],

        'errorHandler' => [
            'errorAction'   => 'guest/error',
        ],

        'session' => [
            'class'                  => 'system.web.CDbHttpSession',
            'connectionID'           => 'db',
            'sessionName'            => 'mwsid',
            'timeout'                => 7200,
            'sessionTableName'       => '{{session}}',
            'autoCreateSessionTable' => false,
            'cookieParams'           => [
                'httponly' => true,
            ],
        ],

        'user' => [
            'class'             => 'backend.components.web.auth.WebUser',
            'allowAutoLogin'    => true,
            'authTimeout'       => 7200,
            'identityCookie'    => [
                'httpOnly'      => true,
            ],
        ],

        'customer' => [
            'class'             => 'customer.components.web.auth.WebCustomer',
            'allowAutoLogin'    => true,
            'loginUrl'          => ['guest/index'],
            'returnUrl'         => ['dashboard/index'],
            'authTimeout'       => 7200,
            'identityCookie'    => [
                'httpOnly'      => true,
            ],
        ],

        'customerSystemInit' => [
            'class' => 'customer.components.init.CustomerSystemInit',
        ],
    ],

    'modules' => [],

    // application-level parameters that can be accessed
    // using app_param('paramName')
    'params'=>[
        // list of controllers where the user doesn't have to be logged in.
        'unprotectedControllers' => ['guest'],
    ],
];
