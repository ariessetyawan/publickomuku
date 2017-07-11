<?php namespace App;

class GoPublic extends \Illuminate\Foundation\Application
{
    public function publicPath()
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'../public_html/';
    }
}