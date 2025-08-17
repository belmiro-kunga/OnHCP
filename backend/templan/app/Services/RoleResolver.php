<?php

namespace App\Services;

use App\Models\RoleMapping;

class RoleResolver
{
    /**
     * Resolve a role_id for a user based on provided attributes.
     * Attributes supported: department_id, job_title, ad_groups (array of strings)
     */
    public function resolve(array $attributes): ?int
    {
      $deptId = $attributes['department_id'] ?? null;
      $job = isset($attributes['job_title']) ? trim((string)$attributes['job_title']) : null;
      $adGroups = array_filter(array_map('strval', (array)($attributes['ad_groups'] ?? [])));

      // Fetch active mappings ordered by priority asc
      $mappings = RoleMapping::query()
        ->where('active', true)
        ->orderBy('priority')
        ->get();

      // 1) Match by AD group
      if (!empty($adGroups)) {
        foreach ($mappings as $m) {
          if ($m->ad_group && in_array($m->ad_group, $adGroups, true)) {
            return (int)$m->role_id;
          }
        }
      }

      // 2) Match by department + job_title
      if ($deptId || $job) {
        foreach ($mappings as $m) {
          if ($m->department_id && (int)$m->department_id === (int)$deptId) {
            if ($m->job_title && $job) {
              if (strcasecmp($m->job_title, $job) === 0) return (int)$m->role_id;
            }
          }
        }
      }

      // 3) Match by department only
      if ($deptId) {
        foreach ($mappings as $m) {
          if ($m->department_id && (int)$m->department_id === (int)$deptId && !$m->job_title) {
            return (int)$m->role_id;
          }
        }
      }

      return null;
    }
}
