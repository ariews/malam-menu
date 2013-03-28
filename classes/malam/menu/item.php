<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * @file    item.php
 * @author  Arie W. Subagja <arie @ malam.or.id>
 */

class Malam_Menu_Item
{
    /**
     * array or attributes
     *
     * @var array
     */
    protected $attributes   = array();

    /**
     * item parent
     *
     * @var Menu_Item
     */
    protected $parent       = NULL;

    /**
     * current url
     *
     * @var string
     */
    protected $current      = NULL;

    /**
     * menu item
     *
     * @var array
     */
    protected $item;

    public function __construct(array $item, $parent = NULL, $current = NULL)
    {
        $this->item = $item + array(
            'params'        => NULL,
            'attributes'    => NULL,
            'title'         => NULL,
            'url'           => NULL,
            'children'      => NULL,
        );

        $this->parent   = $parent;
        $this->current  = $current;
        $this->init();
    }

    private function init()
    {
        if ($this->has_children())
        {
            $child = Menu::factory($this->children(), $this)
                    ->set_theme(Menu::$theme);

            $this->set_children($child);
        }

        $url = $this->url();

        if ($url instanceof Route)
        {
            $url = $url->uri($this->params());
        }
        elseif (! Valid::url($url) && ! in_array($url[0], array('/', '#')))
        {
            try {
                $route = Route::get($url);
                $url   = $route->uri($this->params());
            }
            catch (Kohana_Exception $e)
            { $url = '#'; }
        }

        $this->set_url(ltrim($url, '/'));

        if ($this->is_active())
        {
            $this->set_active();
            $this->set_attribute('class', 'current');
        }

        if ($this->has_children())
        {
            $this->set_attribute('class', 'parent');
        }

        if (! empty($this->item['attributes']))
        {
            foreach ($this->item['attributes'] as $key => $value)
            {
                $this->set_attribute($key, $value);
            }
        }

        unset($this->item['attributes'], $this->item['params']);
    }

    public function set_active()
    {
        $this->set_attribute('class', 'active');

        if ($this->has_parent())
        {
            $this->parent->set_active();
        }
    }

    public function is_active()
    {
        return $this->current == $this->url();
    }

    public function has_children()
    {
        return ! empty($this->item['children']);
    }

    public function has_parent()
    {
        return ! empty($this->parent);
    }

    public function is_parent()
    {
        return $this->has_children();
    }

    public function is_children()
    {
        return ! empty($this->parent);
    }

    public function children()
    {
        return $this->item['children'];
    }

    public function set_children($children)
    {
        $this->item['children'] = $children;
    }

    public function params()
    {
        return $this->item['params'];
    }

    public function title()
    {
        return $this->item['title'];
    }

    public function url()
    {
        return $this->item['url'];
    }

    public function set_url($url)
    {
        $this->item['url'] = $url;
    }

    public function set_attribute($key, $value)
    {
        Menu::add_attribute($this->attributes, $key, $value);
        return $this;
    }

    public function attributes()
    {
        return $this->attributes;
    }

    public function __get($prop)
    {
        switch ($prop):
            case 'children':    return $this->children();   break;
            case 'parent':      return $this->parent;       break;
            case 'attributes':  return $this->attributes(); break;
            case 'title':       return $this->title();      break;
            case 'url':         return $this->url();        break;
        endswitch;
    }
}