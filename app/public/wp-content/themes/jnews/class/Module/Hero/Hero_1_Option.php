<?php
/**
 * @author : Jegtheme
 */
namespace JNews\Module\Hero;

Class Hero_1_Option extends HeroOptionAbstract
{
    protected $number_post = 4;
	protected $second_thumbnail = true;
    protected $thrid_thumbnail = true;

    public function get_module_name()
    {
        return esc_html__('JNews - Hero 1', 'jnews');
    }
}