<?php


/**
 * Twig extension relate to PHP code and used by the profiler and the default exception templates.
 *
 *
 * @author Original Author Fabien Potencier <fabien@symfony.com>
 */
class Ecocode_Profiler_Helper_Code
{
    private $fileLinkFormat;
    private $hostRootDir;
    private $rootDir;
    private $charset;
    private $config;

    /**
     * Constructor.
     * @param null        $format
     * @param null        $rootDir
     * @param null|string $charset
     */
    public function __construct($format = null, $rootDir = null, $charset = 'UTF-8', Ecocode_Profiler_Model_Config $config = null)
    {
        $this->config         = $config ? $config : Mage::getSingleton('ecocode_profiler/config');
        $format               = $format ? $format : $this->config->getValue('file_link_format');
        $this->fileLinkFormat = $format ? $format : ini_get('xdebug.file_link_format') ?: get_cfg_var('xdebug.file_link_format');
        $this->rootDir        = $rootDir ? $rootDir : str_replace('/', DIRECTORY_SEPARATOR, dirname(Mage::getRoot())) . DIRECTORY_SEPARATOR;
        $this->charset        = $charset;
    }

    public function abbrClass($class)
    {
        $parts = explode('\\', $class);
        $short = array_pop($parts);

        return sprintf('<abbr title="%s">%s</abbr>', $class, $short);
    }

    public function abbrMethod($method)
    {
        if (false !== strpos($method, '::')) {
            list($class, $method) = explode('::', $method, 2);
            $result = sprintf('%s::%s()', $this->abbrClass($class), $method);
        } elseif ('Closure' === $method) {
            $result = sprintf('<abbr title="%s">%s</abbr>', $method, $method);
        } else {
            $result = sprintf('<abbr title="%s">%s</abbr>()', $method, $method);
        }

        return $result;
    }

    /**
     * Formats an array as a string.
     *
     * @param array $args The argument array
     *
     * @return string
     */
    public function formatArgs($args)
    {
        $result = [];
        foreach ($args as $key => $item) {
            if ('object' === $item[0]) {
                $parts          = explode('\\', $item[1]);
                $short          = array_pop($parts);
                $formattedValue = sprintf('<em>object</em>(<abbr title="%s">%s</abbr>)', $item[1], $short);
            } elseif ('array' === $item[0]) {
                $formattedValue = sprintf('<em>array</em>(%s)', is_array($item[1]) ? $this->formatArgs($item[1]) : $item[1]);
            } elseif ('string' === $item[0]) {
                $formattedValue = sprintf("'%s'", htmlspecialchars($item[1], ENT_QUOTES, $this->charset));
            } elseif ('null' === $item[0]) {
                $formattedValue = '<em>null</em>';
            } elseif ('boolean' === $item[0]) {
                $formattedValue = '<em>' . strtolower(var_export($item[1], true)) . '</em>';
            } elseif ('resource' === $item[0]) {
                $formattedValue = '<em>resource</em>';
            } else {
                $formattedValue = str_replace("\n", '', var_export(htmlspecialchars((string)$item[1], ENT_QUOTES, $this->charset), true));
            }

            $result[] = is_int($key) ? $formattedValue : sprintf("'%s' => %s", $key, $formattedValue);
        }

        return implode(', ', $result);
    }

    /**
     * Formats an array as a string.
     *
     * @param array $args The argument array
     *
     * @return string
     */
    public function formatArgsAsText($args)
    {
        return strip_tags($this->formatArgs($args));
    }

    /**
     * Returns an excerpt of a code file around the given line number.
     *
     * @param string $file A file path
     * @param int    $line The selected line number
     *
     * @return string An HTML string
     */
    public function fileExcerpt($file, $line)
    {
        if (is_readable($file)) {
            // highlight_file could throw warnings
            // see https://bugs.php.net/bug.php?id=25725
            $code = @highlight_file($file, true);
            // remove main code/span tags
            $code    = preg_replace('#^<code.*?>\s*<span.*?>(.*)</span>\s*</code>#s', '\\1', $code);
            $content = preg_split('#<br />#', $code);

            $lines = [];
            for ($i = max($line - 3, 1), $max = min($line + 3, count($content)); $i <= $max; ++$i) {
                $lines[] = '<li' . ($i == $line ? ' class="selected"' : '') . '><code>' . self::fixCodeMarkup($content[$i - 1]) . '</code></li>';
            }

            return '<ol start="' . max($line - 3, 1) . '">' . implode("\n", $lines) . '</ol>';
        }
    }

