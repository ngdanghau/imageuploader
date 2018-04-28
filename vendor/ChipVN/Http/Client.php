<?php

/**
 * ChipVN_Http_Client class used to sending request and get response like a browser.
 * Use 2 functions: cURL, fsockopen
 * so you can use this class like "curl" WITHOUT CURL extension installed
 * Supports POST (fields, raw data), file uploading, GET, PUT, etc..
 *
 * @author     Phan Thanh Cong <ptcong90@gmail.com>
 * @copyright  2010-2014 Phan Thanh Cong.
 * @license    http://www.opensource.org/licenses/mit-license.php  MIT License
 *
 * @version    2.6.5
 * @relase     Jun 13, 2015
 */
class ChipVN_Http_Client
{
    /**
     * Debug mode.
     *
     * @var bool
     */
    protected $debug = false;

    /**
     * Debug informations.
     * This provides "time_start", "time_process" of a request.
     * If debug mode is enabled, this will give "headers", "body".
     *
     * @var array
     */
    protected $info;

    /**
     * HTTP Version.
     *
     * @var string
     */
    protected $httpVersion;

    /**
     * URL target.
     *
     * @var string
     */
    protected $target;

    /**
     * URL scheme.
     *
     * @var string
     */
    protected $scheme;

    /**
     * URL host.
     *
     * @var string
     */
    protected $host;

    /**
     * URL port.
     *
     * @var int
     */
    protected $port;

    /**
     * URL path.
     *
     * @var string
     */
    protected $path;

    /**
     * Request method.
     *
     * @var string
     */
    protected $method;

    /**
     * Request cookies.
     *
     * @var array
     */
    protected $cookies;

    /**
     * Request headers.
     *
     * @var array
     */
    protected $headers;

    /**
     * Request parameters.
     *
     * @var array
     */
    protected $parameters;

    /**
     * Raw post data.
     *
     * @var mixed
     */
    protected $rawPostData;

    /**
     * Request user agent.
     *
     * @var string
     */
    protected $userAgent;

    /**
     * Number of seconds to timeout.
     *
     * @var int
     */
    protected $timeout;

    /**
     * Determine to get response body text or header only.
     *
     * @var bool
     */
    protected $nobody;

    /**
     * Determine follow response location (if have) or not.
     *
     * @since 2.5.2
     *
     * @var bool
     */
    protected $followRedirect;

    /**
     * The maximum amount of HTTP redirections to follow.
     * True is not limited.
     *
     * @since 2.5.2
     *
     * @var int|true
     */
    protected $maxRedirect;

    /**
     * Redirected count (for use fsockopen).
     *
     * @since 2.5.2
     *
     * @var int
     */
    protected $redirectedCount;

    /**
     * Reditected request objects.
     *
     * @var aray
     */
    protected $redirectedRequests;

    /**
     * Redirected urls.
     *
     * @var array
     */
    protected $redirectedUrls;

    /**
     * Determine the request will use cURL or not.
     *
     * @var bool
     */
    protected $useCurl;

    /**
     * Authentication username.
     *
     * @var string
     */
    protected $authUser;

    /**
     * Authentication password.
     *
     * @var string
     */
    protected $authPassword;

    /**
     * Proxy IP (only cURL).
     *
     * @var string
     */
    protected $proxyIp;

    /**
     * Proxy username (only cURL).
     *
     * @var string
     */
    protected $proxyUser;

    /**
     * Proxy password (only cURL).
     *
     * @var string
     */
    protected $proxyPassword;

    /**
     * Determine the request is multipart or not.
     *
     * @var bool
     */
    protected $isMultipart;

    /**
     * Enctype (application/x-www-form-urlencoded).
     *
     * @var string
     */
    protected $enctype;

    /**
     * Boundary name (use when upload).
     *
     * @var string
     */
    protected $boundary;

    /**
     * Errors while execute.
     *
     * @var array
     */
    public $errors;

    /**
     * Response status code.
     *
     * @var int
     */
    protected $responseStatus;

    /**
     * Response cookies.
     *
     * @var string
     */
    protected $responseCookies;

    /**
     * Response cookies by array with keys:
     * "name", "value", "path", "expires", "domains", "secure", "httponly".
     * Default is null.
     *
     * @var array
     */
    protected $responseArrayCookies;

    /**
     * Response headers.
     *
     * @var array
     */
    protected $responseHeaders;

    /**
     * Response text.
     *
     * @var string
     */
    protected $responseText;

    /**
     * Flag to determine socket is enabled.
     *
     * @var null
     */
    protected static $socketEnabled = null;

