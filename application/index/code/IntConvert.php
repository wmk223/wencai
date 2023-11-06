<?php
/**
 * FileName: IntConvert.php
 * ==============================================
 * Copy right 2016-2018
 * ----------------------------------------------
 * This is not a free software, without any authorization is not allowed to use and spread.
 * ==============================================
 * @author: kong | <iwhero@yeah.com>
 * @date  : 2023/6/7 08:42
 */
declare (strict_types = 1);

namespace app\index\code;


class IntConvert
{

    /**
     * KeyMap 在初始时，建议重新生成一下
     */
    static private $keyMap = [
        'ECVSFGBONHUY6IAM8LXQRZW497D21K35',
        'BQXR1EDKLFUW96NIG28AYC3SVO5ZH7M4',
        'F4U96M8BI5RGADSKCQ7VOXZYENLH31W2',
        'H4AM9SL6F2OICUKB1NX7EDZQV8RY5G3W',
        '54Q7UVCBO6MFZ1SIE2NRYAD9KX3HWG8L',
        'UK3IDNFEVC5H9G7RWSLQ6X4Z281OBYMA',
        'XC64INVUEF9KHD8Z2OQBMSGA3LY71W5R',
        'F9BHIGC15AORNDV2684WY3ZU7QMLSEKX',
        'RG412OZKANQ7W63FHCBUYXED9S5LIV8M',
        'VCUBY7XS4MN52D1LHFRQ6OZG98KAI3WE',
        '6AXQ4CDR2M1BHGWU8VO3YE7N5FLKIS9Z',
        'B5A23R89SV6MYWCQEGUDILKHN7OFX41Z',
        'KCBG21DZ9OWFIU8S37Q45ANRXHEL6YVM',
        'X6GOV574ARKYC3F8HBE9MZNWQSL1D2UI',
        'V1A83OCWGB6UKY7NMF2LR9XZD5HEISQ4',
        'S7MWFX3CZ94ODL2B15R8EUYKNIG6QAVH',
    ];

    /**
     * 生成随机Key
     */
    static public function randomKey()
    {

        header('content-type: text/text; charset=utf-8');
        echo "	#请复制到 IntConvert 头部\n";
        echo "	static private $" . "keyMap = [\n";

        for ($i = 0; $i < 16; $i++) {
            $keys = self::$keyMap[0];
            $keys_new = '';
            $word = '';

            $len = strlen($keys);
            while ($len > 0) {
                $word = substr($keys, rand(0, $len - 1), 1);
                $keys = str_replace($word, '', $keys);
                $keys_new .= $word;
                $len = strlen($keys);
            }
            echo "		'$keys_new',\n";
        }
        echo "	];\n";
        die();
    }

    /**
     * 将数字编码为字符串
     */
    static public function toString($num = 0)
    {

        // 对传入的数字，取hash的第一位
        $hash = self::getHash($num);

        // 根据Hash，选择不同的字典
        $keymap = self::getKeyMap($hash);

        // 数字转二进制
        $map = self::fixBin(decbin($num));

        // 如果不足10位，前面自动补全对应个数的0
        $len = strlen($map);
        if ($len < 10) {
            $map = substr('00000000000000000000', 0, (10 - $len)) . $map;
        }

        // 按5个一组，编码成数字，根据KeyMap加密
        $keys = '';
        for ($index = 0; $index < strlen($map); $index += 5) {
            $keys .= substr($keymap, bindec(substr($map, $index, 5)), 1);
        }

        return $hash . $keys;
    }

    /**
     * 将字符串编码为数字
     */
    static public function toInt($str = '')
    {

        //根据生成规则，最小长度为3
        if (strlen($str) < 3) {
            return false;
        }
        $hash = substr($str, 0, 1);
        $keys = substr($str, 1);

        // 根据Hash，选择不同的字典
        $keymap = self::getKeyMap($hash);

        $bin = '';
        // 根据字典，依次 index，并转换为二进制
        for ($i = 0; $i < strlen($keys); $i++) {
            for ($index = 0; $index < strlen($keymap); $index++) {
                if (strtoupper(substr($keys, $i, 1)) === substr($keymap, $index, 1)) {
                    $bin .= self::fixBin(decbin($index));
                }
            }
        }

        // 二进制转换为数字
        $num = bindec($bin);

        if (self::getHash($num) === $hash) {
            return $num;
        } else {
            return false;
        }

    }

    /**
     * 根据Hash取字典
     */
    static private function getKeyMap($hash = 'A')
    {
        return self::$keyMap[ hexdec($hash) ];
    }

    /**
     * 不足5位的二进制，自动补全二进制位数
     */
    static private function fixBin($bin = '110')
    {
        $len = strlen($bin);
        if ($len % 5 != 0) {
            $bin = substr('00000', 0, (5 - $len % 5)) . $bin;
        }

        return $bin;
    }

    /**
     * 对数字进行Hash
     */
    static private function getHash($num = 0)
    {
        return strtoupper(substr(md5(self::getKeyMap(0) . $num), 1, 1));
    }
}