    /**
     * @param      $class
     * @param null $text
     * @return string
     */
    public function formatClass($class, $text = null)
    {
        $reflectionClass = new ReflectionClass($class);

        if ($text === null) {
            $text = $reflectionClass->getName();
        }

        return $this->formatFile(
            $reflectionClass->getFileName(),
            $reflectionClass->getStartLine(),
            $text
        );
    }

    /**
     * @param      $class
     * @param      $method
     * @param null $text
     * @return string
     */
    public function formatClassMethod($class, $method, $text = null)
    {
        $reflectionMethod = new ReflectionMethod($class, $method);

        if ($text === null) {
            $text = $reflectionMethod->getDeclaringClass()->getName() . ':' . $method;
        }

        return $this->formatFile(
            $reflectionMethod->getFileName(),
            $reflectionMethod->getStartLine(),
            $text
        );
    }


    /**
     * Formats a file path.
     *
     * @param string $file An absolute file path
     * @param int    $line The line number
     * @param string $text Use this text for the link rather than the file path
     *
     * @return string
     */
    public function formatFile($file, $line = 0, $text = null)
    {
        $flags = ENT_QUOTES | ENT_SUBSTITUTE;
        if (null === $text) {
            $file = trim($file);
            $text = str_replace('/', DIRECTORY_SEPARATOR, $file);
            if (0 === strpos($text, $this->rootDir)) {
                $text = substr($text, strlen($this->rootDir));
                $text = explode(DIRECTORY_SEPARATOR, $text, 2);
                $text = sprintf('<abbr title="%s%2$s">%s</abbr>%s', $this->rootDir, $text[0], isset($text[1]) ? DIRECTORY_SEPARATOR.$text[1] : '');
            }

            $text = sprintf('%s at line %d', $text, $line);
        }

        if (false !== $link = $this->getFileLink($file, $line)) {
            return sprintf('<a target="_blank" href="%s" title="Click to open this file" class="file_link">%s</a>', htmlspecialchars($link, $flags, $this->charset), $text);
        }

        return $text;
    }

    /**
     * Returns the link for a given file/line pair.
     *
     * @param string $file An absolute file path
     * @param int    $line The line number
     *
     * @return string A link of false
     */
    public function getFileLink($file, $line = 0)
    {
        if ($this->fileLinkFormat && is_file($file)) {
            return strtr($this->fileLinkFormat, ['%f' => $this->getHostFilePath($file), '%l' => (int)$line]);
        }

        return false;
    }

    public function getHostFilePath($file)
    {
        if ($hostRoot = $this->getHostRoot()) {
            $file = str_replace($this->rootDir, $hostRoot, $file);
        };

        return $file;
    }

    protected function getHostRoot()
    {
        if ($this->hostRootDir === null) {
            $hostRootDir = $this->config->getValue('host_magento_root', false);
            if ($hostRootDir) {
                $hostRootDir = '/' . trim($hostRootDir, '/') . '/';
            }
            $this->hostRootDir = $hostRootDir;
        }

        return $this->hostRootDir;
    }

    public function formatFileFromText($text)
    {
        return preg_replace_callback('/in ("|&quot;)?(.+?)\1(?: +(?:on|at))? +line (\d+)/s', function ($match) {
            return 'in ' . $this->formatFile($match[2], $match[3]);
        }, $text);
    }

    protected static function fixCodeMarkup($line)
    {
        // </span> ending tag from previous line
        $opening = strpos($line, '<span');
        $closing = strpos($line, '</span>');
        if (false !== $closing && (false === $opening || $closing < $opening)) {
            $line = substr_replace($line, '', $closing, 7);
        }

        // missing </span> tag at the end of line
        $opening = strpos($line, '<span');
        $closing = strpos($line, '</span>');
        if (false !== $opening && (false === $closing || $closing > $opening)) {
            $line .= '</span>';
        }

        return $line;
    }
}

