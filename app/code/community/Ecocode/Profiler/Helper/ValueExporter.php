<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Ecocode_Profiler_Helper_ValueExporter
    extends Mage_Core_Helper_Abstract
{
    /**
     * Converts a PHP value to a string.
     *
     * @param mixed $value The PHP value
     * @param int   $depth only for internal usage
     * @param bool  $deep only for internal usage
     *
     * @return string The string representation of the given value
     */
    public function exportValue($value, $depth = 1, $deep = false)
    {
        if (is_object($value)) {
            if ($value instanceof \DateTimeInterface) {
                return sprintf('Object(%s) - %s', get_class($value), $value->format(\DateTime::ISO8601));
            }

            return sprintf('Object(%s)', get_class($value));
        }

        if ($value instanceof \__PHP_Incomplete_Class) {
            return sprintf('__PHP_Incomplete_Class(%s)', $this->getClassNameFromIncomplete($value));
        }

        if (is_array($value)) {
            if (empty($value)) {
                return '[]';
            }

            $indent = str_repeat('  ', $depth);

            $all = [];
            foreach ($value as $key => $val) {
                if (is_array($val)) {
                    $deep = true;
                }
                $all[] = sprintf('%s => %s', $key, $this->exportValue($val, $depth + 1, $deep));
            }

            if ($deep) {
                return sprintf("[\n%s%s\n%s]", $indent, implode(sprintf(", \n%s", $indent), $all), str_repeat('  ', $depth - 1));
            }

            $str = sprintf('[%s]', implode(', ', $all));

            if (80 > strlen($str)) {
                return $str;
            }

            return sprintf("[\n%s%s\n]", $indent, implode(sprintf(",\n%s", $indent), $all));
        }

        if (is_resource($value)) {
            return sprintf('Resource(%s#%d)', get_resource_type($value), $value);
        }

        if (null === $value) {
            return 'null';
        }

        if (false === $value) {
            return 'false';
        }

        if (true === $value) {
            return 'true';
        }

        return (string)$value;
    }

    private function getClassNameFromIncomplete(\__PHP_Incomplete_Class $value)
    {
        $array = new \ArrayObject($value);

        return $array['__PHP_Incomplete_Class_Name'];
    }
}
