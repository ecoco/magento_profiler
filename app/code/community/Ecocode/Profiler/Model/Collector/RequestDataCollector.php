<?php

/**
 * Class Ecocode_Profiler_Model_Collector_RequestDataCollector
 */
class Ecocode_Profiler_Model_Collector_RequestDataCollector
    extends Ecocode_Profiler_Model_Collector_AbstractDataCollector
{
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

        $sessionMetadata   = [];
        $sessionAttributes = [];
        //@TODO get all magento session singletons to split them by namespace
        $flashes           = [];

        /*
        $session           = null
        if (false && $request->hasSession()) {
                    $session = $request->getSession();
                    if ($session->isStarted()) {
                        $sessionMetadata['Created']   = date(DATE_RFC822, $session->getMetadataBag()->getCreated());
                        $sessionMetadata['Last used'] = date(DATE_RFC822, $session->getMetadataBag()->getLastUsed());
                        $sessionMetadata['Lifetime']  = $session->getMetadataBag()->getLifetime();
                        $sessionAttributes            = $session->all();
                        $flashes                      = $session->getFlashBag()->peekAll();
                    }
                }*/

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
            'session_metadata'   => $sessionMetadata,
            'session_attributes' => $sessionAttributes,
            'flashes'            => $flashes,
            'path_info'          => $request->getPathInfo(),
            'controller'         => 'n/a',
            //'locale'             => $request->getLocale(),
        ];

        $this->hideAuthData();

        $controllerData = $this->collectControllerData();
        if ($controllerData) {
            $this->data['controller'] = $controllerData;
        }

        /*if (null !== $session && $session->isStarted()) {
            if ($request->attributes->has('_redirected')) {
                $this->data['redirect'] = $session->remove('sf_redirect');
            }

            if ($response->isRedirect()) {
                $session->set('sf_redirect', [
                    'token'       => $response->headers->get('x-debug-token'),
                    'route'       => $request->attributes->get('_route', 'n/a'),
                    'method'      => $request->getMethod(),
                    'controller'  => $this->parseController($request->attributes->get('_controller')),
                    'status_code' => $statusCode,
                    'status_text' => Response::$statusTexts[(int)$statusCode],
                ]);
            }
        }*/
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
        return $this->data['session_attributes'];
    }

    public function getFlashes()
    {
        return $this->data['flashes'];
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
        return $this->data['controller'];
    }

    /**
     * Gets the previous request attributes.
     *
     * @return array|bool A legacy array of data from the previous redirection response
     *                    or false otherwise
     */
    public function getRedirect()
    {
        return isset($this->data['redirect']) ? $this->data['redirect'] : false;
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
            $r = new \ReflectionClass($controller);

            return [
                'class'  => $r->getName(),
                'method' => null,
                'file'   => $r->getFileName(),
                'line'   => $r->getStartLine(),
            ];
        }

        return (string)$controller ?: 'n/a';
    }

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
        $getData = $request->getQuery();
        if ($getData === null) {
            $getData = [];
        }
        return $getData;
    }


    public function collectRequestData(Mage_Core_Controller_Request_Http $request)
    {
        $postData = $request->getPost();
        if ($postData === null) {
            $postData = [];
        }
        return $postData;
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
