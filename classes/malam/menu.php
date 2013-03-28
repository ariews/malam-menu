<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * @author  arie
 */

class Malam_Menu
{
    protected $items        = array();
    protected $attributes   = array();
    protected $section      = NULL;
    protected $parent       = NULL;
    protected $template     = 'menu/default';
    public static $theme    = NULL;

    public static function factory($section = NULL, $parent = NULL)
    {
        return new Menu($section, $parent);
    }

    public function __construct($section = NULL, $parent = NULL)
    {
        if (! is_array($section) && ! ($section instanceof Menu))
        {
            $section = Kohana::$config->load("menu.$section");
        }

        $this->section = $section;
        $this->parent  = $parent;

        if (isset($section['attributes']))
        {
            $this->attributes = $section['attributes'];
        }

        $this->respawn(Request::current()->uri());
    }

    protected function respawn($url)
    {
        $this->items = array();

        foreach ($this->section as $sec)
        {
            $this->items[] = new Menu_Item($sec, $this->parent, ltrim($url, '/'));
        }
    }

    public function set_theme($theme)
    {
        self::$theme = $theme;
        return $this;
    }

    public function theme()
    {
        return self::$theme;
    }

    public static function add_attribute(& $attributes, $key, $value)
    {
        if (! is_array($value))
            $value = explode(' ', $value);

        if (isset($attributes[$key]))
        {
            $_value = explode(' ', $attributes[$key]);
            $value = array_merge($_value, $value);
        }

        $attributes[$key] = join(' ', $value);
    }

    public function set_attribute($key, $value)
    {
        Menu::add_attribute($this->attributes, $key, $value);
        return $this;
    }

    public function render($template = NULL)
    {
        if (NULL === $template)
        {
            $template = $this->template;
        }

        return View::factory($template, array(
            'items'         => $this->items,
            'attributes'    => $this->attributes
            ))->render();
    }
}
