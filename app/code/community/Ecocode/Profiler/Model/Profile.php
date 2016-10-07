<?php

/**
 * Profile.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Ecocode_Profiler_Model_Profile
{
    protected $token;

    /**
     * @var \Ecocode_Profiler_Model_Collector_DataCollectorInterface[]
     */
    protected $collectors = array();

    protected $ip;
    protected $method;
    protected $url;
    protected $time;
    protected $statusCode;
    protected $collectTime;

    /**
     * @var Ecocode_Profiler_Model_Profile
     */
    protected $parent;

    /**
     * @var Ecocode_Profiler_Model_Profile[]
     */
    protected $children = array();

    /**
     * Constructor.
     *
     * @param string $token The token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Gets the token.
     *
     * @return string The token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Sets the parent token.
     *
     * @param Ecocode_Profiler_Model_Profile $parent The parent Profile
     */
    public function setParent(Ecocode_Profiler_Model_Profile $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Returns the parent profile.
     *
     * @return Ecocode_Profiler_Model_Profile The parent profile
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Returns the parent token.
     *
     * @return null|string The parent token
     */
    public function getParentToken()
    {
        return $this->parent ? $this->parent->getToken() : null;
    }

    /**
     * Returns the IP.
     *
     * @return string The IP
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Sets the IP.
     *
     * @param string $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * Returns the request method.
     *
     * @return string The request method
     */
    public function getMethod()
    {
        return $this->method;
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Returns the URL.
     *
     * @return string The URL
     */
    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Returns the time.
     *
     * @return string The time
     */
    public function getTime()
    {
        if (null === $this->time) {
            return 0;
        }

        return $this->time;
    }

    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return int
     */
    public function getCollectTime()
    {
        return $this->collectTime;
    }


    /**
     * @param $time
     * @return int
     */
    public function setCollectTime($time)
    {
        return $this->collectTime = $time;
    }


    /**
     * Finds children profilers.
     *
     * @return Ecocode_Profiler_Model_Profile[] An array of Profile
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Sets children profiler.
     *
     * @param Ecocode_Profiler_Model_Profile[] $children An array of Profile
     */
    public function setChildren(array $children)
    {
        $this->children = array();
        foreach ($children as $child) {
            $this->addChild($child);
        }
    }

    /**
     * Adds the child token.
     *
     * @param Ecocode_Profiler_Model_Profile $child The child Profile
     */
    public function addChild(Ecocode_Profiler_Model_Profile $child)
    {
        $this->children[] = $child;
        $child->setParent($this);
    }

    /**
     * Gets a Collector by name.
     *
     * @param string $name A collector name
     *
     * @return Ecocode_Profiler_Model_Collector_DataCollectorInterface A DataCollectorInterface instance
     *
     * @throws \InvalidArgumentException if the collector does not exist
     */
    public function getCollector($name)
    {
        if (!isset($this->collectors[$name])) {
            throw new \InvalidArgumentException(sprintf('Collector "%s" does not exist.', $name));
        }

        return $this->collectors[$name];
    }

    /**
     * Gets the Collectors associated with this profile.
     *
     * @return Ecocode_Profiler_Model_Collector_DataCollectorInterface[]
     */
    public function getCollectors()
    {
        return $this->collectors;
    }

    /**
     * Sets the Collectors associated with this profile.
     *
     * @param Ecocode_Profiler_Model_Collector_DataCollectorInterface[] $collectors
     */
    public function setCollectors(array $collectors)
    {
        $this->collectors = array();
        foreach ($collectors as $collector) {
            $this->addCollector($collector);
        }
    }

    /**
     * Adds a Collector.
     *
     * @param Ecocode_Profiler_Model_Collector_DataCollectorInterface $collector A Ecocode_Profiler_Model_Collector_DataCollectorInterface instance
     */
    public function addCollector(Ecocode_Profiler_Model_Collector_DataCollectorInterface $collector)
    {
        $this->collectors[$collector->getName()] = $collector;
    }

    /**
     * Returns true if a Collector for the given name exists.
     *
     * @param string $name A collector name
     *
     * @return bool
     */
    public function hasCollector($name)
    {
        return isset($this->collectors[$name]);
    }

    public function __sleep()
    {
        return array('token', 'parent', 'children', 'collectors', 'ip', 'method', 'url', 'time', 'statusCode', 'collectTime');
    }
}
