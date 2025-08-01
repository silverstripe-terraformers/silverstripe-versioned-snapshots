<?php

namespace SilverStripe\Snapshots\Workflow;

use SilverStripe\Core\Extension;
use SilverStripe\EventDispatcher\Dispatch\Dispatcher;
use SilverStripe\EventDispatcher\Symfony\Event;
use SilverStripe\ORM\DataObject;

class WorkflowExtension extends Extension
{
    protected function onAfterWorkflowPublish(DataObject $target): void
    {
        $record = DataObject::get_by_id($target::class, $target->ID, false);
        Dispatcher::singleton()->trigger('workflowComplete', new Event('publish', [
            'record' => $record,
        ]));
    }

    protected function onAfterWorkflowUnpublish(DataObject $target): void
    {
        $record = DataObject::get_by_id($target::class, $target->ID, false);
        Dispatcher::singleton()->trigger('workflowComplete', new Event('publish', [
            'record' => $record,
        ]));
    }
}
