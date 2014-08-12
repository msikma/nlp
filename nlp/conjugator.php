<?php
//
// Nihon Language Processor
// ¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯
// Conjugator Class
//
// The use of this source code is governed by the MIT license.
// See the COPYRIGHT file for more information.
//

namespace Nlp;

/**
 * Can be used to conjugate and deconjugate verbs and adjectives.
 */
class Conjugator
{
    /** @var string[] Substrings used in conjugation. */
    private static $conjdb = array
    (
        // 辞書形
        'jkei_ends' => array(
            'う', 'く', 'す', 'つ', 'ぬ', 'ふ', 'む', 'ゆ', 'る', 'ぐ', 'ず',
            'ぶ', 'ぷ',
        ),
        // ない形
        'nai_ends' => array(
            'わ', 'か', 'さ', 'た', 'な', 'は', 'ま', 'や', 'ら', 'が', 'ざ',
            'ば', 'ぱ',
        ),
        // た形
        'ta_ends' => array(
            'った', 'いた', 'した', 'った', 'んだ', '', 'んだ', '', 'った',
            'いだ', '', 'んだ', '',
        ),
    );
    
    /**
     * Conjugates a word from the 辞書形 base.
     *
     * @param mixed[] $word Nlp\Word object(TODO).
     */
    public static function conjugate($word)
    {
        // Grab the dictionary form of the word.
        $jkei_kana = @$word['kana']['jisho'];
        $jkei_end = Conjugator::get_jkei_end($jkei_kana);
        
        // Check to ensure we've got a valid word.
        if (!isset($jkei_kana)
        ||  !isset($jkei_end)) {
            throw new \Exception('Not a valid word or not in hiragana: '.var_export($jkei_kana, true));
        }
        
        // Retrieve the root form from which we'll conjugate all variants.
        $root = @$word['kana']['root'];
        $kanji = @$word['kanji']['root'];
        
        // Retrieve the various word endings.
        $nai_end = Conjugator::trns_u_nai($jkei_end);
        $ta_end = Conjugator::trns_u_ta($jkei_end);
        
        // Get ない conjugations.
        $nai_conjs = Conjugator::get_nai_conjs($root, $nai_end);
        // Get た conjugations.
        $ta_conjs = Conjugator::get_ta_conjs($root, $ta_end);
        
        // Kanjify the results.
        $conjs = array_merge($nai_conjs, $ta_conjs);
        $conjs_kanji = Conjugator::kanjify($conjs, $root, $kanji);
        
        $conjs = array(
            'kana' => $conjs,
            'kanji' => $conjs_kanji,
        );
        var_dump($conjs);
    }
    
    /**
     * Description forthcoming.
     *
     */
    private static function kanjify($items, $root, $kanji)
    {
        // Replace all hiragana values in the list of words with
        // its kanji equivalent.
        foreach ($items as $k => $v) {
            $v = str_replace($root, $kanji, $v);
            $items[$k] = $v;
        }
        return $items;
    }
    
    /**
     * Returns the ない-line conjugations.
     *
     * Description forthcoming.
     *
     */
    private static function get_nai_conjs($root, $nai_end)
    {
        $conjs = array(
            'plain_negative_present' => $root.$nai_end.'ない',
            'plain_negative_past' => $root.$nai_end.'なかった',
            'polite_negative_present' => $root.'しません',
            'polite_negative_past' => $root.'しませんでした',
        );
        return $conjs;
    }
    
    /**
     * Returns the た-line conjugations.
     *
     * Description forthcoming.
     *
     */
    private static function get_ta_conjs($root, $ta_end)
    {
        $conjs = array(
        );
        return $conjs;
    }
    
    /**
     * Returns the 辞書形 end of a form.
     *
     * Description forthcoming.
     *
     * @param string $jkei Nlp\Form object in 辞書形.
     * @return string The 辞書形 form end.
     */
    private static function get_jkei_end($jkei)
    {
        // Grab the last character.
        $end = mb_substr($jkei, -1, 1, 'UTF-8');
        // Return it, but only if it's in the list of
        // valid dictionary form endings.
        return in_array($end, Conjugator::$conjdb['jkei_ends']) ? $end : null;
    }
    
    /**
     * Translate う to ない line.
     *
     * Description forthcoming.
     *
     * @param string $u う-line character.
     */
    private static function trns_u_nai($u)
    {
        // Match う-line character with ない-line equivalent.
        // E.g. す = 2, さ = 2.
        $jkei_n = array_flip(Conjugator::$conjdb['jkei_ends']);
        $idx = $jkei_n[$u];
        $a = $idx === false ? null : Conjugator::$conjdb['nai_ends'][$idx];
        return $a;
    }
    
    /**
     * Translate う to た line.
     *
     * Description forthcoming.
     *
     * @param string $u う-line character.
     */
    private static function trns_u_ta($u)
    {
        // Match う-line character with た-line equivalent.
        // E.g. す = 2, した = 2.
        $jkei_n = array_flip(Conjugator::$conjdb['jkei_ends']);
        $idx = $jkei_n[$u];
        $ta = $idx === false ? null : Conjugator::$conjdb['ta_ends'][$idx];
        // Check if this is a valid word ending.
        if ($ta === '') {
            throw new \Exception('Not a valid word ending: '.var_export($u, true));
        }
        return $ta;
    }
}
