<?php

/** Minimal styled OOXML report workbook writer; requires only PHP's zlib extension. */
final class XlsxReport
{
    public function build(array $rows, string $title = 'Business report'): string
    {
        $recordCount = count($rows);
        if (!$rows) $rows = [['Report status' => 'No records found']];
        $headers = array_keys($rows[0]);
        if (!$headers) $headers = ['Report status'];
        $lastColumn = self::columnName(count($headers));
        $lastRow = 5 + count($rows);
        $generatedAt = date('Y-m-d H:i T');
        $sheetRows = '';

        $sheetRows .= '<row r="1" ht="30" customHeight="1">' . $this->inlineCell('A1', $title, 1) . '</row>';
        $sheetRows .= '<row r="2" ht="20" customHeight="1">' . $this->inlineCell('A2', 'Generated at', 2) . $this->inlineCell('B2', $generatedAt) . '</row>';
        $sheetRows .= '<row r="3" ht="20" customHeight="1">' . $this->inlineCell('A3', 'Record count', 2) . $this->numberCell('B3', $recordCount) . '</row>';
        $sheetRows .= '<row r="4" ht="10" customHeight="1"></row>';

        $sheetRows .= '<row r="5" ht="25" customHeight="1">';
        foreach ($headers as $index => $header) {
            $label = ucwords(str_replace('_', ' ', (string) $header));
            $sheetRows .= $this->inlineCell(self::columnName($index + 1) . '5', $label, 3);
        }
        $sheetRows .= '</row>';

        foreach ($rows as $rowIndex => $row) {
            $excelRow = $rowIndex + 6;
            $banded = ($rowIndex % 2) === 1;
            $sheetRows .= '<row r="' . $excelRow . '" ht="20" customHeight="1">';
            foreach ($headers as $columnIndex => $header) {
                $reference = self::columnName($columnIndex + 1) . $excelRow;
                $value = $row[$header] ?? '';
                if (is_int($value) || is_float($value)) {
                    $isAmount = (bool) preg_match('/amount|price|value|balance|debit|credit/i', (string) $header);
                    $style = $isAmount ? ($banded ? 7 : 6) : ($banded ? 5 : 4);
                    $sheetRows .= $this->numberCell($reference, $value, $style);
                } else {
                    $sheetRows .= $this->inlineCell($reference, $value === null ? '' : (string) $value, $banded ? 5 : 4);
                }
            }
            $sheetRows .= '</row>';
        }

        $columnDefinitions = '';
        foreach ($headers as $index => $header) {
            $columnNumber = $index + 1;
            $width = max(14, min(42, strlen((string) $header) * 1.2 + 4));
            foreach (array_slice($rows, 0, 30) as $row) {
                $width = max($width, min(42, strlen((string) ($row[$header] ?? '')) * 0.9 + 3));
            }
            $columnDefinitions .= '<col min="' . $columnNumber . '" max="' . $columnNumber . '" width="' . number_format($width, 1, '.', '') . '" customWidth="1"/>';
        }

        $files = [
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Report" sheetId="1" r:id="rId1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>',
            'xl/styles.xml' => self::stylesXml(),
            'xl/worksheets/sheet1.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><dimension ref="A1:' . $lastColumn . $lastRow . '"/><sheetViews><sheetView showGridLines="0" workbookViewId="0"><pane ySplit="5" topLeftCell="A6" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><sheetFormatPr defaultRowHeight="20"/><cols>' . $columnDefinitions . '</cols><sheetData>' . $sheetRows . '</sheetData><autoFilter ref="A5:' . $lastColumn . $lastRow . '"/><mergeCells count="1"><mergeCell ref="A1:' . $lastColumn . '1"/></mergeCells></worksheet>',
        ];

        return self::zip($files);
    }

    private function inlineCell(string $reference, string $value, int $style = 0): string
    {
        $styleAttribute = $style ? ' s="' . $style . '"' : '';
        return '<c r="' . $reference . '"' . $styleAttribute . ' t="inlineStr"><is><t xml:space="preserve">' . self::xmlText($value) . '</t></is></c>';
    }

    private function numberCell(string $reference, int|float $value, int $style = 0): string
    {
        $styleAttribute = $style ? ' s="' . $style . '"' : '';
        $number = is_finite((float) $value) ? (string) $value : '0';
        return '<c r="' . $reference . '"' . $styleAttribute . ' t="n"><v>' . $number . '</v></c>';
    }

    private static function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><numFmts count="1"><numFmt numFmtId="164" formatCode="#,##0.00;[Red]-#,##0.00;0.00"/></numFmts><fonts count="3"><font><sz val="11"/><color theme="1"/><name val="Calibri"/><family val="2"/></font><font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Calibri"/><family val="2"/></font><font><b/><sz val="16"/><color rgb="FF193553"/><name val="Calibri"/><family val="2"/></font></fonts><fills count="4"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF243E60"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFF1F5F9"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color rgb="FFE2E8F0"/></left><right style="thin"><color rgb="FFE2E8F0"/></right><top style="thin"><color rgb="FFE2E8F0"/></top><bottom style="thin"><color rgb="FFE2E8F0"/></bottom><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="8"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment vertical="center"/></xf><xf numFmtId="0" fontId="0" fillId="3" borderId="0" xfId="0" applyFill="1"/><xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf><xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1"/><xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"/><xf numFmtId="164" fontId="0" fillId="3" borderId="1" xfId="0" applyNumberFormat="1" applyFill="1" applyBorder="1"/></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
    }

    private static function zip(array $files): string
    {
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
        if ($clean === null) $clean = function_exists('iconv') ? (iconv('UTF-8', 'UTF-8//IGNORE', $value) ?: '') : '';
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
