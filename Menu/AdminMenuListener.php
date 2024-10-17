<?php

namespace Oliverde8\PhpEtlSyliusAdminBundle\Menu;

use Knp\Menu\MenuFactory;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AdminMenuListener
{
    private TranslatorInterface $translator;
    private MenuFactory $menuFactory;

    public function __construct(
        TranslatorInterface $translator,
        MenuFactory $menuFactory
    )
    {
        $this->translator = $translator;
        $this->menuFactory = $menuFactory;
    }

    /**
     * @param MenuBuilderEvent $event
     */
    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();
        $menu
            ->addChild('execution')
            ->setLabel($this->translator->trans('sylius.ui.etl_executions'));

        if ($executionsMenu = $menu->getChild('execution')) {

            $dashboardMenu = $this->menuFactory->createItem('etl_executions_dashboard', ['route' => 'oliverde8_admin_etl_execution_dashboard'])
                ->setLabel($this->translator->trans('sylius.ui.dashboard.title'))
                ->setLabelAttribute('icon', 'options');

            $gridMenu = $this->menuFactory->createItem('etl_executions_grid', ['route' => 'oliverde8_admin_etl_execution_index'])
                ->setLabel($this->translator->trans('sylius.ui.etl_executions'))
                ->setLabelAttribute('icon', 'file');

            $executionsMenu->addChild($dashboardMenu);
            $executionsMenu->addChild($gridMenu);
        }
    }
}
