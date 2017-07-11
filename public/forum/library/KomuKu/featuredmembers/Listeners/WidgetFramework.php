<?php
class KomuKu_featuredmembers_Listeners_WidgetFramework
{
    public static function widget_framework_ready(array &$renderers)
    {
        $renderers[] = 'KomuKu_featuredmembers_WidgetRenderer_FeaturedMembers';
        $renderers[] = 'KomuKu_featuredmembers_WidgetRenderer_VerifiedMembers';
    }
}