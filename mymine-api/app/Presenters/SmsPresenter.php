<?php


namespace App\Presenters;


use App\Core\Api\Error;
use App\Core\Api\Response;
use App\Core\Firebase\Db\Database;
use Kreait\Firebase\Exception\ApiException;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Nette\Application\AbortException;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Presenter;
use Nette\Neon\Exception;
use Tracy\Debugger;

final class SmsPresenter extends Presenter
{
    public function startup(){
        parent::startup();
        $this->getHttpResponse()->setHeader('Access-Control-Allow-Origin', '*');
        $this->getHttpResponse()->setHeader('Access-Control-Allow-Method:', 'GET');
        $this->getHttpResponse()->setHeader('Access-Control-Allow-Headers', '*');
    }

    public function actionDefault($timestamp, $phone, $sms, $shortcode, $country, $operator, $att)
    {
        $failed = false;
        if (!$timestamp || !$phone || !$sms || !$shortcode || !$country || !$operator || !$att) {
            $this->error("Missing fileds!");
        }

        $db = new Database();
        try {
            $tryFind = $db->getFirebase()->getAuth()->getUserByEmail(explode(' ', $sms)[1]);
        } catch (UserNotFound $e) {
            //Send response
            $message = "Uživatel nebyl nalezen, jste zaregistrováni pomocí tohoto emailu?;FREE".$shortcode;
            $this->getHttpResponse()->setContentType('text/plain', 'UTF-8');
            $this->getHttpResponse()->setHeader('Content-length',strlen($message))
            $textResponse = new TextResponse($message);
            $this->sendResponse($textResponse);
        }
        if ($tryFind) {
            //Save SMS to database
            try {
                $val = 0;
                switch ($shortcode) {
                    case '90733079':
                        $val = 50;
                        break;
                    case '90733149':
                        $val = 100;
                        break;
                    case '90333':
                        break;
                    case '90944':
                        break;
                    case '90210':
                        break;
                    case '90733':
                        break;
                }

                $ref = $db->push('sms', [
                    'timestamp' => $timestamp,
                    'phone' => $phone,
                    'sms' => explode(' ', $sms),
                    'shortcode' => $shortcode,
                    'value' => $val,
                    'country' => $country,
                    'ext_id' => $att,
                    'operator' => $operator,
                    'attr' => $att,
                    'user_id' => $tryFind->uid,
                    'state' => 'WAITING'
                ]);
            } catch (ApiException $e) {
                Debugger::log($e->getMessage(), Debugger::CRITICAL);
            }

            //Send response
            $message = "Děkujeme za koupení VIP, výhody Vám budou brzy přičteny!;".$shortcode;
            $this->getHttpResponse()->setContentType('text/plain', 'UTF-8');
            $this->getHttpResponse()->setHeader('Content-length',strlen($message));
            $textResponse = new TextResponse($message);
            $this->sendResponse($textResponse);
        }
    }

    public function actionDelivery($timestamp, $request, $status, $message, $att)
    {
        $failed = false;
        $db = new Database();
        try {
            $reference = $db->getDb()->getReference('sms')->orderByChild('ext_id')->equalTo($request);
            $tryFind = $reference->getValue();
            if ($tryFind) {
                $key = null;
                foreach ($tryFind as $k => $item) {
                    $key = $k;
                    $tryFind = $item;
                    break;
                }
                if ($status === 'DELIVERED' && $tryFind['state'] === 'WAITING') {
                    //Update
                    $db->update('sms/' . $key, [
                        'state' => 'DELIVERED'
                    ]);

                    //Update ext data
                    $userRef = $db->getDb()->getReference('users/' . $tryFind['user_id']);
                    $userVal = $userRef->getValue();
                    $ordHistory = $userVal['orderHistory'];
                    $ordHistory[] = [
                        'message' => 'SMS platba',
                        'points' => $tryFind['value'],
                        'time' => time(),
                        'type' => 'PLUS'
                    ];
                    $userRef->update([
                        'points' => $userVal['points'] + $tryFind['value'],
                        'orderHistory' => $ordHistory
                    ]);

                    $response = new Response();
                    $response->setTitle('SMS Gate')->setMsg('SMS byla úspěšně uložena.');
                    $this->sendJson($response->prepare());
                } else {
                    $response = new Response();
                    $response->setTitle('SMS Gate')
                        ->addError((new Error())->setTitle('SMS chyba!')->setMsg('SMS, která přišla nemá správný stav'));
                    $this->sendJson($response->prepare());
                }
            } else {
                $response = new Response();
                $response->setTitle('SMS Gate')
                    ->addError((new Error())->setTitle('SMS chyba!')->setMsg('SMS, která přišla nebyla nalezena v db'));
                $this->sendJson($response->prepare());
            }
        } catch (Exception $e) {
            Debugger::log($e->getMessage(), Debugger::CRITICAL);
            if (!($e instanceof AbortException)) {
                $failed = $e;
            }
        } finally {
            if ($failed instanceof Exception) {
                $response = new Response();
                $response->setTitle('SMS Gate')
                    ->addError((new Error())->setTitle('SMS chyba!')->setMsg($failed->getMessage()));
                $this->sendJson($response->prepare());
            }
        }
    }
}