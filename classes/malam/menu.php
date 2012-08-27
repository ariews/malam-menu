<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * @author  arie
 */

class Malam_Menu
{
    const DEFAULT_SECTION       = 'default';

    protected $_items           = array();

    protected $_attributes      = array();

    public static function factory($section = Menu::DEFAULT_SECTION)
    {
        return new Menu($section);
    }

    public function __construct($section = Menu::DEFAULT_SECTION)
    {
        $_items = (is_array($section))
            ? $section
            : Kohana::$config->load("menu.{$section}");

        if (isset($_items['attributes']))
        {
            $this->_attributes = $_items['attributes'];

            unset($_items['attributes']);
        }

        foreach ($_items as $item)
        {
            if (isset($item['title']) && isset($item['url']))
            {
                $attributes = Arr::get($item, 'attributes');
                $children   = Arr::get($item, 'children');
                $params     = Arr::get($item, 'params');

                $this->add($item['title'], $item['url'], $attributes, $children, $params);
            }
        }
    }

    public function get_items()
    {
        return $this->_items;
    }

    public function add_attribute($key, $value)
    {
        if (! is_array($value))
            $value = explode(' ', $value);

        if (isset($this->_attributes[$key]))
        {
            $_value = explode(' ', $this->_attributes[$key]);
            $value = array_merge($_value, $value);
        }

        $this->_attributes[$key] = join(' ', $value);

        return $this;
    }

    public function add_attributes(array $attributes)
    {
        foreach ($attributes as $key => $value)
        {
            $this->add_attribute($key, $value);
        }

        return $this;
    }

    public function add($title, $url, array $attributes = NULL, array $children = NULL, array $params = NULL)
    {
        if (! ($url instanceof Route) && ! Valid::url($url) && ! in_array($url[0], array('/', '#')))
        {
            try { $url = Route::get($url); }
            catch (Exception $e) {}
        }

        // rematch
        if (! ($url instanceof Route))
        {
            if ($url[0] != '#')
            {
                $url = ltrim($url, '/');
                $url = URL::site($url);
            }
        }

        if (NULL !== $children)
        {
            if (! ($children instanceof Menu) || is_array($children))
            {
                $children = Menu::factory($children);
            }
        }

        $this->_items[] = array(
            'title'     => $title,
            'url'       => $url,
            'children'  => $children,
            'attributes'=> $attributes,
            'params'    => $params,
        );

        return $this;
    }

    public function render()
    {
        $menu = '<ul'.HTML::attributes($this->_attributes).'>';

        foreach ($this->get_items() as $item)
        {
            $has_children = isset($item['children']);

            $class = $attributes = array();

            if (isset($item['attributes']))
            {
                if (isset($item['attributes']['class']))
                {
                    $class = explode(' ', $item['attributes']['class']);
                    unset($item['attributes']['class']);
                }

                $attributes = $item['attributes'];
            }

            $has_children && $class[] = 'parent';

            if (self::check_for_matching_url($item['url']))
            {
                $class = array_merge($class, array('active', 'current'));
            }
            elseif ($has_children && self::search_match_url($item['children']))
            {
                $class[] = 'active';
            }

            $attributes += array('class' => join(' ', array_unique($class)));

            $menu .= '<li'.HTML::attributes($attributes).'>'.HTML::anchor(self::to_url($item), $item['title']);
            $menu .= $has_children ? $item['children']->render() : '';
            $menu .= '</li>';
        }

        $menu .= '</ul>';

        return $menu;
    }

    public function __toString()
    {
        try { return $this->render(); } catch (Exception $e) { return ''; }
    }

    public static function to_url($item)
    {
        $url = $item['url'];
        if ($url instanceof Route)
        {
            $url = $url->uri($item['params']);
        }

        return $url;
    }

    protected static function check_for_matching_url($url)
    {
        if (( is_string($url) && Valid::url($url) && Request::current()->url() == $url)
                OR
            ( $url instanceof Route && $url->matches(Request::current()->uri()) )
        )
        {
            return TRUE;
        }

        return FALSE;
    }

    protected static function search_match_url($items)
    {
        if ($items instanceof Menu)
        {
            $items = $items->get_items();
        }

        foreach ($items as $item)
        {
            return self::check_for_matching_url($item['url']);
        }

        return FALSE;
    }
}
