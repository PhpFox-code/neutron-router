<?php

namespace Phpfox\Router;


class StandardRoute implements RouteInterface
{
    /**
     * @var  string
     */
    protected $expression;

    /**
     * @var string
     */
    protected $name;

    /**
     * regular methods
     */
    protected $methods;
    /**
     * @var  string  route URI
     */
    protected $uri;
    /**
     * @var string
     */
    protected $host;
    /**
     * @var array
     */
    protected $hostExpression = [];
    /**
     * @var string
     */
    protected $hostCompiledExpression;
    /**
     * @var string
     */
    protected $protocol = 'http://';

    /**
     * @var string
     */
    protected $delegate;

    /**
     * @var  array
     */
    protected $defaults = [];

    /**
     * @param array $params
     */
    public function __construct($params)
    {
        $this->name = $params['name'];

        if (isset($params['uri'])) {
            $this->uri = $params['uri'];
        }

        if (isset($params['host'])) {
            $this->host = $params['host'];
        }

        if (isset($params['defaults'])) {
            $this->defaults = $params['defaults'];
        }

        if (!empty($params['delegate'])) {
            $this->setDelegate($params['delegate']);
        }

        if ($this->uri) {
            $this->expression = $this->compile($this->uri,
                isset($params['wheres']) ? $params['wheres'] : []);
        }

        if ($this->host) {
            $this->hostExpression = $this->compile($this->host,
                isset($param['host_expr']) ? $params['host_expr'] : []);
        }
    }

    /**
     * Compile uri
     *
     * @param string $uri
     * @param array  $regex
     *
     * @return string
     */
    public function compile($uri, $regex = [])
    {
        // The URI should be considered literal except for keys and optional parts
        // Escape everything preg_quote would escape except for : ( ) < >
        $expression = preg_replace('#' . '[.\\+*?[^\\]${}=!|]' . '#', '\\\\$0',
            $uri);

        if (strpos($expression, '(') !== false) {
            // Make optional parts of the URI non-capturing and optional
            $expression = str_replace(['(', ')',], ['(?:', ')?',], $expression);
        }

        // Insert default regex for keys
        $expression = str_replace(['<', '>',], ['(?P<', '>' . '[^/]++' . ')',],
            $expression);

        if ($regex) {
            $search = $replace = [];
            foreach ($regex as $key => $value) {
                $search[] = "<$key>" . '[^/]++';
                $replace[] = "<$key>$value";
            }
            $expression = str_replace($search, $replace, $expression);
        }

        return '#^' . $expression . '$#u';
    }

