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
            ->addChild('exports')
            ->setLabel($this->translator->trans('app.ui.exports'));

        if ($exportsMenu = $menu->getChild('exports')) {
            $etlExecution = $this->menuFactory->createItem('etl_executions', ['route' => 'app_admin_etl_execution_index'])
                ->setLabel($this->translator->trans('app.ui.etl_executions'))
                ->setLabelAttribute('icon', 'file');

            $exportsMenu->addChild($etlExecution);
        }
    }
}
