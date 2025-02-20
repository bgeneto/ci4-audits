<?php

use Tests\Support\DatabaseTestCase;
use Tests\Support\Models\WidgetModel;

/**
 * @internal
 */
final class TraitTest extends DatabaseTestCase
{
    public function testInsertAddsAudit()
    {
        $widget = $this->fabricator->make();

        $result = $this->model->insert($widget);
        $this->assertIsInt($result);

        $expected = [
            'source'    => 'widgets',
            'source_id' => $result,
            'event'     => 'insert',
            'summary'   => '5 fields: name, uid, summary, created_at, updated_at',
            'user_id'   => 0,
            'data'      => json_encode((array) $widget),
        ];

        $queue = service('audits')->getQueue();

        $this->assertCount(1, $queue);
        $this->seeAudit($expected);
    }

    public function testUpdateAddsAudit()
    {
        $widget   = fake(WidgetModel::class);
        $widgetId = $widget->id; // @phpstan-ignore-line

        $this->model->update($widgetId, [
            'name' => 'Banana Widget',
        ]);

        $expected = [
            'source'    => 'widgets',
            'source_id' => $widgetId,
            'event'     => 'update',
            'summary'   => '2 fields: name, updated_at',
            'user_id'   => 0,
            'data'      => json_encode(['name' => 'Banana Widget']),
        ];

        $queue = service('audits')->getQueue();

        $this->assertCount(2, $queue);
        $this->seeAudit($expected);
    }

    public function testUpdateAddsMultiple()
    {
        $widget1 = fake(WidgetModel::class);
        $widget2 = fake(WidgetModel::class);
        $ids     = [$widget1->id, $widget2->id]; // @phpstan-ignore-line

        $this->model->update($ids, [
            'name' => 'Banana Widget',
        ]);

        $expected = [
            'source'    => 'widgets',
            'source_id' => $ids[0],
            'event'     => 'update',
            'summary'   => '2 fields: name, updated_at',
            'user_id'   => 0,
            'data'      => json_encode(['name' => 'Banana Widget']),
        ];

        $queue = service('audits')->getQueue();

        $this->assertCount(4, $queue);
        $this->seeAudit($expected);
    }

    public function testUpdateNoChanges()
    {
        $widget   = (object) fake(WidgetModel::class);
        $widgetId = $widget->id;

        $this->model->update($widgetId, [
            'name' => $widget->name,
        ]);

        $queue = service('audits')->getQueue();

        $this->assertCount(2, $queue);
    }

    public function testUpdateMultipleFields()
    {
        $widget   = fake(WidgetModel::class);
        $widgetId = $widget->id; // @phpstan-ignore-line

        $this->model->update($widgetId, [
            'name'    => 'Banana Widget',
            'summary' => 'Updated summary',
        ]);

        $expected = [
            'source'    => 'widgets',
            'source_id' => $widgetId,
            'event'     => 'update',
            'summary'   => '3 fields: name, summary, updated_at',
            'user_id'   => 0,
            'data'      => json_encode(['name' => 'Banana Widget', 'summary' => 'Updated summary']),
        ];

        $queue = service('audits')->getQueue();

        $this->assertCount(2, $queue);
        $this->seeAudit($expected);
    }

    public function testDeleteAddsAudit()
    {
        $widget   = fake(WidgetModel::class);
        $widgetId = $widget->id; // @phpstan-ignore-line

        $this->model->delete($widgetId);

        $expected = [
            'source'    => 'widgets',
            'source_id' => $widgetId,
            'event'     => 'delete',
            'summary'   => 'soft',
            'user_id'   => 0,
            'data'      => json_encode([]),
        ];

        $queue = service('audits')->getQueue();

        $this->assertCount(2, $queue);
        $this->seeAudit($expected);
    }

    public function testAuditEvent()
    {
        $event = 'App\Controllers\Api\AuthController::register';
        $summary = 'Registration request';
        $data = [
            "origem_sigla" => "pf",
            "plataforma_id" => "8",
            "email" => "georgiafd@hotmail.com",
            "agree" => ""
        ];

        $this->model->auditEvent($event, $summary, $data);

        $expected = [
            'source'    => 'Tests\Support\Models\WidgetModel',
            'source_id' => null,
            'event'     => $event,
            'summary'   => $summary,
            'user_id'   => 0,
            'data'      => json_encode($data),
        ];

        $queue = service('audits')->getQueue();

        $this->assertCount(1, $queue);
        $this->seeAudit($expected);
    }
}