    /**
     * @param string $expression
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
    }

    /**
     * @param mixed $methods
     */
    public function setMethods($methods)
    {
        $this->methods = $methods;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @return array
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * @param array $defaults
     */
    function setDefaults($defaults)
    {
        $this->defaults = $defaults;
    }

    /**
     * @return string
     */
    public function getDelegate()
    {
        return $this->delegate;
    }

    /**
     * @param string $delegate
     */
    public function setDelegate($delegate)
    {
        $this->delegate = $delegate;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        $uri,
        $host = null,
        $method = null,
        $result,
        $is_children = false
    ) {
        $params = [];

        if ($host && $this->host) {
            if (!preg_match($this->hostCompiledExpression, $host, $matches)) {
                return false;
            }

            foreach ($matches as $key => $value) {
                if (is_int($key)) {
                    continue;
                }
                // Set the value for all matched keys
                $params[$key] = $value;
            }
        }

        if ($uri && $this->uri) {
            if (!preg_match($this->expression, $uri, $matches)) {
                return false;
            }

            foreach ($matches as $key => $value) {
                if (is_int($key)) {
                    continue;
                }

                // Set the value for all matched keys
                $params[$key] = $value;
            }
        }

        foreach ($this->defaults as $key => $value) {
            if (!isset($params[$key]) OR $params[$key] === '') {
                // Set default values for any key that was not matched
                $params[$key] = $value;
            }
        }

        if (!$is_children) {
            $result->setParams($params);
        } else {
            $result->addVars($params);
        }

        if (false == $this->filter($result)) {
            return false;
        }

        if (\app()->routing()
            ->resolveChildren($this->delegate, $uri, $host, $method, $result)
        ) {
            return true;
        }

        if (\app()->routing()
            ->resolveChildren($this->name, $uri, $host, $method, $result)
        ) {
            return true;
        }

        return true;
    }

    /**
     * @param Result $result
     *
     * @return bool
     */
    protected function filter(Result $result)
    {
        if ($result) {
            ;
        }
        return true;
    }

    /**
     * Generates a URI for the current route based on the parameters given.
     *
     * @param   array $params URI parameters
     *
     * @return  string
     * @uses    Route::REGEX_GROUP
     * @uses    Route::REGEX_KEY
     */
    public function getUrl($params = [])
    {
        /**
         * strict check
         */
        if (!is_array($params)) {
            $params = [];
        }

        $defaults = $this->defaults;
        $usages = [];

        /**
         * @param int   $portion
         * @param bool  $required
         * @param array $usages
         *
         * @return array
         */
        $compile = function ($portion, $required, &$usages) use (
            &$compile,
            $defaults,
            $params
        ) {

            $missing = [];

            $pattern = '#(?:<([a-zA-Z0-9_]++)>|\(((?:(?>[^()]+)|(?R))*)\))#';

            $result = preg_replace_callback($pattern, function ($matches) use (
                &$compile,
                $defaults,
                &$missing,
                $params,
                &$required,
                &$usages
            ) {
                if ($matches[0][0] === '<') {
                    // Parameter, unwrapped
                    $param = $matches[1];

                    if (isset($params[$param])) {
                        // This portion is required when a specified
                        // parameter does not match the default
                        $required = ($required OR !isset($defaults[$param])
                            OR $params[$param] !== $defaults[$param]);

                        $usages[$param] = 1;

                        // Add specified parameter to this result
                        return $params[$param];
                    }

                    // Add default parameter to this result
                    if (isset($defaults[$param])) {
                        return $defaults[$param];
                    }

                    // This portion is missing a parameter
                    $missing[] = $param;
                } else {
                    // Group, unwrapped
                    $result = $compile($matches[2], false, $usages);

                    if ($result[1]) {
                        // This portion is required when it contains a group
                        // that is required
                        $required = true;

                        // Add required groups to this result
                        return $result[0];
                    }

                    // Do not add optional groups to this result
                }
            }, $portion);

            if ($required AND $missing) {
                throw new \InvalidArgumentException("missing route param {'"
                    . implode(',', $missing) . "'}");
            }

            return [
                $result,
                $usages,
                $required,
            ];
        };

        list($uri, $usages) = $compile($this->uri, true, $usages);

        $queryString = '';
        $query = array_diff_key($params, $usages);

        if (!empty($query)) {
            $queryString = '?' . http_build_query($query, null, '&');
        }


        // Trim all extra slashes from the URI
        $uri = preg_replace('#//+#', '/', rtrim($uri, '/'));

        if ($this->isExternal()) {
            // Need to add the host to the URI
            $host = $this->defaults['host'];

            if (strpos($host, '://') === false) {
                // Use the default defined protocol
                $host = $this->protocol . $host;
            }

            // Clean up the host and prepend it to the URI
            $uri = rtrim($host, '/') . '/' . PHPFOX_BASE_DIR . $uri
                . $queryString;
        }

        return PHPFOX_BASE_URL . $uri . $queryString;
    }

    /**
     * Returns whether this route is an external route
     * to a remote controller.
     *
     * @return  boolean
     */
    public function isExternal()
    {
        return isset($this->defaults['host']) && $this->defaults['host'];
    }

    /**
     * @ignore
     * @codeCoverageIgnore
     *
     * @param $tokens
     *
     * @return array
     */
    protected function correctTokensForChildRoute($tokens)
    {

        $result = [];

        foreach ($tokens as $key => $value) {
            $key = preg_replace('(\W+)', '', $key);
            $value = '/' . trim($value, '/');
            $key1 = '(/<' . $key . '>)';
            $key2 = '/<' . $key . '>';


            $result[$key1] = $value;
            $result[$key2] = $value;

        }

        return $result;
    }
}