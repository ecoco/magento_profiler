<?php

/**
 * Class Ecocode_Profiler_Model_Collector_ModelDataCollector
 */
class Ecocode_Profiler_Model_Collector_ModelDataCollector
    extends Ecocode_Profiler_Model_Collector_AbstractDataCollector
{
    CONST LOAD_CALL_THRESHOLD = 20;

    protected $callLog = [];

    /**
     * @codeCoverageIgnore
     * @return int
     */
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

        $traceHashList = array_filter($traceHashList, function ($count) {
            return $count > 1;
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

        $traceHashList = array_filter($traceHashList, function ($trace) {
            return $trace['count'] > 1;
        });

        usort($traceHashList, function ($trace1, $trace2) {
            return $trace2['count'] - $trace1['count'];
        });

        return array_values($traceHashList);
    }


    public function getTotalTime()
    {

        return $this->getData('total_time', 0);
    }

    public function getCallLog()
    {

        return $this->getData('calls', []);
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
        $classGroup = $this->getHelper()->resolveClassGroup($className);
        $trace      = $this->cleanBacktrace($this->getBacktrace(DEBUG_BACKTRACE_IGNORE_ARGS));

        $data       = [
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

    protected function cleanBacktrace(array $backtrace)
    {
        $item = reset($backtrace);
        while ($item && $this->shouldRemoveBacktrace($item)) {
            array_shift($backtrace);
            $item = reset($backtrace);
        }

        $backtrace = array_map(function ($item) {
            unset($item['object'], $item['args'], $item['type']);
            return $item;
        }, $backtrace);

        return $backtrace;
    }


    protected function shouldRemoveBacktrace($data)
    {
        if (!is_array($data)) {
            return false;
        }

        if (!isset($data['class'], $data['function'])) {
            return true;
        }

        if (!in_array($data['class'], ['Mage_Core_Model_Resource_Db_Abstract', 'Mage_Eav_Model_Entity_Abstract'])) {
            return true;
        }

        if (!in_array($data['function'], ['load', 'delete', 'save'])) {
            return true;
        }

        return false;
    }

    /**
     * @codeCoverageIgnore
     * @return Ecocode_Profiler_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('ecocode_profiler');
    }


    /**
     * @codeCoverageIgnore
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'model';
    }

}
