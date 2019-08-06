<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Core\Api\Response;
use Nette;

final class HomepagePresenter extends Nette\Application\UI\Presenter
{
    public function actionDefault(){
        $response = new Response();
        $response->setTitle('mymine api');
        $response->setMsg('version: 0.1');
        $this->sendJson($response->prepare());
    }
}
