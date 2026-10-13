<?php

/** Draws a compact, paginated business report with a real grid table using core PHP only. */
final class PdfReport
{
    private const PAGE_WIDTH = 842;
    private const PAGE_HEIGHT = 595;
    private const MARGIN = 34;
    private const TABLE_TOP = 444;
    private const TABLE_HEADER_HEIGHT = 30;
    private const FOOTER_LIMIT = 44;

    public function build(array $rows, string $reportType): string
    {
        $recordCount = count($rows);
        if (!$rows) $rows = [['Report status' => 'No records found']];
        $headers = array_keys($rows[0]);
        if (!$headers) $headers = ['Report status'];
        $width = self::PAGE_WIDTH - (self::MARGIN * 2);
        $columnWidth = $width / count($headers);
        $pageStreams = [];
        $stream = '';
        $pageNumber = 0;
        $cursor = self::TABLE_TOP;

        $startPage = function () use (&$pageStreams, &$stream, &$pageNumber, &$cursor, $reportType, $rows, $headers, $columnWidth, $width, $recordCount): void {
            if ($pageNumber > 0) $pageStreams[] = $stream;
            $pageNumber++;
            $stream = '';
            $cursor = self::TABLE_TOP;
            $this->rect($stream, 0, 520, self::PAGE_WIDTH, 75, [24, 48, 81]);
            $titleSize = strlen($reportType) > 34 ? 16 : 19;
            $this->text($stream, self::MARGIN, 568, $titleSize, $reportType, [255, 255, 255], true);
            $this->text($stream, self::MARGIN, 546, 8, 'BUSINESS REPORT  /  INTERNAL USE', [211, 224, 242]);
            $this->text($stream, 650, 568, 11, 'iTrack Zimbabwe', [255, 255, 255], true);
            $this->text($stream, 650, 550, 8, 'OPERATIONS & FINANCE', [211, 224, 242]);
            $this->text($stream, self::MARGIN, 503, 8, 'Generated ' . date('Y-m-d H:i T'), [83, 99, 120]);
            $this->text($stream, 650, 503, 8, 'Records: ' . $recordCount, [83, 99, 120]);
            $summary = $this->summaryMetric($rows);
            $cardY = 466;
            $this->rect($stream, self::MARGIN, $cardY, $width, 25, [241, 245, 250]);
            $this->text($stream, self::MARGIN + 10, $cardY + 9, 8, 'REPORT  ' . strtoupper($reportType), [37, 61, 92], true);
            if ($summary !== null) $this->text($stream, 540, $cardY + 9, 8, $summary[0] . '  ' . number_format($summary[1], 2), [37, 61, 92], true);

            $headerBottom = $cursor - self::TABLE_HEADER_HEIGHT;
            $this->rect($stream, self::MARGIN, $headerBottom, $width, self::TABLE_HEADER_HEIGHT, [35, 59, 89]);
            foreach ($headers as $index => $header) {
                $x = self::MARGIN + $index * $columnWidth;
                $label = ucwords(str_replace('_', ' ', (string) $header));
                $lines = $this->wrap($label, max(10, (int) floor(($columnWidth - 10) / 4.2)), 2);
                $textY = $headerBottom + ($lines === [] ? 10 : (count($lines) === 1 ? 11 : 15));
                foreach ($lines as $line) {
                    $this->text($stream, $x + 6, $textY, 7, $line, [255, 255, 255], true);
                    $textY -= 8;
                }
                if ($index > 0) $this->line($stream, $x, $headerBottom, $x, $cursor, [176, 191, 211]);
            }
            $this->line($stream, self::MARGIN, $headerBottom, self::MARGIN + $width, $headerBottom, [176, 191, 211]);
            $cursor = $headerBottom;
        };

        $startPage();
        foreach ($rows as $rowIndex => $row) {
            $cellLines = [];
            $maxLines = 1;
            foreach ($headers as $header) {
                $textValue = $this->formatValue((string) $header, $row[$header] ?? '');
                $lines = $this->wrap($textValue, max(10, (int) floor(($columnWidth - 12) / 4.0)), 4);
                $cellLines[] = $lines;
                $maxLines = max($maxLines, count($lines));
            }
            $rowHeight = max(23, min(48, 10 + ($maxLines * 9)));
            if ($cursor - $rowHeight < self::FOOTER_LIMIT) {
                $startPage();
            }
            $bottom = $cursor - $rowHeight;
            $fill = $rowIndex % 2 === 0 ? [255, 255, 255] : [245, 248, 252];
            $this->rect($stream, self::MARGIN, $bottom, $width, $rowHeight, $fill);
            foreach ($headers as $columnIndex => $header) {
                $x = self::MARGIN + $columnIndex * $columnWidth;
                $lines = $cellLines[$columnIndex];
                $numeric = is_numeric($row[$header] ?? null);
                $baseline = $cursor - 13;
                foreach ($lines as $line) {
                    $textWidth = strlen($line) * 4.0;
                    $textX = $numeric ? max($x + 6, $x + $columnWidth - $textWidth - 7) : $x + 6;
                    $this->text($stream, $textX, $baseline, 8, $line, [45, 57, 73]);
                    $baseline -= 9;
                }
                if ($columnIndex > 0) $this->line($stream, $x, $bottom, $x, $cursor, [222, 228, 236]);
            }
            $this->line($stream, self::MARGIN, $bottom, self::MARGIN + $width, $bottom, [222, 228, 236]);
            $cursor = $bottom;
        }
        $pageStreams[] = $stream;

        $pageCount = count($pageStreams);
        foreach ($pageStreams as $index => &$pageStream) {
            $page = $index + 1;
            $this->line($pageStream, self::MARGIN, 34, self::PAGE_WIDTH - self::MARGIN, 34, [216, 223, 233]);
            $this->text($pageStream, self::MARGIN, 21, 7, 'iTrack Zimbabwe  |  Internal business report  |  Generated ' . date('Y-m-d'), [105, 118, 137]);
            $this->text($pageStream, self::PAGE_WIDTH - 103, 21, 7, 'Page ' . $page . ' of ' . $pageCount, [83, 99, 120], true);
        }
        unset($pageStream);

        return $this->makePdf($pageStreams);
    }

