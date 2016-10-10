<?php

class Ecocode_Profiler_CacheController
    extends Ecocode_Profiler_Controller_AbstractController
{
    public function clearAction()
    {
        $app   = $this->getApp();
        $types = $this->getRequest()->getParam('types', '');
        $types = explode(',', $types);

        foreach ($types as $type) {
            $app->getCacheInstance()->cleanType($type);
        }

        $this->getResponse()
            ->setHeader('content-type', 'application/json')
            ->setBody(json_encode(['ok']));
    }

    public function clearAllAction()
    {
        $this->getApp()->getCacheInstance()->flush();

        $this->getResponse()
            ->setHeader('content-type', 'application/json')
            ->setBody(json_encode(['ok']));
    }

    public function enableAction()
    {
        $types = $this->getRequest()->getParam('types', '');
        $types = explode(',', $types);
        $this->setCacheStatus($types, 1);

        $this->getResponse()
            ->setHeader('content-type', 'application/json')
            ->setBody(json_encode(['ok']));
    }

    public function disableAction()
    {
        $types = $this->getRequest()->getParam('types', '');
        $types = explode(',', $types);
        $this->setCacheStatus($types, 0);

        $this->getResponse()
            ->setHeader('content-type', 'application/json')
            ->setBody(json_encode(['ok']));
    }

    protected function setCacheStatus(array $types, $status)
    {
        $app      = $this->getApp();
        $allTypes = $app->getCacheInstance()->getTypes();
        $allTypes = array_map(function (Varien_Object $type) {
            return $type->getData('status');
        }, $allTypes);

        $updatedTypes = 0;
        foreach ($types as $code) {
            if (isset($allTypes[$code])) {
                $allTypes[$code] = $status;
                $updatedTypes++;
            }
        }
        if ($updatedTypes > 0) {
            $app->saveUseCache($allTypes);
        }
    }
}