    /**
     * Create a ChipVN_Http_Client instance.
     */
    public function __construct()
    {
        if (self::$socketEnabled === null) {
            self::$socketEnabled = function_exists('fsockopen');
            if (!self::$socketEnabled && !function_exists('curl_init')) {
                throw new Exception('The library require fsockopen or curl.');
            }
        }
        $this->reset();
    }

    /**
     * Reset request and response data.
     *
     * @return self
     */
    public function reset()
    {
        return $this
            ->resetRequest()
            ->resetFollowRedirect()
            ->resetResponse();
    }

    /**
     * Dynamic getters, setters.
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if (in_array($type = substr($method, 0, 3), array('get', 'set'), true)) {
            $property = strtolower(substr($method, 3, 1)).substr($method, 4);
            if (!property_exists($this, $property)) {
                throw new Exception(sprintf('Property "%s" is not exist.', $property));
            }
            if ($type == 'get') {
                return $this->$property;
            }
            if ($type == 'set') {
                if (stripos($property, 'response') === 0) {
                    throw new Exception('Properties used to store response informations is not writable.');
                }
                $this->$property = $arguments[0];
            }

            return $this;
        }
    }

    /**
     * Reset request data.
     *
     * @return self
     */
    public function resetRequest()
    {
        $this->httpVersion = '1.1';
        $this->target = '';
        $this->scheme = 'http';
        $this->host = '';
        $this->port = 0;
        $this->path = '';
        $this->method = 'GET';
        $this->parameters = array();
        $this->rawPostData = null;
        $this->cookies = array();
        $this->headers = array();
        $this->timeout = 10;
        $this->userAgent = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:9.0.1) Gecko/20100101 Firefox/9.0.1';
        $this->useCurl = !self::$socketEnabled;
        $this->isMultipart = false;

        $this->proxyIp = '';
        $this->proxyUser = '';
        $this->proxyPassword = '';

        $this->authUser = '';
        $this->authPassword = '';

        $this->enctype = 'application/x-www-form-urlencoded';
        $this->boundary = '--'.md5('Phan Thanh Cong <ptcong90@gmail.com>');

        $this->nobody = false;
        $this->errors = array();

