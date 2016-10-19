<?php

/**
 * Class Ecocode_Profiler_Model_Collector_RequestDataCollector
 */
class Ecocode_Profiler_Model_Collector_RequestDataCollector
    extends Ecocode_Profiler_Model_Collector_AbstractDataCollector
{
    protected $messages = [];

    public static $statusTexts = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC7238
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',                                               // RFC2324
        421 => 'Misdirected Request',                                         // RFC7540
        422 => 'Unprocessable Entity',                                        // RFC4918
        423 => 'Locked',                                                      // RFC4918
        424 => 'Failed Dependency',                                           // RFC4918
        425 => 'Reserved for WebDAV advanced collections expired proposal',   // RFC2817
        426 => 'Upgrade Required',                                            // RFC2817
        428 => 'Precondition Required',                                       // RFC6585
        429 => 'Too Many Requests',                                           // RFC6585
        431 => 'Request Header Fields Too Large',                             // RFC6585
        451 => 'Unavailable For Legal Reasons',                               // RFC7725
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',                      // RFC2295
        507 => 'Insufficient Storage',                                        // RFC4918
        508 => 'Loop Detected',                                               // RFC5842
        510 => 'Not Extended',                                                // RFC2774
        511 => 'Network Authentication Required',                             // RFC6585
    ];

    /**
     * {@inheritdoc}
     */
    public function collect(Mage_Core_Controller_Request_Http $request, Mage_Core_Controller_Response_Http $response, \Exception $exception = null)
    {
        $responseHeaders = $this->collectResponseHeaders($response);
        // attributes are serialized and as they can be anything, they need to be converted to strings.
        $requestAttributes = $this->collectRequestAttributes($request);
        $requestHeaders    = $this->collectRequestHeaders($request);
        $requestContent    = $request->getRawBody();


        $statusCode = $this->detectStatusCode($response);
        $statusText = isset(self::$statusTexts[$statusCode]) ? self::$statusTexts[$statusCode] : '';


        $contentType = isset($responseHeaders['Content-Type']) ? $responseHeaders['Content-Type'] : 'text/html';
        $this->data  = [
            'method'             => $request->getMethod(),
            'content'            => $requestContent,
            'content_type'       => $contentType,
            'status_text'        => $statusText,
            'status_code'        => $statusCode,
            'request_query'      => $this->collectRequestQuery($request),
            'request_request'    => $this->collectRequestData($request),
            'request_headers'    => $requestHeaders,
            'request_server'     => $request->getServer(),
            'request_cookies'    => $request->getCookie(),
            'request_attributes' => $requestAttributes,
            'response_headers'   => $responseHeaders,
            'session_metadata'   => [],
            'session_data'       => [],
            'messages'           => [],
            'path_info'          => $request->getPathInfo(),
            'controller'         => 'n/a',
            //'locale'             => $request->getLocale(),
        ];

        $this->hideAuthData();

        $controllerData = $this->collectControllerData();
        if ($controllerData) {
            $this->data['controller'] = $controllerData;
        }

        $this->collectRedirectData($request, $response);
        $this->collectSessionData();
        $this->collectFlashMessages();
    }

    protected function collectRedirectData(
        Mage_Core_Controller_Request_Http $request,
        Mage_Core_Controller_Response_Http $response
    )
    {
        /** @var $session Ecocode_Profiler_Model_Session $session */
        $session = $this->getHelper()->getSession();
        if (null !== $session) {
            if ($request->getParam('_redirected')) {
                $this->data['redirect'] = $session->getData('eco_redirect', true);
            }

            if ($response->isRedirect()) {
                $session->setData('eco_redirect', [
                    'token'       => $this->getHelper()->getTokenFromResponse($response),
                    'route'       => $this->getRoute(),
                    'method'      => $request->getMethod(),
                    'controller'  => $this->getController(),
                    'status_code' => $this->getStatusCode(),
                    'status_text' => $this->getStatusText(),
                ]);
            }
        }
    }

    protected function collectSessionData()
    {
        $namespaceData  = [];
        $storeData      = [];
        $rawSessionData = $this->getRawSession();

        $defaultSessionData = [
            '_session_validator_data' => false,
            'session_hosts'           => false
        ];


        foreach ($rawSessionData as $key => $data) {
            if (isset($data['messages'])) {
                //dont save messages here
                unset($data['messages']);
            }

            if (isset($data['_session_validator_data'])) {
                unset($rawSessionData[$key]);
                //magento session model
                $filtered = array_diff_key($data, $defaultSessionData);

                if (count($filtered) === 0) {
                    continue;
                }

                if (strpos($key, 'store_') === 0) {
                    $storeCode                 = str_replace('store_', '', $key);
                    $storeData[$storeCode] = $data;
                } else {
                    $namespaceData[$key] = $data;
                }


            }

        }
        //add the missing data to a global namespace
        $this->data['session_data'] = [
            'namespace' => $namespaceData,
            'store'     => $storeData,
            'global'    => $rawSessionData,
        ];
    }

    /**
     * @return Mage_Core_Model_Message_Collection
     */
    protected function getSessionList()
    {
        $rawSessionData = $this->getRawSession();
        $sessionList    = [];
        foreach (Mage::getAllRegistryEntries() as $session) {
            if ($session instanceof Mage_Core_Model_Session_Abstract) {
                $namespace               = array_search($session->getData(), $rawSessionData);
                $sessionList[$namespace] = $session;
            }
        }

        return $sessionList;
    }

    protected function collectFlashMessages()
    {
        $messages    = [];
        $sessionList = $this->getSessionList();
        $helper      = $this->getHelper();

        foreach ($this->messages as $message) {
            $namespace = $message['namespace'];

            $session                = $sessionList[$namespace];
            $message['class_group'] = $helper->resolveClassGroup($session);

            $messages[] = $message;
        }

        $this->data['messages'] = $messages;
    }

    public function captureFlashMessages()
    {
        $messages = [];
        foreach ($this->getRawSession() as $namespace => $data) {
            if (!isset($data['messages'])) {
                continue;
            }

            if ($data['messages']->count()) {
                foreach ($data['messages']->getItems() as $message) {
                    /** @var Mage_Core_Model_Message_Abstract $message */

                    $messages[] = [
                        'namespace' => $namespace,
                        'type'      => $message->getType(),
                        'text'      => $message->getText(),
                    ];
                }
            }
        }

        $this->messages = $messages;
    }

    protected function hideAuthData()
    {
        if (isset($this->data['request_headers']['php-auth-pw'])) {
            $this->data['request_headers']['php-auth-pw'] = '******';
        }

        if (isset($this->data['request_server']['PHP_AUTH_PW'])) {
            $this->data['request_server']['PHP_AUTH_PW'] = '******';
        }

        if (isset($this->data['request_request']['_password'])) {
            $this->data['request_request']['_password'] = '******';
        }
    }

    public function getMethod()
    {
        return $this->data['method'];
    }

    public function getPathInfo()
    {
        return $this->data['path_info'];
    }

    public function getRequestRequest()
    {
        return new Ecocode_Profiler_Model_Http_ParameterBag($this->data['request_request']);
    }

    public function getRequestQuery()
    {
        return new Ecocode_Profiler_Model_Http_ParameterBag($this->data['request_query']);
    }

    public function getRequestHeaders()
    {
        return new Ecocode_Profiler_Model_Http_HeaderBag($this->data['request_headers']);
    }

    public function getRequestServer()
    {
        return new Ecocode_Profiler_Model_Http_ParameterBag($this->data['request_server']);
    }

    public function getRequestCookies()
    {
        return new Ecocode_Profiler_Model_Http_ParameterBag($this->data['request_cookies']);
    }

    public function getRequestAttributes()
    {
        return new Ecocode_Profiler_Model_Http_ParameterBag($this->data['request_attributes']);
    }

    public function getResponseHeaders()
    {
        return new Ecocode_Profiler_Model_Http_ResponseHeaderBag($this->data['response_headers']);
    }

    public function getSessionMetadata()
    {
        return $this->data['session_metadata'];
    }

    public function getSessionAttributes()
    {
        return $this->data['session_data'];
    }

    public function getMessages()
    {
        return $this->data['messages'];
    }

    public function getContent()
    {
        return $this->data['content'];
    }

    public function getContentType()
    {
        return $this->data['content_type'];
    }

    public function getStatusText()
    {
        return $this->data['status_text'];
    }

    public function getStatusCode()
    {
        return $this->data['status_code'];
    }

    public function getFormat()
    {
        return $this->data['format'];
    }

    public function getLocale()
    {
        return $this->data['locale'];
    }

    /**
     * Gets the route name.
     *
     * @return string The route
     */
    public function getRoute()
    {
        return isset($this->data['request_attributes']['_route']) ? $this->data['request_attributes']['_route'] : '';
    }

    /**
     * @return string
     */
    public function getRequestUri()
    {
        return isset($this->data['request_attributes']['request_uri']) ? $this->data['request_attributes']['request_uri'] : '';
    }

    /**
     * @return string
     */
    public function getRequestString()
    {
        return isset($this->data['request_attributes']['request_string']) ? $this->data['request_attributes']['request_string'] : '';
    }

    /**
     * @return string
     */
    public function getRouteName()
    {
        return isset($this->data['request_attributes']['_route_name']) ? $this->data['request_attributes']['_route_name'] : '';
    }

    /**
     * @return string
     */
    public function getModuleName()
    {
        return isset($this->data['request_attributes']['_module']) ? $this->data['request_attributes']['_module'] : '';
    }

    /**
     * @return string
     */
    public function getControllerName()
    {
        return isset($this->data['request_attributes']['_controller']) ? $this->data['request_attributes']['_controller'] : '';
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return isset($this->data['request_attributes']['_action']) ? $this->data['request_attributes']['_action'] : '';
    }

    /**
     * Gets the route parameters.
     *
     * @return array The parameters
     */
    public function getRouteParams()
    {
        return isset($this->data['request_attributes']['_route_params']) ? $this->data['request_attributes']['_route_params'] : [];
    }

    /**
     * Gets the parsed controller.
     *
     * @return array|string The controller as a string or array of data
     *                      with keys 'class', 'method', 'file' and 'line'
     */
    public function getController()
    {
        return $this->getData('controller', []);
    }

    /**
     * Gets the previous request attributes.
     *
     * @return array|bool A legacy array of data from the previous redirection response
     *                    or false otherwise
     */
    public function getRedirect()
    {
        return $this->getData('redirect', false);
    }

    /**
     * @codeCoverageIgnore
     * @SuppressWarnings("superglobals")
     * @return array
     */
    protected function getRawSession()
    {
        return isset($_SESSION) ? $_SESSION : [];
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getName()
    {
        return 'request';
    }

    /**
     * Parse a controller.
     *
     * @param mixed $controller The controller to parse
     *
     * @return array|string An array of controller data or a simple string
     */
    protected function parseController($controller)
    {
        if (is_object($controller)) {
            /** @var Mage_Core_Controller_Varien_Front $controller */

            if ($controller->getData('action')) {
                /** @var Mage_Core_Controller_Varien_Action $actionController */
                $actionController = $controller->getData('action');

                /** @var Mage_Core_Controller_Request_Http $request */
                $request = $actionController->getRequest();

                if ($actionController->hasAction($request->getActionName())) {
                    $actionReflection = new \ReflectionMethod(
                        $actionController,
                        $actionController->getActionMethodName($request->getActionName())
                    );

                    return [
                        'class'  => get_class($actionController),
                        'method' => $actionReflection->getName(),
                        'file'   => $actionReflection->getFileName(),
                        'line'   => $actionReflection->getStartLine(),
                    ];
                }
            }

            $controllerReflection = new \ReflectionClass($controller);

            return [
                'class'  => $controllerReflection->getName(),
                'method' => null,
                'file'   => $controllerReflection->getFileName(),
                'line'   => $controllerReflection->getStartLine(),
            ];
        }

        return (string)$controller ?: 'n/a';
    }

    /**
     * Magento is not very good in setting the right response code
     * so we help out a bit by checking the actual header that is send
     *
     * @param Mage_Core_Controller_Response_Http $response
     * @return int
     */
    protected function detectStatusCode(Mage_Core_Controller_Response_Http $response)
    {
        $statusCode = $response->getHttpResponseCode();
        foreach ($response->getHeaders() as $header) {
            if (substr($header['name'], 0, 5) === 'Http/') {
                preg_match('/^[0-9]{3}/', $header['value'], $matches);
                if ($matches) {
                    $statusCode = (int)reset($matches);
                }

                break;
            }
        }
        return $statusCode;
    }

    protected function collectRequestAttributes(Mage_Core_Controller_Request_Http $request)
    {
        //we need to use a reflection as Request->getParams() takes also get parameters into account
        $class = new ReflectionClass('Mage_Core_Controller_Request_Http');
        /** @var ReflectionProperty $property */
        $property = $class->getProperty('_params');
        $property->setAccessible(true);

        $routeParams = $property->getValue($request);

        $fullActionName = $request->getRequestedRouteName() . '_' .
            $request->getRequestedControllerName() . '_' .
            $request->getRequestedActionName();

        $attributes = [
            'request_string' => $request->getRequestString(),
            'request_uri'    => $request->getRequestUri(),
            '_module'        => $request->getModuleName(),
            '_controller'    => $request->getControllerName(),
            '_action'        => $request->getActionName(),
            '_route'         => $fullActionName,
            '_route_name'    => $request->getRouteName()
        ];

        if ($routeParams) {
            $attributes['_route_params'] = $routeParams;
        }

        return $attributes;
    }

    public function collectRequestHeaders(Mage_Core_Controller_Request_Http $request)
    {
        $headers = [];

        foreach ($request->getServer() as $key => $value) {
            if (substr($key, 0, 5) !== 'HTTP_') {
                continue;
            }
            $header           = str_replace(' ', '-', (str_replace('_', ' ', strtolower(substr($key, 5)))));
            $headers[$header] = $value;
        }

        return $headers;
    }

    public function collectRequestQuery(Mage_Core_Controller_Request_Http $request)
    {
        $queryData = $request->getQuery();

        return $queryData ? $queryData : [];
    }


    public function collectRequestData(Mage_Core_Controller_Request_Http $request)
    {
        $postData = $request->getPost();

        return $postData ? $postData : [];
    }

    protected function collectControllerData()
    {
        //use reflection to make sure we not init a front controller
        $class = new ReflectionClass('Mage_Core_Model_App');
        /** @var ReflectionProperty $property */
        $property = $class->getProperty('_frontController');
        $property->setAccessible(true);

        $app        = Mage::app();
        $controller = $property->getValue($app);

        if ($controller) {
            /** @var Mage_Core_Controller_Varien_Action */
            return $this->parseController($controller);
        }

        return false;
    }

    public function collectResponseHeaders(Mage_Core_Controller_Response_Http $response)
    {
        $headers = [];

        foreach ($response->getHeaders() as $headerData) {
            $headers[$headerData['name']] = $headerData['value'];
        }

        return $headers;
    }
}
