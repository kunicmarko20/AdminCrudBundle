<?php

namespace MWSimple\Bundle\AdminCrudBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware {

    private $translator;

    public function adminMenu(FactoryInterface $factory, array $options) {

        $arrayMenu = $this->container->getParameter('mw_simple_admin_crud.menu');

        $arrayMenuConfig = $this->container->getParameter('mw_simple_admin_crud.menu_setting');

        $translator = $this->container->get('translator');       

        if($arrayMenuConfig['translation'] != false) {

            $translator->setLocale($arrayMenuConfig['translation']);
        }

        $menu = $factory->createItem('root');
        $this->setConfiguracionMenuRoot($menu, $arrayMenu);

        $this->crearChildren($menu, $arrayMenu, $arrayMenuConfig);

        return $menu;
    }

    public function crearChildren(&$menu, $children) {

        ladybug_dump_die($translator);

        foreach ($children as $key => $m) {
            if ($key != 'setting') {
//controla si tiene el role para dibujar el menu
                if (empty($m['roles'])) {
                    $exist = true;
                } else {
                    $exist = false;
                    if ($this->container->get('security.authorization_checker')->isGranted($m['roles'])) {
                        $exist = true;
                    }
                }
                if ($exist) {

                    if (isset($m['url'])) {
                        $menu->addChild($m['name'], array('route' => $m['url']));
                    } else {
                        $menu->addChild($m['name']);
                    }
                    if (isset($m['setting'])) {
                        $this->setConfiguracionMenuChildren($menu, $m);
                    }

                    if (!empty($m['subMenu'])) {
                        if (isset($m['subMenu']['setting']))
                            $this->setConfiguracionMenuRoot($menu[$m['name']], $m['subMenu']);
                        $this->crearChildren($translator->trans($menu[$m['name']]), $m['subMenu']);
                    }
                }
            }
        }
    }

    public function setConfiguracionMenuRoot(&$menu, $configuracion) {

        $settings = array_keys($configuracion['setting']);
        foreach ($settings as $setting) {
            if (!empty($configuracion['setting'][$setting]))
                $menu->setChildrenAttribute($setting, $configuracion['setting'][$setting]);
        }
    }

    public function setConfiguracionMenuChildren(&$menu, $configuracion) {
        $settings = array_keys($configuracion['setting']);
        foreach ($settings as $setting) {
            if (!empty($configuracion['setting'][$setting]))
                $menu[$configuracion['name']]->setAttribute($setting, $configuracion['setting'][$setting]);
        }
    }

}
