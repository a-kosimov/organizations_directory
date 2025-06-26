<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\PhoneNumber;
use App\Models\Activity;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Organization::factory()
            ->count(20)
            ->create()
            ->each(function ($organization) {
                PhoneNumber::factory()->count(rand(1, 3))->create([
                    'organization_id' => $organization->id
                ]);

                $activities = Activity::inRandomOrder()->take(rand(1, 3))->pluck('id');
                $organization->activities()->attach($activities);
            });
    }
}
