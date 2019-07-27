<?php

/**
 * PHP fork of https://firebase.googleblog.com/2015/02/the-2120-ways-to-ensure-unique_68.html
 * Fancy ID generator that creates 20-character string identifiers with the following properties:
 *
 * 1. They're based on timestamp so that they sort *after* any existing ids.
 * 2. They contain 72-bits of random data after the timestamp so that IDs won't collide with other clients' IDs.
 * 3. They sort *lexicographically* (so the timestamp is converted to characters that will sort properly).
 * 4. They're monotonically increasing.  Even if you generate more than one in the same timestamp, the
 *    latter ones will sort after the former ones.  We do this by using the previous random bits
 *    but "incrementing" them by 1 (only in the case of a timestamp collision).
 */
class PushId
{
    const PUSH_CHARS = '-0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz';
    private static $lastPushTime = 0;
    private static $lastRandChars = [];
    public static function generate()
    {
        $now = (int) (microtime(true) * 1000);
        $isDuplicateTime = ($now === static::$lastPushTime);
        static::$lastPushTime = $now;

        $timeStampChars = new SplFixedArray(8);

        for ($i = 7; $i >= 0; $i--) {
            $timeStampChars[$i] = substr(self::PUSH_CHARS, $now % 64, 1);
            $now = (int) floor($now / 64);
        }

        static::assert($now === 0, 'We should have converted the entire timestamp.');

        $id = implode('', $timeStampChars->toArray());

        if (!$isDuplicateTime) {
            for ($i = 0; $i < 12; $i++) {
                $lastRandChars[$i] = floor(rand(0, 63));
            }
        } else {
            for ($i = 11; $i >= 0 && static::$lastRandChars[$i] === 63; $i--) {
                static::$lastRandChars[$i] = 0;
            }
            static::$lastRandChars[$i]++;
        }
        for ($i = 0; $i < 12; $i++) {
            $id .= substr(self::PUSH_CHARS, $lastRandChars[$i], 1);
        }
        static::assert(strlen($id) === 20, 'Length should be 20.');

        return $id;
    }

    private static function assert($condition, $message = '')
    {
        if ($condition !== true) {
            throw new RuntimeException($message);
        }
    }
}
