<?php

namespace App\Http\Controllers;

use App\Models\Bug;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private function buildFilteredQuery(Request $request)
    {
        $query = Bug::with('reporter');

        $period = $request->input('period', '30days');
        if ($period === '7days') {
            $query->where('created_at', '>=', now()->subDays(7));
        } elseif ($period === '30days') {
            $query->where('created_at', '>=', now()->subDays(30));
        } elseif ($period === '90days') {
            $query->where('created_at', '>=', now()->subDays(90));
        } elseif ($period === 'this_year') {
            $query->where('created_at', '>=', now()->startOfYear());
        }

        if ($request->filled('project')) {
            $query->where('project', $request->project);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('status')) {
            if ($request->status === 'new') {
                $query->whereIn('status', ['new', 'open']);
            } elseif ($request->status === 'done_by_development') {
                $query->whereIn('status', ['done_by_development', 'fixed', 'resolved']);
            } elseif ($request->status === 'done_by_support_qa') {
                $query->whereIn('status', ['done_by_support_qa', 'retest']);
            } else {
                $query->where('status', $request->status);
            }
        }

        return $query;
    }

    public function index(Request $request)
    {
        if ($request->user()->role === 'developer') {
            return redirect()->route('bugs');
        }

        $query = $this->buildFilteredQuery($request);
        $bugs = $query->orderBy('created_at', 'desc')->get();

        $totalBugsCount = $bugs->count();

        $projects = Bug::whereNotNull('project')->where('project', '!=', '')->distinct('project')->pluck('project');

        $devs = $bugs->whereNotNull('developer')->where('developer', '!=', '')->pluck('developer')->unique();
        $devCount = max($devs->count(), 1);
        $bugsPerDev = $totalBugsCount > 0 ? round($totalBugsCount / $devCount, 1) : 0;

        $resolvedBugs = $bugs->whereIn('status', ['done_by_development', 'done_by_support_qa', 'fixed', 'resolved', 'closed']);
        if ($resolvedBugs->count() > 0) {
            $totalDays = $resolvedBugs->reduce(function ($carry, $bug) {
                $hours = abs($bug->updated_at->diffInMinutes($bug->created_at)) / 60;

                return $carry + max($hours / 24, 0.1);
            }, 0);
            $avgResolutionTime = round($totalDays / $resolvedBugs->count(), 1);
        } else {
            $avgResolutionTime = 0;
        }

        $resolvedCount = $resolvedBugs->count();
        $openCount = $bugs->whereIn('status', ['new', 'open'])->count();
        $inProgressCount = $bugs->where('status', 'in_progress')->count();

        return view('reports', compact(
            'bugs',
            'totalBugsCount',
            'bugsPerDev',
            'avgResolutionTime',
            'resolvedCount',
            'openCount',
            'inProgressCount',
            'projects'
        ));
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $user = $request->user();
        if ($user->role === 'developer') {
            abort(403, 'Developers are not authorized to export reports.');
        }

        $query = $this->buildFilteredQuery($request);
        $bugs = $query->orderBy('created_at', 'desc')->get();
        $filename = 'qatrack_bugs_report_'.now()->format('Y-m-d_His').'.xls';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($bugs, $user) {
            $exportDate = now()->format('M d, Y H:i:s');
            $totalCount = $bugs->count();
            $roleName = strtoupper(str_replace('_', ' ', $user->role));

            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head>';
            echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
            echo '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Bugs Report</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
            echo '<style>';
            echo 'body { font-family: Calibri, "Segoe UI", Arial, sans-serif; margin: 0; padding: 20px; }';
            echo '.header-title { font-size: 16pt; font-weight: bold; color: #1e3a8a; height: 35px; vertical-align: middle; }';
            echo '.meta-info { font-size: 10pt; color: #475569; height: 24px; vertical-align: middle; }';
            echo '.spacer { height: 12px; }';
            echo 'table { border-collapse: collapse; width: 100%; }';
            echo 'th { background-color: #1e3a8a; color: #ffffff; font-size: 11pt; font-weight: bold; text-align: center; vertical-align: middle; height: 32px; border: 1px solid #1e3a8a; }';
            echo 'td { font-size: 10pt; vertical-align: middle; padding: 8px 10px; border: 1px solid #cbd5e1; }';
            echo '.text-center { text-align: center; }';
            echo '.text-left { text-align: left; }';
            echo '.even-row { background-color: #f8fafc; }';
            echo '.badge-p1 { background-color: #fee2e2; color: #991b1b; font-weight: bold; text-align: center; }';
            echo '.badge-p2 { background-color: #fef3c7; color: #92400e; font-weight: bold; text-align: center; }';
            echo '.badge-p3 { background-color: #eff6ff; color: #1e40af; font-weight: bold; text-align: center; }';
            echo '.status-open { background-color: #f1f5f9; color: #475569; font-weight: 600; text-align: center; }';
            echo '.status-in_progress { background-color: #dbeafe; color: #1e40af; font-weight: 600; text-align: center; }';
            echo '.status-fixed { background-color: #dcfce7; color: #166534; font-weight: 600; text-align: center; }';
            echo '.status-retest { background-color: #f3e8ff; color: #6b21a8; font-weight: 600; text-align: center; }';
            echo '.status-closed { background-color: #e2e8f0; color: #334155; font-weight: 600; text-align: center; }';
            echo '</style>';
            echo '</head>';
            echo '<body>';
            echo '<table>';
            echo '<tr><td colspan="10" class="header-title" style="border:none;">QATrack - Bugs &amp; Issues Analytics Report</td></tr>';
            echo "<tr><td colspan=\"10\" class=\"meta-info\" style=\"border:none;\">Export Date: {$exportDate} | Total Records: {$totalCount} | Exported By: ".htmlspecialchars($user->name)." ({$roleName})</td></tr>";
            echo '<tr><td colspan="10" class="spacer" style="border:none;"></td></tr>';
            echo '<thead>';
            echo '<tr>';
            echo '<th style="width: 100px;">Bug ID</th>';
            echo '<th style="width: 250px;">Title</th>';
            echo '<th style="width: 140px;">Project</th>';
            echo '<th style="width: 100px;">Priority</th>';
            echo '<th style="width: 130px;">Status</th>';
            echo '<th style="width: 160px;">Developer</th>';
            echo '<th style="width: 140px;">Deadline</th>';
            echo '<th style="width: 160px;">Reporter</th>';
            echo '<th style="width: 150px;">Created Date</th>';
            echo '<th style="width: 350px;">Description</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            foreach ($bugs as $index => $bug) {
                $evenClass = ($index % 2 === 1) ? ' even-row' : '';
                $bugCode = 'BUG-'.str_pad($bug->id, 4, '0', STR_PAD_LEFT);
                $title = htmlspecialchars($bug->title);
                $project = htmlspecialchars($bug->project ?: '-');
                $priority = strtoupper($bug->priority);
                $priorityClass = 'badge-'.strtolower($bug->priority);
                $statusFormatted = ucfirst(str_replace('_', ' ', $bug->status));
                $statusClass = 'status-'.strtolower($bug->status);
                $developer = htmlspecialchars($bug->developer ?: 'Unassigned');
                $deadlineFormatted = $bug->deadline ? $bug->deadline->format('Y-m-d') : '-';
                $reporter = htmlspecialchars($bug->reporter ? $bug->reporter->name : 'N/A');
                $createdDate = $bug->created_at->format('Y-m-d H:i');
                $description = htmlspecialchars($bug->description);

                echo "<tr class=\"{$evenClass}\">";
                echo "<td class=\"text-center\" style=\"font-weight:600;\">{$bugCode}</td>";
                echo "<td class=\"text-left\">{$title}</td>";
                echo "<td class=\"text-left\">{$project}</td>";
                echo "<td class=\"{$priorityClass}\">{$priority}</td>";
                echo "<td class=\"{$statusClass}\">{$statusFormatted}</td>";
                echo "<td class=\"text-left\">{$developer}</td>";
                echo "<td class=\"text-center\">{$deadlineFormatted}</td>";
                echo "<td class=\"text-left\">{$reporter}</td>";
                echo "<td class=\"text-center\">{$createdDate}</td>";
                echo "<td class=\"text-left\">{$description}</td>";
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</body>';
            echo '</html>';
        }, 200, $headers);
    }
}
