<?php

/** Minimal OOXML workbook writer for report exports; requires only PHP's zlib extension. */
final class XlsxReport
{
    public function build(array $rows): string
    {
        if (!$rows) $rows = [['Report status' => 'No records found']];
        $headers = array_keys($rows[0]);
        if (!$headers) $headers = ['Report status'];
        $allRows = [$headers];
        foreach ($rows as $row) {
            $allRows[] = array_map(static fn ($header) => $row[$header] ?? '', $headers);
        }

        $sheetRows = '';
        foreach ($allRows as $rowIndex => $row) {
            $excelRow = $rowIndex + 1;
            $sheetRows .= '<row r="' . $excelRow . '">';
            foreach (array_values($row) as $columnIndex => $value) {
                $reference = self::columnName($columnIndex + 1) . $excelRow;
                if (is_int($value) || is_float($value)) {
                    $number = is_finite((float) $value) ? (string) $value : '0';
                    $sheetRows .= '<c r="' . $reference . '" t="n"><v>' . $number . '</v></c>';
                } else {
                    $text = self::xmlText((string) $value);
                    $sheetRows .= '<c r="' . $reference . '" t="inlineStr"><is><t xml:space="preserve">' . $text . '</t></is></c>';
                }
            }
            $sheetRows .= '</row>';
        }

        $lastColumn = self::columnName(count($headers));
        $lastRow = count($allRows);
        $files = [
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Report" sheetId="1" r:id="rId1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>',
            'xl/worksheets/sheet1.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><dimension ref="A1:' . $lastColumn . $lastRow . '"/><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><sheetFormatPr defaultRowHeight="15"/><sheetData>' . $sheetRows . '</sheetData><autoFilter ref="A1:' . $lastColumn . $lastRow . '"/></worksheet>',
        ];

        $localRecords = '';
        $centralRecords = '';
        $localOffset = 0;
        [$dosTime, $dosDate] = self::dosTimestamp();
        foreach ($files as $name => $contents) {
            $compressed = gzdeflate($contents, 6);
            if ($compressed === false) throw new RuntimeException('Could not compress the Excel workbook.');
            $nameLength = strlen($name);
            $contentLength = strlen($contents);
            $compressedLength = strlen($compressed);
            $crc = crc32($contents);
            $localHeader = pack('VvvvvvVVVvv', 0x04034b50, 20, 0, 8, $dosTime, $dosDate, $crc, $compressedLength, $contentLength, $nameLength, 0);
            $localRecords .= $localHeader . $name . $compressed;
            $centralRecords .= pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0, 8, $dosTime, $dosDate, $crc, $compressedLength, $contentLength, $nameLength, 0, 0, 0, 0, 0, $localOffset) . $name;
            $localOffset += strlen($localHeader) + $nameLength + $compressedLength;
        }

        $centralOffset = strlen($localRecords);
        $entryCount = count($files);
        $endRecord = pack('VvvvvVVv', 0x06054b50, 0, 0, $entryCount, $entryCount, strlen($centralRecords), $centralOffset, 0);
        return $localRecords . $centralRecords . $endRecord;
    }

    private static function columnName(int $number): string
    {
        $name = '';
        while ($number > 0) {
            $remainder = ($number - 1) % 26;
            $name = chr(65 + $remainder) . $name;
            $number = intdiv($number - 1, 26);
        }
        return $name ?: 'A';
    }

    private static function xmlText(string $value): string
    {
        $clean = preg_replace('/[^\x09\x0A\x0D\x20-\x{D7FF}\x{E000}-\x{FFFD}]/u', '', $value);
        if ($clean === null) {
            $clean = function_exists('iconv') ? (iconv('UTF-8', 'UTF-8//IGNORE', $value) ?: '') : '';
        }
        return htmlspecialchars($clean, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private static function dosTimestamp(): array
    {
        $time = getdate();
        $dosTime = (($time['hours'] & 0x1f) << 11) | (($time['minutes'] & 0x3f) << 5) | ((int) ($time['seconds'] / 2) & 0x1f);
        $dosDate = ((max(1980, $time['year']) - 1980) << 9) | ($time['mon'] << 5) | $time['mday'];
        return [$dosTime, $dosDate];
    }
}
