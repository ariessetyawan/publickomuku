<?php


class phc_AlphabeticalXF_Helper_Helper
{
    public static $_all = '';
    public static $_options = null;

    public function __construct()
    {
        $all = new XenForo_Phrase('all');
        phc_AlphabeticalXF_Helper_Helper::$_all = $all->render();

        self::$_options = XenForo_Application::get('options');
    }

    public static function alphaNation()
    {
        $pagination = array();

        if(!self::$_options->alphaxf_languages)
        {
            if(self::$_options->alphaxf_latin)
            {
                $latin = new XenForo_Phrase('alphaxf_latin');
                $latin_phrase = $latin->render();

                $pagination[$latin_phrase] = phc_AlphabeticalXF_Helper_Helper::getLatinABC();
            }
        }
        else
        {
            $pagination = phc_AlphabeticalXF_Helper_Helper::getLangugageABCForOther();
        }


        return $pagination;
    }

    public static function getLangugageABCForOther()
    {
        $languageId = XenForo_Visitor::getInstance()->language_id;

        if(!$languageId)
        {
            $languageId = self::$_options->defaultLanguageId;
        }

        $latin = new XenForo_Phrase('alphaxf_latin');
        $latin_phrase = $latin->render();

        $pagination = array();

        if(self::$_options->alphaxf_merge && self::$_options->alphaxf_latin)
        {
            $pagination[$latin_phrase] = phc_AlphabeticalXF_Helper_Helper::getLatinABC();
        }

        foreach(self::$_options->alphaxf_languages as $alpha)
        {
            $abc = $alpha['abc'];

            if(self::$_options->alphaxf_merge)
            {
                phc_AlphabeticalXF_Helper_Helper::$_all = ($alpha['all'] ? $alpha['all'] : phc_AlphabeticalXF_Helper_Helper::$_all);
                $pagination[$alpha['lang_text']] = self::getLetters($abc);
            }
            else
            {
                if($alpha['language'] == $languageId)
                {
                    phc_AlphabeticalXF_Helper_Helper::$_all = ($alpha['all'] ? $alpha['all'] : phc_AlphabeticalXF_Helper_Helper::$_all);
                    $pagination[$alpha['lang_text']] = self::getLetters($abc);
                    break;
                }
            }
        }

        if(!$pagination && self::$_options->alphaxf_latin)
        {
            $pagination[$latin_phrase] = phc_AlphabeticalXF_Helper_Helper::getLatinABC();
        }

        return $pagination;
    }

    private static function getOtherLetters($all = null)
    {
        $pagination = array();

        if(self::$_options->alphaxf_all_block_side == 'left')
        {
            $pagination[''] = (!$all ? phc_AlphabeticalXF_Helper_Helper::$_all : $all);
            $pagination['0-9'] = '0-9';
            $pagination['other'] = '@';
        }
        else
        {
            $pagination['other'] = '@';
            $pagination['0-9'] = '0-9';
            $pagination[''] = (!$all ? phc_AlphabeticalXF_Helper_Helper::$_all : $all);
        }

        return $pagination;
    }

    private static function getLetters($abc)
    {
        $pagination = array();
        $abc = preg_split('#,#', $abc, -1, PREG_SPLIT_NO_EMPTY);

        if(self::$_options->alphaxf_all_block_side == 'left')
        {
            $pagination = self::getOtherLetters();
        }

        if($abc)
        {
            foreach($abc as $letter)
            {
                $letter = trim($letter);
                $pagination[$letter] = $letter;
            }
        }

        if(self::$_options->alphaxf_all_block_side == 'right')
        {
            $pagination = array_merge($pagination, self::getOtherLetters());
        }

        return $pagination;
    }

    public static function getLatinABC()
    {
        $pagination = array();

        if(self::$_options->alphaxf_all_block_side == 'left')
        {
            $pagination = self::getOtherLetters();
        }

        $alphaRange = range('A', 'Z');
        foreach($alphaRange as $letter)
        {
            $pagination[$letter] = $letter;
        }

        if(self::$_options->alphaxf_all_block_side == 'right')
        {
            $pagination = array_merge($pagination, self::getOtherLetters());
        }

        return $pagination;
    }
}