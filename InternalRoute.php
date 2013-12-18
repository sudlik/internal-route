<?php

/** Internal Router
 * @brief Inspired by Kohana Route
 */
class Internal
{
    const MESSAGE_CONSTRUCT = 'Invalid parameter: :type';
    const MESSAGE_SET       = 'Invalid parameters: :name, :path';
    const MESSAGE_GET       = 'Invalid parameter :name';
    const MESSAGE_REQUIRED  = 'Missing parameter :param';
    const MESSAGE_TRANSFORM = 'Transform failed';
    const MESSAGE_SECURE    = 'Unsafe parameter :param';
    const PATTERN_SEPARATOR = '#/+|\\\\+#';
    const PATTERN_VARIABLE  = '#\<([^\<\>]+)\>#';
    const PATTERN_OPTIONAL  = '#(\(.*)?\(([^\(\)]+)\)(.*\))?#';
    const PATTERN_SECURE    = '#(/\.\.?)|(\.?\./)#';
    
    private static $CHARTOTOKEN = [
        '#<#'  => 'INTERNAL_TOKEN_A',
        '#>#'  => 'INTERNAL_TOKEN_B',
        '#\(#' => 'INTERNAL_TOKEN_C',
        '#\)#' => 'INTERNAL_TOKEN_D',
    ];

    private static $TOKENTOCHAR = [
        '#INTERNAL_TOKEN_A#' => '<',
        '#INTERNAL_TOKEN_B#' => '>',
        '#INTERNAL_TOKEN_C#' => '(',
        '#INTERNAL_TOKEN_D#' => ')',
    ];
    
    private static $PATH  = [];
    private static $CACHE = FALSE;
    
    private $Type;
    private $SplFileInfo;
    private $Last;
    
    public function __construct(SplFileInfo $SplFileInfo, $type = false, $last = false)
    {
        $this->Type        = $type;
        $this->SplFileInfo = $SplFileInfo;
        $this->Last        = $last;
    }
    
    public function __destruct()
    {
        if (self::$CACHE) {
            Cache::instance()->set(__CLASS__, self::$PATH);
        }
    }
    
    private static function get_helper($match)
    {
        $return = strstr($match[ 2 ], '<') ? '' : $match[2];

        if ($match[1]) {
            return preg_replace_callback(self::PATTERN_OPTIONAL, ['self', 'get_helper'], $match[1] . $return . $match[3]);
        } else {
            return $return;
        }
    }
    
    private static function name($name)
    {
        return is_scalar($name) || is_null($name);
    }
    
    private static function assert($test, $message, $params)
    {
        if ($test) {
            throw new Exception(strtr($message, $params));

            return false;
        } else {
            return true;
        }
    }
    
    public static function charToToken($string)
    {
        return preg_replace(array_keys(self::$CHARTOTOKEN), self::$CHARTOTOKEN, $string);
    }
    
    public static function tokenToChar($string)
    {
        return preg_replace(array_keys(self::$TOKENTOCHAR), self::$TOKENTOCHAR, $string);
    }
    
    public static function cache($cache = null)
    {
        if (is_bool($cache)) {
            self::$CACHE = $cache;
        } else {
            self::$PATH = Cache::instance()->get(__CLASS__);
            return self::$PATH;
        }
    }
    
    public static function set($name = 'default', $path = '', $type = null, array $default = [])
    {
        if (self::assert(self::name($name) && is_string($path), self::MESSAGE_SET,[':name' => $name, ':path' => $path])) {
            self::$PATH[$name] = [
                'path'      => preg_replace(
                    self::PATTERN_SEPARATOR,
                    DIRECTORY_SEPARATOR,
                    $path
                ),
                'default'   => $default,
                'type'      => $type,
            ];
        }
    }
    
    public static function get($name = 'default', array $param = [])
    {
        if (self::assert(self::name($name) && isset(self::$PATH[$name]), self::MESSAGE_GET, [':name' => $name])) {
            foreach ( $param as $v ) {
                self::assert(!preg_match(self::PATTERN_SECURE, $v), self::MESSAGE_SECURE, [':param' => $v]);
            }
            
            $default    = self::$PATH[$name]['default'];
            $type       = self::$PATH[$name]['type'];
            $self       = __CLASS__;

            $path = self::_tokenToChar(
                preg_replace_callback(
                    self::PATTERN_OPTIONAL,
                    array('self', '_get_helper'),
                    preg_replace_callback(
                        self::PATTERN_VARIABLE,
                        function ($match) use ($param, $default, $self) {
                            if (isset($param[$match[1]])) {
                                return $self::charToToken($param[$match[1]]);
                            } elseif (isset($default[$match[1]])) {
                                return $self::charToToken($default[$match[1]]);
                            } else {
                                return $match[0];
                            }
                        },
                        self::$PATH[$name]['path']
                    )
                )
            );

            $SplFileInfo = new SplFileInfo($path);
            
            self::assert(!strstr($path, '<'), self::MESSAGE_TRANSFORM);
            
            if (!$type && $SplFileInfo->isReadable()) {
                $type = File::mime($path);
            }
            return new self($SplFileInfo, $type, !!preg_match('#\\' . DIRECTORY_SEPARATOR . '$#', $path));
        }
    }
    
    public function readable()
    {
        return $this->SplFileInfo->isReadable();
    }
    
    public function writable()
    {
        return $this->SplFileInfo->isWritable();
    }
    
    public function path()
    {
        return $this->SplFileInfo->getPathname() . ($this->Last ? DIRECTORY_SEPARATOR : '');
    }
    
    public function type()
    {
        if (!$this->Type && $this->SplFileInfo->isReadable()) {
            $this->Type = File::mime($this->SplFileInfo->getPathname());
        }

        return $this->Type;
    }
    
    public function modified()
    {
        return $this->SplFileInfo->getMTime();
    }
    
    public function file()
    {
        return $this->SplFileInfo->isFile();
    }
    
    public function dir()
    {
        return $this->SplFileInfo->isDir();
    }
}