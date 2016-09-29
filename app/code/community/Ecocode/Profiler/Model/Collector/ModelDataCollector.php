<?php

class Ecocode_Profiler_Model_Collector_ModelDataCollector
    extends Ecocode_Profiler_Model_Collector_AbstractDataCollector
{
    CONST LOAD_CALL_THRESHOLD = 20;

    protected $callLog = [];

    public function getLoadCallThreshold()
    {
        return static::LOAD_CALL_THRESHOLD;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Mage_Core_Controller_Request_Http $request, Mage_Core_Controller_Response_Http $response, \Exception $exception = null)
    {
        $stats = [
            'load'      => 0,
            'loop_load' => 0,
            'save'      => 0,
            'delete'    => 0,
        ];

        $totalTime     = 0;
        $traceHashList = [];

        foreach ($this->callLog as &$item) {
            switch ($item['action']) {
                case 'load':
                    $stats[$item['action']]++;

                    //try to detect load calls in loops
                    $traceHash = $item['trace_hash'];
                    if (!isset($traceHashList[$traceHash])) {
                        $traceHashList[$traceHash] = 0;
                    }
                    $traceHashList[$traceHash]++;
                    break;
                case 'save':
                case 'delete':
                    $stats[$item['action']]++;
                    break;
            }
            if (!isset($item['time'])) {
                $item['time'] = null;
            }
            $totalTime += $item['time'];
        }

        $traceHashList = array_filter($traceHashList, function ($v) {
            return $v > 1;
        });

        $stats['loop_load'] = array_sum($traceHashList);

        $this->data = [
            'total_time' => $totalTime,
            'metrics'    => $stats,
            'calls'      => $this->callLog
        ];

    }

    public function getLoadLoopCalls()
    {
        $traceHashList = [];

        foreach ($this->getCallLog() as $item) {
            if ($item['action'] !== 'load') {
                continue;
            }
            //try to detect load calls in loops
            $traceHash = $item['trace_hash'];
            if (!isset($traceHashList[$traceHash])) {
                $item['count']             = 0;
                $item['total_time']        = 0;
                $traceHashList[$traceHash] = $item;
            }
            $traceHashList[$traceHash]['count']++;
            $traceHashList[$traceHash]['total_time'] += $item['time'];
        }

        $traceHashList = array_filter($traceHashList, function ($v) {
            return $v['count'] > 1;
        });

        return $traceHashList;
    }


    public function getTotalTime()
    {

        return $this->data['total_time'];
    }

    public function getCallLog()
    {

        return $this->data['calls'];
    }


    public function getMetric($key = null)
    {
        if ($key === null) {
            return $this->data['metrics'];
        }

        return $this->data['metrics'][$key];
    }


    public function trackModelLoad(Varien_Event_Observer $observer)
    {
        $this->trackEvent('load', $observer->getEvent());
    }

    public function trackModelSave(Varien_Event_Observer $observer)
    {
        $this->trackEvent('save', $observer->getEvent());
    }

    public function trackModelDelete(Varien_Event_Observer $observer)
    {
        $this->trackEvent('delete', $observer->getEvent());
    }

    protected function trackEvent($action, Varien_Event $event)
    {
        $object = $event->getData('object');

        $this->track($action, $object, $event->getData('time'));
    }

    protected function track($action, Varien_Object $object, $time = null)
    {
        $className  = get_class($object);
        $classGroup = Mage::helper('ecocode_profiler')->getClassGroup($className);
        $trace      = $this->getBacktrace();

        $data = [
            'action'      => $action,
            'class'       => $className,
            'class_group' => $classGroup,
            'trace'       => $trace,
            'trace_hash'  => md5(serialize($trace))
        ];

        if ($time) {
            $data['time'] = $time;
        }

        $this->callLog[] = $data;
    }

    protected function getBacktrace()
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        //remove log
        while ($this->_shouldRemoveTraceItem(reset($backtrace))) {
            array_shift($backtrace);
        }
        $backtrace = array_map(function ($item) {
            unset($item['object'], $item['args'], $item['type']);
            return $item;
        }, $backtrace);

        return $backtrace;
    }

    protected function _shouldRemoveTraceItem($data)
    {
        if (!is_array($data)) {
            return false;
        }

        if (!isset($data['class'], $data['function'])) {
            return true;
        }

        if ($data['class'] !== 'Mage_Core_Model_Resource_Db_Abstract' && $data['class'] !== 'Mage_Eav_Model_Entity_Abstract') {
            return true;
        }

        if ($data['function'] !== 'load' && $data['function'] !== 'save' && $data['function'] !== 'delete') {
            return true;
        }

        return false;
    }


    public function getName()
    {
        return 'model';
    }

}