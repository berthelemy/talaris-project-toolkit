<?php

/**
 * File documentation for app/Libraries/Modules/HelloWorldModuleApi.php.
 */

namespace App\Libraries\Modules;

use App\Modules\HelloWorldProgramme\Models\HelloWorldEntryModel as HelloWorldProgrammeEntryModel;
use App\Modules\HelloWorldProject\Models\HelloWorldEntryModel as HelloWorldProjectEntryModel;

class HelloWorldModuleApi implements ModuleApiInterface
{
    public function read(string $moduleSlug, string $resource, array $query, int $requesterId): array
    {
        if ($resource !== 'entries') {
            return ['ok' => false, 'error' => 'unsupported_resource'];
        }

        $scopeType = (string) ($query['scope_type'] ?? '');
        $scopeId = (int) ($query['scope_id'] ?? 0);

        $entries = $this->modelForSlug($moduleSlug)
            ->where('module_slug', $moduleSlug)
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->orderBy('id', 'DESC')
            ->limit(50)
            ->findAll();

        return ['ok' => true, 'data' => $entries];
    }

    public function create(string $moduleSlug, string $resource, array $data, int $requesterId): array
    {
        if ($resource !== 'entries') {
            return ['ok' => false, 'error' => 'unsupported_resource'];
        }

        $message = trim((string) ($data['message'] ?? ''));
        if ($message === '' || strlen($message) > 500) {
            return ['ok' => false, 'error' => 'invalid_message'];
        }

        $id = $this->modelForSlug($moduleSlug)->insert([
            'module_slug' => $moduleSlug,
            'scope_type' => (string) ($data['scope_type'] ?? ''),
            'scope_id' => (int) ($data['scope_id'] ?? 0),
            'message' => $message,
            'created_by_user_id' => $requesterId,
        ], true);

        if (! is_int($id)) {
            return ['ok' => false, 'error' => 'insert_failed'];
        }

        return ['ok' => true, 'id' => $id];
    }

    public function update(string $moduleSlug, string $resource, int $id, array $data, int $requesterId): array
    {
        if ($resource !== 'entries') {
            return ['ok' => false, 'error' => 'unsupported_resource'];
        }

        $model = $this->modelForSlug($moduleSlug);
        $entry = $model->find($id);
        if (! is_array($entry) || (string) ($entry['module_slug'] ?? '') !== $moduleSlug) {
            return ['ok' => false, 'error' => 'not_found'];
        }

        $message = trim((string) ($data['message'] ?? ''));
        if ($message === '' || strlen($message) > 500) {
            return ['ok' => false, 'error' => 'invalid_message'];
        }

        $lastUpdatedAt = (string) ($data['last_updated_at'] ?? '');
        if ($lastUpdatedAt !== '' && $lastUpdatedAt !== (string) ($entry['updated_at'] ?? '')) {
            return ['ok' => false, 'error' => 'conflict'];
        }

        $model->update($id, ['message' => $message]);
        $updated = $model->find($id);

        return ['ok' => true, 'entry' => $updated];
    }

    /**
     * @return HelloWorldProjectEntryModel|HelloWorldProgrammeEntryModel
     */
    private function modelForSlug(string $moduleSlug): HelloWorldProjectEntryModel|HelloWorldProgrammeEntryModel
    {
        if ($moduleSlug === ModuleRegistryService::HELLO_WORLD_PROGRAMME) {
            return new HelloWorldProgrammeEntryModel();
        }

        return new HelloWorldProjectEntryModel();
    }
}
