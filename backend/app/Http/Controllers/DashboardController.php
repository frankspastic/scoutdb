<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\Person;
use App\Models\Scout;
use App\Models\AdultLeader;
use App\Models\AuditLog;
use App\Models\SyncLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function statistics()
    {
        $now = now();

        $stats = [
            'families' => [
                'total' => Family::count(),
                'active' => Family::whereNull('deleted_at')->count(),
            ],
            'persons' => [
                'total' => Person::count(),
                'scouts' => Person::scouts()->count(),
                'parents' => Person::parents()->count(),
                'siblings' => Person::where('person_type', 'sibling')->count(),
                'leaders' => Person::where('person_type', 'adult_leader')->count(),
                'orphaned' => Person::orphaned()->count(),
            ],
            'scouts' => [
                'total' => Scout::count(),
                'active' => Scout::where('registration_status', 'active')
                    ->where('registration_expiration_date', '>', $now)
                    ->count(),
                'expiring_soon' => Scout::where('registration_status', 'active')
                    ->whereBetween('registration_expiration_date', [$now, $now->copy()->addDays(30)])
                    ->count(),
                'expired' => Scout::where('registration_expiration_date', '<', $now)->count(),
            ],
            'leaders' => [
                'total' => AdultLeader::count(),
                'ypt_current' => AdultLeader::where('ypt_expiration_date', '>', $now->copy()->addDays(30))->count(),
                'ypt_expiring_soon' => AdultLeader::whereBetween('ypt_expiration_date', [
                    $now,
                    $now->copy()->addDays(30)
                ])->count(),
                'ypt_expired' => AdultLeader::where('ypt_expiration_date', '<', $now)->count(),
                'ypt_unknown' => AdultLeader::whereNull('ypt_expiration_date')->count(),
            ],
        ];

        return response()->json($stats);
    }

    public function recentActivity(Request $request)
    {
        $limit = $request->input('limit', 10);

        $activity = AuditLog::with('user')
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'entity_type' => $log->entity_type,
                    'entity_id' => $log->entity_id,
                    'changes' => $log->changes,
                    'created_at' => $log->created_at,
                ];
            });

        return response()->json($activity);
    }

    public function expiringRecords(Request $request)
    {
        $days = $request->input('days', 60);
        $now = now();

        $expiring_scouts = Scout::with('person.family')
            ->whereBetween('registration_expiration_date', [$now, $now->copy()->addDays($days)])
            ->where('registration_status', 'active')
            ->get();

        $expiring_ypt = AdultLeader::with('person.family')
            ->whereBetween('ypt_expiration_date', [$now, $now->copy()->addDays($days)])
            ->get();

        return response()->json([
            'scouts' => $expiring_scouts,
            'leaders' => $expiring_ypt,
        ]);
    }

    public function orphanedPersons()
    {
        $orphaned = Person::orphaned()
            ->with('scout', 'leader')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(25);

        return response()->json($orphaned);
    }

    public function syncStatus()
    {
        $scoutbook_sync = SyncLog::where('sync_type', 'scoutbook')
            ->latest('created_at')
            ->first();

        $mailchimp_sync = SyncLog::where('sync_type', 'mailchimp_import')
            ->latest('created_at')
            ->first();

        return response()->json([
            'scoutbook' => $scoutbook_sync ? [
                'status' => $scoutbook_sync->status,
                'last_sync' => $scoutbook_sync->created_at,
                'records_processed' => $scoutbook_sync->records_processed,
                'created' => $scoutbook_sync->created,
                'updated' => $scoutbook_sync->updated,
                'skipped' => $scoutbook_sync->skipped,
                'errors' => $scoutbook_sync->errors,
            ] : null,
            'mailchimp' => $mailchimp_sync ? [
                'status' => $mailchimp_sync->status,
                'last_sync' => $mailchimp_sync->created_at,
                'records_processed' => $mailchimp_sync->records_processed,
                'created' => $mailchimp_sync->created,
                'updated' => $mailchimp_sync->updated,
                'skipped' => $mailchimp_sync->skipped,
                'errors' => $mailchimp_sync->errors,
            ] : null,
        ]);
    }

    public function syncHistory(Request $request)
    {
        $type = $request->input('type');
        $limit = $request->input('limit', 10);

        $history = SyncLog::when($type, function ($q) use ($type) {
            return $q->where('sync_type', $type);
        })
        ->latest('created_at')
        ->limit($limit)
        ->get();

        return response()->json($history);
    }

    public function familyMembers(Request $request)
    {
        $family_id = $request->input('family_id');
        $family = Family::with('persons', 'scouts', 'parents', 'siblings', 'leaders')
            ->findOrFail($family_id);

        return response()->json([
            'family' => $family,
            'summary' => [
                'total_members' => $family->persons->count(),
                'scouts' => $family->scouts->count(),
                'parents' => $family->parents->count(),
                'siblings' => $family->siblings->count(),
                'leaders' => $family->leaders->count(),
            ],
        ]);
    }

    public function denMembership()
    {
        $dens = Scout::select('den')
            ->distinct()
            ->orderBy('den')
            ->get()
            ->map(function ($scout) {
                return [
                    'den' => $scout->den,
                    'count' => Scout::where('den', $scout->den)
                        ->where('registration_status', 'active')
                        ->count(),
                ];
            });

        return response()->json($dens);
    }

    public function rankDistribution()
    {
        $ranks = Scout::select('rank')
            ->distinct()
            ->orderBy('rank')
            ->get()
            ->map(function ($scout) {
                return [
                    'rank' => $scout->rank,
                    'count' => Scout::where('rank', $scout->rank)
                        ->where('registration_status', 'active')
                        ->count(),
                ];
            });

        return response()->json($ranks);
    }
}
