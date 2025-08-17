<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\RoleResolver;

class LdapSyncGroups extends Command
{
    protected $signature = 'ldap:sync-groups {--limit=500 : Users per batch}';
    protected $description = 'Sync user group memberships from LDAP/AD and auto-assign roles via RoleResolver.';

    public function handle(): int
    {
        if (!config('ldap.default.hosts') && !env('LDAP_HOST')) {
            $this->warn('LDAP not configured. Skipping.');
            return self::SUCCESS;
        }

        $limit = (int)$this->option('limit');
        /** @var RoleResolver $resolver */
        $resolver = app(RoleResolver::class);

        $count = 0;

        // Attempt to use LdapRecord if available
        $ldapAvailable = class_exists('LdapRecord\\Connection');
        if (!$ldapAvailable) {
            $this->warn('LdapRecord not installed. Run composer require directorytree/ldaprecord-laravel');
            return self::SUCCESS;
        }

        // Build LDAP connection
        try {
            $connection = app('ldap'); // requires LdapRecord service provider
        } catch (\Throwable $e) {
            $this->error('LDAP connection not available: '.$e->getMessage());
            return self::FAILURE;
        }

        User::query()->orderBy('id')->chunk($limit, function ($users) use (&$count, $resolver, $connection) {
            foreach ($users as $user) {
                try {
                    // Match by email or userPrincipalName
                    $filter = sprintf('(|(mail=%s)(userPrincipalName=%s))', $user->email, $user->email);
                    $search = $connection->query()->whereRaw($filter)->first();
                    $groups = [];
                    if ($search) {
                        // Load memberOf attribute or query group memberships
                        $memberOf = (array) ($search->getFirstAttribute('memberOf') ?? []);
                        foreach ($memberOf as $dn) {
                            $groups[] = (string)$dn;
                        }
                    }

                    $user->ad_groups = $groups;
                    $user->save();

                    // Resolve role based on AD groups if none or if changed
                    $resolved = $resolver->resolve([
                        'department_id' => $user->department_id,
                        'job_title' => $user->job_title ?? null,
                        'ad_groups' => $groups,
                    ]);
                    if ($resolved && (int)($user->role_id ?? 0) !== (int)$resolved) {
                        $user->role_id = $resolved;
                        $user->save();
                    }
                    $count++;
                } catch (\Throwable $e) {
                    $this->error('User '.$user->id.' sync error: '.$e->getMessage());
                }
            }
        });

        $this->info('LDAP group sync completed. Users processed: '.$count);
        return self::SUCCESS;
    }
}
