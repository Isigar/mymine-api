<?php


namespace App\Presenters;


use App\Core\Api\Error;
use App\Core\Api\Response;
use App\Core\Firebase\Db\Database;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Nette\Application\UI\Presenter;
use Nette\Neon\Exception;
use Nette\Utils\Random;
use Tracy\Debugger;

final class VipPresenter extends Presenter
{
    public function actionDefault(){
        $resp = new Response();
        $resp->setTitle('VIP Gate')
            ->setMsg('version: 0.1');
        $this->sendJson($resp->prepare());
    }

    public function actionGenerate($uid,$resolve){
        try{
            if(!$uid || !$resolve){
                $res = new Response();
                $res->setTitle('VIP Gate')->addError((new Error())->setTitle('Nastala chyba')->setMsg('Chybějící parametry!'));
                $this->sendJson($res->prepare());
            }
            $code = Random::generate(6);
            $db = new Database();
            $res = $db->push('vip',[
                'user_uid' => $uid,
                'resolve' => $resolve,
                'code' => $code,
                'state' => 'WAITING'
            ]);

            $res = new Response();
            $res->setTitle('VIP Gate')->setData(['code' => $code])->setMsg('Váš kód byl vygenerován!');
            $this->sendJson($res->prepare());
        }catch (Exception $e){
            Debugger::log($e->getMessage(),Debugger::CRITICAL);
            $res = new Response();
            $res->setTitle('VIP Gate')->addError((new Error())->setTitle('Nastala chyba')->setMsg($e->getMessage()));
            $this->sendJson($res->prepare());
        }
    }

    /**
     * @param string $code
     * @throws \Nette\Application\AbortException
     */
    public function actionGetCode(string $code){
        try{
            $db = new Database();
            $ref = $db->getDb()->getReference('vip')->orderByChild('code')->equalTo($code);
            $tryFind = $ref->getValue();

            if(!empty($tryFind)){
                $tryFind = reset($tryFind);

                unset($tryFind['user_uid']);
                $res = new Response();
                $res->setTitle('VIP Gate')->setData($tryFind);
                $this->sendJson($res->prepare());
            }else{
                $res = new Response();
                $res->setTitle('VIP Gate')->addError((new Error())->setTitle('Nastala chyba')->setMsg('Kód nebyl nalezen!'));
                $this->sendJson($res->prepare());
            }
        }catch (Exception $e){
            Debugger::log($e->getMessage(),Debugger::CRITICAL);
            $res = new Response();
            $res->setTitle('VIP Gate')->addError((new Error())->setTitle('Nastala chyba')->setMsg($e->getMessage()));
            $this->sendJson($res->prepare());
        }
    }

    /**
     * @param string $code
     * @throws \Kreait\Firebase\Exception\ApiException
     * @throws \Nette\Application\AbortException
     */
    public function actionUseCode(string $code){
        try{
            $db = new Database();
            $ref = $db->getDb()->getReference('vip')->orderByChild('code')->equalTo($code);
            $val = $ref->getValue();

            if(!empty($val)){
                $key = null;
                $tryFind = null;
                foreach ($val as $k => $item){
                    $key = $k;
                    $tryFind = $item;
                    break;
                }

                if($tryFind['state'] === 'WAITING'){
                    $ref = $db->update('vip/'.$key,[
                        'state' => 'USED'
                    ]);

                    $res = new Response();
                    $res->setTitle('VIP Gate')->setMsg('Kód byl právě použit')->setData(true);
                    $this->sendJson($res->prepare());
                }else{
                    $res = new Response();
                    $res->setTitle('VIP Gate')->setMsg('Tento kód je neplatný')->setData(false);
                    $this->sendJson($res->prepare());
                }
            }else{
                $res = new Response();
                $res->setTitle('VIP Gate')->addError((new Error())->setTitle('Nastala chyba')->setMsg('Kód nebyl nalezen!'));
                $this->sendJson($res->prepare());
            }
        }catch (Exception $e){
            Debugger::log($e->getMessage(),Debugger::CRITICAL);
            $res = new Response();
            $res->setTitle('VIP Gate')->addError((new Error())->setTitle('Nastala chyba')->setMsg($e->getMessage()));
            $this->sendJson($res->prepare());
        }
    }
}