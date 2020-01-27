<?php


namespace SilverStripe\Snapshots\Handler\Form;

use SilverStripe\Forms\Form;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Snapshots\Listener\EventContext;
use SilverStripe\Snapshots\Snapshot;
use SilverStripe\Snapshots\SnapshotEvent;
use SilverStripe\Versioned\Versioned;

class SaveHandler extends Handler
{
    /**
     * Avoid recording useless save actions to prevent multiple snapshots of the same version
     * This feature relies on the published state and will not cover all cases
     * For example if user is has an unpublished change and save action is called
     * the action will be recorded as snapshot as we are comparing draft against published state
     * @param EventContext $context
     * @return Snapshot|null
     * @throws ValidationException
     */
    protected function createSnapshot(EventContext $context): ?Snapshot
    {
        /** @var Form $form */
        $form = $context->get('form');

        if ($form === null) {
            return parent::createSnapshot($context);
        }

        /** @var DataObject|Versioned $record */
        $record = $form->getRecord();

        if ($record === null) {
            return parent::createSnapshot($context);
        }

        if (!$record->hasExtension(Versioned::class)) {
            return parent::createSnapshot($context);
        }

        if ($record instanceof SnapshotEvent) {
            return parent::createSnapshot($context);
        }

        if ($this->hasNewVersion($record)) {
            return parent::createSnapshot($context);
        }

        return null;
    }

    /**
     * This logic tries to determine if we need to create a snapshot or not based on the action
     * if save action had no impact, there is no reason to create a snapshot for such action
     *
     * @param DataObject|Versioned $record
     * @return bool
     */
    protected function hasNewVersion(DataObject $record): bool
    {
        // in case draft is the same as live - no need to create snapshot
        if (!$record->isModifiedOnDraft()) {
            return false;
        }

        // attempt to find the most recent snapshot where the record acts as the origin
        /** @var Snapshot $snapshot */
        $snapshot = Snapshot::get()
            ->filter([
                'OriginID' => $record->ID,
                'OriginClass' => $record->baseClass(),
            ])
            ->sort([
                'LastEdited' => 'DESC',
                'ID' => 'DESC',
            ])
            ->first();

        // no such snapshot found - we need to create a snapshot
        if ($snapshot === null) {
            return true;
        }

        // attempt to find snapshot item which represents the origin
        $version = 0;
        foreach ($snapshot->Items() as $item) {
            if ((int) $item->ObjectID !== (int) $record->ID) {
                continue;
            }

            if ($item->ObjectClass !== $record->baseClass()) {
                continue;
            }

            if ($version > $item->Version) {
                continue;
            }

            $version = $item->Version;
        }

        // no version was found for the record in the snapshot history - we need to create a snapshot
        if ($version === 0) {
            return true;
        }

        // record has a newer version compated to snapshot - we need to create a snapshot
        if ($record->Version > $version) {
            return true;
        }

        // record doesn't have a new version - no need to create snapshot
        return false;
    }
}
