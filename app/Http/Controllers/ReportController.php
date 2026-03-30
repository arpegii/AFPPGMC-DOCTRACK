<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use TCPDF;

class ReportController extends Controller
{
    public function generate(Request $request)
    {
        $data = $this->resolveReportData($request);
        $format = $data['format'];

        if ($format === 'pdf') {
            return $this->generatePDF(
                $data['documents'],
                $data['startDate'],
                $data['endDate'],
                $data['statusFilter'],
                $data['documentTypeFilter']
            );
        } else {
            return $this->generateExcel(
                $data['documents'],
                $data['startDate'],
                $data['endDate'],
                $data['statusFilter'],
                $data['documentTypeFilter']
            );
        }
    }

    private function resolveReportData(Request $request): array
    {
        $rules = [
            'start_date'               => 'required|date',
            'end_date'                 => 'required|date|after_or_equal:start_date',
            'status_filter'            => 'required|in:all,received,incoming,rejected',
            'format'                   => 'required|in:pdf,excel',
            'document_type_filter'     => 'nullable|string',
            'document_type_other_filter' => 'nullable|string',
        ];

        $validated = $request->validate($rules);

        $startDate    = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate      = Carbon::parse($validated['end_date'])->endOfDay();
        $statusFilter = $validated['status_filter'];
        $format       = $validated['format'];

        // Resolve the effective document type filter:
        // If "Others" was selected, use the free-text field value instead.
        $rawTypeFilter   = $validated['document_type_filter'] ?? '';
        $otherTypeFilter = trim($validated['document_type_other_filter'] ?? '');

        if ($rawTypeFilter === 'Others' && $otherTypeFilter !== '') {
            $documentTypeFilter = $otherTypeFilter;
        } elseif ($rawTypeFilter !== '' && $rawTypeFilter !== 'Others') {
            $documentTypeFilter = $rawTypeFilter;
        } else {
            $documentTypeFilter = ''; // empty = All Types
        }

        $documents = $this->buildDocumentsQuery($startDate, $endDate, $statusFilter, $documentTypeFilter)
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'startDate'          => $startDate,
            'endDate'            => $endDate,
            'statusFilter'       => $statusFilter,
            'format'             => $format,
            'documentTypeFilter' => $documentTypeFilter,
            'rawTypeFilter'      => $rawTypeFilter,
            'documents'          => $documents,
            'analytics'          => $this->buildAnalytics($documents, $startDate, $endDate),
        ];
    }

    private function buildDocumentsQuery(Carbon $startDate, Carbon $endDate, string $statusFilter, string $documentTypeFilter = '')
    {
        $user  = auth()->user();
        $query = Document::with(['senderUnit', 'receivingUnit'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if (!$user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('sender_unit_id', $user->unit_id)
                  ->orWhere('receiving_unit_id', $user->unit_id);
            });
        }

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        // Filter by document type when one is specified.
        // For "Others", the controller already resolved the free-text value,
        // so we just do a LIKE search to catch any custom type strings.
        if ($documentTypeFilter !== '') {
            $query->where('document_type', 'LIKE', '%' . $documentTypeFilter . '%');
        }

        return $query;
    }

    private function getStatusDisplay($status)
    {
        $statusMap = [
            'incoming' => 'Pending',
            'received' => 'Received',
            'rejected' => 'Rejected',
        ];
        return $statusMap[$status] ?? ucfirst($status);
    }

    private function getFilterDisplay($statusFilter, string $documentTypeFilter = ''): string
    {
        $statusMap = [
            'all'      => 'All Documents',
            'received' => 'Received Only',
            'incoming' => 'Incoming Only',
            'rejected' => 'Rejected Only',
        ];

        $label = $statusMap[$statusFilter] ?? $statusFilter;

        if ($documentTypeFilter !== '') {
            $label .= ' — Type: ' . $documentTypeFilter;
        }

        return $label;
    }

    private function buildAnalytics($documents, $startDate, $endDate): array
    {
        $total    = $documents->count();
        $incoming = $documents->where('status', 'incoming')->count();
        $received = $documents->where('status', 'received')->count();
        $rejected = $documents->where('status', 'rejected')->count();

        $days        = max(1, $startDate->copy()->startOfDay()->diffInDays($endDate->copy()->endOfDay()) + 1);
        $avgPerDay   = round($total / $days, 2);
        $receivedRate = $total > 0 ? round(($received / $total) * 100, 2) : 0;
        $rejectedRate = $total > 0 ? round(($rejected / $total) * 100, 2) : 0;

        $topTypes = $documents
            ->groupBy('document_type')
            ->map(fn($items) => $items->count())
            ->sortDesc()
            ->take(5);

        return [
            'total'         => $total,
            'incoming'      => $incoming,
            'received'      => $received,
            'rejected'      => $rejected,
            'avg_per_day'   => $avgPerDay,
            'received_rate' => $receivedRate,
            'rejected_rate' => $rejectedRate,
            'top_types'     => $topTypes,
        ];
    }

    private function generatePDF($documents, $startDate, $endDate, $statusFilter, string $documentTypeFilter = '')
    {
        $analytics = $this->buildAnalytics($documents, $startDate, $endDate);

        $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('Document Tracking System');
        $pdf->SetAuthor('Document Tracking System');
        $pdf->SetTitle('Document Report');
        $pdf->SetSubject('Document Report');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 10);
        $pdf->AddPage();

        $margins      = $pdf->getMargins();
        $contentWidth = $pdf->getPageWidth() - $margins['left'] - $margins['right'];

        $pdf->SetFont('helvetica', '', 10);
        $currentY = 15;

        // Logo
        $logoPath = public_path('/images/logo.png');
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 130, $currentY, 30, 30, 'PNG', '', 'T', false, 300, 'C', false, false, 0, false, false, false);
            $currentY += 35;
        }

        // Title
        $pdf->SetY($currentY);
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->SetTextColor(11, 31, 58);
        $pdf->Cell(0, 10, 'Document Tracking System', 0, 1, 'C');

        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor(55, 65, 81);
        $pdf->Cell(0, 8, 'Document Report', 0, 1, 'C');
        $pdf->Ln(5);

        // Report Info
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(55, 65, 81);
        $pdf->Cell(40, 6, 'Report Period:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(107, 114, 128);
        $pdf->Cell(0, 6, $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d'), 0, 1, 'L');

        $pdf->SetTextColor(55, 65, 81);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(40, 6, 'Filter:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(107, 114, 128);
        $pdf->Cell(0, 6, $this->getFilterDisplay($statusFilter, $documentTypeFilter), 0, 1, 'L');

        // Document Type Filter row — only shown when a type is selected
        if ($documentTypeFilter !== '') {
            $pdf->SetTextColor(55, 65, 81);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(40, 6, 'Document Type:', 0, 0, 'L');
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(107, 114, 128);
            $pdf->Cell(0, 6, $documentTypeFilter, 0, 1, 'L');
        }

        $pdf->SetTextColor(55, 65, 81);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(40, 6, 'Total Documents:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(107, 114, 128);
        $pdf->Cell(0, 6, $analytics['total'], 0, 1, 'L');
        $pdf->Ln(3);

        // Analytics Summary
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(11, 31, 58);
        $pdf->Cell(0, 7, 'Analytics Summary', 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(55, 65, 81);
        $metricWidth = $contentWidth / 5;
        $pdf->SetFillColor(248, 250, 252);
        $pdf->Cell($metricWidth, 8, 'Pending: '       . $analytics['incoming'], 1, 0, 'C', true);
        $pdf->Cell($metricWidth, 8, 'Received: '      . $analytics['received'], 1, 0, 'C', true);
        $pdf->Cell($metricWidth, 8, 'Rejected: '      . $analytics['rejected'], 1, 0, 'C', true);
        $pdf->Cell($metricWidth, 8, 'Received Rate: ' . $analytics['received_rate'] . '%', 1, 0, 'C', true);
        $pdf->Cell($metricWidth, 8, 'Avg/Day: '       . $analytics['avg_per_day'], 1, 1, 'C', true);

        $topTypesLabel = 'Top Types: ';
        if ($analytics['top_types']->isEmpty()) {
            $topTypesLabel .= 'N/A';
        } else {
            $topTypesLabel .= $analytics['top_types']
                ->map(fn($count, $type) => "{$type} ({$count})")
                ->implode(', ');
        }
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Cell($contentWidth, 8, $topTypesLabel, 1, 1, 'L', true);
        $pdf->Ln(5);

        // Table Header
        $pdf->SetFillColor(11, 31, 58);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 9);

        $columnWidths = [10, 25, 55, 35, 35, 25, 20, 40];
        $headers      = ['No.', 'Document No.', 'Title', 'Sender Unit', 'Receiving Unit', 'Type', 'Status', 'Date'];

        foreach ($headers as $index => $header) {
            $pdf->Cell($columnWidths[$index], 8, $header, 1, 0, 'C', true);
        }
        $pdf->Ln();

        if (count($documents) === 0) {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(107, 114, 128);
            $pdf->Cell(array_sum($columnWidths), 20, 'No documents found for the selected period and filter.', 1, 1, 'C');
        } else {
            $pdf->SetFont('helvetica', '', 8);
            $fill = false;

            foreach ($documents as $index => $doc) {
                if ($fill) {
                    $pdf->SetFillColor(249, 250, 251);
                } else {
                    $pdf->SetFillColor(255, 255, 255);
                }

                $pdf->SetTextColor(55, 65, 81);

                if ($pdf->GetY() > 180) {
                    $pdf->AddPage();
                    $pdf->SetFillColor(11, 31, 58);
                    $pdf->SetTextColor(255, 255, 255);
                    $pdf->SetFont('helvetica', 'B', 9);
                    foreach ($headers as $idx => $header) {
                        $pdf->Cell($columnWidths[$idx], 8, $header, 1, 0, 'C', true);
                    }
                    $pdf->Ln();
                    $pdf->SetFont('helvetica', '', 8);
                }

                // No.
                $pdf->SetTextColor(55, 65, 81);
                $pdf->MultiCell($columnWidths[0], 6, ($index + 1), 1, 'C', $fill, 0, '', '', true, 0, false, true, 6, 'M');

                // Document No.
                $pdf->SetTextColor(17, 24, 39);
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->MultiCell($columnWidths[1], 6, $doc->document_number, 1, 'C', $fill, 0, '', '', true, 0, false, true, 6, 'M');
                $pdf->SetFont('helvetica', '', 8);

                // Title
                $pdf->SetTextColor(55, 65, 81);
                $pdf->MultiCell($columnWidths[2], 6, $doc->title, 1, 'L', $fill, 0, '', '', true, 0, false, true, 6, 'M');

                // Sender Unit
                $pdf->MultiCell($columnWidths[3], 6, $doc->senderUnit->name ?? '-', 1, 'C', $fill, 0, '', '', true, 0, false, true, 6, 'M');

                // Receiving Unit
                $pdf->MultiCell($columnWidths[4], 6, $doc->receivingUnit->name ?? '-', 1, 'C', $fill, 0, '', '', true, 0, false, true, 6, 'M');

                // Type — show the actual stored document_type value (which for Others
                // will be the free-text value saved at upload time)
                $pdf->SetTextColor(37, 99, 235);
                $pdf->MultiCell($columnWidths[5], 6, $doc->document_type, 1, 'C', $fill, 0, '', '', true, 0, false, true, 6, 'M');

                // Status
                if ($doc->status === 'received') {
                    $pdf->SetTextColor(5, 150, 105);
                } elseif ($doc->status === 'rejected') {
                    $pdf->SetTextColor(220, 38, 38);
                } else {
                    $pdf->SetTextColor(202, 138, 4);
                }
                $pdf->MultiCell($columnWidths[6], 6, $this->getStatusDisplay($doc->status), 1, 'C', $fill, 0, '', '', true, 0, false, true, 6, 'M');

                // Date
                $pdf->SetTextColor(107, 114, 128);
                $pdf->MultiCell($columnWidths[7], 6, $doc->created_at->format('M d, Y h:i A'), 1, 'C', $fill, 1, '', '', true, 0, false, true, 6, 'M');

                $fill = !$fill;
            }
        }

        $filename = 'document_report_' . now()->format('Y-m-d') . '.pdf';
        return response($pdf->Output($filename, 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    private function generateExcel($documents, $startDate, $endDate, $statusFilter, string $documentTypeFilter = '')
    {
        $analytics = $this->buildAnalytics($documents, $startDate, $endDate);

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Document Report');

        $currentRow = 1;

        // Logo
        $logoPath = public_path('/images/logo.png');
        if (file_exists($logoPath)) {
            $drawing = new Drawing();
            $drawing->setName('Logo');
            $drawing->setDescription('Logo');
            $drawing->setPath($logoPath);
            $drawing->setHeight(80);
            $drawing->setCoordinates('D1');
            $drawing->setWorksheet($sheet);
            $currentRow = 6;
        }

        // Title
        $sheet->mergeCells("A{$currentRow}:H{$currentRow}");
        $sheet->setCellValue("A{$currentRow}", 'Document Tracking System');
        $sheet->getStyle("A{$currentRow}")->getFont()->setSize(16)->setBold(true)->setColor(new Color('0B1F3A'));
        $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $currentRow++;

        // Subtitle
        $sheet->mergeCells("A{$currentRow}:H{$currentRow}");
        $sheet->setCellValue("A{$currentRow}", 'Document Report');
        $sheet->getStyle("A{$currentRow}")->getFont()->setSize(14)->setBold(true)->setColor(new Color('374151'));
        $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $currentRow += 2;

        // Report Info
        $sheet->setCellValue("A{$currentRow}", 'Report Period:');
        $sheet->setCellValue("B{$currentRow}", $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d'));
        $sheet->mergeCells("B{$currentRow}:D{$currentRow}");
        $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true)->setColor(new Color('374151'));
        $sheet->getStyle("B{$currentRow}")->getFont()->setColor(new Color('6B7280'));
        $currentRow++;

        $sheet->setCellValue("A{$currentRow}", 'Filter:');
        $sheet->setCellValue("B{$currentRow}", $this->getFilterDisplay($statusFilter, $documentTypeFilter));
        $sheet->mergeCells("B{$currentRow}:D{$currentRow}");
        $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true)->setColor(new Color('374151'));
        $sheet->getStyle("B{$currentRow}")->getFont()->setColor(new Color('6B7280'));
        $currentRow++;

        // Document Type row — only shown when a type filter is active
        if ($documentTypeFilter !== '') {
            $sheet->setCellValue("A{$currentRow}", 'Document Type:');
            $sheet->setCellValue("B{$currentRow}", $documentTypeFilter);
            $sheet->mergeCells("B{$currentRow}:D{$currentRow}");
            $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true)->setColor(new Color('374151'));
            $sheet->getStyle("B{$currentRow}")->getFont()->setColor(new Color('6B7280'));
            $currentRow++;
        }

        $sheet->setCellValue("A{$currentRow}", 'Total Documents:');
        $sheet->setCellValue("B{$currentRow}", $analytics['total']);
        $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true)->setColor(new Color('374151'));
        $sheet->getStyle("B{$currentRow}")->getFont()->setColor(new Color('6B7280'));
        $currentRow += 2;

        // Analytics block
        $sheet->mergeCells("A{$currentRow}:H{$currentRow}");
        $sheet->setCellValue("A{$currentRow}", 'Analytics Summary');
        $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true)->setColor(new Color('0B1F3A'));
        $sheet->getStyle("A{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE5E7EB');
        $currentRow++;

        $sheet->setCellValue("A{$currentRow}", 'Pending');
        $sheet->setCellValue("B{$currentRow}", $analytics['incoming']);
        $sheet->setCellValue("C{$currentRow}", 'Received');
        $sheet->setCellValue("D{$currentRow}", $analytics['received']);
        $sheet->setCellValue("E{$currentRow}", 'Rejected');
        $sheet->setCellValue("F{$currentRow}", $analytics['rejected']);
        $sheet->setCellValue("G{$currentRow}", 'Received %');
        $sheet->setCellValue("H{$currentRow}", $analytics['received_rate'] . '%');
        $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('D1D5DB'));
        $currentRow++;

        $sheet->setCellValue("A{$currentRow}", 'Avg per day');
        $sheet->setCellValue("B{$currentRow}", $analytics['avg_per_day']);
        $topTypesText = $analytics['top_types']->isEmpty()
            ? 'N/A'
            : $analytics['top_types']->map(fn($count, $type) => "{$type} ({$count})")->implode(', ');
        $sheet->mergeCells("C{$currentRow}:H{$currentRow}");
        $sheet->setCellValue("C{$currentRow}", 'Top Types: ' . $topTypesText);
        $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('D1D5DB'));
        $sheet->getStyle("C{$currentRow}")->getAlignment()->setWrapText(true);
        $currentRow += 2;

        // Table Headers
        $headers   = ['No.', 'Document No.', 'Title', 'Sender Unit', 'Receiving Unit', 'Type', 'Status', 'Date'];
        $headerRow = $currentRow;

        foreach ($headers as $index => $header) {
            $col = chr(65 + $index);
            $sheet->setCellValue("{$col}{$currentRow}", $header);
            $sheet->getStyle("{$col}{$currentRow}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF0B1F3A');
            $sheet->getStyle("{$col}{$currentRow}")->getFont()->setBold(true)->setColor(new Color('FFFFFF'));
            $sheet->getStyle("{$col}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("{$col}{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('CCCCCC'));
        }
        $currentRow++;

        if (count($documents) === 0) {
            $sheet->mergeCells("A{$currentRow}:H{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", 'No documents found for the selected period and filter.');
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A{$currentRow}")->getFont()->setColor(new Color('6B7280'));
            $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('CCCCCC'));
            $sheet->getRowDimension($currentRow)->setRowHeight(40);
        } else {
            foreach ($documents as $index => $doc) {
                $rowFill = $index % 2 === 0 ? 'FFF9FAFB' : 'FFFFFFFF';

                $sheet->setCellValue("A{$currentRow}", $index + 1);
                $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("A{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($rowFill);
                $sheet->getStyle("A{$currentRow}")->getFont()->setColor(new Color('374151'));

                $sheet->setCellValue("B{$currentRow}", $doc->document_number);
                $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("B{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($rowFill);
                $sheet->getStyle("B{$currentRow}")->getFont()->setBold(true)->setColor(new Color('111827'));

                $sheet->setCellValue("C{$currentRow}", $doc->title);
                $sheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
                $sheet->getStyle("C{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($rowFill);
                $sheet->getStyle("C{$currentRow}")->getFont()->setColor(new Color('374151'));

                $sheet->setCellValue("D{$currentRow}", $doc->senderUnit->name ?? '-');
                $sheet->getStyle("D{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("D{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($rowFill);
                $sheet->getStyle("D{$currentRow}")->getFont()->setColor(new Color('374151'));

                $sheet->setCellValue("E{$currentRow}", $doc->receivingUnit->name ?? '-');
                $sheet->getStyle("E{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("E{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($rowFill);
                $sheet->getStyle("E{$currentRow}")->getFont()->setColor(new Color('374151'));

                // Type — renders the actual stored value (free-text for Others docs)
                $sheet->setCellValue("F{$currentRow}", $doc->document_type);
                $sheet->getStyle("F{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("F{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($rowFill);
                $sheet->getStyle("F{$currentRow}")->getFont()->setColor(new Color('2563EB'));

                $statusColor = $doc->status === 'received' ? '059669' : ($doc->status === 'rejected' ? 'DC2626' : 'CA8A04');
                $sheet->setCellValue("G{$currentRow}", $this->getStatusDisplay($doc->status));
                $sheet->getStyle("G{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("G{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($rowFill);
                $sheet->getStyle("G{$currentRow}")->getFont()->setColor(new Color($statusColor));

                $sheet->setCellValue("H{$currentRow}", $doc->created_at->format('M d, Y h:i A'));
                $sheet->getStyle("H{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("H{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($rowFill);
                $sheet->getStyle("H{$currentRow}")->getFont()->setColor(new Color('6B7280'));

                $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('CCCCCC'));

                $currentRow++;
            }
        }

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(22);

        for ($row = $headerRow; $row < $currentRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(25);
        }

        $filename = 'document_report_' . now()->format('Y-m-d') . '.xlsx';
        $writer   = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
}