<?php

namespace SilverStripe\Snapshots\Elemental;

use DNADesign\Elemental\Forms\ElementalAreaField;
use Psr\Container\NotFoundExceptionInterface;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Validation\ValidationException;
use SilverStripe\EventDispatcher\Dispatch\Dispatcher;
use SilverStripe\EventDispatcher\Symfony\Event;

/**
 * @extends Extension<ElementalAreaField>
 */
class SaveListener extends Extension
{
    /**
     * TODO extension point no longer available (this listener might be legacy now)
     * Extension point in @see ElementalAreaField::saveInto()
     *
     * @param array $elements
     * @return void
     * @throws NotFoundExceptionInterface
     * @throws ValidationException
     */
    protected function onSaveInto(array $elements): void
    {
        $owner = $this->getOwner();
        $page = $owner
            ->getArea()
            ->getOwnerPage();

        Dispatcher::singleton()->trigger(
            'elementalAreaUpdated',
            Event::create(
                $owner->getName(),
                [
                    'elements' => $elements,
                    'elementalArea' => $owner,
                    'page' => $page,
                ]
            )
        );
    }
}
