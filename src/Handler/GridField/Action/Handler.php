<?php

namespace SilverStripe\Snapshots\Handler\GridField\Action;

use SilverStripe\Core\Validation\ValidationException;
use SilverStripe\EventDispatcher\Event\EventContextInterface;
use SilverStripe\Forms\Form;
use SilverStripe\ORM\DataObject;
use SilverStripe\Snapshots\Handler\HandlerAbstract;
use SilverStripe\Snapshots\Snapshot;
use SilverStripe\Versioned\Versioned;

class Handler extends HandlerAbstract
{
    /**
     * @param EventContextInterface $context
     * @return Snapshot|null
     * @throws ValidationException
     */
    protected function createSnapshot(EventContextInterface $context): ?Snapshot
    {
        $action = $context->getAction();

        if ($action === null) {
            return null;
        }

        $grid = $context->get('gridField');

        if (!$grid) {
            return null;
        }

        /** @var Form $form */
        $form = $grid->getForm();

        if (!$form) {
            return null;
        }

        /** @var DataObject $record */
        $record = $form->getRecord();

        if (!$record || !$record->hasExtension(Versioned::class)) {
            return null;
        }

        return Snapshot::singleton()->createSnapshot($record);
    }
}
