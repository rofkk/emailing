<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * Controller file for service process.
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class PaymentGatewayPaypalExtCustomerIpnController extends ExtensionController
{
    /**
     * Process the IPN
     *
     * @return void
     */
    public function actionIndex()
    {
        if (!request()->getIsPostRequest()) {
            $this->redirect(['price_plans/index']);
        }

        $postData = app_param('POST');
        if (!$postData->itemAt('custom')) {
            app()->end();
        }

        /** @var PricePlanOrderTransaction $transaction */
        $transaction = PricePlanOrderTransaction::model()->findByAttributes([
            'payment_gateway_transaction_id' => (string)$postData->itemAt('custom', ''),
            'status'                         => PricePlanOrderTransaction::STATUS_PENDING_RETRY,
        ]);

        if (empty($transaction)) {
            app()->end();
        }

        $newTransaction = clone $transaction;
        $newTransaction->setIsNewRecord(true);
        $newTransaction->transaction_id                 = null;
        $newTransaction->transaction_uid                = '';
        $newTransaction->date_added                     = MW_DATETIME_NOW;
        $newTransaction->status                         = PricePlanOrderTransaction::STATUS_FAILED;
        $newTransaction->payment_gateway_response       = print_r($postData->toArray(), true);
        $newTransaction->payment_gateway_transaction_id = $postData->itemAt('txn_id');

        /** @var PaymentGatewayPaypalExtCommon $model */
        $model = container()->get(PaymentGatewayPaypalExtCommon::class);

        $postData->add('cmd', '_notify-validate');

        /** @var string $responseBody */
        $responseBody = '';

        /** @var Psr\Http\Message\ResponseInterface $response */
        $response = null;

        try {
            $response = (new GuzzleHttp\Client())->post($model->getModeUrl(), [
                'form_params' => $postData->toArray(),
            ]);
            $responseBody = (string)$response->getBody();
        } catch (Exception $e) {
            app()->end();
        }

        // 1.7.7
        $newTransaction->payment_gateway_response .= $responseBody;

        if ((int)$response->getStatusCode() !== 200) {
            $newTransaction->save(false);
            app()->end();
        }

        $paymentStatus  = strtolower(trim((string)$postData->itemAt('payment_status')));
        $paymentPending = strpos($paymentStatus, 'pending') === 0;
        $paymentFailed  = strpos($paymentStatus, 'failed') === 0;
        $paymentSuccess = strpos($paymentStatus, 'completed') === 0;

        $verified  = strpos(strtolower(trim((string)$responseBody)), 'verified') === 0;
        $order     = $transaction->order;

        if ($order->status == PricePlanOrder::STATUS_COMPLETE) {
            $newTransaction->save(false);
            app()->end();
        }

        if (!$verified || $paymentFailed) {
            $order->status = PricePlanOrder::STATUS_FAILED;
            $order->save(false);

            $transaction->status = PricePlanOrderTransaction::STATUS_FAILED;
            $transaction->save(false);

            $newTransaction->save(false);

            app()->end();
        }

        if ($paymentPending) {
            $newTransaction->status = PricePlanOrderTransaction::STATUS_PENDING_RETRY;
            $newTransaction->save(false);
            app()->end();
        }

        $order->status = PricePlanOrder::STATUS_COMPLETE;
        $order->save(false);

        $transaction->status = PricePlanOrderTransaction::STATUS_SUCCESS;
        $transaction->save(false);

        $newTransaction->status = PricePlanOrderTransaction::STATUS_SUCCESS;
        $newTransaction->save(false);

        app()->end();
    }
}
