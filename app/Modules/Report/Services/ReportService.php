<?php

namespace App\Modules\Report\Services;

use App\Modules\Attempt\Repositories\Contracts\AttemptRepositoryInterface;
use App\Modules\Report\Models\ReportExport;
use App\Modules\Test\Repositories\Contracts\TestRepositoryInterface;
use App\Shared\Services\BaseService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ReportService extends BaseService
{
    public function __construct(
        private TestRepositoryInterface $testRepository,
        private AttemptRepositoryInterface $attemptRepository,
    ) {
    }

    public function testReport(string $testId, string $tenantId, int $page = 1, int $perPage = 15): array
    {
        $test = $this->testRepository->findByIdAndTenant($testId, $tenantId);

        if (!$test) {
            throw ValidationException::withMessages([
                'test' => ['Test not found.'],
            ]);
        }

        $attempts = $this->attemptRepository->listFinalizedByTest($testId, $tenantId, $page, $perPage);
        $allFinalized = $this->attemptRepository->listFinalizedByTest($testId, $tenantId, 1, 100000);
        $scores = collect($allFinalized->items())->pluck('total_score')->filter(fn ($score) => $score !== null);
        $passed = collect($allFinalized->items())->where('is_passed', true)->count();
        $failed = collect($allFinalized->items())->where('is_passed', false)->count();

        return [
            'test' => [
                'id' => $test->id,
                'title' => $test->title,
                'passing_score' => $test->passing_score,
            ],
            'summary' => [
                'finalized_attempts' => $allFinalized->total(),
                'passed' => $passed,
                'failed' => $failed,
                'average_score' => $scores->isEmpty() ? null : round($scores->avg(), 2),
                'highest_score' => $scores->isEmpty() ? null : $scores->max(),
                'lowest_score' => $scores->isEmpty() ? null : $scores->min(),
            ],
            'attempts' => $attempts,
        ];
    }

    public function exportTestReport(string $testId, string $tenantId, string $requestedBy): array
    {
        $report = $this->testReport($testId, $tenantId, 1, 100000);
        $export = ReportExport::create([
            'tenant_id' => $tenantId,
            'test_id' => $testId,
            'requested_by' => $requestedBy,
            'status' => 'processing',
        ]);

        $path = "report_exports/{$export->id}.csv";
        Storage::disk('local')->put($path, $this->buildCsv($report));

        $export->update([
            'status' => 'completed',
            'file_path' => $path,
            'completed_at' => now(),
        ]);

        return [
            'job_id' => $export->id,
            'status' => $export->status,
            'download_endpoint' => "/api/v1/jobs/{$export->id}/download",
        ];
    }

    public function resolveExport(string $jobId, string $tenantId): ReportExport
    {
        $export = ReportExport::where('tenant_id', $tenantId)->where('id', $jobId)->first();

        if (!$export || $export->status !== 'completed' || !$export->file_path || !Storage::disk('local')->exists($export->file_path)) {
            throw ValidationException::withMessages([
                'job' => ['Export is not ready for download.'],
            ]);
        }

        return $export;
    }

    private function buildCsv(array $report): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Test ID', $report['test']['id']]);
        fputcsv($handle, ['Title', $report['test']['title']]);
        fputcsv($handle, ['Passing Score', $report['test']['passing_score']]);
        fputcsv($handle, []);
        fputcsv($handle, ['Finalized Attempts', $report['summary']['finalized_attempts']]);
        fputcsv($handle, ['Passed', $report['summary']['passed']]);
        fputcsv($handle, ['Failed', $report['summary']['failed']]);
        fputcsv($handle, ['Average Score', $report['summary']['average_score']]);
        fputcsv($handle, []);
        fputcsv($handle, ['Attempt ID', 'Assignee ID', 'Assignee Email', 'Total Score', 'Passed', 'Finalized At']);

        foreach ($report['attempts']->items() as $attempt) {
            fputcsv($handle, [
                $attempt->id,
                $attempt->assignee_id,
                $attempt->assignment?->assignee?->email,
                $attempt->total_score,
                $attempt->is_passed ? 'yes' : 'no',
                $attempt->updated_at,
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