        return $this;
    }

    /**
     * Reset request follow redirect option.
     *
     * @return self
     */
    public function resetFollowRedirect()
    {
        $this->followRedirect = false;
        $this->maxRedirect = true;
        $this->redirectedCount = 0;
        $this->redirectedUrls = array();
        $this->redirectedRequests = array();

        return $this;
    }

    /**
     * Reset response data.
     *
     * @return self
     */
    public function resetResponse()
    {
        $this->responseStatus = 0;
        $this->responseHeaders = array();
        $this->responseCookies = '';
        $this->responseArrayCookies = array();
        $this->responseText = '';

        return $this;
    }

    /**
     * Set http version.
     *
     * @since  2.5
     *
     * @param string $version
     *
     * @return self
     */
    public function setHttpVersion($version)
    {
        if (in_array($version, array('1.0', '1.1'))) {
            $this->httpVersion = $version;
        }

        return $this;
    }

    /**
     * Set follow response location (if have).
     *
     * @param bool     $follow
     * @param int|null $maxRedirect Null to use default value
     *
     * @return self
     */
    public function setFollowRedirect($follow = true, $maxRedirect = null)
    {
        $this->followRedirect = (boolean) $follow;
        if ($maxRedirect === true) {
            $this->maxRedirect = true;
        } elseif ($maxRedirect !== null) {
            $this->maxRedirect = max(1, (int) $maxRedirect);
        }

        return $this;
    }

    /**
     * Set URL target.
     *
     * @param string $target
     *
     * @return self
     */
    public function setTarget($target)
    {
        $this->target = trim($target);

        return $this;
    }

    /**
     * Set request URL referer.
     *
     * @param string $referer
     *
     * @return self
     */
    public function setReferer($referer)
    {
        $this->headers['Referer'] = $referer;

        return $this;
    }

    /**
     * Set number of seconds to time out.
     *
     * @param int $seconds
     *
     * @return self
     */
    public function setTimeout($seconds)
    {
        if ($seconds > 0) {
            $this->timeout = $seconds;
        }

        return $this;
    }

    /**
     * Set request raw post data.
     *
     * @param string $data
     *
     * @return self
     */
    public function setRawPost($data)
    {
        $this->rawPostData = array(
            'type' => 'raw',
            'data' => $data,
        );

        return $this;
    }

    /**
     * Set request raw post data.
     *
     * @param string $path
     *
     * @return self
     */
    public function setRawPostFile($path)
    {
        $this->rawPostData = array(
            'type' => 'file',
            'data' => $path,
        );

        return $this;
    }

    /**
     * Set request method.
     *
     * @param string $method
     *
     * @return self
     */
    public function setMethod($method)
    {
        $this->method = strtoupper(trim($method));

        return $this;
    }

    /**
     * Add request parameters.
     *
     * @since 2.5.4
     *
     * @param string|array $name
     * @param string|null  $value
     *
     * @return self
     */
    public function setParameters($name, $value = null)
    {
        if (func_num_args() == 2) {
            $this->parameters[$name] = $value;
        } else {
            if (is_array($name)) {
                foreach ($name as $key => $value) {
                    // key-value pairs
                    if (!is_int($key)) {
                        $this->setParameters($key, $value);
                    } else {
                        $this->setParameters($value);
                    }
                }
            } elseif (is_string($name)) {
                $name = str_replace('+', '%2B', preg_replace_callback(
                    '#&[a-z]+;#',
                    create_function('$match', 'return rawurlencode($match[0]);'),
                    $name));

                $array = $this->parseParameters($name);
                $this->setParameters($array);
            }
        }

        return $this;
    }

    /**
     * Add request headers.
     *
     * @since 2.5.4
     *
     * @param string|array $name
     * @param string|null  $value
     *
     * @return self
     */
    public function setHeaders($name, $value = null)
    {
        if (func_num_args() == 2) {
            if (strcasecmp($name, 'Cookie') === 0) {
                return $this->setCookiesPairs($value);
            }
            $this->headers[trim($name)] = trim($value);
        } else {
            if (is_array($name)) {
                foreach ($name as $key => $value) {
                    // key-value pairs
                    if (!is_int($key)) {
                        $this->setHeaders($key, $value);
                    } else {
                        $this->setHeaders($value);
                    }
                }
            } elseif (is_string($name)) {
                list($key, $value) = explode(':', $name, 2);

                $this->setHeaders($key, $value);
            }
        }

        return $this;
    }

    /**
     * Add request cookies.
     *
     * @since 2.5.4
     *
     * @param string|array $name
     * @param string|null  $value
     *
     * @return self
     */
    public function setCookies($name, $value = null)
    {
        if (func_num_args() == 2) {
            if (is_string($value)) {
                $this->cookies[$name] = $this->parseCookie($name.'='.$value);
            } elseif (is_array($value)) {
                if ($this->isValidCookie($value)) {
                    $this->cookies[$value['name']] = $value;
                }
            }
        } else {
            if ($name && is_array($name)) {
                $multiple = is_array($name[key($name)]);
                if (!$multiple && $this->isValidCookie($name)) {
                    $this->cookies[$name['name']] = $name;
                } else {
                    foreach ($name as $key => $value) {
                        if ($multiple || !is_int($key)) {
                            $this->setCookies($key, $value);
                        } else {
                            $this->setCookies($value);
                        }
                    }
                }
            } else {
                if ($cookie = $this->parseCookie($name)) {
                    $this->cookies[$cookie['name']] = $cookie;
                }
            }
        }

        return $this;
    }

    /**
     * Helper to set multiple cookies by list string of name-value pairs.
     *
     * @since 2.5.9
     *
     * @param string $value
     *
     * @return self
     */
    public function setCookiesPairs($value)
    {
        if (preg_match_all('#(?:^|;)\s*([^=]+)=([^;]+)\s*?#', $value, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                list(, $name, $value) = $match;
                if (!strcasecmp($name, 'expires') && strtotime($value)
                    || !strcasecmp($name, 'path') && urldecode($value) == $value
                    || !strcasecmp($name, 'max-age')
                ) {
                    continue;
                }
                $this->setCookies($name, $value);
            }
        }

        return $this;
    }

    /**
     * Remove a request header by name or all headers.
     *
     * @param string|true $name True to remove all headers.
     *
     * @return self
     */
    public function removeHeaders($name)
    {
        if ($name === true) {
            $this->headers = array();
        } else {
            unser($this->headers[$name]);
        }

        return $this;
    }

    /**
     * Remove a request cookie by name or all cookies.
     *
     * @param string|true $name True to remove all cookies.
     *
     * @return self
     */
    public function removeCookies($name)
    {
        if ($name === true) {
            $this->cookies = array();
        } else {
            unser($this->cookies[$name]);
        }

        return $this;
    }

    /**
     * Remove a request parameter by name or all paramters.
     * If parameters is an array name[0], name[1]
     * you may only remove [1] by `$obj->removeParameters('name.1');`.
     *
     * @param string|true $name True to remove all cookies.
     *
     * @return self
     */
    public function removeParameters($name)
    {
        if ($name === true) {
            $this->parameters = array();
        } else {
            $subs = explode('.', $name);
            $last = array_pop($subs);
            $temp = & $this->parameters;

            foreach ($subs as $sub) {
                if (isset($temp[$sub])) {
                    $temp = & $temp[$sub];
                }
            }
            unset($temp[$last]);
        }

        return $this;
    }

    /**
     * Avoid use dynamic setter then bypass requirement checking.
     *
     * @param bool $value
     */
    public function setUseCurl($value)
    {
        return $this->useCurl($value);
    }

    /**
     * Determine if the request will use cURL or not.
     * Default is use fsockopen.
     *
     * @param bool $useCurl
     *
     * @return self
     *
     * @throws Exception
     */
    public function useCurl($useCurl)
    {
        $this->useCurl = (boolean) $useCurl;
        if (!$this->useCurl && !self::$socketEnabled) {
            throw new Exception('Function "fsockopen" is disabled, please enable to use otherwise use "curl".');
        }

        return $this;
    }

    /**
     * Set submit multipart.
     *
     * @param string $type
     * @param string $method
     *
     * @return self
     */
    public function setSubmitMultipart($type = 'form-data', $method = 'POST')
    {
        $this->isMultipart = true;
        $this->setMethod($method);
        $this->setEnctype('multipart/'.$type);

        return $this;
    }

    /**
     * Set submit normal.
     *
     * @param string $enctype
     * @param string $method
     *
     * @return self
     */
    public function setSubmitNormal($enctype = 'application/x-www-form-urlencoded', $method = 'POST')
    {
        $this->isMultipart = false;
        $this->setMethod($method);
        $this->setEnctype($enctype);

        return $this;
    }

    /**
     * Set request with proxy.
     *
     * @param string $proxyIp  Format: ipaddress:port
     * @param string $username
     * @param string $password
     *
     * @return self
     */
    public function setProxy($proxyIp, $username = '', $password = '')
    {
        $this->proxyIp = trim($proxyIp);
        $this->proxyUser = $username;
        $this->proxyPassword = $password;

        return $this;
    }

    /**
     * Set request authentication.
     *
     * @param string $username
     * @param string $password
     *
     * @return self
     */
    public function setAuth($username, $password = '')
    {
        $this->authUser = $username;
        $this->authPassword = $password;

        return $this;
    }

    /**
     * Set boundary.
     *
     * @param string $boundary
     *
     * @return self
     */
    public function setBoundary($boundary)
    {
        $this->boundary = $boundary;

        return $this;
    }

    /**
     * Determine a value is a cookie array (supported by this class).
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isValidCookie($value)
    {
        return !array_diff_key(
            array_flip(array('name', 'value', 'expires', 'path', 'domain', 'secure', 'httponly')),
            (array) $value
        );
    }

    /**
     * Determine if cookie is valid for url.
     *
     * @param array  $cookie
     * @param string $host
     * @param string $path
     *
     * @return bool
     */
    public function isValidCookieForHost(array $cookie, $host, $path = '')
    {
        if ($path && !empty($cookie['path']) && strpos($path, $cookie['path']) !== 0) {
            return false;
        }
        if (!empty($cookie['domain'])
            && (substr($cookie['domain'], 0, 1) == '.'
                    && substr($host, -(strlen($cookie['domain']) - 1)) != substr($cookie['domain'], 1)
                || substr($cookie['domain'], 0, 1) != '.'
                    && $cookie['domain'] != $host
            )
        ) {
            return false;
        }

        return true;
    }

    /**
     * Parses a URL and returns an associative array.
     *
     * @param string $value
     *
     * @return array|false False if can't parse the value
     */
    public function parseCookie($value)
    {
        if (is_string($value) && preg_match_all('#([^=;\s]+)(?:=([^;]+))?;?\s*?#', $value, $matches)) {
            $name = array_shift($matches[1]);
            $value = array_shift($matches[2]);

            $cookie = array();
            if ($matches[1] && $matches[2]) {
                $matches[1] = array_map('strtolower', $matches[1]);
                $cookie = array_combine($matches[1], $matches[2]);
                foreach (array('secure', 'httponly') as $k) {
                    if (isset($cookie[$k])) {
                        $cookie[$k] = true;
                    }
                }
            }
            if (isset($cookie['domain'])) {
                $cookie['domain'] = strtolower($cookie['domain']);
            }

            return  $cookie + array(
                'name'     => $name,
                'value'    => $value,
                'expires'  => null,
                'path'     => null,
                'domain'   => null,
                'secure'   => false,
                'httponly' => false,
            );
        }

        return false;
    }

    /**
     * Create cookie from array.
     *
     * @param array $cookie
     *
     * @return string
     */
    public function createCookie(array $cookie)
    {
        return $cookie['name'].'='.$cookie['value'].';';
    }

    /**
     * Get request headers.
     *
     * @return array
     */
    protected function prepareRequestHeaders()
    {
        $headers = array();

        if ($this->authUser) {
            $this->setHeaders('Authorization', 'Basic '.base64_encode($this->authUser.':'.$this->authPassword));
        }
        if ($this->userAgent) {
            $this->setHeaders('User-Agent', $this->userAgent);
        }
        if ($this->enctype && $this->isPut()) {
            $this->setHeaders('Content-Type',  $this->enctype.($this->isMultipart ? ';boundary='.$this->boundary : ''));
        }
        if ($this->headers) {
            foreach ($this->headers as $name => $value) {
                $headers[] = $name.': '.$value;
            }
        }

        $cookies = '';
        foreach ($this->cookies as $name => $cookie) {
            if (!$this->isValidCookieForHost($cookie, $this->host, $this->path)) {
                unset($this->cookies[$name]);
                continue;
            }
            $cookie = $this->createCookie($cookie);
            $cookies .= ($cookies && $cookie ? ' ' : '').$cookie;
        }

        if ($cookies) {
            $headers[] = 'Cookie: '.$cookies;
        }

        return $headers;
    }

    /**
     * Get request body.
     *
     * @return string
     */
    protected function prepareRequestBody()
    {
        $body = '';
        if ($this->rawPostData) {
            $body .= $this->isMultipart ? '--'.$this->boundary."\r\n" : '';
            // if use only raw data, don't append EOL to data
            if ($this->rawPostData['type'] == 'file') {
                $body .= $this->getFileData($this->rawPostData['data']);
            } else {
                $body .= $this->rawPostData['data'];
            }
        }

        if ($this->isPut()) {
            $data = http_build_query($this->parameters);
            if ($data && $this->rawPostData) {
                // append EOL to separate rawdata with form data
                $body .= "\r\n";
            }
            if ($this->isMultipart) {
                if (preg_match_all('#([^=&]+)=([^&]*)#i', $data, $matches)) {
                    foreach (array_combine($matches[1], $matches[2]) as $name => $value) {
                        $name = urldecode($name);
                        $value = urldecode($value);
                        if (substr($value, 0, 1) == '@') {
                            $uploadFilePath = substr($value, 1);
                            if (file_exists($uploadFilePath)) {
                                $body .= '--'.$this->boundary."\r\n";
                                $body .= 'Content-disposition: form-data; name="'.$name.'"; filename="'.basename($uploadFilePath)."\"\r\n";
                                $body .= 'Content-Type: '.$this->getFileType($uploadFilePath)."\r\n";
                                $body .= "Content-Transfer-Encoding: binary\r\n\r\n";
                                $body .= $this->getFileData($uploadFilePath)."\r\n";
                            }
                        } else {
                            $body .= '--'.$this->boundary."\r\n";
                            $body .= 'Content-Disposition: form-data; name="'.$name."\"\r\n";
                            $body .= "\r\n";
                            $body .= $value."\r\n";
                        }
                    }
                    $body .= '--'.$this->boundary."--\r\n"; // end
                };
            } else {
                $body .= preg_replace_callback('#([^=&]+)=([^&]*)#i', create_function('$match',
                    'return urlencode($match[1]).\'=\'.rawurlencode(urldecode($match[2]));'
                ), $data);
            }
        }

        if ($body && $this->isPut()) {
            $this->setHeaders('Content-Length', strlen($body));
        }

        return $body;
    }

    /**
     * Execute sending request and trigger errors messages if have.
     *
     * @param string|null       $target
     * @param string|null       $method
     * @param string|array|null $parameters
     * @param string|null       $referer
     *
     * @return bool
     */
    public function execute($target = null, $method = null, $parameters = null, $referer = null)
    {
        if ($target) {
            $this->setTarget($target);
        }
        if ($method) {
            $this->setMethod($method);
        }
        if ($parameters) {
            $this->setParameters($parameters);
        }
        if ($referer) {
            $this->setReferer($referer);
        }

        if (empty($this->target)) {
            $this->errors[] = 'ERROR: Target url must be no empty.';

            return false;
        }

        if ($this->parameters && $this->method == 'GET') {
            $this->target .= ($this->method == 'GET' ? (strpos($this->target, '?') ? '&' : '?')
                .http_build_query($this->parameters) : '');
        }

        $urlParsed = parse_url($this->target);
        $this->scheme = strtolower($urlParsed['scheme']);
        $this->host = strtolower($urlParsed['host']);

        if (!$this->port) {
            if ($this->scheme == 'https') {
                $this->port = isset($urlParsed['port']) ? $urlParsed['port'] : 443;
            } else {
                $this->port = isset($urlParsed['port']) ? $urlParsed['port'] : 80;
            }
        }

        $this->path = (isset($urlParsed['path']) ? $urlParsed['path'] : '/')
                    .(isset($urlParsed['query']) ? '?'.$urlParsed['query'] : '');

        $body = $this->prepareRequestBody();
        $headers = $this->prepareRequestHeaders();

        $this->info = array('time_start' => microtime(true));
        if ($this->debug) {
            $this->info += array(
                'headers' => $headers,
                'body'    => $body,
            );
        }

        if ($this->useCurl) {
            $result = $this->executeWithCurl($headers, $body);
        } else {
            $result = $this->executeWithSocket($headers, $body);
        }
        $this->info['time_process'] = microtime(true) - $this->info['time_start'];

        return $result;
    }

    /**
     * Execute sending request with cURL.
     *
     * @param array  $headers
     * @param string $body
     *
     * @return bool
     */
    protected function executeWithCurl($headers, $body)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->target);

        $httpVersion = CURL_HTTP_VERSION_1_0;
        if ($this->httpVersion = '1.1') {
            $httpVersion = CURL_HTTP_VERSION_1_1;
        }
        curl_setopt($ch, CURLOPT_HTTP_VERSION, $httpVersion);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        if ($this->nobody) {
            curl_setopt($ch, CURLOPT_NOBODY, true);
        }
        if ($this->timeout) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        }
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if ($this->isPut()) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        if ($this->proxyIp) {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxyIp);
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);

            if ($this->proxyUser) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxyUser.':'.$this->proxyPassword);
            }
        }
        // send request
        $response = curl_exec($ch);

        if ($response === false) {
            $this->errors[] = sprintf('ERROR: %d - %s.', curl_errno($ch), curl_error($ch));

            return false;
        }
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $responseHeader = (string) substr($response, 0, $headerSize);
        $responseBody = (string) substr($response, $headerSize);

        $this->parseResponseHeaders($responseHeader);
        $this->responseText = $responseBody;
        curl_close($ch);

        // don't use "CURLOPT_FOLLOWLOCATION" and "CURLOPT_MAXREDIRS"
        // because if redirect count greater than $maxRedirect
        // CURL will trigger an error, so we can't get any responses
        if (null !== $responseStatus = $this->followRedirect()) {
            return $responseStatus;
        }

        return true;
    }

    /**
     * Execute sending request with Socket.
     *
     * @param array  $headers
     * @param string $body
     *
     * @return bool
     */
    protected function executeWithSocket($headers, $body)
    {
        static $errorHandler = null;
        if ($errorHandler === null) {
            $errorHandler = create_function('', '');
        }
        // Ignore warning
        $handler = set_error_handler($errorHandler);

        $filePointer = fsockopen(
            ($this->scheme == 'https' ? 'ssl://' : '').$this->host,
            $this->port,
            $errno,
            $errstr,
            $this->timeout
        );

        // restore error handler
        $handler ? set_error_handler($handler) : restore_error_handler();

        if (!$filePointer) {
            if ($errstr) {
                $this->errors[] = sprintf('ERROR: %d - %s.', $errno, $errstr);
            } else {
                $this->errors[] = sprintf('ERROR: Cannot connect to "%s" with port "%s"', $this->target, $this->port);
            }

            return false;
        }
        $requestHeader = $this->method.' '.$this->path.' HTTP/'.$this->httpVersion."\r\n";
        $requestHeader .= 'Host: '.$this->host."\r\n";
        if ($headers) {
            $requestHeader .= implode("\r\n", $headers)."\r\n";
        }
        if (stripos($requestHeader, 'Connection:') === false) {
            $requestHeader .= "Connection: close\r\n";
        }
        $requestHeader .= "\r\n";

        if ($body && $this->isPut()) {
            $requestHeader .= $body;
        }
        $requestHeader .= "\r\n\r\n";

        // send request
        fwrite($filePointer, $requestHeader);

        $responseHeader = '';
        $responseBody = '';
        do {
            $responseHeader .= fgets($filePointer, 128);
        } while (strpos($responseHeader, "\r\n\r\n") === false);

        $this->parseResponseHeaders($responseHeader);

        // get body
        if (!$this->nobody) {
            while (!feof($filePointer)) {
                $responseBody .= fgets($filePointer);
            }
        }
        fclose($filePointer);

        if (null !== $responseStatus = $this->followRedirect()) {
            return $responseStatus;
        }

        // remove chunked
        if (!$this->nobody
            && isset($this->responseHeaders['transfer-encoding'])
            && $this->responseHeaders['transfer-encoding'] == 'chunked'
        ) {
            $data = $responseBody;
            $len = strlen($data);
            $outData = '';
            $pos = 0;

            while ($pos < $len) {
                $rawnum = substr($data, $pos, strpos(substr($data, $pos), "\r\n") + 2);
                $num = hexdec(trim($rawnum));
                $pos += strlen($rawnum);
                $chunk = substr($data, $pos, $num);
                $outData .= $chunk;
                $pos += strlen($chunk);
            }
            $responseBody = $outData;
        }
        $this->responseText = $responseBody;

        return true;
    }

    /**
     * Execute follow redirect.
     *
     * @return null|bool {@link execute()}
     */
    protected function followRedirect()
    {
        if (
            $this->followRedirect
            && ($location = $this->getResponseHeaders('location'))
            && ($this->maxRedirect === true || $this->redirectedCount < $this->maxRedirect)
        ) {
            $location = $this->getAbsoluteUrl($location, $this->target);
            $referer = isset($this->headers['Referer']) ? $this->headers['Referer'] : null;
            $userAgent = $this->userAgent;

            $this->redirectedCount++;
            $this->redirectedUrls[] = $this->getTarget();
            $this->redirectedRequests[] = $this->getClone();

            // remove old request.
            $this->resetRequest();
            $this->setUserAgent($userAgent);
            if ($referer) {
                $this->setReferer($referer);
            }
            $firstRequest = $this->redirectedRequests[0];
            $this->setCookies($firstRequest->getCookies());

            $urlInfo = parse_url($location) + array('path' => '');
            foreach ($this->redirectedRequests as $obj) {
                $objHost = $obj->getHost();
                foreach ($obj->getResponseArrayCookies() as $cookie) {
                    if (empty($cookie['domain'])) {
                        $cookie['domain'] = $objHost;
                    }
                    if ($objHost == $urlInfo['host']
                        || $this->isValidCookieForHost($cookie, $urlInfo['host'], $urlInfo['path'])
                    ) {
                        $this->setCookies($cookie);
                    }
                }
            }
            // remove old responses.
            $this->resetResponse();

            return $this->execute($location);
        }

        return;
    }

    /**
     * Don't use __clone(), because sometimes
     * we need to clone everything of current object.
     *
     * @return self
     */
    public function getClone()
    {
        $obj = clone $this;
        $obj->redirectedRequests = array();
        $obj->redirectedUrls = array();

        return $obj;
    }

    /**
     * Parse response headers.
     *
     * @param string $headers
     */
    protected function parseResponseHeaders($headers)
    {
        $this->responseHeaders = array();
        $lines = explode("\n", $headers);
        foreach ($lines as $line) {
            if ($line = trim($line)) {
                // parse headers to array
                if (!isset($this->responseHeaders['status'])
                    && preg_match('#HTTP/.*?\s+(\d+)#i', $line, $match)
                ) {
                    $this->responseStatus = intval($match[1]);
                    $this->responseHeaders['status'] = $line;
                } elseif (strpos($line, ':')) {
                    list($key, $value) = explode(':', $line, 2);
                    $value = ltrim($value);
                    $key = strtolower($key);
                    // parse cookie
                    if ($key == 'set-cookie') {
                        $this->responseCookies .= $value.';';

                        if ($cookie = $this->parseCookie($value)) {
                            if (empty($cookie['domain'])) {
                                $cookie['domain'] = $this->host;
                            }
                            $this->responseArrayCookies[$cookie['name']] = $cookie;
                        }
                        if (!isset($this->responseHeaders[$key])) {
                            $this->responseHeaders[$key] = array();
                        }
                    }
                    if (array_key_exists($key, $this->responseHeaders)) {
                        if (!is_array($this->responseHeaders[$key])) {
                            $temp = $this->responseHeaders[$key];
                            unset($this->responseHeaders[$key]);
                            $this->responseHeaders[$key][] = $temp;
                            $this->responseHeaders[$key][] = $value;
                        } else {
                            $this->responseHeaders[$key][] = $value;
                        }
                    } else {
                        $this->responseHeaders[$key] = $value;
                    }
                }
            }
        }
    }

    /**
     * Get redirected count.
     *
     * @return int
     */
    public function getRedirectedCount()
    {
        return $this->redirectedCount;
    }

    /**
     * Get response status code.
     *
     * @return int
     */
    public function getResponseStatus()
    {
        return $this->responseStatus;
    }

    /**
     * Get response cookies.
     *
     * @return string
     */
    public function getResponseCookies()
    {
        return $this->responseCookies;
    }

    /**
     * Get response cookies by array with keys:
     * "name", "value", "path", "expires", "domains", "secure", "httponly".
     * If response cookie does not provides the keys, default is null.
     *
     * @param string|null $name Null to get all cookies
     *
     * @return array|false False if cookie name is not exist.
     */
    public function getResponseArrayCookies($name = null)
    {
        if ($name !== null) {
            if (array_key_exists($name, $this->responseArrayCookies)) {
                return $this->responseArrayCookies[$name];
            }

            return false;
        }

        return $this->responseArrayCookies;
    }

    /**
     * Get response headers.
     *
     * @param string|null $name Null to get all headers
     *
     * @return mixed|bool False If get header by name and it is not exist
     */
    public function getResponseHeaders($name = null)
    {
        if ($name !== null) {
            if (array_key_exists($name, $this->responseHeaders)) {
                return $this->responseHeaders[$name];
            }

            return false;
        }

        return $this->responseHeaders;
    }

    /**
     * Get response text.
     *
     * @return string
     */
    public function getResponseText()
    {
        return $this->responseText;
    }

    /**
     * Get response text.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getResponseText();
    }

    /**
     * Determine if method is put/post.
     *
     * @return bool
     */
    public function isPut()
    {
        return $this->method === 'POST' || $this->method === 'PUT';
    }

    /**
     * There is a bug while using parse_str function built in PHP.
     *
     * @example
     *     @code   : parse_str('.a=1&.b=2', $array);
     *     @output : array('_a' => 1, '_b' => 2);
     *     @expect : array('.a' => 1, '.b' => 2);
     *
     * The issue occured when i try to make a script automatic login Yahoo.
     * So we just create the method to get the expected result.
     *
     * @since 2.5.4
     *
     * @param string $query
     * @param array  &$array
     */
    protected function parseParameters($query, &$array = array())
    {
        $array = array();
        foreach (explode('&', $query) as $param) {
            // handle if set parameters without value.
            list($key, $value) = explode('=', $param, 2) + array(0, '');
            if (preg_match_all('#\[([^\]]+)?\]#i', $key, $matches)) {
                $key = str_replace($matches[0], '', $key);
                if (!isset($array[$key])) {
                    $array[$key] = array();
                }
                $children = & $array[$key];
                $deth = array();
                foreach ($matches[1] as $sub) {
                    $sub = $sub !== '' ? $sub : count($children);
                    if (!array_key_exists($sub, $children)) {
                        $children[$sub] = array();
                    }
                    $children = & $children[$sub];
                }
                $children = urldecode($value);
            } else {
                $array[$key] = urldecode($value);
            }
        }

        return $array;
    }

    /**
     * Get absolute url for following redirect.
     *
     * @param string $relative
     * @param string $base
     *
     * @return string
     */
    protected function getAbsoluteUrl($relative, $base)
    {
        // remove query string
        $base = preg_replace('#(\?|\#).*?$#', '', $base);

        if (parse_url($relative, PHP_URL_SCHEME) != '') {
            return $relative;
        }
        if ($relative[0] == '#' || $relative[0] == '?') {
            return $base.$relative;
        }
        extract(parse_url($base));

        $path = preg_replace('#/[^/]*$#', '', $path);

        if ($relative[0] == '/') {
            $path = '';
        }
        $absolute = $host.$path.'/'.$relative;

        $patterns = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
        for ($count = 1; $count > 0; $absolute = preg_replace($patterns, '/', $absolute, -1, $count)) {
        }

        return $scheme.'://'.$absolute;
    }

    /**
     * Read binary data of file.
     *
     * @param string $filePath
     *
     * @return string Binary data
     */
    protected function getFileData($filePath)
    {
        $data = '';
        if (file_exists($filePath)) {
            ob_start();
            readfile($filePath);
            $data = ob_get_clean();
        }

        return $data;
    }

    /**
     * Get mime type of file.
     *
     * @param string $filePath
     *
     * @return string
     */
    protected function getFileType($filePath)
    {
        $filename = realpath($filePath);
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (preg_match('/^(?:jpe?g|png|[gt]if|bmp|swf)$/', $extension)) {
            $file = getimagesize($filename);

            if (isset($file['mime'])) {
                return $file['mime'];
            }
        }
        if (class_exists('finfo', false)) {
            if ($info = new finfo(defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME)) {
                return $info->file($filename);
            }
        }
        if (ini_get('mime_magic.magicfile') && function_exists('mime_content_type')) {
            return mime_content_type($filename);
        }

        return false;
    }
}