    private function summaryMetric(array $rows): ?array
    {
        if (!$rows) return null;
        foreach (['total_amount' => 'Reported total', 'stock_value' => 'Stock value'] as $key => $label) {
            if (!array_key_exists($key, $rows[0])) continue;
            $total = 0.0;
            foreach ($rows as $row) if (is_numeric($row[$key] ?? null)) $total += (float) $row[$key];
            return [$label, $total];
        }
        return null;
    }

    private function formatValue(string $column, mixed $value): string
    {
        if ($value === null) return '—';
        if (is_numeric($value) && preg_match('/amount|price|value|balance|debit|credit/i', $column)) return number_format((float) $value, 2, '.', ',');
        if (is_string($value)) return $this->ascii($value);
        return $this->ascii((string) $value);
    }

    private function wrap(string $value, int $width, int $limit = 4): array
    {
        $value = trim($this->ascii($value));
        if ($value === '') return ['—'];
        $words = preg_split('/\s+/', $value) ?: [$value];
        $lines = [];
        $line = '';
        foreach ($words as $word) {
            while (strlen($word) > $width) {
                $piece = substr($word, 0, $width);
                $word = substr($word, $width);
                if ($line !== '') { $lines[] = $line; $line = ''; }
                $lines[] = $piece;
                if (count($lines) >= $limit) return $lines;
            }
            $candidate = $line === '' ? $word : $line . ' ' . $word;
            if (strlen($candidate) > $width && $line !== '') {
                $lines[] = $line;
                $line = $word;
                if (count($lines) >= $limit) return $lines;
            } else {
                $line = $candidate;
            }
        }
        if ($line !== '' && count($lines) < $limit) $lines[] = $line;
        if (count($lines) === $limit && strlen(implode(' ', $words)) > strlen(implode(' ', $lines))) {
            $lines[$limit - 1] = rtrim(substr($lines[$limit - 1], 0, max(1, $width - 3))) . '...';
        }
        return $lines ?: ['—'];
    }

    private function ascii(string $value): string
    {
        if (function_exists('iconv')) $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        return preg_replace('/[^\x20-\x7E]/', '', $value) ?? '';
    }

    private function text(string &$stream, float $x, float $y, int $size, string $text, array $color = [0, 0, 0], bool $bold = false): void
    {
        $pdfText = str_replace(["\\", '(', ')', "\r", "\n"], ["\\\\", '\\(', '\\)', ' ', ' '], $this->ascii($text));
        $stream .= sprintf("BT /%s %d Tf %s rg 1 0 0 1 %.2f %.2f Tm (%s) Tj ET\n", $bold ? 'F2' : 'F1', $size, $this->rgb($color), $x, $y, $pdfText);
    }

    private function rect(string &$stream, float $x, float $y, float $width, float $height, array $color): void
    {
        $stream .= sprintf("q %s rg %.2f %.2f %.2f %.2f re f Q\n", $this->rgb($color), $x, $y, $width, $height);
    }

    private function line(string &$stream, float $x1, float $y1, float $x2, float $y2, array $color): void
    {
        $stream .= sprintf("q %s RG 0.45 w %.2f %.2f m %.2f %.2f l S Q\n", $this->rgb($color), $x1, $y1, $x2, $y2);
    }

    private function rgb(array $color): string
    {
        return implode(' ', array_map(static fn ($channel) => number_format(max(0, min(255, (int) $channel)) / 255, 3, '.', ''), $color));
    }

    private function makePdf(array $pageStreams): string
    {
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>',
        ];
        $pageRefs = [];
        foreach ($pageStreams as $index => $stream) {
            $pageId = 5 + ($index * 2);
            $contentId = $pageId + 1;
            $pageRefs[] = $pageId . ' 0 R';
            $objects[$pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' . self::PAGE_WIDTH . ' ' . self::PAGE_HEIGHT . '] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents ' . $contentId . ' 0 R >>';
            $objects[$contentId] = '<< /Length ' . strlen($stream) . ">>\nstream\n" . $stream . "\nendstream";
        }
        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $pageRefs) . '] /Count ' . count($pageStreams) . ' >>';
        ksort($objects);
        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];
        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $object . "\nendobj\n";
        }
        $xrefOffset = strlen($pdf);
        $maxId = max(array_keys($objects));
        $pdf .= "xref\n0 " . ($maxId + 1) . "\n0000000000 65535 f \n";
        for ($id = 1; $id <= $maxId; $id++) $pdf .= sprintf("%010d 00000 n \n", $offsets[$id] ?? 0);
        $pdf .= "trailer\n<< /Size " . ($maxId + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";
        return $pdf;
    }
}
