<?php

class KomuKu_FAQ_Listener_Widgets
{
    public static function widget_framework_ready(array &$renderers)
    {
        $renderers[] = 'KomuKu_FAQ_WidgetRenderer_MostPopular';
        $renderers[] = 'KomuKu_FAQ_WidgetRenderer_LatestAnswers';
        $renderers[] = 'KomuKu_FAQ_WidgetRenderer_StickyAnswers';
    }
}
