<?php

function readInventorySpreadsheet(array $file): array
{
    $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException($error === UPLOAD_ERR_NO_FILE ? 'Choose an Excel (.xlsx) or CSV file to upload.' : 'The inventory file could not be uploaded.');
    }
    if ((int)($file['size'] ?? 0) > 5 * 1024 * 1024) throw new InvalidArgumentException('Inventory uploads must be 5 MB or smaller.');
    $extension = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
    if ($extension === 'csv') return readInventoryCsv((string)$file['tmp_name']);
    if ($extension !== 'xlsx') throw new InvalidArgumentException('Use an .xlsx Excel workbook or .csv file.');
    return readInventoryXlsx((string)$file['tmp_name']);
}

function readInventoryCsv(string $path): array
{
    $handle = fopen($path, 'rb');
    if ($handle === false) throw new InvalidArgumentException('The CSV file could not be read.');
    try {
        $headers = fgetcsv($handle);
        if (!is_array($headers)) throw new InvalidArgumentException('The CSV file must contain a header row.');
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string)$headers[0]);
        $rows = [];
        while (($cells = fgetcsv($handle)) !== false) {
            $row = spreadsheetRow($headers, $cells);
            if ($row !== null) $rows[] = $row;
            if (count($rows) > 500) throw new InvalidArgumentException('A single upload can contain at most 500 inventory rows.');
        }
        return $rows;
    } finally {
        fclose($handle);
    }
}

function readInventoryXlsx(string $path): array
{
    $zip = new ZipArchive();
    if ($zip->open($path) !== true) throw new InvalidArgumentException('The Excel workbook could not be opened.');
    try {
        $workbook = spreadsheetXml($zip, 'xl/workbook.xml');
        $relationships = spreadsheetXml($zip, 'xl/_rels/workbook.xml.rels');
        $relationshipId = null;
        $namespaces = $workbook->getNamespaces(true);
        $relationshipNamespace = $namespaces['r'] ?? 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
        foreach ($workbook->sheets->sheet ?? [] as $sheet) {
            $attributes = $sheet->attributes($relationshipNamespace);
            $relationshipId = (string)($attributes['id'] ?? '');
            if ($relationshipId !== '') break;
        }
        if ($relationshipId === null || $relationshipId === '') throw new InvalidArgumentException('The Excel workbook has no worksheets.');

        $target = null;
        foreach ($relationships->Relationship ?? [] as $relationship) {
            if ((string)$relationship['Id'] === $relationshipId) {
                $target = (string)$relationship['Target'];
                break;
            }
        }
        if ($target === null || $target === '') throw new InvalidArgumentException('The first worksheet could not be found in the Excel workbook.');
        $worksheetPath = ltrim(str_starts_with($target, '/') ? $target : 'xl/' . $target, '/');
        if (str_contains($worksheetPath, '..')) throw new InvalidArgumentException('The Excel worksheet path is invalid.');
        $worksheet = spreadsheetXml($zip, $worksheetPath);
        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $stringDocument = simplexml_load_string($sharedXml, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA | LIBXML_NOBLANKS);
            if ($stringDocument === false) throw new InvalidArgumentException('The Excel shared strings are malformed.');
            foreach ($stringDocument->si ?? [] as $item) {
                $parts = [];
                foreach ($item->xpath('.//*[local-name()="t"]') ?: [] as $text) $parts[] = (string)$text;
                $sharedStrings[] = implode('', $parts);
            }
        }
        $sheetRows = $worksheet->sheetData->row ?? [];
        if (count($sheetRows) < 1) throw new InvalidArgumentException('The Excel worksheet is empty.');
        $matrix = [];
        foreach ($sheetRows as $sheetRow) {
            $cells = [];
            foreach ($sheetRow->c ?? [] as $cell) {
                $reference = (string)($cell['r'] ?? '');
                if (!preg_match('/^([A-Z]+)/i', $reference, $match)) continue;
                $column = spreadsheetColumnIndex(strtoupper($match[1]));
                $value = (string)($cell->v ?? '');
                if ((string)($cell['t'] ?? '') === 's') $value = $sharedStrings[(int)$value] ?? '';
                elseif ((string)($cell['t'] ?? '') === 'inlineStr') $value = implode('', array_map('strval', $cell->is->xpath('.//*[local-name()="t"]') ?: []));
                $cells[$column] = $value;
            }
            if ($cells) {
                $lastColumn = max(array_keys($cells));
                for ($column = 0; $column <= $lastColumn; $column++) $cells[$column] = $cells[$column] ?? '';
                ksort($cells);
                $matrix[] = array_values($cells);
            }
            if (count($matrix) > 501) throw new InvalidArgumentException('A single upload can contain at most 500 inventory rows.');
        }
        if (!$matrix) throw new InvalidArgumentException('The Excel worksheet is empty.');
        $headers = array_shift($matrix);
        $rows = [];
        foreach ($matrix as $cells) {
            $row = spreadsheetRow($headers, $cells);
            if ($row !== null) $rows[] = $row;
        }
        if (count($rows) > 500) throw new InvalidArgumentException('A single upload can contain at most 500 inventory rows.');
        return $rows;
    } finally {
        $zip->close();
    }
}

function spreadsheetXml(ZipArchive $zip, string $path): SimpleXMLElement
{
    $content = $zip->getFromName($path);
    if ($content === false) throw new InvalidArgumentException('The Excel workbook is missing a required worksheet file.');
    $xml = simplexml_load_string($content, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA | LIBXML_NOBLANKS);
    if ($xml === false) throw new InvalidArgumentException('The Excel workbook contains malformed XML.');
    return $xml;
}

function spreadsheetColumnIndex(string $letters): int
{
    $index = 0;
    foreach (str_split($letters) as $letter) $index = ($index * 26) + ord($letter) - 64;
    return $index - 1;
}

function spreadsheetRow(array $headers, array $cells): ?array
{
    $aliases = [
        'category' => 'item_category', 'itemcategory' => 'item_category',
        'type' => 'item_type', 'itemtype' => 'item_type',
        'qty' => 'quantity', 'serial' => 'identifier', 'imei' => 'identifier',
        'serialimei' => 'identifier', 'simline' => 'identifier',
        'cost' => 'cost_price', 'costprice' => 'cost_price',
        'selling' => 'selling_price', 'sellingprice' => 'selling_price',
    ];
    $row = [];
    foreach ($headers as $index => $header) {
        $key = strtolower((string)preg_replace('/[^a-z]/i', '', (string)$header));
        $key = $aliases[$key] ?? $key;
        if (in_array($key, ['item_category', 'item_type', 'quantity', 'identifier', 'unit', 'cost_price', 'selling_price'], true)) {
            $row[$key] = trim((string)($cells[$index] ?? ''));
        }
    }
    return count(array_filter($row, static fn($value): bool => $value !== '')) === 0 ? null : $row;
}
