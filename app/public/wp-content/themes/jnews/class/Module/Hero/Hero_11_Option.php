<?php
/**
 * @author : Jegtheme
 */
namespace JNews\Module\Hero;

Class Hero_11_Option extends HeroOptionAbstract
{
    protected $number_post = 5;
	protected $second_thumbnail = true;

    public function get_module_name()
    {
        return esc_html__('JNews - Hero 11', 'jnews');
    }
}