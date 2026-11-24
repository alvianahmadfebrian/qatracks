<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>QATrack - Laporan Rekapitulasi Bug</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            color: #1e293b;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }

        /* Header styling */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .brand-title {
            font-size: 18pt;
            font-weight: bold;
            color: #1e3a8a;
            letter-spacing: -0.5px;
            margin: 0;
        }
        .brand-subtitle {
            font-size: 8pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }
        .doc-title {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            text-align: right;
            margin: 0;
        }
        .doc-meta {
            font-size: 8pt;
            color: #64748b;
            text-align: right;
            margin-top: 3px;
        }

        /* Filter Summary Info */
        .filter-box {
            background-color: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px 12px;
            margin-bottom: 12px;
            font-size: 8pt;
            color: #334155;
        }
        .filter-box strong {
            color: #0f172a;
        }

        /* Main Data Table */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.data-table th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 7px 6px;
            border: 1px solid #1e3a8a;
            text-align: center;
        }
        table.data-table td {
            font-size: 8pt;
            padding: 6px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* Alignment helpers */
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }

        /* Priority Badges */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7.5pt;
            font-weight: bold;
            text-align: center;
        }
        .badge-p1 {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .badge-p2 {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .badge-p3 {
            background-color: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7.5pt;
            font-weight: 600;
        }
        .status-new {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
        .status-in-progress {
            background-color: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        .status-dev-done {
            background-color: #fef9c3;
            color: #854d0e;
            border: 1px solid #fef08a;
        }
        .status-qa-done, .status-closed {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        /* Resolution time text */
        .resolution-closed {
            color: #166534;
            font-weight: bold;
        }
        .resolution-pending {
            color: #94a3b8;
            font-style: italic;
        }

        /* Footer & Signatures */
        .signature-table {
            width: 100%;
            margin-top: 20px;
            border: none;
        }
        .signature-box {
            text-align: center;
            width: 35%;
            font-size: 8.5pt;
        }
        .signature-space {
            height: 50px;
        }

        .footer-note {
            margin-top: 15px;
            padding-top: 6px;
            border-top: 1px solid #e2e8f0;
            font-size: 7.5pt;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 50%; vertical-align: bottom;">
                <div class="brand-title">QATrack</div>
                <div class="brand-subtitle">Quality Assurance &amp; Bug Tracking System</div>
            </td>
            <td style="width: 50%; vertical-align: bottom;">
                <div class="doc-title">REKAPITULASI LAPORAN BUG</div>
                <div class="doc-meta">
                    Dicetak pada: <strong>{{ now()->format('d M Y, H:i') }} WIB</strong> | 
                    Oleh: <strong>{{ auth()->user()->name ?? 'QA Officer' }}</strong> ({{ strtoupper(str_replace('_', ' ', auth()->user()->role ?? 'QA')) }})
                </div>
            </td>
        </tr>
    </table>

    <!-- Filter & Scope Box -->
    <div class="filter-box">
        <table style="width: 100%; border: none;" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width: 25%;"><strong>Total Rekapitulasi:</strong> {{ $bugs->count() }} Data Bug</td>
                <td style="width: 25%;"><strong>Filter Periode:</strong> {{ $periodLabel ?? 'Semua Waktu' }}</td>
                <td style="width: 25%;"><strong>Project:</strong> {{ request('project') ?: 'Semua Project' }}</td>
                <td style="width: 25%;"><strong>Prioritas:</strong> {{ request('priority') ? strtoupper(request('priority')) : 'Semua Prioritas' }}</td>
            </tr>
        </table>
    </div>

    <!-- Main Data Table (Sesuai 9 Kolom Standar Penguji) -->
    <table class="data-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 9%;">Bug ID</th>
                <th style="width: 23%;">Title</th>
                <th style="width: 11%;">Project</th>
                <th style="width: 8%;">Priority</th>
                <th style="width: 14%;">Status</th>
                <th style="width: 11%;">Developer</th>
                <th style="width: 9%;">Reporter</th>
                <th style="width: 9%;">Created Date</th>
                <th style="width: 8%;">Resolution Time</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($bugs as $bug)
                @php
                    // Priority Badge formatting
                    $prioCode = strtoupper($bug->priority);
                    $prioBadgeClass = match(strtolower($bug->priority)) {
                        'p1' => 'badge-p1',
                        'p2' => 'badge-p2',
                        default => 'badge-p3',
                    };

                    // Status Badge formatting
                    $statusRaw = strtolower($bug->status);
                    $statusLabel = match($statusRaw) {
                        'new', 'open' => 'New',
                        'in_progress' => 'In Progress',
                        'done_by_development', 'fixed', 'resolved' => 'Done by Dev',
                        'done_by_support_qa' => 'Done by QA',
                        'closed' => 'Closed',
                        default => ucfirst(str_replace('_', ' ', $bug->status)),
                    };

                    $statusBadgeClass = match($statusRaw) {
                        'new', 'open' => 'status-new',
                        'in_progress' => 'status-in-progress',
                        'done_by_development', 'fixed', 'resolved' => 'status-dev-done',
                        'done_by_support_qa', 'closed' => 'status-qa-done',
                        default => 'status-new',
                    };

                    // Resolution Time calculation: khusus bug yang sudah Closed / Selesai
                    $isClosedOrDone = in_array($statusRaw, ['done_by_support_qa', 'closed', 'done_by_development', 'fixed', 'resolved']);
                    if ($isClosedOrDone && $bug->updated_at && $bug->created_at) {
                        $diffMinutes = abs($bug->updated_at->diffInMinutes($bug->created_at));
                        if ($diffMinutes < 60) {
                            $resTimeText = max($diffMinutes, 1) . ' Menit';
                        } elseif ($diffMinutes < 1440) {
                            $hours = round($diffMinutes / 60, 1);
                            $resTimeText = $hours . ' Jam';
                        } else {
                            $days = round($diffMinutes / 1440, 1);
                            $resTimeText = $days . ' Hari';
                        }
                    } else {
                        $resTimeText = '-';
                    }
                @endphp
                <tr>
                    <td class="text-center" style="font-weight: bold; color: #1e3a8a;">
                        BUG-{{ str_pad($bug->id, 4, '0', STR_PAD_LEFT) }}
                    </td>
                    <td class="text-left" style="font-weight: 500;">
                        {{ $bug->title }}
                    </td>
                    <td class="text-left">
                        {{ $bug->project ?: ($bug->projectFolder?->name ?? '-') }}
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $prioBadgeClass }}">{{ $prioCode }}</span>
                    </td>
                    <td class="text-center">
                        <span class="status-badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                    </td>
                    <td class="text-left">
                        {{ $bug->developer ?: 'Unassigned' }}
                    </td>
                    <td class="text-left">
                        {{ $bug->reporter?->name ?? 'N/A' }}
                    </td>
                    <td class="text-center">
                        {{ $bug->created_at ? $bug->created_at->format('d/m/Y') : '-' }}
                    </td>
                    <td class="text-center">
                        @if($resTimeText !== '-')
                            <span class="resolution-closed">{{ $resTimeText }}</span>
                        @else
                            <span class="resolution-pending">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px; color: #64748b;">
                        Tidak ada data bug yang sesuai dengan kriteria filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signature Block / Pengesahan Laporan -->
    <table class="signature-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="signature-box" style="text-align: left;">
                <p style="margin: 0; color: #64748b;">Keterangan Resolusi:</p>
                <p style="margin: 3px 0 0 0; font-size: 7.5pt; color: #64748b; line-height: 1.4;">
                    * <strong>Resolution Time</strong> hanya dihitung untuk tiket yang telah diselesaikan (Closed / Done).<br>
                    * Dokumen ini dibuat otomatis sebagai arsip resmi yang terkunci (non-editable).
                </p>
            </td>
            <td style="width: 30%;"></td>
            <td class="signature-box" style="text-align: right;">
                <p style="margin: 0; color: #475569;">Disiapkan &amp; Diverifikasi oleh,</p>
                <div class="signature-space"></div>
                <p style="margin: 0; font-weight: bold; text-decoration: underline; color: #0f172a;">{{ auth()->user()->name ?? 'QA Engineer' }}</p>
                <p style="margin: 2px 0 0 0; font-size: 8pt; color: #64748b;">Quality Assurance Specialist</p>
            </td>
        </tr>
    </table>

    <!-- Footer Note -->
    <div class="footer-note">
        <span>QATrack Console - Laporan Resmi Rekapitulasi Pengujian Sistem</span>
        <span>Dokumen PDF Sah &bull; Halaman 1 dari 1</span>
    </div>

</body>
</html>
