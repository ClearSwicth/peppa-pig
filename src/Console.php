<?php
/**
 *
 * User: daikai
 * Date: 2023/3/10
 */
namespace  Clearswitch\PeppaPigCli;

/**
 * php cli color
 * @package Package\peppa_pig\src
 */
class Console
{

    /**
     * 字体颜色
     * @var array
     */
    static private $style = [
        'none' => null,
        'bold' => '1',
        'dark' => '2',
        'italic' => '3',
        'underline' => '4',
        'blink' => '5',
        'reverse' => '7',
        'concealed' => '8',
        'default' => '39',
        'black' => '30',
        'red' => '31',
        'green' => '32',
        'yellow' => '33',
        'blue' => '34',
        'magenta' => '35',
        'cyan' => '36',
        'light_gray' => '37',
        'dark_gray' => '90',
        'light_red' => '91',
        'light_green' => '92',
        'light_yellow' => '93',
        'light_blue' => '94',
        'light_magenta' => '95',
        'light_cyan' => '96',
        'white' => '97',
        'bg_default' => '49',
        'bg_black' => '40',
        'bg_red' => '41',
        'bg_green' => '42',
        'bg_yellow' => '43',
        'bg_blue' => '44',
        'bg_magenta' => '45',
        'bg_cyan' => '46',
        'bg_light_gray' => '47',
        'bg_dark_gray' => '100',
        'bg_light_red' => '101',
        'bg_light_green' => '102',
        'bg_light_yellow' => '103',
        'bg_light_blue' => '104',
        'bg_light_magenta' => '105',
        'bg_light_cyan' => '106',
        'bg_white' => '107',
    ];

    /**
     * 输出
     * @param $text
     * @author clearSwitch
     */
    public static function outPut($text)
    {
        fwrite(STDOUT, $text . PHP_EOL);
    }

    /**
     * 设置样式
     * @param $text
     * @param mixed ...$formats
     * @author clearSwitch
     */
    static public function setStyle($text, ...$formats)
    {
        $style = implode(';', static::verify($formats));
        return "\033[" . $style . "m" . $text . "\033[0m";
    }

    /**
     * 样式的叠加
     * @param $formats
     * @return array
     * @author clearSwitch
     */
    protected static function verify($formats)
    {
        $style = [];
        foreach ($formats as $format) {
            if (!empty(static::$style[$format])) {
                $style[] = static::$style[$format];
            }
        }
        return $style;
    }

    /**
     * 进度条
     * @param int $size
     * @param int $count
     * @param int $width
     * @param string $prefix
     * @author clearSwitch
     */
    public static function process(int $size, int $count, int $width, $prefix = null)
    {
        $ratio = $size / $count;
        $num = ($ratio * $width);
        $completed = str_repeat(isset($prefix) ? $prefix : '|', $num);//的条数
        $undone = str_repeat(' ', $width - $num);//空的是多少
        $percent = sprintf("%.2f", ($ratio * 100)); //替换成百分比
        static::setProcessStyle($completed . $undone, $percent);
    }

    /**
     * 设置进度条的样式
     * @param $content
     * @param $percent
     * @author clearSwitch
     */
    protected static function setProcessStyle($content, $percent)
    {
        $hideCursor = "\033[?25l";
        $content = "\033[32m" . $content . "\033[33m" . $percent . "%";
        $firstCursor = "\033[105D"; ////移动光标到行首，105是进度条最大长度，再大点没关系
        $end = "";
        if (100 == $percent) {
            $end = "\n\33[?25h";
        }
        fwrite(STDOUT, $hideCursor . $content . $firstCursor . $end);
    }

    /**
     * 输出表格
     * @param array $data
     * @param array $headers
     * @param mixed ...$formats
     * @author clearSwitch
     */
    static public function table(array $data, array $headers = [], ...$formats)
    {
        $point = "+";
        $middle = '|';
        $bottom = '-';
        $leftTop = "┌";
        $leftBottom = '└';
        $rightTop = '┐';
        $rightBottom = '┘';
        $columnsWidth = static::computeColumnsWidth($data, $headers,);
        $horizontals = [];
        foreach ($columnsWidth as $width) {
            $horizontals[] = str_repeat($bottom, $width + 1);
        }
        $topBorder = $leftTop . implode($point, $horizontals) . $rightTop;
        $bottomBorder = $leftBottom . implode($point, $horizontals) . $rightBottom;
        $middleRow = $point . implode($point, $horizontals) . $point;
        static::outPut($topBorder);
        if (count($headers)) {
            array_unshift($data, $headers);
        }
        foreach ($data as $index => $item) {
            if ($index !== 0) {
                static::output($middleRow);
            }
            $content = "";
            foreach ($item as $key => $value) {
                $widthDiff = $columnsWidth[$key] - static::stringWidth($value);
                $element = ' ' . $value;
                if ($widthDiff > 0) {
                    $element .= str_repeat(' ', $widthDiff);
                }
                if (count($formats) > 0) {
                    $content .= $middle . static::setStyle($element, ...$formats);
                } else {
                    $content .= $middle . $element;
                }
            }
            static::outPut($content . $middle);
        }
        static::outPut($bottomBorder);
    }

    /**
     * 计算每一列的最大宽度
     * @param $data
     * @param $header
     * @return array
     * @author clearSwitch
     */
    public static function computeColumnsWidth($data, $header)
    {
        array_unshift($data, $header);
        $columnsWidth = [];
        foreach ($data as $item) {
            if (is_array($item)) {
                foreach ($item as $index => $row) {
                    $length = static::stringWidth($row);
                    if (empty($columnsWidth[$index])) {
                        $columnsWidth[$index] = $length;
                    }
                    if ($length > $columnsWidth[$index]) {
                        $columnsWidth[$index] = $length;
                    }
                }
            } else {
                throw new \ Exception("必须是一个二维数组");
            }
        }
        return $columnsWidth;
    }

    /**
     * 计算字符串的长度
     * @param $string
     * @return false|float|int
     * @author clearSwitch
     */
    public static function stringWidth($string)
    {
        $string = static::removeStyle($string);
        $length = iconv_strlen($string);
        $length += (strlen($string) - $length) / 2;
        return $length;
    }

    /**
     * 除去颜色
     * @param $string
     * @return string|string[]|null
     * @author clearSwitch
     */
    public static function removeStyle($string)
    {
        return preg_replace('/\033\[(\d|\d\d)(;(\d|\d\d)){0,}m/', '', $string);
    }
}