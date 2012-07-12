<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * @author  arie
 */

class Malam_Menu
{
    const DEFAULT_SECTION       = 'default';

    protected $_items           = array();

    public static function factory($section = Menu::DEFAULT_SECTION)
    {
        return new Menu($section);
    }

    public function __construct($section = Menu::DEFAULT_SECTION)
    {
        $_items = (is_array($section))
            ? $section
            : Kohana::$config->load("menu.{$section}");
        
        foreach ($_items as $item)
        {
            if (isset($item['title']) && isset($item['url']))
            {
                $attributes = Arr::get($item, 'attributes');
                $children   = Arr::get($item, 'children');
                
                $this->add($item['title'], $item['url'], $attributes, $children);
            }
        }
    }

    public function get_items()
    {
        return $this->_items;
    }

    public function add($title, $url, array $attributes = NULL, $children = NULL)
    {
        if ($url instanceof Route)
        {
            $url = $url->uri();
        }
        elseif (! Valid::url($url))
        {
            try {
                $url = Route::get($url)->uri();
            }
            catch (Kohana_Exception $e)
            {
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
        );

        return $this;
    }

    private function current_uri()
    {
        return Request::initial()->uri();
    }

    public function render()
    {
        $menu = '<ul>';

        foreach ($this->get_items() as $item)
        {
            $has_children = isset($item['children']);

            $class = array();

            if (isset($item['attributes']) && isset($item['attributes']['class']))
            {
                $class = explode(' ', $item['attributes']['class']);
            }

            $has_children && $class[] = 'parent';

            if ($this->current_uri() == $item['url'])
            {
                $class = array_merge($class, array('active', 'current'));
            }
            elseif ($has_children && self::search_match_url($this->current_uri(), $item['children']))
            {
                $class[] = 'active';
            }
            
            $class = array('class' => join(' ', array_unique($class)));
            
            $menu .= '<li'.HTML::attributes($class).'>'.HTML::anchor($item['url'], $item['title']);
            $menu .= $has_children ? $item['children']->render() : '';
            $menu .= '</li>';

        }

        $menu .= '</ul>';

        return $menu;
    }

    public function __toString()
    {
        return $this->render();
    }

    private static function search_match_url($search_url, $items)
    {
        if ($items instanceof Menu)
        {
            $items = $items->get_items();
        }
        
        foreach ($items as $item)
        {
            if (($search_url == $item['url'])
                OR
                (isset($item['children']) && self::search_match_url($search_url, $item['children']))
            )
            {
                return TRUE;
            }
        }

        return FALSE;
    }
}