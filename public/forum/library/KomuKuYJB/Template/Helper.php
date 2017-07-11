<?php /*aba6744711223e3416d5d464faf9cb981b7d691d*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 4
 * @since      1.0.0 RC 4
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Template_Helper
{
    public static function getPrice($price, $currencyId = null, $onlyIncludeId = false)
    {
        if ($currencyId === null)
        {
            $currencyId = 'IDR';
        }

        $price = round($price, 2);
        $price = XenForo_Locale::numberFormat($price, 2);// number_format($price, 2, '.', ',');

        return $currencyId . ' ' . $price;
    }

    public static function getPrefixTitle($prefixId, $outputType = 'html', $append = null)
    {
        if (is_array($prefixId))
        {
            if (!isset($prefixId['prefix_id']))
            {
                return '';
            }

            $prefixId = $prefixId['prefix_id'];
        }

        $prefixId = intval($prefixId);
        $prefixes = GFNCore_Registry::get('classifiedPrefixes') ?: array();

        if (!$prefixId || !isset($prefixes[$prefixId]))
        {
            return '';
        }

        $text = new XenForo_Phrase('classifieds_prefix_' . $prefixId);
        $text = $text->render(false);

        if ($text === '')
        {
            return '';
        }

        switch ($outputType)
        {
            case 'html':
                $text = sprintf('<span class="classifiedPrefix %s">%s</span>', htmlspecialchars($prefixes[$prefixId]), htmlspecialchars($text));

                if ($append === null)
                {
                    $append = ' ';
                }
                break;

            case 'plain':
                break;

            case 'escaped':
            default:
                $text = htmlspecialchars($text);
        }

        if ($append === null)
        {
            $append = ' - ';
        }

        return $text . $append;
    }

    public static function getPrefixGroupTitle($groupId)
    {
        return new XenForo_Phrase('classifieds_prefix_group_' . $groupId);
    }

    public static function getExpiresInPhrase($expireDate)
    {
        $expireDate = intval($expireDate);

        if ($expireDate === 0)
        {
            return new XenForo_Phrase('never');
        }

        if ($expireDate < XenForo_Application::$time)
        {
            return new XenForo_Phrase('expired');
        }

        $now = new DateTime();
        $now->setTimestamp(XenForo_Application::$time);
        $expire = new DateTime();
        $expire->setTimestamp($expireDate);

        $i = $expire->diff($now, true);
        $pieces = array();
        $count = 0;

        if ($i->y)
        {
            $pieces[] = new XenForo_Phrase($i->y > 1 ? 'x_years' : '1_year', array('years' => $i->y));
            $count++;
        }

        if ($i->m)
        {
            $pieces[] = new XenForo_Phrase($i->m > 1 ? 'x_months' : '1_month', array('months' => $i->m));
            $count++;
        }

        if ($i->d)
        {
            if ($count < 2)
            {
                $pieces[] = new XenForo_Phrase($i->d > 1 ? 'x_days' : '1_day', array('days' => $i->d));
                $count++;
            }
        }

        if ($i->h)
        {
            if ($count < 2)
            {
                $pieces[] = new XenForo_Phrase($i->h > 1 ? 'x_hours' : '1_hour', array('count' => $i->h));
                $count++;
            }
        }

        if ($i->i)
        {
            if ($count < 2)
            {
                $pieces[] = new XenForo_Phrase($i->i > 1 ? 'x_minutes' : '1_minute', array('count' => $i->i));
                $count++;
            }
        }

        if ($i->s)
        {
            if ($count < 2)
            {
                $pieces[] = new XenForo_Phrase($i->s > 1 ? 'x_seconds' : '1_second', array('time' => $i->s));
            }
        }

        if (count($pieces) < 2)
        {
            return $pieces[0];
        }
        else
        {
            return new XenForo_Phrase('x_comma_y', array('x' => $pieces[0], 'y' => $pieces[1]));
        }
    }

    public static function getFeaturedImage(array $classified)
    {
        if (empty($classified['featured_image_date']))
        {
            if (!$imagePath = XenForo_Template_Helper_Core::styleProperty('imagePath'))
            {
                $imagePath = 'styles/default';
            }

            return $imagePath . '/KomuKuYJB/no_image.png';
        }

        return sprintf('%s/classifieds/icons/%d/%d.jpg?%d',
            XenForo_Application::$externalDataUrl,
            floor($classified['classified_id'] / 1000),
            $classified['classified_id'],
            $classified['featured_image_date']
        );
    }

    public static function getFieldTitle($fieldId)
    {
        if (is_array($fieldId))
        {
            $fieldId = $fieldId['field_id'];
        }

        return new XenForo_Phrase('classifieds_field_' . $fieldId);
    }

    public static function fieldIsHint($field)
    {
        if (!is_array($field))
        {
            /** @var KomuKuYJB_Model_Field $model */
            $model = XenForo_Model::create('KomuKuYJB_Model_Field');
            $fields = $model->getFieldCache();
            if (!$fields[$field])
            {
                return false;
            }

            $field = $fields[$field];
        }

        return $field['field_type'] == 'hint_text';
    }

    public static function getFieldValue(array $classified, $field, $value = null, $allowedTemplate = null)
    {
        if (!is_array($field))
        {
            /** @var KomuKuYJB_Model_Field $model */
            $model = XenForo_Model::create('KomuKuYJB_Model_Field');
            $fields = $model->getFieldCache();
            if (!$fields[$field])
            {
                return '';
            }

            $field = $fields[$field];

            if ($allowedTemplate !== null && empty($field['include_in_' . $allowedTemplate]))
            {
                return '';
            }
        }

        if (!XenForo_Application::isRegistered('view'))
        {
            return 'No view registered';
        }

        if ($field['field_type'] == 'hint_text')
        {
            $value = $field['hint_text'];
        }
        elseif ($value === null && isset($field['field_value']))
        {
            $value = $field['field_value'];
        }

        if ($value === '' || $value === null)
        {
            return '';
        }

        $valueRaw = null;
        $multiChoice = false;
        $choice = '';
        $view = XenForo_Application::get('view');

        switch ($field['field_type'])
        {
            case 'date':
                $valueRaw = intval($value);
                $value = $valueRaw ? XenForo_Locale::date($valueRaw, 'absolute') : '';
                break;

            case 'radio':
            case 'select':
                $choice = $value;
                $value = new XenForo_Phrase("classifieds_field_$field[field_id]_choice_$value");
                $value->setPhraseNameOnInvalid(false);
                $valueRaw = $value;
                break;

            case 'checkbox':
            case 'multiselect':
                $multiChoice = true;
                if (!is_array($value) || count($value) == 0)
                {
                    return '';
                }

                $newValues = array();
                foreach ($value AS $id => $choice)
                {
                    $phrase = new XenForo_Phrase("classifieds_field_$field[field_id]_choice_$choice");
                    $phrase->setPhraseNameOnInvalid(false);
                    $newValues[$choice] = $phrase;
                }

                $value = $newValues;
                $valueRaw = $value;
                break;

            case 'hint_text':
            case 'bbcode':
                $valueRaw = htmlspecialchars(XenForo_Helper_String::censorString($value));
                $bbCodeParser = new XenForo_BbCode_Parser(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $view)));
                $value = $bbCodeParser->render($value, array('noFollowDefault' => empty($classified['isTrusted'])));
                break;

            default:
            case 'textbox':
            case 'textarea':
                $valueRaw = htmlspecialchars(XenForo_Helper_String::censorString($value));
                $value = XenForo_Template_Helper_Core::callHelper('bodytext', array($value));
                break;
        }

        if (!empty($field['display_template']))
        {
            if ($multiChoice && is_array($value))
            {
                foreach ($value AS $choice => &$thisValue)
                {
                    $thisValue = strtr($field['display_template'], array(
                        '{$fieldId}' => $field['field_id'],
                        '{$value}' => $thisValue,
                        '{$valueRaw}' => $thisValue,
                        '{$valueUrl}' => urlencode($thisValue),
                        '{$valueHrefSrc}' => urldecode($valueRaw),
                        '{$choice}' => $choice,
                    ));
                }
            }
            else
            {
                $value = strtr($field['display_template'], array(
                    '{$fieldId}' => $field['field_id'],
                    '{$value}' => $value,
                    '{$valueRaw}' => $valueRaw,
                    '{$valueUrl}' => urlencode($value),
                    '{$valueHrefSrc}' => urldecode($valueRaw),
                    '{$choice}' => $choice,
                ));
            }
        }

        if (is_array($value))
        {
            if (empty($value))
            {
                return '';
            }
            return '<ul class="plainList"><li>' . implode('</li><li>', $value) . '</li></ul>';
        }

        return $value;
    }

    public static function getAdvertTypeBadge($advertTypeId, $checkCondition = false, $completed = false, $link = null)
    {
        if (is_array($advertTypeId))
        {
            $advertTypeId = $advertTypeId['advert_type_id'];
        }

        $advertTypes = GFNCore_Registry::get('classifiedAdvertTypes');
        if (!isset($advertTypes[$advertTypeId]))
        {
            return '';
        }

        $advertType = $advertTypes[$advertTypeId];
        if ($checkCondition && !$advertType['show_badge'])
        {
            return '';
        }

        if (is_string($completed))
        {
            $completed = ($completed == 'completed');
        }

        $link = trim($link);
        $text = new XenForo_Phrase('classifieds_advert_type_' . $advertTypeId . ($completed ? '_complete_text' : '_title'));
        $badgeColor = $completed ? $advertType['complete_badge_color'] : $advertType['badge_color'];
        $borderColor = GFNCore_Template_Helper_Core::helperBrightnessAdjustment($badgeColor, -60);
        $isBright = GFNCore_Template_Helper_Core::helperColorIsBright($badgeColor);

        if ($link)
        {
            return sprintf(
                '<a class="advertType %s" style="background-color: %s; border-color: %s;" href="%s">%s</a>',
                ($isBright ? 'bright' : 'dark') . ($completed ? ' completed' : ''), $badgeColor, $borderColor, $link, $text
            );
        }
        else
        {
            return sprintf(
                '<span class="advertType %s" style="background-color: %s; border-color: %s;">%s</span>',
                ($isBright ? 'bright' : 'dark') . ($completed ? ' completed' : ''), $badgeColor, $borderColor, $text
            );
        }
    }

    public static function getPriceByAdvertType($advertTypeId, $price, $currencyId = null, $onlyIncludeId = false)
    {
        $price = floatval($price);
        if ($price <> 0)
        {
            return self::getPrice($price, $currencyId, $onlyIncludeId);
        }

        if (is_array($advertTypeId))
        {
            $advertTypeId = $advertTypeId['advert_type_id'];
        }

        $advertTypes = GFNCore_Registry::get('classifiedAdvertTypes');
        if (!isset($advertTypes[$advertTypeId]))
        {
            return self::getPrice($price, $currencyId, $onlyIncludeId);
        }

        $phrase = new XenForo_Phrase('classifieds_advert_type_' . $advertTypeId . '_zero_value_text');
        return $phrase->render(false) ?: self::getPrice($price, $currencyId, $onlyIncludeId);
    }

    public static function getTraderRatingCriteriaTitle($criteriaId)
    {
        if (is_array($criteriaId))
        {
            $criteriaId = $criteriaId['criteria_id'];
        }

        return new XenForo_Phrase('classifieds_rating_criteria_' . $criteriaId);
    }

    public static function getTraderRatingCriteriaFeedback($rating)
    {
        if (is_array($rating))
        {
            $rating = $rating['rating'];
        }

        if ($rating == -1)
        {
            return new XenForo_Phrase('unacceptable');
        }
        elseif ($rating == 1)
        {
            return new XenForo_Phrase('outstanding');
        }
        else
        {
            return new XenForo_Phrase('average');
        }
    }

    public static function getConversationResponseTime($time)
    {
        switch (true)
        {
            case $time < 60;
                if ($time == 1)
                {
                    return new XenForo_Phrase('1_second');
                }
                else
                {
                    return new XenForo_Phrase('x_seconds', array('time' => $time));
                }

            case $time < 3600;
                if (($time = floor($time / 60)) == 1)
                {
                    return new XenForo_Phrase('1_minute');
                }
                else
                {
                    return new XenForo_Phrase('x_minutes', array('count' => $time));
                }

            case $time < 86400;
                if (($time = floor($time / 3600)) == 1)
                {
                    return new XenForo_Phrase('1_hour');
                }
                else
                {
                    return new XenForo_Phrase('x_hours', array('count' => $time));
                }

            case $time < 604800;
                if (($time = floor($time / 86400)) == 1)
                {
                    return new XenForo_Phrase('1_day');
                }
                else
                {
                    return new XenForo_Phrase('x_days', array('days' => $time));
                }

            case $time < 2592000;
                if (($time = floor($time / 604800)) == 1)
                {
                    return new XenForo_Phrase('1_week');
                }
                else
                {
                    return new XenForo_Phrase('x_weeks', array('weeks' => $time));
                }

            case $time < 31536000;
                if (($time = floor($time / 2592000)) == 1)
                {
                    return new XenForo_Phrase('1_month');
                }
                else
                {
                    return new XenForo_Phrase('x_months', array('months' => $time));
                }

            default:
                if (($time = floor($time / 31536000)) == 1)
                {
                    return new XenForo_Phrase('1_year');
                }
                else
                {
                    return new XenForo_Phrase('x_years', array('years' => $time));
                }
        }
    }
}